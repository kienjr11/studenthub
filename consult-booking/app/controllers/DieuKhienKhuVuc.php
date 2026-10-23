<?php
declare(strict_types=1);

/** Cổng vào theo vai trò. Hai trang sinh viên/giảng viên là khung để các module khác gắn chức năng vào. */
final class DieuKhienKhuVuc extends BoDieuKhien
{
    public function index(): void
    {
        XacThuc::requireLogin();
        redirect(match (XacThuc::user()['vai_tro']) {
            'quan_tri'   => 'admin/dashboard',
            'giang_vien' => 'dashboard/lecturer',
            default      => 'dashboard/student',
        });
    }

    public function student(): void
    {
        XacThuc::requireRole('sinh_vien');
        $this->view('khuvuc/trangcanhan', ['title' => 'Khu vực sinh viên', 'portal' => 'sinh_vien', 'me' => XacThuc::user()]);
    }

    public function lecturer(): void
    {
        XacThuc::requireRole('giang_vien');
        $id = (int) XacThuc::id();
        $model = new HoSoGiangVien();
        $profile = $model->find($id);
        $this->view('khuvuc/giangvien/bang_dieu_khien', [
            'title'        => 'Bảng điều khiển giảng viên',
            'me'           => XacThuc::user(),
            'profile'      => $profile,
            'completeness' => HoSoGiangVien::completeness($profile),
            'stats'        => $model->stats($id),
            'upcoming'     => $model->upcoming($id, 5),
        ]);
    }

    /** Hiển thị trang đặt lịch hẹn tư vấn cho sinh viên */
    public function datLich(): void
    {
        XacThuc::requireRole('sinh_vien');
        $hoSoModel = new HoSoGiangVien();
        $danhSachGiangVien = $hoSoModel->layTatCaGiangVienKemThongTin();

        $this->view('khuvuc/sinhvien/dat_lich', [
            'title'              => 'Đặt Lịch Hẹn Tư Vấn',
            'me'                 => XacThuc::user(),
            'danhSachGiangVien' => $danhSachGiangVien,
        ]);
    }
}