<?php
declare(strict_types=1);

class DieuKhienPhanHoi extends BoDieuKhien
{
    private PhanHoi $phanHoiModel;

    public function __construct()
    {
        XacThuc::requireRole('sinh_vien');
        $this->phanHoiModel = new PhanHoi();
    }

    /**
     * Trang "Lịch hẹn & Đánh giá": liệt kê cuộc hẹn của sinh viên và cho phép gửi/sửa đánh giá.
     */
    public function index(): void
    {
        $maSinhVien = (int) XacThuc::id();
        $trangThai = (string) ($_GET['trang_thai'] ?? '');

        $this->view('khuvuc/sinhvien/lich_hen', [
            'title'          => 'Lịch hẹn & Đánh giá',
            'danh_sach'      => $this->phanHoiModel->layDanhSachChoSinhVien($maSinhVien, $trangThai !== '' ? $trangThai : null),
            'trang_thai_loc' => $trangThai,
        ]);
    }

    /**
     * Xử lý lưu đánh giá/phản hồi từ sinh viên
     */
    public function gui(): void
    {
        $maCuocHen = (int) ($_POST['ma_cuoc_hen'] ?? 0);
        $diemDanhGia = (int) ($_POST['diem_danh_gia'] ?? 0);
        $nhanXet = mb_substr($this->input('nhan_xet'), 0, 500);   // cột nhan_xet là VARCHAR(500)
        $maSinhVien = (int) XacThuc::id();

        if ($diemDanhGia < 1 || $diemDanhGia > 5) {
            flash('danger', 'Vui lòng chọn số sao từ 1 đến 5.');
            redirect('sinh-vien/phan-hoi');
        }

        // Cuộc hẹn phải thuộc về sinh viên này
        $cuocHen = $this->phanHoiModel->kiemTraQuyenSoHuu($maCuocHen, $maSinhVien);
        if (!$cuocHen) {
            flash('danger', 'Cuộc hẹn không tồn tại hoặc bạn không có quyền đánh giá.');
            redirect('sinh-vien/phan-hoi');
        }

        // Chặn ở server: chỉ cho đánh giá khi cuộc hẹn đã duyệt/hoàn thành (không tin vào việc view ẩn nút)
        if (!in_array($cuocHen['trang_thai'], PhanHoi::TRANG_THAI_DUOC_DANH_GIA, true)) {
            flash('warning', 'Chỉ có thể đánh giá cuộc hẹn đã được duyệt hoặc đã hoàn thành.');
            redirect('sinh-vien/phan-hoi');
        }

        $this->phanHoiModel->luuDanhGia($maCuocHen, $diemDanhGia, $nhanXet);

        flash('success', 'Cảm ơn bạn đã gửi đánh giá cho buổi tư vấn!');
        redirect('sinh-vien/phan-hoi');
    }
}
