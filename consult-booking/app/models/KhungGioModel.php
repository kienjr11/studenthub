<?php
declare(strict_types=1);

/** Khung giờ tư vấn (slot) do giảng viên mở. Bảng: khung_gio. */
class KhungGioModel extends MoHinh
{
    /** Cuộc hẹn ở các trạng thái này coi như đã chiếm khung giờ (khớp với kiemTraTrungLich trong CuocHen). */
    private const TRANG_THAI_CHIEM = "'cho_duyet','da_duyet','hoan_thanh'";

    /**
     * Danh sách khung giờ của 1 giảng viên trong 1 ngày, kèm cờ da_dat (đã có người đặt).
     * $chiConTrong = true  -> chỉ lấy slot chưa ai đặt và chưa qua giờ (dùng cho phía sinh viên).
     */
    public function layTheoGiangVienVaNgay(int $maGiangVien, string $ngay, bool $chiConTrong = false): array
    {
        $dau  = $ngay . ' 00:00:00';
        $cuoi = (new DateTime($ngay))->modify('+1 day')->format('Y-m-d') . ' 00:00:00';

        $sql = "SELECT kg.ma_khung_gio, kg.ma_giang_vien, kg.bat_dau, kg.ket_thuc, kg.dia_diem,
                       EXISTS (SELECT 1 FROM cuoc_hen ch
                                WHERE ch.ma_khung_gio = kg.ma_khung_gio
                                  AND ch.trang_thai IN (" . self::TRANG_THAI_CHIEM . ")) AS da_dat
                  FROM khung_gio kg
                 WHERE kg.ma_giang_vien = :gv
                   AND kg.bat_dau >= :dau AND kg.bat_dau < :cuoi";
        $params = [':gv' => $maGiangVien, ':dau' => $dau, ':cuoi' => $cuoi];

        if ($chiConTrong) {
            $sql .= " AND kg.bat_dau > NOW()
                      AND NOT EXISTS (SELECT 1 FROM cuoc_hen ch
                                       WHERE ch.ma_khung_gio = kg.ma_khung_gio
                                         AND ch.trang_thai IN (" . self::TRANG_THAI_CHIEM . "))";
        }
        $sql .= " ORDER BY kg.bat_dau ASC";

        $st = $this->db->prepare($sql);
        $st->execute($params);
        $rows = $st->fetchAll();
        foreach ($rows as &$r) {
            $r['da_dat'] = (bool) $r['da_dat'];
        }
        return $rows;
    }

    public function timTheoId(int $maKhungGio, int $maGiangVien): ?array
    {
        $st = $this->db->prepare('SELECT * FROM khung_gio WHERE ma_khung_gio = :id AND ma_giang_vien = :gv');
        $st->execute([':id' => $maKhungGio, ':gv' => $maGiangVien]);
        return $st->fetch() ?: null;
    }

    /** Slot mới có chồng lên slot khác của chính giảng viên đó không? ($boQua = id đang sửa) */
    public function biTrung(int $maGiangVien, string $batDau, string $ketThuc, int $boQua = 0): bool
    {
        $st = $this->db->prepare(
            'SELECT COUNT(*) FROM khung_gio
              WHERE ma_giang_vien = :gv AND ma_khung_gio <> :bo
                AND bat_dau < :kt AND ket_thuc > :bd'
        );
        $st->execute([':gv' => $maGiangVien, ':bo' => $boQua, ':bd' => $batDau, ':kt' => $ketThuc]);
        return (int) $st->fetchColumn() > 0;
    }

    /** Đã có người đặt (đang chờ duyệt / đã duyệt / hoàn thành)? -> không cho sửa. */
    public function daCoNguoiDat(int $maKhungGio): bool
    {
        $st = $this->db->prepare(
            'SELECT COUNT(*) FROM cuoc_hen WHERE ma_khung_gio = :id AND trang_thai IN (' . self::TRANG_THAI_CHIEM . ')'
        );
        $st->execute([':id' => $maKhungGio]);
        return (int) $st->fetchColumn() > 0;
    }

    /** Có bất kỳ cuộc hẹn nào (kể cả đã hủy/từ chối) -> không xóa được vì khóa ngoại ON DELETE RESTRICT. */
    public function coLichSuCuocHen(int $maKhungGio): bool
    {
        $st = $this->db->prepare('SELECT COUNT(*) FROM cuoc_hen WHERE ma_khung_gio = :id');
        $st->execute([':id' => $maKhungGio]);
        return (int) $st->fetchColumn() > 0;
    }

    public function them(int $maGiangVien, string $batDau, string $ketThuc, ?string $diaDiem): int
    {
        $this->db->prepare(
            'INSERT INTO khung_gio (ma_giang_vien, bat_dau, ket_thuc, dia_diem) VALUES (:gv, :bd, :kt, :dd)'
        )->execute([':gv' => $maGiangVien, ':bd' => $batDau, ':kt' => $ketThuc, ':dd' => $diaDiem]);
        return (int) $this->db->lastInsertId();
    }

    public function capNhat(int $maKhungGio, int $maGiangVien, string $batDau, string $ketThuc, ?string $diaDiem): bool
    {
        $st = $this->db->prepare(
            'UPDATE khung_gio SET bat_dau = :bd, ket_thuc = :kt, dia_diem = :dd
              WHERE ma_khung_gio = :id AND ma_giang_vien = :gv'
        );
        return $st->execute([':bd' => $batDau, ':kt' => $ketThuc, ':dd' => $diaDiem, ':id' => $maKhungGio, ':gv' => $maGiangVien]);
    }

    public function xoa(int $maKhungGio, int $maGiangVien): bool
    {
        $st = $this->db->prepare('DELETE FROM khung_gio WHERE ma_khung_gio = :id AND ma_giang_vien = :gv');
        return $st->execute([':id' => $maKhungGio, ':gv' => $maGiangVien]);
    }
}