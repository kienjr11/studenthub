<?php
declare(strict_types=1);

class PhanHoi extends MoHinh
{
    /** Trạng thái cuộc hẹn được phép đánh giá (phải khớp với điều kiện hiển thị nút trong view danh_gia.php). */
    public const TRANG_THAI_DUOC_DANH_GIA = ['da_duyet', 'hoan_thanh'];

    /**
     * Danh sách cuộc hẹn của một sinh viên, kèm thông tin giảng viên, khung giờ và đánh giá (nếu có).
     * Tên cột diem_danh_gia / nhan_xet_sinh_vien khớp với view khuvuc/sinhvien/danh_gia.php.
     */
    public function layDanhSachChoSinhVien(int $maSinhVien, ?string $trangThai = null): array
    {
        $params = [':ma_sv' => $maSinhVien];
        $where = 'WHERE ch.ma_sinh_vien = :ma_sv';

        if ($trangThai !== null && in_array($trangThai, ['cho_duyet', 'da_duyet', 'tu_choi', 'da_huy', 'hoan_thanh'], true)) {
            $where .= ' AND ch.trang_thai = :trang_thai';
            $params[':trang_thai'] = $trangThai;
        }

        (new KhungGioModel())->damBaoCotChuyenMon();   // đảm bảo có cột khung_gio.ma_chuyen_mon trước khi truy vấn

        $sql = "SELECT ch.ma_cuoc_hen, ch.chu_de, ch.trang_thai, ch.ghi_chu_giang_vien,
                       gv.ho_ten AS ten_giang_vien,
                       kg.bat_dau, kg.ket_thuc, kg.dia_diem, cm.ten_chuyen_mon,
                       ph.diem_danh_gia, ph.nhan_xet AS nhan_xet_sinh_vien
                FROM cuoc_hen ch
                JOIN khung_gio kg ON ch.ma_khung_gio = kg.ma_khung_gio
                LEFT JOIN chuyen_mon cm ON cm.ma_chuyen_mon = kg.ma_chuyen_mon
                JOIN nguoi_dung gv ON kg.ma_giang_vien = gv.ma_nguoi_dung
                LEFT JOIN phan_hoi ph ON ch.ma_cuoc_hen = ph.ma_cuoc_hen
                {$where}
                ORDER BY kg.bat_dau DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Kiểm tra xem cuộc hẹn có thuộc về sinh viên đó không
     */
    public function kiemTraQuyenSoHuu(int $maCuocHen, int $maSinhVien): ?array
    {
        $sql = "SELECT * FROM cuoc_hen WHERE ma_cuoc_hen = :ma_cuoc_hen AND ma_sinh_vien = :ma_sv";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':ma_cuoc_hen' => $maCuocHen, ':ma_sv' => $maSinhVien]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    /**
     * Lấy phản hồi theo mã cuộc hẹn
     */
    public function layTheoCuocHen(int $maCuocHen): ?array
    {
        $sql = "SELECT * FROM phan_hoi WHERE ma_cuoc_hen = :ma_cuoc_hen LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':ma_cuoc_hen' => $maCuocHen]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    /**
     * Thêm mới hoặc cập nhật đánh giá của sinh viên.
     * Bảng phan_hoi có UNIQUE (ma_cuoc_hen) nên dùng upsert một câu lệnh, tránh race condition giữa SELECT và INSERT.
     */
    public function luuDanhGia(int $maCuocHen, int $diemDanhGia, string $nhanXet): bool
    {
        $sql = "INSERT INTO phan_hoi (ma_cuoc_hen, diem_danh_gia, nhan_xet)
                VALUES (:ma_cuoc_hen, :diem, :nhan_xet)
                ON DUPLICATE KEY UPDATE diem_danh_gia = VALUES(diem_danh_gia), nhan_xet = VALUES(nhan_xet)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':ma_cuoc_hen' => $maCuocHen,
            ':diem'        => $diemDanhGia,
            ':nhan_xet'    => $nhanXet !== '' ? $nhanXet : null,
        ]);
    }
}