<?php
declare(strict_types=1);

final class DieuKhienQuanTri extends BoDieuKhien
{
    private const PER_PAGE = 10;
    private const ROLES = ['sinh_vien', 'giang_vien', 'quan_tri'];
    private const STATUSES = ['hoat_dong', 'cho_duyet', 'da_khoa'];

    public function __construct()
    {
        XacThuc::requireRole('quan_tri');   // mọi action trong controller này chỉ dành cho admin
    }

    public function dashboard(): void
    {
        $users = new NguoiDung();
        $cuocHenModel = new CuocHen();

        $this->view('quantri/bangdieukhien', [
            'title'          => 'Bảng điều khiển quản trị',
            'stats'          => $users->stats(),
            'pending'        => $users->pendingLecturers(5),
            'deptCount'      => (new DanhMuc('departments'))->count(),
            'specCount'      => (new DanhMuc('specialties'))->count(),
            'thongKeCuocHen' => $cuocHenModel->thongKeTongQuanAdmin(),
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
        flash('success', match ($action) {
            'lock'    => "Đã khóa tài khoản {$target['email']}.",
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

        $users->setRoleAndDepartment((int) $target['ma_nguoi_dung'], $role, $departmentId);
        flash('success', "Đã đổi vai trò của {$target['email']} thành " . role_label($role) . '.');
        $this->back();
    }

    /** Quản lý danh sách cuộc hẹn toàn hệ thống dành cho Admin */
    public function lichHen(): void
    {
        $trangThai = $_GET['trang_thai'] ?? null;
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