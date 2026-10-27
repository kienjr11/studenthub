<?php
declare(strict_types=1);

/** Khung giờ tư vấn (slot) do giảng viên mở. Bảng: khung_gio. Mỗi slot nhận tối đa `suc_chua` sinh viên. */
class KhungGioModel extends MoHinh
{
    /** Cuộc hẹn ở các trạng thái này coi như đang chiếm 1 chỗ trong khung giờ (khớp với cột khung_gio_hieu_luc). */
    private const TRANG_THAI_CHIEM = "'cho_duyet','da_duyet','hoan_thanh'";

    /** Biểu thức SQL đếm số sinh viên đang chiếm chỗ của slot `kg`. */
    private const DEM_DANG_KY = "(SELECT COUNT(*) FROM cuoc_hen ch
                                   WHERE ch.ma_khung_gio = kg.ma_khung_gio
                                     AND ch.trang_thai IN (" . self::TRANG_THAI_CHIEM . "))";

    /**
     * Danh sách khung giờ của 1 giảng viên trong 1 ngày, kèm suc_chua, so_dang_ky, con_lai, da_day.
     * $chiConTrong = true -> chỉ lấy slot còn chỗ và chưa qua giờ (dùng cho phía sinh viên).
     */
    public function layTheoGiangVienVaNgay(int $maGiangVien, string $ngay, bool $chiConTrong = false): array
    {
        $dau  = $ngay . ' 00:00:00';
        $cuoi = (new DateTime($ngay))->modify('+1 day')->format('Y-m-d') . ' 00:00:00';

        $sql = "SELECT kg.ma_khung_gio, kg.ma_giang_vien, kg.bat_dau, kg.ket_thuc, kg.dia_diem, kg.suc_chua,
                       " . self::DEM_DANG_KY . " AS so_dang_ky
                  FROM khung_gio kg
                 WHERE kg.ma_giang_vien = :gv
                   AND kg.bat_dau >= :dau AND kg.bat_dau < :cuoi";
        $params = [':gv' => $maGiangVien, ':dau' => $dau, ':cuoi' => $cuoi];

        if ($chiConTrong) {
            $sql .= " AND kg.bat_dau > :bay_gio AND " . self::DEM_DANG_KY . " < kg.suc_chua";
            $params[':bay_gio'] = date('Y-m-d H:i:s');   // dùng giờ PHP (Asia/Ho_Chi_Minh) thay vì NOW() của MySQL
        }
        $sql .= " ORDER BY kg.bat_dau ASC";

        $st = $this->db->prepare($sql);
        $st->execute($params);
        $rows = $st->fetchAll();
        foreach ($rows as &$r) {
            $r['suc_chua']    = (int) $r['suc_chua'];
            $r['so_dang_ky']  = (int) $r['so_dang_ky'];
            $r['con_lai']     = max(0, $r['suc_chua'] - $r['so_dang_ky']);
            $r['da_day']      = $r['so_dang_ky'] >= $r['suc_chua'];
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

    /** Số sinh viên đang chiếm chỗ (chờ duyệt / đã duyệt / hoàn thành). */
    public function demDangKy(int $maKhungGio): int
    {
        $st = $this->db->prepare(
            'SELECT COUNT(*) FROM cuoc_hen WHERE ma_khung_gio = :id AND trang_thai IN (' . self::TRANG_THAI_CHIEM . ')'
        );
        $st->execute([':id' => $maKhungGio]);
        return (int) $st->fetchColumn();
    }

    /** Danh sách sinh viên đã đăng ký slot (mọi trạng thái, mới nhất cuối) để giảng viên xem. */
    public function layDanhSachDangKy(int $maKhungGio): array
    {
        $st = $this->db->prepare(
            'SELECT ch.ma_cuoc_hen, ch.chu_de, ch.trang_thai, ch.ngay_tao,
                    sv.ho_ten, sv.email
               FROM cuoc_hen ch
               JOIN nguoi_dung sv ON sv.ma_nguoi_dung = ch.ma_sinh_vien
              WHERE ch.ma_khung_gio = :id
              ORDER BY ch.ngay_tao ASC, ch.ma_cuoc_hen ASC'
        );
        $st->execute([':id' => $maKhungGio]);
        return $st->fetchAll();
    }

    /** Có bất kỳ cuộc hẹn nào (kể cả đã hủy/từ chối) -> không xóa được vì khóa ngoại ON DELETE RESTRICT. */
    public function coLichSuCuocHen(int $maKhungGio): bool
    {
        $st = $this->db->prepare('SELECT COUNT(*) FROM cuoc_hen WHERE ma_khung_gio = :id');
        $st->execute([':id' => $maKhungGio]);
        return (int) $st->fetchColumn() > 0;
    }

    public function them(int $maGiangVien, string $batDau, string $ketThuc, ?string $diaDiem, int $sucChua): int
    {
        $this->db->prepare(
            'INSERT INTO khung_gio (ma_giang_vien, bat_dau, ket_thuc, dia_diem, suc_chua)
             VALUES (:gv, :bd, :kt, :dd, :sc)'
        )->execute([':gv' => $maGiangVien, ':bd' => $batDau, ':kt' => $ketThuc, ':dd' => $diaDiem, ':sc' => $sucChua]);
        return (int) $this->db->lastInsertId();
    }

    public function capNhat(int $maKhungGio, int $maGiangVien, string $batDau, string $ketThuc, ?string $diaDiem, int $sucChua): bool
    {
        $st = $this->db->prepare(
            'UPDATE khung_gio SET bat_dau = :bd, ket_thuc = :kt, dia_diem = :dd, suc_chua = :sc
              WHERE ma_khung_gio = :id AND ma_giang_vien = :gv'
        );
        return $st->execute([
            ':bd' => $batDau, ':kt' => $ketThuc, ':dd' => $diaDiem, ':sc' => $sucChua,
            ':id' => $maKhungGio, ':gv' => $maGiangVien,
        ]);
    }

    public function xoa(int $maKhungGio, int $maGiangVien): bool
    {
        $st = $this->db->prepare('DELETE FROM khung_gio WHERE ma_khung_gio = :id AND ma_giang_vien = :gv');
        return $st->execute([':id' => $maKhungGio, ':gv' => $maGiangVien]);
    }
}