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
     * Hiển thị giao diện Đặt lịch hẹn dành cho Sinh viên
     */
    public function hienThiFormDatLich(): void
    {
        XacThuc::requireRole('sinh_vien');

        $this->view('khuvuc/sinhvien/dat_lich', [
            'title'             => 'Đặt lịch hẹn tư vấn',
            'scripts'           => ['js/Dat_lich_khung_gio.js'],
            'danhSachGiangVien' => (new NguoiDung())->layGiangVienChoDatLich(),
            'cu'                => $_SESSION['_dat_lich_cu'] ?? $this->giaTriDienSan(),
        ]);
        unset($_SESSION['_dat_lich_cu']);
    }

    /** Giá trị điền sẵn cho form đặt lịch từ URL (ví dụ khi bấm 1 khung giờ trong nút "Lịch rảnh" ở trang Tìm giảng viên). */
    private function giaTriDienSan(): array
    {
        $cu = [
            'giang_vien_id' => (int) ($_GET['giang_vien_id'] ?? 0),
            'ma_khung_gio'  => (int) ($_GET['ma_khung_gio'] ?? 0),
        ];
        // Có khung giờ được chọn sẵn -> điền luôn ngày/giờ của khung giờ đó vào form (nếu còn ở tương lai)
        if ($cu['giang_vien_id'] > 0 && $cu['ma_khung_gio'] > 0) {
            $kg = (new KhungGioModel())->timTheoId($cu['ma_khung_gio'], $cu['giang_vien_id']);
            if ($kg && strtotime($kg['bat_dau']) > time()) {
                $cu['ngay_hen']      = date('Y-m-d', strtotime($kg['bat_dau']));
                $cu['gio_bat_dau']   = date('H:i', strtotime($kg['bat_dau']));
                $cu['gio_ket_thuc']  = date('H:i', strtotime($kg['ket_thuc']));
            } else {
                $cu['ma_khung_gio'] = 0;
            }
        }
        return $cu;
    }

    /**
     * Xử lý lưu yêu cầu Đặt lịch hẹn của Sinh viên
     */
    public function xuLyDatLich(): void
    {
        XacThuc::requireRole('sinh_vien');

        $giangVienId = (int) ($_POST['giang_vien_id'] ?? 0);
        $maKhungGio  = (int) ($_POST['ma_khung_gio'] ?? 0);   // > 0: sinh viên bấm 1 khung giờ rảnh có sẵn của giảng viên
        $ngayHen     = $this->input('ngay_hen');
        $gioBatDau   = $this->input('gio_bat_dau');
        $gioKetThuc  = $this->input('gio_ket_thuc');
        $chuDe       = mb_substr($this->input('chu_de'), 0, 255);   // cột chu_de là VARCHAR(255)
        $monHoc      = $this->input('mon_hoc');                    // tên môn sinh viên chọn trên form

        $loi = function (string $msg) use ($giangVienId, $maKhungGio, $ngayHen, $gioBatDau, $gioKetThuc, $chuDe): never {
            $_SESSION['_dat_lich_cu'] = [
                'giang_vien_id' => $giangVienId, 'ma_khung_gio' => $maKhungGio, 'ngay_hen' => $ngayHen,
                'gio_bat_dau' => $gioBatDau, 'gio_ket_thuc' => $gioKetThuc, 'chu_de' => $chuDe,
            ];
            flash('danger', $msg);
            redirect('sinh-vien/dat-lich');
        };

        $thieuGio = $maKhungGio <= 0 && ($ngayHen === '' || $gioBatDau === '' || $gioKetThuc === '');
        if ($giangVienId <= 0 || $chuDe === '' || $thieuGio) {
            $loi('Vui lòng nhập đầy đủ các thông tin bắt buộc.');
        }

        // Giảng viên phải tồn tại và đang hoạt động
        $hopLe = array_column((new NguoiDung())->layTheoVaiTro('giang_vien'), 'ma_nguoi_dung');
        if (!in_array((string) $giangVienId, array_map('strval', $hopLe), true)) {
            $loi('Giảng viên không hợp lệ.');
        }

        if ($maKhungGio > 0) {
            // Khung giờ phải đúng là của giảng viên đã chọn (chống sửa HTML gửi id bậy).
            $kgChon = (new KhungGioModel())->timTheoId($maKhungGio, $giangVienId);
            if ($kgChon === null) {
                $loi('Khung giờ không thuộc giảng viên đã chọn. Vui lòng chọn lại.');
            }
            // Sinh viên đã sửa ngày/giờ khác với khung giờ đang chọn -> hiểu là tự nhập giờ, bỏ qua khung giờ cũ
            if ($ngayHen !== '' && $gioBatDau !== '' && $gioKetThuc !== ''
                && ($ngayHen !== date('Y-m-d', strtotime($kgChon['bat_dau']))
                    || $gioBatDau !== date('H:i', strtotime($kgChon['bat_dau']))
                    || $gioKetThuc !== date('H:i', strtotime($kgChon['ket_thuc'])))) {
                $maKhungGio = 0;
            }
        }

        if ($maKhungGio > 0) {
            // Khung giờ giảng viên đã mở cho một môn thì sinh viên chỉ được đặt đúng môn đó.
            $monKhung = (new KhungGioModel())->layTenChuyenMonCuaKhungGio($maKhungGio);
            if ($monKhung !== null && $monHoc !== '' && $monHoc !== $monKhung) {
                $loi('Khung giờ này giảng viên mở cho môn "' . $monKhung . '", bạn không thể đặt môn "' . $monHoc . '". Vui lòng chọn đúng môn hoặc chọn khung giờ khác.');
            }
            // Đường 1: đặt vào khung giờ có sẵn
            try {
                $this->cuocHenModel->datVaoKhungGio((int) XacThuc::id(), $maKhungGio, $chuDe);
            } catch (DomainException $e) {
                $loi($e->getMessage());
            } catch (Throwable $e) {
                error_log('Đặt lịch thất bại: ' . $e->getMessage());
                $loi('Đã xảy ra lỗi, vui lòng thử lại.');
            }
        } else {
            // Đường 2: sinh viên tự nhập ngày giờ
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
        $ngay = $_GET['ngay'] ?? null; // Bắt thêm tham số ngày từ URL khi bấm vào ô "Lịch hẹn hôm nay"
        $trang = max(1, (int)($_GET['trang'] ?? 1));

        // Truyền thêm biến $ngay vào hàm layDanhSachChoGiangVien ở Model
        $data = $this->cuocHenModel->layDanhSachChoGiangVien((int)$maGiangVien, $trangThai, $trang, 10, $ngay);

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
                    try {
                        $this->cuocHenModel->duyetCuocHen($maCuocHen);   // kiểm tra còn chỗ rồi mới duyệt (duyệt xong chỗ mới bị trừ)
                        flash('success', 'Đã phê duyệt lịch hẹn thành công.');
                    } catch (DomainException $e) {
                        flash('danger', $e->getMessage());
                    }
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