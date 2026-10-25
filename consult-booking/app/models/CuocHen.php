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

    /**
     * Giảng viên đã có khung giờ (còn hiệu lực) chồng lên khoảng [bắt đầu, kết thúc) chưa?
     * Khung giờ chỉ tính là "bận" khi có cuộc hẹn đang cho_duyet / da_duyet / hoan_thanh.
     */
    public function kiemTraTrungLich(int $maGiangVien, string $batDau, string $ketThuc): bool
    {
        $st = $this->db->prepare(
            "SELECT COUNT(*)
               FROM khung_gio kg
               JOIN cuoc_hen ch ON ch.ma_khung_gio = kg.ma_khung_gio
              WHERE kg.ma_giang_vien = :gv
                AND ch.trang_thai IN ('cho_duyet', 'da_duyet', 'hoan_thanh')
                AND kg.bat_dau < :ket_thuc AND kg.ket_thuc > :bat_dau"
        );
        $st->execute([':gv' => $maGiangVien, ':bat_dau' => $batDau, ':ket_thuc' => $ketThuc]);
        return (int) $st->fetchColumn() > 0;
    }

    /**
     * Sinh viên đặt lịch: tạo khung giờ theo giờ sinh viên chọn rồi tạo cuộc hẹn (cho_duyet) trong một transaction.
     * Trả về mã cuộc hẹn mới.
     */
    public function taoCuocHen(int $maSinhVien, int $maGiangVien, string $batDau, string $ketThuc, string $chuDe): int
    {
        $this->db->beginTransaction();
        try {
            $this->db->prepare('INSERT INTO khung_gio (ma_giang_vien, bat_dau, ket_thuc) VALUES (:gv, :bd, :kt)')
                ->execute([':gv' => $maGiangVien, ':bd' => $batDau, ':kt' => $ketThuc]);
            $maKhungGio = (int) $this->db->lastInsertId();

            $this->db->prepare("INSERT INTO cuoc_hen (ma_khung_gio, ma_sinh_vien, chu_de, trang_thai) VALUES (:kg, :sv, :cd, 'cho_duyet')")
                ->execute([':kg' => $maKhungGio, ':sv' => $maSinhVien, ':cd' => $chuDe]);
            $maCuocHen = (int) $this->db->lastInsertId();

            $this->db->commit();
            return $maCuocHen;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Thống kê tổng quan số lượng cuộc hẹn cho Admin (toàn hệ thống)
     */
    public function thongKeTongQuanAdmin(): array
    {
        $sql = "SELECT 
                    COUNT(*) as tong_so,
                    SUM(CASE WHEN ch.trang_thai = 'cho_duyet' THEN 1 ELSE 0 END) as cho_duyet,
                    SUM(CASE WHEN ch.trang_thai = 'da_duyet' THEN 1 ELSE 0 END) as da_duyet,
                    SUM(CASE WHEN ch.trang_thai = 'tu_choi' THEN 1 ELSE 0 END) as tu_choi,
                    SUM(CASE WHEN ch.trang_thai = 'da_huy' THEN 1 ELSE 0 END) as da_huy,
                    SUM(CASE WHEN ch.trang_thai = 'hoan_thanh' THEN 1 ELSE 0 END) as hoan_thanh
                FROM cuoc_hen ch";
        $stmt = $this->db->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'tong_so' => 0, 'cho_duyet' => 0, 'da_duyet' => 0, 
            'tu_choi' => 0, 'da_huy' => 0, 'hoan_thanh' => 0
        ];
    }

    /**
     * Thống kê số lượng cuộc hẹn riêng cho một Giảng viên cụ thể
     */
    public function thongKeChoGiangVien(int $maGiangVien): array
    {
        $sql = "SELECT 
                    COUNT(*) as tong_so,
                    SUM(CASE WHEN ch.trang_thai = 'cho_duyet' THEN 1 ELSE 0 END) as cho_duyet,
                    SUM(CASE WHEN ch.trang_thai = 'da_duyet' THEN 1 ELSE 0 END) as da_duyet,
                    SUM(CASE WHEN ch.trang_thai = 'tu_choi' THEN 1 ELSE 0 END) as tu_choi,
                    SUM(CASE WHEN ch.trang_thai = 'da_huy' THEN 1 ELSE 0 END) as da_huy,
                    SUM(CASE WHEN ch.trang_thai = 'hoan_thanh' THEN 1 ELSE 0 END) as hoan_thanh
                FROM cuoc_hen ch
                JOIN khung_gio kg ON ch.ma_khung_gio = kg.ma_khung_gio
                WHERE kg.ma_giang_vien = :ma_gv";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':ma_gv' => $maGiangVien]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'tong_so' => 0, 'cho_duyet' => 0, 'da_duyet' => 0, 
            'tu_choi' => 0, 'da_huy' => 0, 'hoan_thanh' => 0
        ];
    }

    /**
     * Thống kê chi tiết phục vụ cho giao diện Dashboard chi tiết của Giảng viên ($stats)
     */
    public function thongKeChiTietChoGiangVien(int $maGiangVien): array
    {
        $sql = "SELECT 
                    SUM(CASE WHEN ch.trang_thai = 'cho_duyet' THEN 1 ELSE 0 END) as cho_duyet,
                    SUM(CASE WHEN ch.trang_thai = 'da_duyet' AND DATE(kg.bat_dau) = CURDATE() THEN 1 ELSE 0 END) as hom_nay,
                    SUM(CASE WHEN ch.trang_thai = 'hoan_thanh' THEN 1 ELSE 0 END) as hoan_thanh
                FROM cuoc_hen ch
                JOIN khung_gio kg ON ch.ma_khung_gio = kg.ma_khung_gio
                WHERE kg.ma_giang_vien = :ma_gv";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':ma_gv' => $maGiangVien]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $sqlSlot = "SELECT COUNT(*) FROM khung_gio WHERE ma_giang_vien = :ma_gv AND ket_thuc >= NOW()";
        $stmtSlot = $this->db->prepare($sqlSlot);
        $stmtSlot->execute([':ma_gv' => $maGiangVien]);
        $slotSapToi = (int) $stmtSlot->fetchColumn();

        $sqlDanhGia = "SELECT COUNT(ph.ma_phan_hoi) as so_danh_gia, AVG(ph.diem_danh_gia) as diem_tb
                       FROM phan_hoi ph
                       JOIN cuoc_hen ch ON ph.ma_cuoc_hen = ch.ma_cuoc_hen
                       JOIN khung_gio kg ON ch.ma_khung_gio = kg.ma_khung_gio
                       WHERE kg.ma_giang_vien = :ma_gv";
        $stmtDG = $this->db->prepare($sqlDanhGia);
        $stmtDG->execute([':ma_gv' => $maGiangVien]);
        $dg = $stmtDG->fetch(PDO::FETCH_ASSOC) ?: ['so_danh_gia' => 0, 'diem_tb' => 0];

        return [
            'cho_duyet'    => (int) ($res['cho_duyet'] ?? 0),
            'hom_nay'      => (int) ($res['hom_nay'] ?? 0),
            'slot_sap_toi' => $slotSapToi,
            'hoan_thanh'   => (int) ($res['hoan_thanh'] ?? 0),
            'so_danh_gia'  => (int) ($dg['so_danh_gia'] ?? 0),
            'diem_tb'      => (float) ($dg['diem_tb'] ?? 0),
        ];
    }

    /**
     * Lấy danh sách các cuộc hẹn sắp tới của giảng viên ($upcoming)
     */
    public function layCuocHenSapToiChoGiangVien(int $maGiangVien, int $gioiHan = 5): array
    {
        $sql = "SELECT ch.ma_cuoc_hen, ch.chu_de, ch.trang_thai,
                       sv.ho_ten AS ten_sinh_vien,
                       kg.bat_dau, kg.ket_thuc
                FROM cuoc_hen ch
                JOIN nguoi_dung sv ON ch.ma_sinh_vien = sv.ma_nguoi_dung
                JOIN khung_gio kg ON ch.ma_khung_gio = kg.ma_khung_gio
                WHERE kg.ma_giang_vien = :ma_gv 
                  AND ch.trang_thai IN ('cho_duyet', 'da_duyet')
                  AND kg.bat_dau >= NOW()
                ORDER BY kg.bat_dau ASC
                LIMIT {$gioiHan}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':ma_gv' => $maGiangVien]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}