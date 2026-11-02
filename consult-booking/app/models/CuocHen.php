<?php
declare(strict_types=1);

class CuocHen extends MoHinh
{
    public function layDanhSachChoGiangVien(int $maGiangVien, ?string $trangThai = null, int $trang = 1, int $gioiHan = 10, ?string $ngay = null): array
    {
        $this->tuDongTuChoiQuaHan();   // yêu cầu chờ duyệt mà đã qua giờ hẹn -> tự động từ chối
        $offset = ($trang - 1) * $gioiHan;
        $params = [':ma_gv' => $maGiangVien];
        $whereSql = "WHERE kg.ma_giang_vien = :ma_gv";

        if ($trangThai && in_array($trangThai, ['cho_duyet', 'da_duyet', 'tu_choi', 'da_huy', 'hoan_thanh'])) {
            $whereSql .= " AND ch.trang_thai = :trang_thai";
            $params[':trang_thai'] = $trangThai;
        }

        // Bổ sung điều kiện lọc chính xác theo ngày nếu có truyền vào từ URL
        if ($ngay) {
            $whereSql .= " AND DATE(kg.bat_dau) = :ngay";
            $params[':ngay'] = $ngay;
        }

        (new KhungGioModel())->damBaoCotChuyenMon();   // đảm bảo có cột khung_gio.ma_chuyen_mon trước khi truy vấn

        $sql = "SELECT ch.ma_cuoc_hen, ch.ma_khung_gio, ch.chu_de, ch.trang_thai, ch.ghi_chu_giang_vien, ch.ly_do_huy, ch.huy_boi, ch.ngay_tao,
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

    /**
     * Lấy danh sách toàn bộ cuộc hẹn phục vụ cho Quản trị viên (có lọc trạng thái và phân trang)
     */
    public function layDanhSachChoAdmin(?string $trangThai = null, int $trang = 1, int $gioiHan = 15): array
    {
        $this->tuDongTuChoiQuaHan();   // yêu cầu chờ duyệt mà đã qua giờ hẹn -> tự động từ chối
        $offset = ($trang - 1) * $gioiHan;
        $params = [];
        $whereSql = "WHERE 1=1";

        if ($trangThai && in_array($trangThai, ['cho_duyet', 'da_duyet', 'tu_choi', 'da_huy', 'hoan_thanh'], true)) {
            $whereSql .= " AND ch.trang_thai = :trang_thai";
            $params[':trang_thai'] = $trangThai;
        }

        $sql = "SELECT ch.ma_cuoc_hen, ch.chu_de, ch.trang_thai, ch.ghi_chu_giang_vien, ch.ly_do_huy, ch.huy_boi, ch.ngay_tao,
                       sv.ho_ten AS ten_sinh_vien, sv.email AS email_sinh_vien,
                       gv.ho_ten AS ten_giang_vien, gv.email AS email_giang_vien,
                       kg.bat_dau, kg.ket_thuc, kg.dia_diem
                FROM cuoc_hen ch
                JOIN nguoi_dung sv ON ch.ma_sinh_vien = sv.ma_nguoi_dung
                JOIN khung_gio kg ON ch.ma_khung_gio = kg.ma_khung_gio
                JOIN nguoi_dung gv ON kg.ma_giang_vien = gv.ma_nguoi_dung
                {$whereSql}
                ORDER BY kg.bat_dau DESC
                LIMIT {$gioiHan} OFFSET {$offset}";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $danhSach = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sqlCount = "SELECT COUNT(*) FROM cuoc_hen ch JOIN khung_gio kg ON ch.ma_khung_gio = kg.ma_khung_gio {$whereSql}";
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
        $this->tuDongTuChoiQuaHan();   // yêu cầu chờ duyệt mà đã qua giờ hẹn -> tự động từ chối
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

    /** Buổi tư vấn đã KẾT THÚC chưa (giờ hiện tại >= giờ kết thúc của khung giờ). */
    public function daQuaGioTuVan(string $ketThuc): bool
    {
        return time() >= strtotime($ketThuc);
    }

    private static ?bool $coCotLyDoHuy = null;

    /**
     * Đảm bảo bảng cuoc_hen có cột ly_do_huy (lý do hủy / từ chối) và huy_boi (ai hủy: sinh viên / giảng viên / hệ thống).
     * Tự thêm cột nếu CSDL cũ chưa có. Không gọi trong transaction (ALTER TABLE tự commit).
     */
    public function damBaoCotLyDoHuy(): bool
    {
        if (self::$coCotLyDoHuy !== null) {
            return self::$coCotLyDoHuy;
        }
        try {
            if (!$this->db->query("SHOW COLUMNS FROM cuoc_hen LIKE 'ly_do_huy'")->fetch()) {
                $this->db->exec(
                    "ALTER TABLE cuoc_hen
                       ADD COLUMN ly_do_huy VARCHAR(500) NULL DEFAULT NULL AFTER ghi_chu_giang_vien,
                       ADD COLUMN huy_boi ENUM('sinh_vien','giang_vien','he_thong') NULL DEFAULT NULL AFTER ly_do_huy"
                );
            }
            self::$coCotLyDoHuy = true;
        } catch (Throwable $e) {
            error_log('Không thêm được cột cuoc_hen.ly_do_huy: ' . $e->getMessage());
            self::$coCotLyDoHuy = false;
        }
        return self::$coCotLyDoHuy;
    }

    private static bool $daChayTuDongTuChoi = false;

    /** Cuộc hẹn ĐÃ DUYỆT mà quá hạn này (tính từ giờ kết thúc) vẫn chưa được đánh dấu hoàn thành thì hệ thống tự hoàn thành. */
    public const GIO_TU_DONG_HOAN_THANH = 24;

    /**
     * Tự động TỪ CHỐI mọi yêu cầu còn ở trạng thái CHỜ DUYỆT mà giờ bắt đầu của khung giờ đã qua
     * (giảng viên không duyệt kịp). Chạy theo kiểu "lười": được gọi trước khi đọc/ghi dữ liệu lịch hẹn,
     * mỗi request chỉ chạy một lần (trừ khi $batBuoc = true). Trạng thái 'tu_choi' làm cột
     * khung_gio_hieu_luc về NULL nên khung giờ tự được giải phóng. Trả về số yêu cầu bị hủy.
     * Đồng thời tự HOÀN THÀNH các cuộc hẹn đã duyệt mà giảng viên quên bấm "Hoàn thành" sau GIO_TU_DONG_HOAN_THANH giờ
     * kể từ lúc kết thúc, để sinh viên vẫn đánh giá được và số liệu không bị kẹt ở "Đã duyệt".
     */
    public function tuDongTuChoiQuaHan(bool $batBuoc = false): int
    {
        $coCot = $this->damBaoCotLyDoHuy();   // luôn đảm bảo cột lý do hủy có mặt trước các truy vấn khác
        if (self::$daChayTuDongTuChoi && !$batBuoc) {
            return 0;
        }
        self::$daChayTuDongTuChoi = true;
        if (!$coCot) {
            return 0;
        }
        try {
            $st = $this->db->prepare(
                "UPDATE cuoc_hen ch
                   JOIN khung_gio kg ON kg.ma_khung_gio = ch.ma_khung_gio
                    SET ch.trang_thai = 'tu_choi',
                        ch.ly_do_huy = :ly_do,
                        ch.huy_boi = 'he_thong'
                  WHERE ch.trang_thai = 'cho_duyet' AND kg.bat_dau <= :now"
            );
            $st->execute([
                ':ly_do' => 'Giảng viên chưa duyệt yêu cầu trước giờ hẹn nên hệ thống tự động từ chối.',
                ':now'   => date('Y-m-d H:i:s'),
            ]);
            $soTuChoi = $st->rowCount();

            $st2 = $this->db->prepare(
                "UPDATE cuoc_hen ch
                   JOIN khung_gio kg ON kg.ma_khung_gio = ch.ma_khung_gio
                    SET ch.trang_thai = 'hoan_thanh'
                  WHERE ch.trang_thai = 'da_duyet' AND kg.ket_thuc <= :han"
            );
            $st2->execute([':han' => date('Y-m-d H:i:s', time() - self::GIO_TU_DONG_HOAN_THANH * 3600)]);

            return $soTuChoi;
        } catch (Throwable $e) {
            error_log('Tự động xử lý lịch quá hạn thất bại: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Đánh dấu HOÀN THÀNH một cuộc hẹn đã duyệt. Chỉ cho phép khi buổi tư vấn đã KẾT THÚC (qua giờ kết thúc):
     * chưa qua giờ thì ném DomainException (không thể hoàn thành buổi tư vấn chưa diễn ra xong).
     */
    public function hoanThanhCuocHen(int $maCuocHen): void
    {
        $st = $this->db->prepare(
            'SELECT ch.trang_thai, kg.bat_dau, kg.ket_thuc
               FROM cuoc_hen ch
               JOIN khung_gio kg ON kg.ma_khung_gio = ch.ma_khung_gio
              WHERE ch.ma_cuoc_hen = :id'
        );
        $st->execute([':id' => $maCuocHen]);
        $ch = $st->fetch(PDO::FETCH_ASSOC);
        if (!$ch) {
            throw new DomainException('Cuộc hẹn không tồn tại.');
        }
        if ($ch['trang_thai'] !== 'da_duyet') {
            throw new DomainException('Chỉ hoàn thành được cuộc hẹn đang ở trạng thái đã duyệt.');
        }
        if (!$this->daQuaGioTuVan((string) $ch['ket_thuc'])) {
            throw new DomainException(
                'Buổi tư vấn chưa kết thúc (kết thúc lúc ' . date('H:i d/m/Y', strtotime($ch['ket_thuc'])) . ') nên chưa thể đánh dấu hoàn thành.'
            );
        }
        $this->capNhatTrangThai($maCuocHen, 'hoan_thanh');
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

    public function kiemTraTrungLich(int $maGiangVien, string $batDau, string $ketThuc): bool
    {
        $st = $this->db->prepare(
            "SELECT COUNT(*)
               FROM khung_gio kg
               JOIN cuoc_hen ch ON ch.ma_khung_gio = kg.ma_khung_gio
              WHERE kg.ma_giang_vien = :gv
                AND ch.khung_gio_hieu_luc IS NOT NULL
                AND kg.bat_dau < :ket_thuc AND kg.ket_thuc > :bat_dau"
        );
        $st->execute([':gv' => $maGiangVien, ':bat_dau' => $batDau, ':ket_thuc' => $ketThuc]);
        return (int) $st->fetchColumn() > 0;
    }

    public function taoCuocHen(int $maSinhVien, int $maGiangVien, string $batDau, string $ketThuc, string $chuDe, ?string $diaDiem = null): int
    {
        $coCot = (new KhungGioModel())->damBaoCotTuNhap();   
        $this->db->beginTransaction();
        try {
            $this->khoaNguoiDung($maSinhVien);
            $this->khoaNguoiDung($maGiangVien);   // 2 sinh viên cùng đặt 1 giảng viên một lúc phải xếp hàng
            $this->chanNeuSinhVienTrungGio($maSinhVien, $batDau, $ketThuc);   // không cho đặt 2 lịch cùng một khung giờ

            // Giảng viên đã có yêu cầu (chờ duyệt) hoặc lịch (đã duyệt / hoàn thành) trùng giờ -> không nhận thêm sinh viên khác
            $trungGv = $this->timLichHenTrungCuaGiangVien($maGiangVien, $batDau, $ketThuc, null);   // null = mọi cuộc hẹn đang hiệu lực
            if ($trungGv !== null) {
                $st = $this->db->prepare('SELECT ho_ten FROM nguoi_dung WHERE ma_nguoi_dung = :id');
                $st->execute([':id' => $maGiangVien]);
                throw new DomainException(sprintf(
                    'Giảng viên %s đã có lịch hẹn vào %s–%s ngày %s. Vui lòng chọn khung giờ khác.',
                    (string) $st->fetchColumn(),
                    date('H:i', strtotime($trungGv['bat_dau'])),
                    date('H:i', strtotime($trungGv['ket_thuc'])),
                    date('d/m/Y', strtotime($trungGv['bat_dau']))
                ));
            }
            $this->db->prepare('INSERT INTO khung_gio (ma_giang_vien, bat_dau, ket_thuc, dia_diem' . ($coCot ? ', la_tu_nhap' : '') . ') VALUES (:gv, :bd, :kt, :dd' . ($coCot ? ', 1' : '') . ')')
                ->execute([':gv' => $maGiangVien, ':bd' => $batDau, ':kt' => $ketThuc, ':dd' => $diaDiem]);
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
     * Khóa dòng người dùng (sinh viên hoặc giảng viên) trong transaction để các yêu cầu đồng thời liên quan
     * tới CÙNG một người phải xếp hàng, tránh lọt cả hai qua bước kiểm tra trùng giờ.
     */
    private function khoaNguoiDung(int $maNguoiDung): void
    {
        $st = $this->db->prepare('SELECT ma_nguoi_dung FROM nguoi_dung WHERE ma_nguoi_dung = :id FOR UPDATE');
        $st->execute([':id' => $maNguoiDung]);
        $st->fetchColumn();
    }

    /**
     * Tìm lịch hẹn của GIẢNG VIÊN có thời gian giao với [batDau, ketThuc), thuộc các trạng thái $trangThai.
     * $boQuaKhungGio: bỏ qua khung giờ này (nhiều sinh viên cùng chung một khung giờ có sức chứa > 1 là hợp lệ).
     * Trả về null nếu không trùng.
     */
    private function timLichHenTrungCuaGiangVien(int $maGiangVien, string $batDau, string $ketThuc, ?array $trangThai, int $boQuaKhungGio = 0): ?array
    {
        // $trangThai = null -> mọi cuộc hẹn đang hiệu lực (cột khung_gio_hieu_luc khác NULL)
        $dieuKien = $trangThai === null
            ? 'ch.khung_gio_hieu_luc IS NOT NULL'
            : 'ch.trang_thai IN (' . implode(',', array_fill(0, count($trangThai), '?')) . ')';
        $st = $this->db->prepare(
            "SELECT ch.ma_cuoc_hen, kg.bat_dau, kg.ket_thuc
               FROM cuoc_hen ch
               JOIN khung_gio kg ON kg.ma_khung_gio = ch.ma_khung_gio
              WHERE kg.ma_giang_vien = ?
                AND kg.ma_khung_gio <> ?
                AND $dieuKien
                AND kg.bat_dau < ? AND kg.ket_thuc > ?
              ORDER BY kg.bat_dau
              LIMIT 1"
        );
        $st->execute(array_merge([$maGiangVien, $boQuaKhungGio], $trangThai ?? [], [$ketThuc, $batDau]));
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    /**
     * Tìm lịch hẹn của sinh viên có thời gian GIAO với [batDau, ketThuc), bất kể giảng viên nào.
     * Hai khoảng giao nhau khi: bắt đầu A < kết thúc B và kết thúc A > bắt đầu B
     * (09:00–10:00 và 10:00–11:00 chỉ "chạm" nhau nên không tính là trùng).
     * Trả về null nếu không trùng.
     */
    private function timLichHenTrungCuaSinhVien(int $maSinhVien, string $batDau, string $ketThuc, ?array $trangThai, int $boQua = 0): ?array
    {
        // $trangThai = null -> mọi cuộc hẹn đang hiệu lực (cột khung_gio_hieu_luc khác NULL)
        $dieuKien = $trangThai === null
            ? 'ch.khung_gio_hieu_luc IS NOT NULL'
            : 'ch.trang_thai IN (' . implode(',', array_fill(0, count($trangThai), '?')) . ')';
        $st = $this->db->prepare(
            "SELECT ch.ma_cuoc_hen, ch.trang_thai, kg.bat_dau, kg.ket_thuc, gv.ho_ten AS ten_giang_vien
               FROM cuoc_hen ch
               JOIN khung_gio kg ON kg.ma_khung_gio = ch.ma_khung_gio
               JOIN nguoi_dung gv ON gv.ma_nguoi_dung = kg.ma_giang_vien
              WHERE ch.ma_sinh_vien = ?
                AND ch.ma_cuoc_hen <> ?
                AND $dieuKien
                AND kg.bat_dau < ? AND kg.ket_thuc > ?
              ORDER BY kg.bat_dau
              LIMIT 1"
        );
        $st->execute(array_merge([$maSinhVien, $boQua], $trangThai ?? [], [$ketThuc, $batDau]));
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    /** Ném DomainException nếu sinh viên đã có lịch hẹn còn hiệu lực trùng giờ với khoảng thời gian muốn đặt. */
    private function chanNeuSinhVienTrungGio(int $maSinhVien, string $batDau, string $ketThuc): void
    {
        $trung = $this->timLichHenTrungCuaSinhVien($maSinhVien, $batDau, $ketThuc, null);   // null = mọi cuộc hẹn đang hiệu lực
        if ($trung === null) {
            return;
        }
        $nhan = ['cho_duyet' => 'đang chờ duyệt', 'da_duyet' => 'đã được duyệt', 'hoan_thanh' => 'đã hoàn thành'][$trung['trang_thai']] ?? '';
        throw new DomainException(sprintf(
            'Bạn đã có lịch với giảng viên %s vào %s–%s ngày %s (%s). Hãy chọn khung giờ khác, hoặc hủy lịch đó trước khi đặt lịch mới.',
            $trung['ten_giang_vien'],
            date('H:i', strtotime($trung['bat_dau'])),
            date('H:i', strtotime($trung['ket_thuc'])),
            date('d/m/Y', strtotime($trung['bat_dau'])),
            $nhan
        ));
    }

    public function datVaoKhungGio(int $maSinhVien, int $maKhungGio, string $chuDe): int
    {
        $this->db->beginTransaction();
        try {
            $this->khoaNguoiDung($maSinhVien);
            $st = $this->db->prepare('SELECT bat_dau, ket_thuc, suc_chua FROM khung_gio WHERE ma_khung_gio = :id FOR UPDATE');
            $st->execute([':id' => $maKhungGio]);
            $kg = $st->fetch();
            if (!$kg) {
                throw new DomainException('Khung giờ này không còn tồn tại.');
            }
            if (new DateTime($kg['bat_dau']) <= new DateTime()) {
                throw new DomainException('Khung giờ này đã qua. Vui lòng chọn khung giờ khác.');
            }

            $st = $this->db->prepare(
                // Chỉ tính cuộc hẹn ĐANG HIỆU LỰC của khung giờ (cột khung_gio_hieu_luc): chờ duyệt, đã duyệt, hoàn thành
                "SELECT COUNT(*) AS hieu_luc,
                        COALESCE(SUM(ma_sinh_vien = :sv), 0) AS cua_toi
                   FROM cuoc_hen
                  WHERE khung_gio_hieu_luc = :id"
            );
            $st->execute([':id' => $maKhungGio, ':sv' => $maSinhVien]);
            $dem = $st->fetch();
            if ((int) $dem['cua_toi'] > 0) {
                throw new DomainException('Bạn đã gửi yêu cầu cho khung giờ này rồi.');
            }
            // Không cho đặt 2 giảng viên khác nhau cùng một khung giờ
            $this->chanNeuSinhVienTrungGio($maSinhVien, (string) $kg['bat_dau'], (string) $kg['ket_thuc']);
            if ((int) $dem['hieu_luc'] >= (int) $kg['suc_chua']) {
                throw new DomainException(
                    (int) $kg['suc_chua'] === 1
                        ? 'Khung giờ này đã có sinh viên khác đặt trước. Vui lòng chọn khung giờ khác.'
                        : 'Khung giờ này đã kín chỗ. Vui lòng chọn khung giờ khác.'
                );
            }

            try {
                $this->db->prepare("INSERT INTO cuoc_hen (ma_khung_gio, ma_sinh_vien, chu_de, trang_thai) VALUES (:kg, :sv, :cd, 'cho_duyet')")
                    ->execute([':kg' => $maKhungGio, ':sv' => $maSinhVien, ':cd' => $chuDe]);
            } catch (PDOException $e) {
                // UNIQUE (khung_gio_hieu_luc): CSDL không cho hai cuộc hẹn đang hiệu lực cùng giữ một khung giờ
                if ($e->getCode() === '23000') {
                    throw new DomainException('Khung giờ này đã có sinh viên khác đặt trước. Vui lòng chọn khung giờ khác.');
                }
                throw $e;
            }
            $maCuocHen = (int) $this->db->lastInsertId();

            $this->db->commit();
            return $maCuocHen;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Duyệt một yêu cầu đặt lịch (chỉ khi còn chỗ, không trùng giờ của sinh viên).
     * $diaDiem: địa điểm giảng viên nhập lúc duyệt — chỉ được ghi vào khung giờ khi khung giờ đó CHƯA có địa điểm
     * (lịch sinh viên tự chọn giờ); khung giờ giảng viên đã đặt địa điểm thì giữ nguyên.
     */
    public function duyetCuocHen(int $maCuocHen, ?string $diaDiem = null): void
    {
        $this->tuDongTuChoiQuaHan(true);   // yêu cầu đã qua giờ hẹn bị từ chối trước, không thể duyệt nữa
        $this->db->beginTransaction();
        try {
            $st = $this->db->prepare(
                'SELECT ch.ma_khung_gio, ch.ma_sinh_vien, ch.trang_thai, kg.ma_giang_vien, kg.suc_chua, kg.bat_dau, kg.ket_thuc
                   FROM cuoc_hen ch
                   JOIN khung_gio kg ON kg.ma_khung_gio = ch.ma_khung_gio
                  WHERE ch.ma_cuoc_hen = :id FOR UPDATE'
            );
            $st->execute([':id' => $maCuocHen]);
            $ch = $st->fetch();
            if (!$ch || $ch['trang_thai'] !== 'cho_duyet') {
                throw new DomainException('Yêu cầu này không còn ở trạng thái chờ duyệt (có thể đã quá giờ hẹn nên bị tự động từ chối).');
            }

            $st = $this->db->prepare(
                "SELECT COUNT(*) FROM cuoc_hen WHERE ma_khung_gio = :kg AND trang_thai IN ('da_duyet', 'hoan_thanh')"
            );
            $st->execute([':kg' => $ch['ma_khung_gio']]);
            if ((int) $st->fetchColumn() >= (int) $ch['suc_chua']) {
                throw new DomainException('Khung giờ này đã đủ số sinh viên được duyệt nên không thể duyệt thêm. Bạn có thể từ chối yêu cầu này.');
            }

            // Giảng viên đã duyệt một lịch khác (khung giờ khác) trùng giờ -> không thể ở 2 nơi cùng lúc
            $this->khoaNguoiDung((int) $ch['ma_giang_vien']);
            $trungGv = $this->timLichHenTrungCuaGiangVien(
                (int) $ch['ma_giang_vien'], (string) $ch['bat_dau'], (string) $ch['ket_thuc'],
                ['da_duyet', 'hoan_thanh'], (int) $ch['ma_khung_gio']
            );
            if ($trungGv !== null) {
                throw new DomainException(sprintf(
                    'Bạn đã duyệt một lịch khác vào %s–%s ngày %s, trùng khung giờ này nên không thể duyệt thêm. Bạn có thể từ chối yêu cầu này.',
                    date('H:i', strtotime($trungGv['bat_dau'])),
                    date('H:i', strtotime($trungGv['ket_thuc'])),
                    date('d/m/Y', strtotime($trungGv['bat_dau']))
                ));
            }

            // Sinh viên đã có lịch hẹn ĐÃ DUYỆT với giảng viên khác trùng giờ (dữ liệu đặt trước khi có quy tắc chặn trùng giờ)
            if ($this->timLichHenTrungCuaSinhVien((int) $ch['ma_sinh_vien'], (string) $ch['bat_dau'], (string) $ch['ket_thuc'], ['da_duyet', 'hoan_thanh'], $maCuocHen) !== null) {
                throw new DomainException('Sinh viên này đã có một lịch hẹn đã duyệt trùng khung giờ này nên không thể duyệt. Bạn có thể từ chối yêu cầu này.');
            }

            $this->db->prepare("UPDATE cuoc_hen SET trang_thai = 'da_duyet' WHERE ma_cuoc_hen = :id")
                ->execute([':id' => $maCuocHen]);

            if ($diaDiem !== null && trim($diaDiem) !== '') {
                $this->db->prepare(
                    "UPDATE khung_gio SET dia_diem = :dd
                      WHERE ma_khung_gio = :kg AND (dia_diem IS NULL OR dia_diem = '')"
                )->execute([':dd' => trim($diaDiem), ':kg' => $ch['ma_khung_gio']]);
            }
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function thongKeTongQuanAdmin(): array
    {
        $this->tuDongTuChoiQuaHan();   // yêu cầu chờ duyệt mà đã qua giờ hẹn -> tự động từ chối
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

    public function thongKeChoGiangVien(int $maGiangVien): array
    {
        $this->tuDongTuChoiQuaHan();   // yêu cầu chờ duyệt mà đã qua giờ hẹn -> tự động từ chối
        $sql = "SELECT 
                    COUNT(*) as tong_so,
                    SUM(CASE WHEN ch.trang_thai = 'cho_duyet' THEN 1 ELSE 0 END) as cho_duyet,
                    SUM(CASE WHEN ch.trang_thai = 'da_duyet' AND DATE(kg.bat_dau) = CURDATE() THEN 1 ELSE 0 END) as hom_nay,
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
            'tong_so' => 0, 'cho_duyet' => 0, 'hom_nay' => 0, 'da_duyet' => 0, 
            'tu_choi' => 0, 'da_huy' => 0, 'hoan_thanh' => 0
        ];
    }

    /**
     * Sinh viên hủy lịch của CHÍNH MÌNH (chỉ khi đang chờ duyệt / đã duyệt). Lý do là bắt buộc và được lưu lại
     * để giảng viên / admin xem. Chỉ ghi khi trạng thái hiện tại còn cho phép hủy (không ghi đè hoàn thành / từ chối / đã hủy).
     */
    public function huyLichBoiSinhVien(int $maCuocHen, int $maSinhVien, string $lyDo): bool
    {
        $this->damBaoCotLyDoHuy();
        // Chỉ hủy được TRƯỚC giờ bắt đầu: buổi đã/đang diễn ra thì không hủy nữa
        $st = $this->db->prepare(
            "UPDATE cuoc_hen ch JOIN khung_gio kg ON kg.ma_khung_gio = ch.ma_khung_gio
                SET ch.trang_thai = 'da_huy', ch.ly_do_huy = :ly_do, ch.huy_boi = 'sinh_vien'
              WHERE ch.ma_cuoc_hen = :id AND ch.ma_sinh_vien = :sv AND ch.trang_thai IN ('cho_duyet', 'da_duyet')
                AND kg.bat_dau > :now"
        );
        $st->execute([':ly_do' => $lyDo, ':id' => $maCuocHen, ':sv' => $maSinhVien, ':now' => date('Y-m-d H:i:s')]);
        return $st->rowCount() > 0;
    }

    /**
     * Giảng viên hủy một cuộc hẹn ĐÃ DUYỆT, kèm lý do (sinh viên và admin xem được).
     * Quyền sở hữu cuộc hẹn do controller kiểm tra trước (timTheoIdVaGiangVien).
     */
    public function huyBoiGiangVien(int $maCuocHen, string $lyDo): bool
    {
        $this->damBaoCotLyDoHuy();
        // Chỉ hủy được TRƯỚC giờ bắt đầu buổi tư vấn
        $st = $this->db->prepare(
            "UPDATE cuoc_hen ch JOIN khung_gio kg ON kg.ma_khung_gio = ch.ma_khung_gio
                SET ch.trang_thai = 'da_huy', ch.ly_do_huy = :ly_do, ch.huy_boi = 'giang_vien'
              WHERE ch.ma_cuoc_hen = :id AND ch.trang_thai = 'da_duyet' AND kg.bat_dau > :now"
        );
        $st->execute([':ly_do' => $lyDo, ':id' => $maCuocHen, ':now' => date('Y-m-d H:i:s')]);
        return $st->rowCount() > 0;
    }
}