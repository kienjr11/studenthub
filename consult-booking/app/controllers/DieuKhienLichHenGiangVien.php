<?php
declare(strict_types=1);

class DieuKhienLichHenGiangVien extends BoDieuKhien
{
    private CuocHen $cuocHenModel;

    public function __construct()
    {
        // Kiểm tra quyền giảng viên
        if (method_exists('XacThuc', 'requireRole')) {
            XacThuc::requireRole('giang_vien');
        } elseif (method_exists('XacThuc', 'yeuCauVaiTro')) {
            XacThuc::yeuCauVaiTro('giang_vien');
        }
        
        $this->cuocHenModel = new CuocHen();
    }

    /**
     * Hiển thị danh sách cuộc hẹn của giảng viên
     */
    public function index(): void
    {
        $user = XacThuc::user();
        $maGiangVien = $user['ma_nguoi_dung'] ?? null;
        $trangThai = $_GET['trang_thai'] ?? null;
        $trang = max(1, (int)($_GET['trang'] ?? 1));

        $data = $this->cuocHenModel->layDanhSachChoGiangVien((int)$maGiangVien, $trangThai, $trang);

        // Sử dụng GiaoDien::render chuẩn của framework
        GiaoDien::render('khuvuc/giangvien/danh_sach_hen', [
            'tieu_de'        => 'Quản lý lịch hẹn tư vấn',
            'danh_sach'      => $data['danh_sach'] ?? [],
            'tong_trang'     => $data['tong_trang'] ?? 1,
            'trang_hien_tai' => $data['trang_hien_tai'] ?? 1,
            'trang_thai_loc' => $trangThai
        ]);
    }

    /**
     * Xử lý cập nhật trạng thái (Duyệt / Từ chối / Hoàn thành)
     */
    public function capNhatTrangThai(): void
    {
        $maCuocHen = (int)($_POST['ma_cuoc_hen'] ?? 0);
        $hanhDong = $_POST['hanh_dong'] ?? '';
        $user = XacThuc::user();
        $maGiangVien = $user['ma_nguoi_dung'] ?? null;

        $cuocHen = $this->cuocHenModel->timTheoIdVaGiangVien($maCuocHen, (int)$maGiangVien);
        if (!$cuocHen) {
            flash('error', 'Cuộc hẹn không tồn tại hoặc bạn không có quyền thao tác.');
            redirect(url('giang-vien/lich-hen'));
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
            default:
                flash('error', 'Hành động không hợp lệ.');
                break;
        }

        redirect(url('giang-vien/lich-hen'));
    }

    /**
     * Lưu ghi chú phản hồi
     */
    public function luuPhanHoi(): void
    {
        $maCuocHen = (int)($_POST['ma_cuoc_hen'] ?? 0);
        $ghiChu = trim($_POST['ghi_chu_giang_vien'] ?? '');
        $user = XacThuc::user();
        $maGiangVien = $user['ma_nguoi_dung'] ?? null;

        $cuocHen = $this->cuocHenModel->timTheoIdVaGiangVien($maCuocHen, (int)$maGiangVien);
        if (!$cuocHen) {
            flash('error', 'Cuộc hẹn không tồn tại.');
            redirect(url('giang-vien/lich-hen'));
            return;
        }

        $this->cuocHenModel->capNhatGhiChuGiangVien($maCuocHen, $ghiChu);
        flash('success', 'Đã lưu ghi chú và phản hồi buổi tư vấn.');
        redirect(url('giang-vien/lich-hen'));
    }
}