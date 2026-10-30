<?php
declare(strict_types=1);

/** Khung giờ tư vấn (slot) do giảng viên mở. Bảng: khung_gio. Mỗi slot nhận tối đa `suc_chua` sinh viên. */
class KhungGioModel extends MoHinh
{
    /** Chỉ cuộc hẹn ĐÃ ĐƯỢC GIẢNG VIÊN DUYỆT (hoặc đã hoàn thành) mới chiếm 1 chỗ; yêu cầu đang chờ duyệt chưa làm đổi số chỗ. */
    private const TRANG_THAI_CHIEM = "'da_duyet','hoan_thanh'";

    /** Biểu thức SQL đếm số sinh viên đang chiếm chỗ của slot `kg`. */
    private const DEM_DANG_KY = "(SELECT COUNT(*) FROM cuoc_hen ch
                                   WHERE ch.ma_khung_gio = kg.ma_khung_gio
                                     AND ch.trang_thai IN (" . self::TRANG_THAI_CHIEM . "))";

    private static bool $daCoCotChuyenMon = false;

    /**
     * Đảm bảo bảng khung_gio có cột ma_chuyen_mon (môn / chuyên môn của khung giờ, cho phép NULL).
     * Tự thêm cột + khóa ngoại nếu chưa có nên không cần chạy SQL thủ công.
     */
    public function damBaoCotChuyenMon(): void
    {
        if (self::$daCoCotChuyenMon) {
            return;
        }
        if (!$this->db->query("SHOW COLUMNS FROM khung_gio LIKE 'ma_chuyen_mon'")->fetch()) {
            $this->db->exec('ALTER TABLE khung_gio ADD COLUMN ma_chuyen_mon INT UNSIGNED NULL DEFAULT NULL AFTER dia_diem');
            try {
                $this->db->exec(
                    'ALTER TABLE khung_gio ADD CONSTRAINT fk_khung_gio_chuyen_mon
                       FOREIGN KEY (ma_chuyen_mon) REFERENCES chuyen_mon (ma_chuyen_mon) ON DELETE RESTRICT'
                );
            } catch (Throwable $e) {
                error_log('Không thêm được khóa ngoại khung_gio.ma_chuyen_mon: ' . $e->getMessage());
            }
        }
        self::$daCoCotChuyenMon = true;
    }

    /** Tên môn mà giảng viên đã mở cho khung giờ; null nếu khung giờ không gắn môn. */
    public function layTenChuyenMonCuaKhungGio(int $maKhungGio): ?string
    {
        $this->damBaoCotChuyenMon();
        $st = $this->db->prepare(
            'SELECT cm.ten_chuyen_mon
               FROM khung_gio kg
               JOIN chuyen_mon cm ON cm.ma_chuyen_mon = kg.ma_chuyen_mon
              WHERE kg.ma_khung_gio = :id'
        );
        $st->execute([':id' => $maKhungGio]);
        $ten = $st->fetchColumn();
        return $ten === false ? null : (string) $ten;
    }

    /** Các môn (chuyên môn) mà giảng viên dạy, lấy từ bảng giang_vien_chuyen_mon. */
    public function layChuyenMonCuaGiangVien(int $maGiangVien): array
    {
        $st = $this->db->prepare(
            'SELECT cm.ma_chuyen_mon, cm.ten_chuyen_mon
               FROM giang_vien_chuyen_mon gv
               JOIN chuyen_mon cm ON cm.ma_chuyen_mon = gv.ma_chuyen_mon
              WHERE gv.ma_giang_vien = :gv
              ORDER BY cm.ten_chuyen_mon'
        );
        $st->execute([':gv' => $maGiangVien]);
        return $st->fetchAll();
    }

    /**
     * Danh sách khung giờ của 1 giảng viên trong 1 ngày, kèm suc_chua, so_dang_ky, con_lai, da_day.
     * $chiConTrong = true -> chỉ lấy slot còn chỗ và chưa qua giờ (dùng cho phía sinh viên).
     */
    public function layTheoGiangVienVaNgay(int $maGiangVien, string $ngay, bool $chiConTrong = false): array
    {
        $dau  = $ngay . ' 00:00:00';
        $cuoi = (new DateTime($ngay))->modify('+1 day')->format('Y-m-d') . ' 00:00:00';

        $this->damBaoCotChuyenMon();
        $sql = "SELECT kg.ma_khung_gio, kg.ma_giang_vien, kg.bat_dau, kg.ket_thuc, kg.dia_diem, kg.suc_chua,
                       kg.ma_chuyen_mon, cm.ten_chuyen_mon,
                       " . self::DEM_DANG_KY . " AS so_dang_ky
                  FROM khung_gio kg
                  LEFT JOIN chuyen_mon cm ON cm.ma_chuyen_mon = kg.ma_chuyen_mon
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

    /**
     * TẤT CẢ khung giờ do giảng viên tạo (không lọc ngày), kèm suc_chua, so_dang_ky, con_lai, da_day.
     * Khung giờ sắp tới xếp trước (gần nhất lên đầu), khung giờ đã qua xếp sau (mới nhất lên đầu).
     */
    public function layTatCaTheoGiangVien(int $maGiangVien, int $toiDa = 500): array
    {
        $this->damBaoCotChuyenMon();
        $locTuNhap = $this->damBaoCotTuNhap() ? 'AND kg.la_tu_nhap = 0' : '';   // bỏ khung giờ sinh viên tự nhập khi đặt lịch

        $sql = "SELECT kg.ma_khung_gio, kg.ma_giang_vien, kg.bat_dau, kg.ket_thuc, kg.dia_diem, kg.suc_chua,
                       kg.ma_chuyen_mon, cm.ten_chuyen_mon,
                       " . self::DEM_DANG_KY . " AS so_dang_ky
                  FROM khung_gio kg
                  LEFT JOIN chuyen_mon cm ON cm.ma_chuyen_mon = kg.ma_chuyen_mon
                 WHERE kg.ma_giang_vien = :gv
                   " . $locTuNhap . "
                 ORDER BY (kg.bat_dau <= :now1) ASC,
                          CASE WHEN kg.bat_dau > :now2 THEN kg.bat_dau END ASC,
                          kg.bat_dau DESC
                 LIMIT " . (int) $toiDa;
        $bayGio = date('Y-m-d H:i:s');   // giờ PHP (Asia/Ho_Chi_Minh)
        $st = $this->db->prepare($sql);
        $st->execute([':gv' => $maGiangVien, ':now1' => $bayGio, ':now2' => $bayGio]);
        $rows = $st->fetchAll();
        foreach ($rows as &$r) {
            $r['suc_chua']   = (int) $r['suc_chua'];
            $r['so_dang_ky'] = (int) $r['so_dang_ky'];
            $r['con_lai']    = max(0, $r['suc_chua'] - $r['so_dang_ky']);
            $r['da_day']     = $r['so_dang_ky'] >= $r['suc_chua'];
        }
        return $rows;
    }

    /** null = chưa kiểm tra; true/false = bảng khung_gio có/không có cột la_tu_nhap. */
    private static ?bool $coCotTuNhap = null;

    /**
     * Đảm bảo bảng khung_gio có cột la_tu_nhap (1 = khung giờ do sinh viên tự nhập khi đặt lịch).
     * Tự thêm cột nếu chưa có nên không cần chạy SQL thủ công. Trả về false nếu không thêm được (khi đó bỏ qua việc lọc).
     */
    public function damBaoCotTuNhap(): bool
    {
        if (self::$coCotTuNhap !== null) {
            return self::$coCotTuNhap;
        }
        try {
            if (!$this->db->query("SHOW COLUMNS FROM khung_gio LIKE 'la_tu_nhap'")->fetch()) {
                $this->db->exec('ALTER TABLE khung_gio ADD COLUMN la_tu_nhap TINYINT(1) NOT NULL DEFAULT 0 AFTER suc_chua');
                // Khung giờ tự nhập có từ trước: được tạo cùng lúc với cuộc hẹn đầu tiên của nó, không có địa điểm, sức chứa 1
                $this->db->exec(
                    'UPDATE khung_gio kg SET kg.la_tu_nhap = 1
                      WHERE kg.dia_diem IS NULL AND kg.suc_chua = 1
                        AND EXISTS (SELECT 1 FROM cuoc_hen ch
                                     WHERE ch.ma_khung_gio = kg.ma_khung_gio
                                       AND ABS(TIMESTAMPDIFF(SECOND, ch.ngay_tao, kg.ngay_tao)) <= 2)'
                );
            }
            self::$coCotTuNhap = true;
        } catch (Throwable $e) {
            error_log('Không thêm được cột khung_gio.la_tu_nhap: ' . $e->getMessage());
            self::$coCotTuNhap = false;
        }
        return self::$coCotTuNhap;
    }

    /**
     * Các khung giờ CÒN CHỖ và chưa qua giờ của 1 giảng viên, từ bây giờ tới $soNgay ngày nữa (dùng cho nút "Lịch rảnh").
     * Trả về tối đa $toiDa slot, sắp xếp theo thời gian, kèm suc_chua, so_dang_ky, con_lai.
     */
    public function laySapToiConTrong(int $maGiangVien, int $soNgay = 60, int $toiDa = 100): array
    {
        $this->damBaoCotChuyenMon();
        $locTuNhap = $this->damBaoCotTuNhap() ? 'AND kg.la_tu_nhap = 0' : '';   // chỉ khung giờ do giảng viên tạo
        $bayGio = new DateTime();
        $han    = (clone $bayGio)->modify('+' . $soNgay . ' days');

        $sql = "SELECT kg.ma_khung_gio, kg.bat_dau, kg.ket_thuc, kg.dia_diem, kg.suc_chua,
                       kg.ma_chuyen_mon, cm.ten_chuyen_mon,
                       " . self::DEM_DANG_KY . " AS so_dang_ky
                  FROM khung_gio kg
                  LEFT JOIN chuyen_mon cm ON cm.ma_chuyen_mon = kg.ma_chuyen_mon
                 WHERE kg.ma_giang_vien = :gv
                   " . $locTuNhap . "
                   AND kg.bat_dau > :bay_gio AND kg.bat_dau < :han
                   AND " . self::DEM_DANG_KY . " < kg.suc_chua
                 ORDER BY kg.bat_dau ASC
                 LIMIT " . (int) $toiDa;
        $st = $this->db->prepare($sql);
        $st->execute([
            ':gv'      => $maGiangVien,
            ':bay_gio' => $bayGio->format('Y-m-d H:i:s'),   // giờ PHP (Asia/Ho_Chi_Minh), giống layTheoGiangVienVaNgay
            ':han'     => $han->format('Y-m-d H:i:s'),
        ]);
        $rows = $st->fetchAll();
        foreach ($rows as &$r) {
            $r['suc_chua']   = (int) $r['suc_chua'];
            $r['so_dang_ky'] = (int) $r['so_dang_ky'];
            $r['con_lai']    = max(0, $r['suc_chua'] - $r['so_dang_ky']);
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
                    sv.ho_ten, sv.email,
                    ph.diem_danh_gia, ph.nhan_xet
               FROM cuoc_hen ch
               JOIN nguoi_dung sv ON sv.ma_nguoi_dung = ch.ma_sinh_vien
               LEFT JOIN phan_hoi ph ON ph.ma_cuoc_hen = ch.ma_cuoc_hen
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

    public function them(int $maGiangVien, string $batDau, string $ketThuc, ?string $diaDiem, int $sucChua, ?int $maChuyenMon = null): int
    {
        $this->damBaoCotChuyenMon();
        $this->db->prepare(
            'INSERT INTO khung_gio (ma_giang_vien, bat_dau, ket_thuc, dia_diem, suc_chua, ma_chuyen_mon)
             VALUES (:gv, :bd, :kt, :dd, :sc, :cm)'
        )->execute([':gv' => $maGiangVien, ':bd' => $batDau, ':kt' => $ketThuc, ':dd' => $diaDiem, ':sc' => $sucChua, ':cm' => $maChuyenMon]);
        return (int) $this->db->lastInsertId();
    }

    public function capNhat(int $maKhungGio, int $maGiangVien, string $batDau, string $ketThuc, ?string $diaDiem, int $sucChua, ?int $maChuyenMon = null): bool
    {
        $this->damBaoCotChuyenMon();
        $st = $this->db->prepare(
            'UPDATE khung_gio SET bat_dau = :bd, ket_thuc = :kt, dia_diem = :dd, suc_chua = :sc, ma_chuyen_mon = :cm
              WHERE ma_khung_gio = :id AND ma_giang_vien = :gv'
        );
        return $st->execute([
            ':bd' => $batDau, ':kt' => $ketThuc, ':dd' => $diaDiem, ':sc' => $sucChua, ':cm' => $maChuyenMon,
            ':id' => $maKhungGio, ':gv' => $maGiangVien,
        ]);
    }

    public function xoa(int $maKhungGio, int $maGiangVien): bool
    {
        $st = $this->db->prepare('DELETE FROM khung_gio WHERE ma_khung_gio = :id AND ma_giang_vien = :gv');
        return $st->execute([':id' => $maKhungGio, ':gv' => $maGiangVien]);
    }
}