<?php
declare(strict_types=1);

final class DieuKhienTrangChu extends BoDieuKhien
{
    public function index(): void
    {
        $departments = new DanhMuc('departments');
        $specialties = new DanhMuc('specialties');
        $this->view('trangchu/trangchu', [
            'title'       => 'Trang chủ',
            'stats'       => (new NguoiDung())->stats(),
            'departments' => $departments->all(),
            'specialties' => $specialties->all(),
        ]);
    }
}
