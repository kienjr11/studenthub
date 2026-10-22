<?php
declare(strict_types=1);

class CuocHen extends MoHinh
{
    public function layDanhSachChoGiangVien(int $maGiangVien, ?string $trangThai = null, int $trang = 1, int $gioiHan = 10): array
    {
        $offset = ($trang - 1) * $gioiHan;
        $params = [':ma_gv' => $maGiangVien];
        $whereSql = "WHERE kg.ma_giang_vien = :ma_gv";

        if ($trangThai && in_array($trangThai, ['cho_duyet', 'da_duyet', 'tu_choi', 'da_huy', 'hoan_thanh'])) {
            $whereSql .= " AND ch.trang_thai = :trang_thai";
            $params[':trang_thai'] = $trangThai;
        }

        $sql = "SELECT ch.ma_cuoc_hen, ch.ma_khung_gio, ch.chu_de, ch.trang_thai, ch.ghi_chu_giang_vien, ch.ngay_tao,
                       sv.ho_ten AS ten_sinh_vien, sv.email AS email_sinh_vien,
                       kg.bat_dau, kg.ket_thuc, kg.dia_diem,
                       ph.diem_danh_gia, ph.nhan_xet AS nhan_xet_sinh_vien
                FROM cuoc_hen ch
                JOIN nguoi_dung sv ON ch.ma_sinh_vien = sv.ma_nguoi_dung
                JOIN khung_gio kg ON ch.ma_khung_gio = kg.ma_khung_gio
                LEFT JOIN phan_hoi ph ON ch.ma_cuoc_hen = ph.ma_cuoc_hen
                {$whereSql}
                ORDER BY kg.bat_dau DESC
                LIMIT {$gioiHan} OFFSET {$offset}";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $danhSach = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sqlCount = "SELECT COUNT(*) FROM cuoc_hen ch 
                     JOIN khung_gio kg ON ch.ma_khung_gio = kg.ma_khung_gio 
                     {$whereSql}";
        $stmtCount = $this->db->prepare($sqlCount);
        foreach ($params as $k => $v) {
            $stmtCount->bindValue($k, $v);
        }
        $stmtCount->execute();
        $tongBanGhi = (int) $stmtCount->fetchColumn();

        return [
            'danh_sach'      => $danhSach,
            'tong_so'        => $tongBanGhi,
            'tong_trang'     => (int) ceil($tongBanGhi / $gioiHan),
            'trang_hien_tai' => $trang
        ];
    }

    public function timTheoIdVaGiangVien(int $maCuocHen, int $maGiangVien): ?array
    {
        $sql = "SELECT ch.*, kg.ma_giang_vien, kg.bat_dau, kg.ket_thuc, kg.dia_diem,
                       sv.ho_ten AS ten_sinh_vien, sv.email AS email_sinh_vien
                FROM cuoc_hen ch
                JOIN khung_gio kg ON ch.ma_khung_gio = kg.ma_khung_gio
                JOIN nguoi_dung sv ON ch.ma_sinh_vien = sv.ma_nguoi_dung
                WHERE ch.ma_cuoc_hen = :ma_cuoc_hen AND kg.ma_giang_vien = :ma_gv";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':ma_cuoc_hen' => $maCuocHen, ':ma_gv' => $maGiangVien]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        return $res ?: null;
    }

    public function capNhatTrangThai(int $maCuocHen, string $trangThaiMoi): bool
    {
        $sql = "UPDATE cuoc_hen SET trang_thai = :trang_thai WHERE ma_cuoc_hen = :ma_cuoc_hen";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':trang_thai'  => $trangThaiMoi,
            ':ma_cuoc_hen' => $maCuocHen
        ]);
    }

    public function capNhatGhiChuGiangVien(int $maCuocHen, string $ghiChu): bool
    {
        $sql = "UPDATE cuoc_hen SET ghi_chu_giang_vien = :ghi_chu WHERE ma_cuoc_hen = :ma_cuoc_hen";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':ghi_chu'     => $ghiChu,
            ':ma_cuoc_hen' => $maCuocHen
        ]);
    }
}