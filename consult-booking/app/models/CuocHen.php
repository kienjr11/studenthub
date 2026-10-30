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

        (new KhungGioModel())->damBaoCotChuyenMon();   // đảm bảo có cột khung_gio.ma_chuyen_mon trước khi truy vấn

        $sql = "SELECT ch.ma_cuoc_hen, ch.ma_khung_gio, ch.chu_de, ch.trang_thai, ch.ghi_chu_giang_vien, ch.ngay_tao,
                       sv.ho_ten AS ten_sinh_vien, sv.email AS email_sinh_vien,
                       kg.bat_dau, kg.ket_thuc, kg.dia_diem, cm.ten_chuyen_mon,
                       ph.diem_danh_gia, ph.nhan_xet AS nhan_xet_sinh_vien
                FROM cuoc_hen ch
                JOIN nguoi_dung sv ON ch.ma_sinh_vien = sv.ma_nguoi_dung
                JOIN khung_gio kg ON ch.ma_khung_gio = kg.ma_khung_gio
                LEFT JOIN chuyen_mon cm ON cm.ma_chuyen_mon = kg.ma_chuyen_mon
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
     * Khung giờ chỉ tính là "bận" khi có cuộc hẹn đã được duyệt (da_duyet / hoan_thanh).
     */
    public function kiemTraTrungLich(int $maGiangVien, string $batDau, string $ketThuc): bool
    {
        $st = $this->db->prepare(
            "SELECT COUNT(*)
               FROM khung_gio kg
               JOIN cuoc_hen ch ON ch.ma_khung_gio = kg.ma_khung_gio
              WHERE kg.ma_giang_vien = :gv
                AND ch.trang_thai IN ('da_duyet', 'hoan_thanh')
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
        $coCot = (new KhungGioModel())->damBaoCotTuNhap();   // phải chạy trước beginTransaction (ALTER TABLE tự commit)
        $this->db->beginTransaction();
        try {
            $this->db->prepare('INSERT INTO khung_gio (ma_giang_vien, bat_dau, ket_thuc' . ($coCot ? ', la_tu_nhap' : '') . ') VALUES (:gv, :bd, :kt' . ($coCot ? ', 1' : '') . ')')
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
     * Sinh viên đặt vào MỘT KHUNG GIỜ CÓ SẴN của giảng viên (khung giờ do giảng viên mở ở trang "Khung giờ tư vấn").
     * Khóa dòng khung giờ trong transaction để 2 sinh viên không cùng giành chỗ cuối.
     * Ném DomainException (thông báo tiếng Việt, hiển thị được cho người dùng) nếu không đặt được.
     * Trả về mã cuộc hẹn mới.
     */
    public function datVaoKhungGio(int $maSinhVien, int $maKhungGio, string $chuDe): int
    {
        $this->db->beginTransaction();
        try {
            $st = $this->db->prepare('SELECT bat_dau, suc_chua FROM khung_gio WHERE ma_khung_gio = :id FOR UPDATE');
            $st->execute([':id' => $maKhungGio]);
            $kg = $st->fetch();
            if (!$kg) {
                throw new DomainException('Khung giờ này không còn tồn tại.');
            }
            if (new DateTime($kg['bat_dau']) <= new DateTime()) {
                throw new DomainException('Khung giờ này đã qua. Vui lòng chọn khung giờ khác.');
            }

            // Chỗ chỉ bị trừ khi giảng viên DUYỆT -> ở đây chỉ đếm lịch đã duyệt/hoàn thành để chặn khung giờ đã đủ người.
            $st = $this->db->prepare(
                "SELECT COALESCE(SUM(trang_thai IN ('da_duyet', 'hoan_thanh')), 0) AS da_duyet,
                        COALESCE(SUM(ma_sinh_vien = :sv AND trang_thai IN ('cho_duyet', 'da_duyet', 'hoan_thanh')), 0) AS cua_toi
                   FROM cuoc_hen
                  WHERE ma_khung_gio = :id"
            );
            $st->execute([':id' => $maKhungGio, ':sv' => $maSinhVien]);
            $dem = $st->fetch();
            if ((int) $dem['cua_toi'] > 0) {
                throw new DomainException('Bạn đã gửi yêu cầu cho khung giờ này rồi.');
            }
            if ((int) $dem['da_duyet'] >= (int) $kg['suc_chua']) {
                throw new DomainException('Khung giờ này đã đủ số sinh viên. Vui lòng chọn khung giờ khác.');
            }

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
     * Giảng viên duyệt 1 yêu cầu: lúc này chỗ trong khung giờ mới bị trừ.
     * Khóa dòng cuộc hẹn + khung giờ, kiểm tra còn chỗ rồi mới đổi trạng thái.
     * Ném DomainException (thông báo tiếng Việt) nếu không duyệt được.
     */
    public function duyetCuocHen(int $maCuocHen): void
    {
        $this->db->beginTransaction();
        try {
            $st = $this->db->prepare(
                'SELECT ch.ma_khung_gio, ch.trang_thai, kg.suc_chua
                   FROM cuoc_hen ch
                   JOIN khung_gio kg ON kg.ma_khung_gio = ch.ma_khung_gio
                  WHERE ch.ma_cuoc_hen = :id FOR UPDATE'
            );
            $st->execute([':id' => $maCuocHen]);
            $ch = $st->fetch();
            if (!$ch || $ch['trang_thai'] !== 'cho_duyet') {
                throw new DomainException('Yêu cầu này không còn ở trạng thái chờ duyệt.');
            }

            $st = $this->db->prepare(
                "SELECT COUNT(*) FROM cuoc_hen WHERE ma_khung_gio = :kg AND trang_thai IN ('da_duyet', 'hoan_thanh')"
            );
            $st->execute([':kg' => $ch['ma_khung_gio']]);
            if ((int) $st->fetchColumn() >= (int) $ch['suc_chua']) {
                throw new DomainException('Khung giờ này đã đủ số sinh viên được duyệt nên không thể duyệt thêm. Bạn có thể từ chối yêu cầu này.');
            }

            $this->db->prepare("UPDATE cuoc_hen SET trang_thai = 'da_duyet' WHERE ma_cuoc_hen = :id")
                ->execute([':id' => $maCuocHen]);
            $this->db->commit();
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
     * Hủy lịch hẹn bởi Sinh viên
     * Kiểm tra quyền sở hữu và không cho phép hủy lịch đã hoàn thành.
     */
    public function huyLichBoiSinhVien(int $maCuocHen, int $maSinhVien): bool
    {
        // 1. Kiểm tra tồn tại cuộc hẹn thuộc về sinh viên này
        $sqlSelect = "SELECT trang_thai FROM cuoc_hen WHERE ma_cuoc_hen = :ma_cuoc_hen AND ma_sinh_vien = :ma_sv";
        $stmtSelect = $this->db->prepare($sqlSelect);
        $stmtSelect->execute([
            ':ma_cuoc_hen' => $maCuocHen,
            ':ma_sv'       => $maSinhVien
        ]);
        $cuocHen = $stmtSelect->fetch(PDO::FETCH_ASSOC);

        if (!$cuocHen) {
            return false;
        }

        // 2. Quy tắc nghiệp vụ: Không hủy lịch đã hoàn thành
        if ($cuocHen['trang_thai'] === 'hoan_thanh') {
            return false;
        }

        // 3. Cập nhật trạng thái thành 'da_huy'
        $sqlUpdate = "UPDATE cuoc_hen SET trang_thai = 'da_huy' WHERE ma_cuoc_hen = :ma_cuoc_hen";
        $stmtUpdate = $this->db->prepare($sqlUpdate);
        return $stmtUpdate->execute([':ma_cuoc_hen' => $maCuocHen]);
    }
}