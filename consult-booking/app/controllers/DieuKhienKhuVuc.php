<?php
declare(strict_types=1);

/** Cổng vào theo vai trò. Hai trang sinh viên/giảng viên là khung để các module khác gắn chức năng vào. */
final class DieuKhienKhuVuc extends BoDieuKhien
{
    public function index(): void
    {
        XacThuc::requireLogin();
        redirect(match (XacThuc::user()['vai_tro']) {
            'quan_tri'    => 'admin/dashboard',
            'giang_vien' => 'dashboard/lecturer',
            default    => 'dashboard/student',
        });
    }

    public function student(): void
    {
        XacThuc::requireRole('sinh_vien');
        $me = XacThuc::user();
        $dsHen = (new PhanHoi())->layDanhSachChoSinhVien((int) XacThuc::id());

        // Thống kê nhanh + các cuộc hẹn sắp tới (chờ duyệt / đã duyệt, chưa qua giờ), gần nhất trước
        $thongKe = ['cho_duyet' => 0, 'sap_toi' => 0, 'hoan_thanh' => 0, 'chua_danh_gia' => 0];
        $sapToi = [];
        $bayGio = time();
        foreach ($dsHen as $h) {
            $conHieuLuc = strtotime((string) $h['ket_thuc']) >= $bayGio;
            if ($h['trang_thai'] === 'cho_duyet') {
                $thongKe['cho_duyet']++;
            }
            if ($h['trang_thai'] === 'da_duyet' && $conHieuLuc) {
                $thongKe['sap_toi']++;
            }
            if ($h['trang_thai'] === 'hoan_thanh') {
                $thongKe['hoan_thanh']++;
                if ($h['diem_danh_gia'] === null) {
                    $thongKe['chua_danh_gia']++;
                }
            }
            if (in_array($h['trang_thai'], ['cho_duyet', 'da_duyet'], true) && $conHieuLuc) {
                $sapToi[] = $h;
            }
        }
        usort($sapToi, static fn (array $a, array $b): int => strcmp((string) $a['bat_dau'], (string) $b['bat_dau']));

        $this->view('khuvuc/sinhvien/bang_dieu_khien', [
            'title'    => 'Khu vực sinh viên',
            'me'       => $me,
            'stats'    => $thongKe,
            'upcoming' => array_slice($sapToi, 0, 5),
        ]);
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
}