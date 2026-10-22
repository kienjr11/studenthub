<?php
declare(strict_types=1);

/**
 * Tài khoản của tôi: hồ sơ cá nhân, lĩnh vực tư vấn của giảng viên và đổi mật khẩu.
 * Mọi vai trò đã đăng nhập đều dùng được; mỗi người chỉ thao tác trên chính tài khoản của mình
 * (mã người dùng luôn lấy từ phiên đăng nhập, không bao giờ nhận từ form).
 */
final class DieuKhienTaiKhoan extends BoDieuKhien
{
    public function __construct()
    {
        XacThuc::requireLogin();
    }

    // ---------------- Hồ sơ ----------------

    public function hoSo(): void
    {
        $this->hienThiHoSo([], []);
    }

    private function hienThiHoSo(array $cu, array $loi): void
    {
        $toi = XacThuc::user();
        $laGiangVien = $toi['vai_tro'] === 'giang_vien';
        $this->view('taikhoan/hoso', [
            'title'       => 'Hồ sơ cá nhân',
            'toi'         => $toi,
            'cu'          => $cu,
            'loi'         => $loi,
            'dsChuyenMon' => $laGiangVien ? (new DanhMuc('specialties'))->all() : [],
            'daChon'      => $laGiangVien ? (new NguoiDung())->layChuyenMon((int) $toi['ma_nguoi_dung']) : [],
        ]);
    }

    /** Chỉ cho sửa họ tên. Email, vai trò, trạng thái, đơn vị do hệ thống/quản trị viên quản lý nên bị bỏ qua dù có gửi lên. */
    public function capNhatHoSo(): void
    {
        $v = new KiemTraDuLieu($_POST);
        $v->required('ho_ten', 'Họ tên')->length('ho_ten', 'Họ tên', 2, 100)->personName('ho_ten', 'Họ tên');
        if ($v->fails()) {
            $this->hienThiHoSo(['ho_ten' => $v->value('ho_ten')], $v->errors());
            return;
        }
        (new NguoiDung())->capNhatHoTen((int) XacThuc::user()['ma_nguoi_dung'], $v->value('ho_ten'));
        flash('success', 'Đã cập nhật hồ sơ.');
        redirect('tai-khoan/ho-so');
    }

    /** Giảng viên chọn lĩnh vực tư vấn từ danh mục do quản trị viên tạo. */
    public function capNhatChuyenMon(): void
    {
        $toi = XacThuc::user();
        if ($toi['vai_tro'] !== 'giang_vien') {
            abort(403, 'Chỉ giảng viên mới có lĩnh vực tư vấn.');
        }
        $hopLe = array_map('intval', array_column((new DanhMuc('specialties'))->all(), 'ma'));
        $gui = $_POST['chuyen_mon'] ?? [];
        $gui = is_array($gui) ? array_map('intval', array_filter($gui, 'is_scalar')) : [];
        $chon = array_values(array_unique(array_intersect($gui, $hopLe)));   // bỏ mã không tồn tại hoặc không hợp lệ

        (new NguoiDung())->datChuyenMon((int) $toi['ma_nguoi_dung'], $chon);
        flash('success', 'Đã cập nhật lĩnh vực tư vấn.');
        redirect('tai-khoan/ho-so');
    }

    // ---------------- Đổi mật khẩu ----------------

    public function formDoiMatKhau(): void
    {
        $this->hienThiDoiMatKhau([]);
    }

    private function hienThiDoiMatKhau(array $loi): void
    {
        $this->view('taikhoan/doimatkhau', ['title' => 'Đổi mật khẩu', 'loi' => $loi]);
    }

    public function doiMatKhau(): void
    {
        $toi = XacThuc::user();
        $id = (int) $toi['ma_nguoi_dung'];
        $nguoiDung = new NguoiDung();
        $toiDa = (int) config('security.max_failed_logins', 5);
        $phutKhoa = (int) config('security.lock_minutes', 10);

        $cu = (string) ($_POST['mat_khau_hien_tai'] ?? '');
        $moi = (string) ($_POST['mat_khau_moi'] ?? '');

        $v = new KiemTraDuLieu($_POST);
        if ($cu === '') {
            $v->add('mat_khau_hien_tai', 'Vui lòng nhập mật khẩu hiện tại.');
        }
        $v->password('mat_khau_moi', (int) config('security.min_password_length', 8))
          ->matches('nhap_lai_mat_khau_moi', 'mat_khau_moi', 'Mật khẩu nhập lại không khớp.');
        if (!$v->fails() && $moi === $cu) {
            $v->add('mat_khau_moi', 'Mật khẩu mới phải khác mật khẩu hiện tại.');
        }
        if ($v->fails()) {
            $this->hienThiDoiMatKhau($v->errors());
            return;
        }

        // Đang bị khóa tạm (có thể do người khác thử mật khẩu): không cho thử tiếp.
        if ($toi['khoa_den'] !== null && strtotime($toi['khoa_den']) > time()) {
            $v->add('mat_khau_hien_tai', 'Bạn đã nhập sai quá nhiều lần. Vui lòng thử lại sau ít phút.');
            $this->hienThiDoiMatKhau($v->errors());
            return;
        }

        $bam = $nguoiDung->layMatKhauBam($id);
        if ($bam === null || !password_verify($cu, $bam)) {
            // Dùng chung bộ đếm sai với đăng nhập để kẻ chiếm được phiên không dò được mật khẩu hiện tại.
            $seBiKhoa = ((int) $toi['so_lan_dang_nhap_sai'] + 1) >= $toiDa;
            $nguoiDung->recordFailedLogin($toi, $toiDa, $phutKhoa);
            if ($seBiKhoa) {
                XacThuc::logout();
                flash('danger', 'Bạn đã nhập sai mật khẩu hiện tại quá nhiều lần. Tài khoản bị khóa tạm thời, vui lòng thử lại sau.');
                redirect('auth/login');
            }
            $v->add('mat_khau_hien_tai', 'Mật khẩu hiện tại không đúng.');
            $this->hienThiDoiMatKhau($v->errors());
            return;
        }

        $bamMoi = password_hash($moi, PASSWORD_DEFAULT);
        $nguoiDung->capNhatMatKhau($id, $bamMoi);
        XacThuc::lamMoiPhien($bamMoi);   // đổi session id; các phiên khác của tài khoản này bị vô hiệu
        flash('success', 'Đã đổi mật khẩu. Các thiết bị khác đang đăng nhập đã bị đăng xuất.');
        redirect('tai-khoan/doi-mat-khau');
    }
}
