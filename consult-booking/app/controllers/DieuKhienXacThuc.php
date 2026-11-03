<?php
declare(strict_types=1);

final class DieuKhienXacThuc extends BoDieuKhien
{
    /** Hash giả để thời gian phản hồi khi email không tồn tại gần giống khi sai mật khẩu (chống dò email qua timing). */
    private const DUMMY_HASH = '$2y$10$ZqYShCGpHNth4ed9k1FzQuBdsOrrrvcbt0zwLBFkmu2HB1sGzt8fG';

    public function showLogin(): void
    {
        if (XacThuc::check()) {
            redirect('dashboard/index');
        }
        $this->view('xacthuc/formdangnhap', ['title' => 'Đăng nhập', 'old' => [], 'error' => null]);
    }

    public function login(): void
    {
        $email = mb_strtolower($this->input('email'));
        $error = $this->attempt($email, (string) ($_POST['password'] ?? ''));
        if ($error === null) {
            redirect('dashboard/index');
        }
        $this->view('xacthuc/formdangnhap', ['title' => 'Đăng nhập', 'old' => ['email' => $email], 'error' => $error]);
    }

    /** @return ?string thông báo lỗi, hoặc null nếu đăng nhập thành công */
    private function attempt(string $email, string $password): ?string
    {
        if ($email === '' || $password === '') {
            return 'Vui lòng nhập email và mật khẩu.';
        }
        $users = new NguoiDung();
        $user = $users->findByEmail($email);

        if ($user === null) {
            password_verify($password, self::DUMMY_HASH);
            return 'Email hoặc mật khẩu không đúng.';
        }

        $lockedUntil = $user['khoa_den'] !== null ? strtotime($user['khoa_den']) : 0;
        if ($lockedUntil > time()) {
            $minutes = (int) ceil(($lockedUntil - time()) / 60);
            return "Đăng nhập sai quá nhiều lần. Vui lòng thử lại sau {$minutes} phút.";
        }

        if (!password_verify($password, $user['mat_khau_bam'])) {
            $users->recordFailedLogin($user, (int) config('security.max_failed_logins', 5), (int) config('security.lock_minutes', 10));
            return 'Email hoặc mật khẩu không đúng.';
        }

        // Chỉ tiết lộ trạng thái tài khoản SAU KHI đã nhập đúng mật khẩu.
        if ($user['trang_thai'] === 'cho_duyet') {
            return 'Tài khoản giảng viên của bạn đang chờ quản trị viên phê duyệt.';
        }
        if ($user['trang_thai'] === 'da_khoa') {
            return 'Tài khoản đã bị khóa. Vui lòng liên hệ quản trị viên.';
        }

        if (password_needs_rehash($user['mat_khau_bam'], PASSWORD_DEFAULT)) {
            $user['mat_khau_bam'] = password_hash($password, PASSWORD_DEFAULT);   // cập nhật cả bản sao để dấu vân tay phiên khớp
            $users->updatePasswordHash((int) $user['ma_nguoi_dung'], $user['mat_khau_bam']);
        }
        $users->markLoggedIn((int) $user['ma_nguoi_dung']);
        XacThuc::login($user);
        return null;
    }

    public function logout(): void
    {
        XacThuc::logout();
        flash('success', 'Bạn đã đăng xuất.');
        redirect('home/index');
    }

    /** Endpoint JSON cho Fetch API: kiểm tra nhanh email đã được đăng ký chưa. */
    public function checkEmail(): void
    {
        // Giới hạn 20 lượt/phút/phiên để hạn chế việc dò danh sách email.
        $now = time();
        $hits = array_values(array_filter($_SESSION['_email_hits'] ?? [], static fn ($t) => $t > $now - 60));
        if (count($hits) >= 20) {
            $this->json(['ok' => false, 'message' => 'Bạn thao tác quá nhanh, vui lòng thử lại sau.'], 429);
        }
        $hits[] = $now;
        $_SESSION['_email_hits'] = $hits;

        $email = mb_strtolower(trim((string) ($_GET['email'] ?? '')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(['ok' => true, 'available' => null, 'message' => 'Email chưa đúng định dạng.']);
        }
        $exists = (new NguoiDung())->emailExists($email);
        $this->json([
            'ok' => true, 'available' => !$exists,
            'message' => $exists ? 'Email này đã được sử dụng.' : 'Email có thể sử dụng.',
        ]);
    }
}
