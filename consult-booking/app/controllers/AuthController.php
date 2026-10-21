<?php
declare(strict_types=1);

final class AuthController extends Controller
{
    /** Hash giả để thời gian phản hồi khi email không tồn tại gần giống khi sai mật khẩu (chống dò email qua timing). */
    private const DUMMY_HASH = '$2y$10$ZqYShCGpHNth4ed9k1FzQuBdsOrrrvcbt0zwLBFkmu2HB1sGzt8fG';

    public function showLogin(): void
    {
        if (Auth::check()) {
            redirect('dashboard/index');
        }
        $this->view('auth/login', ['title' => 'Đăng nhập', 'old' => [], 'error' => null]);
    }

    public function login(): void
    {
        $email = mb_strtolower($this->input('email'));
        $error = $this->attempt($email, (string) ($_POST['password'] ?? ''));
        if ($error === null) {
            redirect('dashboard/index');
        }
        $this->view('auth/login', ['title' => 'Đăng nhập', 'old' => ['email' => $email], 'error' => $error]);
    }

    /** @return ?string thông báo lỗi, hoặc null nếu đăng nhập thành công */
    private function attempt(string $email, string $password): ?string
    {
        if ($email === '' || $password === '') {
            return 'Vui lòng nhập email và mật khẩu.';
        }
        $users = new User();
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
        if ($user['trang_thai'] === 'pending') {
            return 'Tài khoản giảng viên của bạn đang chờ quản trị viên phê duyệt.';
        }
        if ($user['trang_thai'] === 'locked') {
            return 'Tài khoản đã bị khóa. Vui lòng liên hệ quản trị viên.';
        }

        if (password_needs_rehash($user['mat_khau_bam'], PASSWORD_DEFAULT)) {
            $users->updatePasswordHash((int) $user['ma_nguoi_dung'], password_hash($password, PASSWORD_DEFAULT));
        }
        $users->markLoggedIn((int) $user['ma_nguoi_dung']);
        Auth::login($user);
        return null;
    }

    public function showRegister(): void
    {
        if (Auth::check()) {
            redirect('dashboard/index');
        }
        $this->renderRegister([], []);
    }

    public function register(): void
    {
        $v = new Validator($_POST);
        $v->required('full_name', 'Họ tên')->length('full_name', 'Họ tên', 2, 100)->personName('full_name', 'Họ tên')
          ->required('email', 'Email')->email('email')->length('email', 'Email', 5, 190)
          ->in('role', 'Vai trò', ['student', 'lecturer'])   // KHÔNG cho tự đăng ký admin
          ->password('password', (int) config('security.min_password_length', 8))
          ->matches('password_confirm', 'password', 'Mật khẩu xác nhận không khớp.');

        $role = $v->value('role');
        $email = mb_strtolower($v->value('email'));
        $users = new User();

        $departmentId = null;
        if ($role === 'lecturer') {
            $id = filter_var($v->value('department_id'), FILTER_VALIDATE_INT);
            if ($id === false || (new Catalog('departments'))->find($id) === null) {
                $v->add('department_id', 'Vui lòng chọn đơn vị công tác hợp lệ.');
            } else {
                $departmentId = $id;
            }
        }
        if (!$v->fails() && $users->emailExists($email)) {
            $v->add('email', 'Email này đã được đăng ký.');
        }

        if (!$v->fails()) {
            try {
                $users->create([
                    'ho_ten'       => $v->value('full_name'),
                    'email'        => $email,
                    'mat_khau_bam' => password_hash((string) $_POST['password'], PASSWORD_DEFAULT),
                    'vai_tro'      => $role,
                    'trang_thai'   => $role === 'lecturer' ? 'pending' : 'active',   // giảng viên phải được admin duyệt
                    'ma_don_vi'    => $departmentId,
                ]);
                flash('success', $role === 'lecturer'
                    ? 'Đăng ký thành công. Tài khoản giảng viên cần được quản trị viên phê duyệt trước khi đăng nhập.'
                    : 'Đăng ký thành công. Bạn có thể đăng nhập ngay.');
                redirect('auth/login');
            } catch (PDOException $e) {
                if ($e->getCode() !== '23000') {   // 23000 = trùng email do 2 request đồng thời
                    throw $e;
                }
                $v->add('email', 'Email này đã được đăng ký.');
            }
        }
        $this->renderRegister(array_diff_key($_POST, ['password' => 1, 'password_confirm' => 1, '_csrf' => 1]), $v->errors());
    }

    private function renderRegister(array $old, array $errors): void
    {
        $this->view('auth/register', [
            'title' => 'Đăng ký', 'old' => $old, 'errors' => $errors,
            'departments' => (new Catalog('departments'))->all(),
        ]);
    }

    public function logout(): void
    {
        Auth::logout();
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
        $exists = (new User())->emailExists($email);
        $this->json([
            'ok' => true, 'available' => !$exists,
            'message' => $exists ? 'Email này đã được sử dụng.' : 'Email có thể sử dụng.',
        ]);
    }
}
