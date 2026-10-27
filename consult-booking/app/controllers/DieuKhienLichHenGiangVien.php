<?php
declare(strict_types=1);

class DieuKhienLichHenGiangVien extends BoDieuKhien
{
    private CuocHen $cuocHenModel;

    public function __construct()
    {
        $this->cuocHenModel = new CuocHen();
    }

    /**
     * Hiển thị bảng điều khiển / trang chủ của Giảng viên (bao gồm thống kê cuộc hẹn)
     */
    public function dashboard(): void
    {
        if (method_exists('XacThuc', 'requireRole')) {
            XacThuc::requireRole('giang_vien');
        } elseif (method_exists('XacThuc', 'yeuCauVaiTro')) {
            XacThuc::yeuCauVaiTro('giang_vien');
        }

        $user = XacThuc::user();
        if (!is_array($user)) {
            $user = [];
        }
        $user['ho_ten'] = $user['ho_ten'] ?? 'Giảng viên';
        $user['email'] = $user['email'] ?? '';
        $maGiangVien = (int) ($user['ma_nguoi_dung'] ?? 0);

        // Lấy thông tin hồ sơ và chuyên môn của giảng viên
        $hoSoModel = new HoSoGiangVien();
        $profile = $hoSoModel->layHoSoVaChuyenMon($maGiangVien) ?? [
            'ten_don_vi' => 'Chưa gán đơn vị',
            'chuyen_mon' => []
        ];

        // Lấy số liệu thống kê chi tiết cho các tiles
        $stats = $this->cuocHenModel->thongKeChiTietChoGiangVien($maGiangVien);
       
        // Lấy danh sách cuộc hẹn sắp tới
        $upcoming = $this->cuocHenModel->layCuocHenSapToiChoGiangVien($maGiangVien, 5);

        // Tính % hoàn thiện hồ sơ
        $completeness = [
            'percent' => !empty($profile['chuyen_mon']) ? 100 : 50,
            'missing' => empty($profile['chuyen_mon']) ? ['Lĩnh vực chuyên môn'] : []
        ];

        echo GiaoDien::render('khuvuc/giangvien/bang_dieu_khien', [
            'tieu_de'      => 'Bảng điều khiển Giảng viên',
            'me'           => $user,
            'profile'      => $profile,
            'completeness' => $completeness,
            'stats'        => $stats,
            'upcoming'     => $upcoming
        ]);
    }

    /**
     * Hiển thị giao diện Đặt lịch hẹn dành cho Sinh viên
     */
    public function hienThiFormDatLich(): void
    {
        XacThuc::requireRole('sinh_vien');

        $this->view('khuvuc/sinhvien/dat_lich', [
            'title'             => 'Đặt lịch hẹn tư vấn',
            'danhSachGiangVien' => (new NguoiDung())->layGiangVienChoDatLich(),
            'cu'                => $_SESSION['_dat_lich_cu'] ?? ['giang_vien_id' => (int) ($_GET['giang_vien_id'] ?? 0)],
        ]);
        unset($_SESSION['_dat_lich_cu']);
    }

    /**
     * Xử lý lưu yêu cầu Đặt lịch hẹn của Sinh viên
     */
    public function xuLyDatLich(): void
    {
        XacThuc::requireRole('sinh_vien');

        $giangVienId = (int) ($_POST['giang_vien_id'] ?? 0);
        $ngayHen     = $this->input('ngay_hen');
        $gioBatDau   = $this->input('gio_bat_dau');
        $gioKetThuc  = $this->input('gio_ket_thuc');
        $chuDe       = mb_substr($this->input('chu_de'), 0, 255);   // cột chu_de là VARCHAR(255)

        $loi = function (string $msg) use ($giangVienId, $ngayHen, $gioBatDau, $gioKetThuc, $chuDe): never {
            $_SESSION['_dat_lich_cu'] = [
                'giang_vien_id' => $giangVienId, 'ngay_hen' => $ngayHen,
                'gio_bat_dau' => $gioBatDau, 'gio_ket_thuc' => $gioKetThuc, 'chu_de' => $chuDe,
            ];
            flash('danger', $msg);
            redirect('sinh-vien/dat-lich');
        };

        if ($giangVienId <= 0 || $ngayHen === '' || $gioBatDau === '' || $gioKetThuc === '' || $chuDe === '') {
            $loi('Vui lòng nhập đầy đủ các thông tin bắt buộc.');
        }

        $batDau  = DateTime::createFromFormat('Y-m-d H:i', "$ngayHen $gioBatDau");
        $ketThuc = DateTime::createFromFormat('Y-m-d H:i', "$ngayHen $gioKetThuc");
        if (!$batDau || !$ketThuc) {
            $loi('Ngày hoặc giờ không hợp lệ.');
        }
        if ($ketThuc <= $batDau) {
            $loi('Giờ kết thúc phải sau giờ bắt đầu.');
        }
        if ($batDau <= new DateTime()) {
            $loi('Thời gian hẹn phải ở tương lai.');
        }

        // Giảng viên phải tồn tại và đang hoạt động
        $hopLe = array_column((new NguoiDung())->layTheoVaiTro('giang_vien'), 'ma_nguoi_dung');
        if (!in_array((string) $giangVienId, array_map('strval', $hopLe), true)) {
            $loi('Giảng viên không hợp lệ.');
        }

        $bd = $batDau->format('Y-m-d H:i:s');
        $kt = $ketThuc->format('Y-m-d H:i:s');
        if ($this->cuocHenModel->kiemTraTrungLich($giangVienId, $bd, $kt)) {
            $loi('Giảng viên đã có lịch hẹn trong khoảng thời gian này. Vui lòng chọn giờ khác.');
        }

        try {
            $this->cuocHenModel->taoCuocHen((int) XacThuc::id(), $giangVienId, $bd, $kt, $chuDe);
        } catch (Throwable $e) {
            error_log('Đặt lịch thất bại: ' . $e->getMessage());
            $loi('Đã xảy ra lỗi, vui lòng thử lại.');
        }

        flash('success', 'Đặt lịch hẹn tư vấn thành công! Vui lòng chờ giảng viên phê duyệt.');
        redirect('sinh-vien/phan-hoi');   // trang "Lịch hẹn & Đánh giá" của sinh viên
    }

    /**
     * Hiển thị danh sách cuộc hẹn của giảng viên
     */
    public function index(): void
    {
        if (method_exists('XacThuc', 'requireRole')) {
            XacThuc::requireRole('giang_vien');
        } elseif (method_exists('XacThuc', 'yeuCauVaiTro')) {
            XacThuc::yeuCauVaiTro('giang_vien');
        }

        $user = XacThuc::user();
        $maGiangVien = $user['ma_nguoi_dung'] ?? null;
        $trangThai = $_GET['trang_thai'] ?? null;
        $trang = max(1, (int)($_GET['trang'] ?? 1));

        $data = $this->cuocHenModel->layDanhSachChoGiangVien((int)$maGiangVien, $trangThai, $trang);

        echo GiaoDien::render('khuvuc/giangvien/danh_sach_hen', [
            'tieu_de'        => 'Quản lý lịch hẹn tư vấn',
            'danh_sach'      => $data['danh_sach'] ?? [],
            'tong_trang'     => $data['tong_trang'] ?? 1,
            'trang_hien_tai' => $data['trang_hien_tai'] ?? 1,
            'trang_thai_loc' => $trangThai
        ]);
    }

    /**
     * Xử lý cập nhật trạng thái (Duyệt / Từ chối / Hoàn thành / Hủy)
     */
    public function capNhatTrangThai(): void
    {
        if (method_exists('XacThuc', 'requireRole')) {
            XacThuc::requireRole('giang_vien');
        } elseif (method_exists('XacThuc', 'yeuCauVaiTro')) {
            XacThuc::yeuCauVaiTro('giang_vien');
        }

        $maCuocHen = (int)($_POST['ma_cuoc_hen'] ?? 0);
        $hanhDong = $_POST['hanh_dong'] ?? '';
        $lyDoHuy = trim($_POST['ly_do_huy'] ?? ''); // Lấy lý do hủy do giảng viên nhập từ Modal
        $user = XacThuc::user();
        $maGiangVien = $user['ma_nguoi_dung'] ?? null;

        $cuocHen = $this->cuocHenModel->timTheoIdVaGiangVien($maCuocHen, (int)$maGiangVien);
        if (!$cuocHen) {
            flash('danger', 'Cuộc hẹn không tồn tại hoặc bạn không có quyền thao tác.');
            redirect('giang-vien/lich-hen');
            return;
        }

        switch ($hanhDong) {
            case 'duyet':
                if ($cuocHen['trang_thai'] === 'cho_duyet') {
                    $this->cuocHenModel->capNhatTrangThai($maCuocHen, 'da_duyet');
                    flash('success', 'Đã phê duyệt lịch hẹn thành công.');
                }
                break;
            case 'tu_choi':
                if ($cuocHen['trang_thai'] === 'cho_duyet') {
                    $this->cuocHenModel->capNhatTrangThai($maCuocHen, 'tu_choi');
                    flash('success', 'Đã từ chối lịch hẹn.');
                }
                break;
            case 'hoan_thanh':
                if ($cuocHen['trang_thai'] === 'da_duyet') {
                    $this->cuocHenModel->capNhatTrangThai($maCuocHen, 'hoan_thanh');
                    flash('success', 'Đã đánh dấu hoàn thành buổi tư vấn.');
                }
                break;
            case 'da_huy':
                if ($cuocHen['trang_thai'] === 'da_duyet') {
                    // Cập nhật trạng thái thành 'da_huy'
                    $this->cuocHenModel->capNhatTrangThai($maCuocHen, 'da_huy');
                   
                    // Nếu có nhập lý do hủy, lưu vào ghi chú của giảng viên để bên Sinh viên có thể đọc được
                    if (!empty($lyDoHuy)) {
                        $this->cuocHenModel->capNhatGhiChuGiangVien($maCuocHen, "Lý do hủy lịch hẹn: " . $lyDoHuy);
                    }
                   
                    flash('success', 'Đã hủy lịch hẹn và ghi nhận lý do thành công.');
                }
                break;
            default:
                flash('danger', 'Hành động không hợp lệ.');
                break;
        }

        redirect('giang-vien/lich-hen');
    }

    /**
     * Lưu ghi chú phản hồi
     */
    public function luuPhanHoi(): void
    {
        if (method_exists('XacThuc', 'requireRole')) {
            XacThuc::requireRole('giang_vien');
        } elseif (method_exists('XacThuc', 'yeuCauVaiTro')) {
            XacThuc::yeuCauVaiTro('giang_vien');
        }

        $maCuocHen = (int)($_POST['ma_cuoc_hen'] ?? 0);
        $ghiChu = trim($_POST['ghi_chu_giang_vien'] ?? '');
        $user = XacThuc::user();
        $maGiangVien = $user['ma_nguoi_dung'] ?? null;

        $cuocHen = $this->cuocHenModel->timTheoIdVaGiangVien($maCuocHen, (int)$maGiangVien);
        if (!$cuocHen) {
            flash('danger', 'Cuộc hẹn không tồn tại.');
            redirect('giang-vien/lich-hen');
            return;
        }

        $this->cuocHenModel->capNhatGhiChuGiangVien($maCuocHen, $ghiChu);
        flash('success', 'Đã lưu ghi chú và phản hồi buổi tư vấn.');
        redirect('giang-vien/lich-hen');
    }

    /**
     * Xử lý hành động Hủy lịch hẹn phía Sinh viên
     */
    public function huyLichSinhVien(): void
    {
        // Kiểm tra quyền truy cập của sinh viên
        XacThuc::requireRole('sinh_vien');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $maCuocHen = (int) ($_POST['ma_cuoc_hen'] ?? 0);
            $maSinhVien = (int) XacThuc::id();

            if ($maCuocHen > 0 && $maSinhVien > 0) {
                $ketQua = $this->cuocHenModel->huyLichBoiSinhVien($maCuocHen, $maSinhVien);

                if ($ketQua) {
                    flash('success', 'Hủy lịch hẹn thành công.');
                } else {
                    flash('danger', 'Không thể hủy lịch hẹn (Lịch đã hoàn thành hoặc không hợp lệ).');
                }
            } else {
                flash('danger', 'Dữ liệu không hợp lệ.');
            }
        }

        redirect('sinh-vien/phan-hoi');
    }
}