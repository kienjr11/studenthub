<?php
declare(strict_types=1);

final class DieuKhienQuanTri extends BoDieuKhien
{
    private const PER_PAGE = 10;
    private const ROLES = ['sinh_vien', 'giang_vien', 'quan_tri'];
    private const STATUSES = ['hoat_dong', 'cho_duyet', 'da_khoa'];
    private const IMPORT_MAX_ROWS = 200;          // bcrypt chậm (~0,05–0,1 s/tài khoản) nên giới hạn mỗi lần nhập
    private const IMPORT_MAX_BYTES = 1048576;     // 1 MB
    private const IMPORT_TTL = 1800;              // giữ kết quả (có mật khẩu tạm) trong phiên tối đa 30 phút
    private const ROLE_ALIASES = [
        'sinh_vien' => 'sinh_vien', 'sinh viên' => 'sinh_vien', 'sinh vien' => 'sinh_vien', 'sv' => 'sinh_vien',
        'giang_vien' => 'giang_vien', 'giảng viên' => 'giang_vien', 'giang vien' => 'giang_vien', 'gv' => 'giang_vien',
    ];

    public function __construct()
    {
        XacThuc::requireRole('quan_tri');   // mọi action trong controller này chỉ dành cho admin
    }

    public function dashboard(): void
    {
        $users = new NguoiDung();
        $cuocHenModel = new CuocHen();
        [$tuNgay, $denNgay] = CuocHen::khoangNgayThongKe(
            is_string($_GET['tu_ngay'] ?? null) ? $_GET['tu_ngay'] : null,
            is_string($_GET['den_ngay'] ?? null) ? $_GET['den_ngay'] : null
        );

        $this->view('quantri/bangdieukhien', [
            'title'          => 'Bảng điều khiển quản trị',
            'stats'          => $users->stats(),
            'pending'        => $users->pendingLecturers(5),
            'deptCount'      => (new DanhMuc('departments'))->count(),
            'specCount'      => (new DanhMuc('specialties'))->count(),
            'thongKeCuocHen' => $cuocHenModel->thongKeTongQuanAdmin(),
            'thongKeNgay'    => $cuocHenModel->thongKeTheoNgay($tuNgay, $denNgay),
            'tuNgay'         => $tuNgay,
            'denNgay'        => $denNgay,
        ]);
    }

    public function users(): void
    {
        $role = (string) ($_GET['role'] ?? '');
        $status = (string) ($_GET['status'] ?? '');
        $filters = [
            'q'      => mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100),
            'role'   => in_array($role, self::ROLES, true) ? $role : '',
            'status' => in_array($status, self::STATUSES, true) ? $status : '',
        ];

        $users = new NguoiDung();
        $total = $users->countSearch($filters);
        $pages = max(1, (int) ceil($total / self::PER_PAGE));
        $page = min(max(1, (int) ($_GET['page'] ?? 1)), $pages);

        $this->view('quantri/quanlynguoidung', [
            'title'       => 'Quản lý tài khoản',
            'rows'        => $users->search($filters, self::PER_PAGE, ($page - 1) * self::PER_PAGE),
            'filters'     => $filters,
            'page'        => $page,
            'pages'       => $pages,
            'total'       => $total,
            'ret'         => http_build_query(array_filter($filters + ['page' => $page > 1 ? $page : ''], static fn ($v) => $v !== '')),
            'departments' => (new DanhMuc('departments'))->all(),
            'meId'        => XacThuc::id(),
        ]);
    }

    /** Form admin tạo tài khoản sinh viên / giảng viên. */
    public function createForm(): void
    {
        $this->renderCreate([], []);
    }

    /** Lưu tài khoản mới do admin tạo. Tài khoản hoạt động ngay (không cần phê duyệt). */
    public function store(): void
    {
        $v = new KiemTraDuLieu($_POST);
        $v->required('full_name', 'Họ tên')->length('full_name', 'Họ tên', 2, 100)->personName('full_name', 'Họ tên')
          ->required('email', 'Email')->email('email')->length('email', 'Email', 5, 190)
          ->in('role', 'Vai trò', ['sinh_vien', 'giang_vien'])
          ->password('password', (int) config('security.min_password_length', 8))
          ->matches('password_confirm', 'password', 'Mật khẩu xác nhận không khớp.');

        $role = $v->value('role');
        $email = mb_strtolower($v->value('email'));
        $users = new NguoiDung();

        $departmentId = null;
        if ($role === 'giang_vien') {
            $id = filter_var($v->value('department_id'), FILTER_VALIDATE_INT);
            if ($id === false || (new DanhMuc('departments'))->find($id) === null) {
                $v->add('department_id', 'Vui lòng chọn đơn vị công tác hợp lệ.');
            } else {
                $departmentId = $id;
            }
        }
        if (!$v->fails() && $users->emailExists($email)) {
            $v->add('email', 'Email này đã được sử dụng.');
        }

        if (!$v->fails()) {
            try {
                $users->create([
                    'ho_ten'       => $v->value('full_name'),
                    'email'        => $email,
                    'mat_khau_bam' => password_hash((string) $_POST['password'], PASSWORD_DEFAULT),
                    'vai_tro'      => $role,
                    'trang_thai'   => 'hoat_dong',
                    'ma_don_vi'    => $departmentId,
                ]);
                flash('success', 'Đã tạo tài khoản ' . role_label($role) . " {$email}. Hãy gửi mật khẩu tạm cho người dùng và nhắc họ đổi mật khẩu sau khi đăng nhập.");
                redirect('admin/users');
            } catch (PDOException $e) {
                if ($e->getCode() !== '23000') {   // 23000 = trùng email do 2 request đồng thời
                    throw $e;
                }
                $v->add('email', 'Email này đã được sử dụng.');
            }
        }
        $this->renderCreate(array_diff_key($_POST, ['password' => 1, 'password_confirm' => 1, '_csrf' => 1]), $v->errors());
    }

    private function renderCreate(array $old, array $errors): void
    {
        $this->view('quantri/themnguoidung', [
            'title'       => 'Thêm tài khoản',
            'old'         => $old,
            'errors'      => $errors,
            'departments' => (new DanhMuc('departments'))->all(),
        ]);
    }

    // ---- Nhập hàng loạt từ file CSV ----

    public function importForm(): void
    {
        $this->view('quantri/nhapnguoidung', ['title' => 'Nhập tài khoản từ file CSV', 'maxRows' => self::IMPORT_MAX_ROWS]);
    }

    /** Tải file CSV mẫu (UTF-8 có BOM để Excel đọc đúng tiếng Việt). */
    public function importTemplate(): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="mau-nhap-tai-khoan.csv"');
        echo "\xEF\xBB\xBF";
        $out = fopen('php://output', 'w');
        fputcsv($out, ['ho_ten', 'email', 'vai_tro', 'don_vi', 'mat_khau'], ',', '"', '');
        fputcsv($out, ['Nguyễn Văn An', 'sv.an@truong.edu.vn', 'sinh_vien', '', ''], ',', '"', '');
        fputcsv($out, ['Trần Thị Bình', 'gv.binh@truong.edu.vn', 'giang_vien', 'Khoa Công nghệ thông tin', ''], ',', '"', '');
        exit;
    }

    /** Đọc file CSV, tạo từng tài khoản; dòng lỗi được bỏ qua và báo lại, không làm hỏng cả file. */
    public function import(): void
    {
        $f = $_FILES['file'] ?? null;
        if (!is_array($f) || ($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file((string) $f['tmp_name'])) {
            flash('danger', 'Vui lòng chọn file CSV hợp lệ (tối đa 1 MB).');
            redirect('admin/user-import');
        }
        if ((int) $f['size'] > self::IMPORT_MAX_BYTES) {
            flash('danger', 'File quá lớn (tối đa 1 MB).');
            redirect('admin/user-import');
        }
        $defaultRole = $this->input('default_role');
        if (!in_array($defaultRole, ['sinh_vien', 'giang_vien'], true)) {
            $defaultRole = 'sinh_vien';
        }

        [$rows, $loi] = $this->parseCsv((string) file_get_contents((string) $f['tmp_name']));
        if ($loi !== null) {
            flash('danger', $loi);
            redirect('admin/user-import');
        }

        @set_time_limit(180);
        $users = new NguoiDung();
        $deptByName = [];
        foreach ((new DanhMuc('departments'))->all() as $d) {
            $deptByName[mb_strtolower(trim((string) $d['ten']))] = (int) $d['ma'];
        }
        $minLen = (int) config('security.min_password_length', 8);
        $seen = [];
        $created = [];
        $errors = [];

        foreach ($rows as $line => $r) {
            $name = trim((string) ($r['ho_ten'] ?? ''));
            $email = mb_strtolower(trim((string) ($r['email'] ?? '')));
            $roleRaw = mb_strtolower(trim((string) ($r['vai_tro'] ?? '')));
            $role = $roleRaw === '' ? $defaultRole : (self::ROLE_ALIASES[$roleRaw] ?? '');
            $deptName = trim((string) ($r['don_vi'] ?? ''));
            $pwd = trim((string) ($r['mat_khau'] ?? ''));
            $generated = $pwd === '';
            if ($generated) {
                $pwd = $this->randomPassword();
            }

            $v = new KiemTraDuLieu(['full_name' => $name, 'email' => $email, 'role' => $role, 'password' => $pwd]);
            $v->required('full_name', 'Họ tên')->length('full_name', 'Họ tên', 2, 100)->personName('full_name', 'Họ tên')
              ->required('email', 'Email')->email('email')->length('email', 'Email', 5, 190)
              ->in('role', 'Vai trò', ['sinh_vien', 'giang_vien'])
              ->password('password', $minLen);

            $departmentId = null;
            if ($role === 'giang_vien') {
                $departmentId = $deptByName[mb_strtolower($deptName)] ?? null;
                if ($departmentId === null) {
                    $v->add('department_id', $deptName === ''
                        ? 'Giảng viên phải có đơn vị (cột don_vi).'
                        : "Không tìm thấy đơn vị \"{$deptName}\" (phải trùng tên trong Danh mục đơn vị).");
                }
            }
            if (!$v->fails()) {
                if (isset($seen[$email])) {
                    $v->add('email', 'Email bị trùng với dòng khác trong file.');
                } elseif ($users->emailExists($email)) {
                    $v->add('email', 'Email này đã được sử dụng.');
                }
            }

            if (!$v->fails()) {
                try {
                    $users->create([
                        'ho_ten' => $name, 'email' => $email,
                        'mat_khau_bam' => password_hash($pwd, PASSWORD_DEFAULT),
                        'vai_tro' => $role, 'trang_thai' => 'hoat_dong', 'ma_don_vi' => $departmentId,
                    ]);
                    $seen[$email] = true;
                    $created[] = ['ho_ten' => $name, 'email' => $email, 'vai_tro' => $role, 'don_vi' => $role === 'giang_vien' ? $deptName : '', 'mat_khau' => $pwd, 'tu_tao' => $generated];
                    continue;
                } catch (PDOException $e) {
                    if ($e->getCode() !== '23000') {
                        throw $e;
                    }
                    $v->add('email', 'Email này đã được sử dụng.');
                }
            }
            $errors[] = ['dong' => $line, 'ho_ten' => $name, 'email' => $email, 'ly_do' => (string) (array_values($v->errors())[0] ?? 'Dữ liệu không hợp lệ.')];
        }

        $_SESSION['_import_result'] = ['time' => time(), 'created' => $created, 'errors' => $errors];
        redirect('admin/user-import-result');
    }

    public function importResult(): void
    {
        $res = $this->importSession();
        header('Cache-Control: no-store');   // trang có mật khẩu tạm, không để trình duyệt lưu cache
        $this->view('quantri/ketquanhap', ['title' => 'Kết quả nhập tài khoản', 'created' => $res['created'], 'errors' => $res['errors']]);
    }

    /** Tải CSV gồm email + mật khẩu tạm của các tài khoản vừa tạo để phát cho từng người. */
    public function importDownload(): void
    {
        $res = $this->importSession();
        if (!$res['created']) {
            flash('warning', 'Không có tài khoản nào để tải.');
            redirect('admin/users');
        }
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="tai-khoan-moi-' . date('Ymd-His') . '.csv"');
        header('Cache-Control: no-store');
        echo "\xEF\xBB\xBF";
        $out = fopen('php://output', 'w');
        fputcsv($out, ['ho_ten', 'email', 'vai_tro', 'don_vi', 'mat_khau_tam'], ',', '"', '');
        foreach ($res['created'] as $c) {
            fputcsv($out, [$this->csvSafe($c['ho_ten']), $c['email'], role_label($c['vai_tro']), $this->csvSafe($c['don_vi']), $c['mat_khau']], ',', '"', '');
        }
        exit;
    }

    /** Kết quả nhập gần nhất trong phiên (hết hạn sau 30 phút); không có thì quay về danh sách. */
    private function importSession(): array
    {
        $res = $_SESSION['_import_result'] ?? null;
        if (!is_array($res) || (int) ($res['time'] ?? 0) < time() - self::IMPORT_TTL) {
            unset($_SESSION['_import_result']);
            flash('warning', 'Không còn kết quả nhập nào (có thể đã hết hạn). Hãy nhập lại nếu cần.');
            redirect('admin/users');
        }
        return $res;
    }

    /** @return array{0: array<int, array<string,string>>, 1: ?string} [các dòng dữ liệu theo số dòng trong file, thông báo lỗi] */
    private function parseCsv(string $raw): array
    {
        if (str_starts_with($raw, "\xEF\xBB\xBF")) {
            $raw = substr($raw, 3);
        }
        if (!mb_check_encoding($raw, 'UTF-8')) {
            $raw = mb_convert_encoding($raw, 'UTF-8', 'Windows-1258');   // CSV "ANSI" của Excel tiếng Việt
        }
        $firstLine = (string) strtok($raw, "\n");
        $delim = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';

        $h = fopen('php://temp', 'r+');
        fwrite($h, $raw);
        rewind($h);

        $alias = [
            'ho_ten' => 'ho_ten', 'ho_va_ten' => 'ho_ten', 'họ_tên' => 'ho_ten', 'họ_và_tên' => 'ho_ten',
            'email' => 'email',
            'vai_tro' => 'vai_tro', 'vai_trò' => 'vai_tro',
            'don_vi' => 'don_vi', 'đơn_vị' => 'don_vi',
            'mat_khau' => 'mat_khau', 'mật_khẩu' => 'mat_khau',
        ];
        $header = fgetcsv($h, 0, $delim, '"', '');
        $cols = [];
        foreach (($header ?: []) as $i => $name) {
            $key = (string) preg_replace('/\s+/u', '_', mb_strtolower(trim((string) $name)));
            if (isset($alias[$key])) {
                $cols[$i] = $alias[$key];
            }
        }
        if (!in_array('ho_ten', $cols, true) || !in_array('email', $cols, true)) {
            return [[], 'Dòng đầu tiên của file phải có ít nhất 2 cột: ho_ten và email (tải file mẫu để xem định dạng).'];
        }

        $rows = [];
        $line = 1;
        while (($rec = fgetcsv($h, 0, $delim, '"', '')) !== false) {
            $line++;
            if ($rec === [null] || count(array_filter($rec, static fn ($c) => trim((string) $c) !== '')) === 0) {
                continue;   // dòng trống
            }
            if (count($rows) >= self::IMPORT_MAX_ROWS) {
                return [[], 'File có quá ' . self::IMPORT_MAX_ROWS . ' dòng dữ liệu. Hãy chia nhỏ file rồi nhập từng lần.'];
            }
            $row = [];
            foreach ($cols as $i => $key) {
                $row[$key] = (string) ($rec[$i] ?? '');
            }
            $rows[$line] = $row;
        }
        fclose($h);
        if (!$rows) {
            return [[], 'File không có dòng dữ liệu nào.'];
        }
        return [$rows, null];
    }

    /** Mật khẩu ngẫu nhiên 10 ký tự (bỏ ký tự dễ nhầm như 0/O, 1/l/I), luôn có cả chữ và số. */
    private function randomPassword(int $len = 10): string
    {
        $letters = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
        $digits = '23456789';
        $all = $letters . $digits;
        $chars = [$letters[random_int(0, strlen($letters) - 1)], $digits[random_int(0, strlen($digits) - 1)]];
        while (count($chars) < $len) {
            $chars[] = $all[random_int(0, strlen($all) - 1)];
        }
        for ($i = count($chars) - 1; $i > 0; $i--) {   // xáo trộn bằng random_int (an toàn mật mã)
            $j = random_int(0, $i);
            [$chars[$i], $chars[$j]] = [$chars[$j], $chars[$i]];
        }
        return implode('', $chars);
    }

    /** Chống CSV injection: ô bắt đầu bằng = + - @ sẽ bị Excel hiểu là công thức. */
    private function csvSafe(string $v): string
    {
        return $v !== '' && strpbrk($v[0], "=+-@\t\r") !== false ? "'" . $v : $v;
    }

    /** Khóa / mở khóa / phê duyệt tài khoản. */
    public function updateStatus(): void
    {
        $target = $this->targetUser();
        $action = $this->input('action');

        if ((int) $target['ma_nguoi_dung'] === XacThuc::id()) {
            flash('danger', 'Bạn không thể thay đổi trạng thái của chính mình.');
            $this->back();
        }

        $new = match ($action) {
            'lock'            => 'da_khoa',
            'unlock', 'approve' => 'hoat_dong',
            default           => null,
        };
        if ($new === null) {
            abort(404, 'Thao tác không hợp lệ.');
        }
        (new NguoiDung())->setStatus((int) $target['ma_nguoi_dung'], $new);
        $soDong = $action === 'lock' ? (new CuocHen())->dongLichKhiKhoaTaiKhoan((int) $target['ma_nguoi_dung']) : 0;
        flash('success', match ($action) {
            'lock'    => "Đã khóa tài khoản {$target['email']}." . ($soDong > 0 ? " Đã đóng $soDong lịch hẹn sắp diễn ra liên quan." : ''),
            'approve' => "Đã phê duyệt tài khoản {$target['email']}.",
            default   => "Đã mở khóa tài khoản {$target['email']}.",
        });
        $this->back();
    }

    /** Phân quyền: đổi vai trò (+ đơn vị nếu là giảng viên). */
    public function updateRole(): void
    {
        $target = $this->targetUser();
        $users = new NguoiDung();
        $role = $this->input('role');

        if ((int) $target['ma_nguoi_dung'] === XacThuc::id()) {
            flash('danger', 'Bạn không thể tự đổi vai trò của chính mình.');
            $this->back();
        }
        if (!in_array($role, self::ROLES, true)) {
            flash('danger', 'Vai trò không hợp lệ.');
            $this->back();
        }

        $departmentId = null;
        if ($role === 'giang_vien') {
            $id = filter_var($this->input('department_id'), FILTER_VALIDATE_INT);
            if ($id === false || (new DanhMuc('departments'))->find($id) === null) {
                flash('danger', 'Giảng viên phải thuộc một đơn vị hợp lệ.');
                $this->back();
            }
            $departmentId = $id;
        }
        if ($target['vai_tro'] === 'giang_vien' && $role !== 'giang_vien' && $users->hasSlots((int) $target['ma_nguoi_dung'])) {
            flash('danger', 'Giảng viên này đã có khung giờ tư vấn nên không thể đổi sang vai trò khác.');
            $this->back();
        }
        if ($role !== $target['vai_tro'] && (new CuocHen())->demLichDangHieuLucCuaNguoiDung((int) $target['ma_nguoi_dung']) > 0) {
            flash('danger', 'Tài khoản này còn lịch hẹn chờ duyệt / đã duyệt chưa diễn ra nên chưa thể đổi vai trò.');
            $this->back();
        }

        $users->setRoleAndDepartment((int) $target['ma_nguoi_dung'], $role, $departmentId);
        flash('success', "Đã đổi vai trò của {$target['email']} thành " . role_label($role) . '.');
        $this->back();
    }

    /** Quản lý danh sách cuộc hẹn toàn hệ thống dành cho Admin */
    public function lichHen(): void
    {
        $trangThai = is_string($_GET['trang_thai'] ?? null) ? $_GET['trang_thai'] : null;
        $trang     = max(1, (int)($_GET['trang'] ?? 1));

        // Gọi qua Model CuocHen để lấy dữ liệu an toàn, không gọi trực tiếp kết nối CSDL tại Controller
        $cuocHenModel = new CuocHen();
        $data = $cuocHenModel->layDanhSachChoAdmin($trangThai, $trang, 15);

        $this->view('quantri/danh_sach_hen', [
            'title'          => 'Quản lý danh sách cuộc hẹn toàn hệ thống',
            'danh_sach'      => $data['danh_sach'] ?? [],
            'tong_trang'     => $data['tong_trang'] ?? 1,
            'trang_hien_tai' => $data['trang_hien_tai'] ?? 1,
            'trang_thai_loc' => $trangThai
        ]);
    }

    private function targetUser(): array
    {
        $user = (new NguoiDung())->find((int) ($_POST['id'] ?? 0));
        if ($user === null) {
            abort(404, 'Không tìm thấy tài khoản.');
        }
        return $user;
    }

    /** Quay lại đúng trang/bộ lọc trước đó. Chỉ nhận các khóa trong danh sách trắng nên không có open redirect. */
    private function back(): never
    {
        if (($_POST['back'] ?? '') === 'dashboard') {
            redirect('admin/dashboard');
        }
        parse_str((string) ($_POST['ret'] ?? ''), $ret);
        $keep = array_filter(
            array_intersect_key($ret, array_flip(['q', 'role', 'status', 'page'])),
            static fn ($v) => is_string($v) && $v !== ''
        );
        redirect('admin/users', $keep);
    }
}