<?php
declare(strict_types=1);

/** Module Hồ sơ & Chuyên môn: hồ sơ cá nhân (giảng viên), danh sách + chi tiết giảng viên (mọi người dùng đã đăng nhập). */
final class DieuKhienHoSoGiangVien extends BoDieuKhien
{
    private const PER_PAGE = 9;

    // ---- Giảng viên tự quản lý hồ sơ ----

    public function profile(): void
    {
        XacThuc::requireRole('giang_vien');
        $profile = (new HoSoGiangVien())->find((int) XacThuc::id());
        $this->renderForm($profile, [], [
            'ho_ten' => $profile['ho_ten'], 'ma_don_vi' => (string) $profile['ma_don_vi'], 'hoc_ham' => (string) $profile['hoc_ham'],
            'hoc_vi' => (string) $profile['hoc_vi'], 'so_dien_thoai' => (string) $profile['so_dien_thoai'],
            'phong_lam_viec' => (string) $profile['phong_lam_viec'], 'gioi_thieu' => (string) $profile['gioi_thieu'],
            'chuyen_mon' => array_map('intval', array_column($profile['chuyen_mon'], 'ma_chuyen_mon')),
        ]);
    }

    public function saveProfile(): void
    {
        XacThuc::requireRole('giang_vien');
        $id = (int) XacThuc::id();
        $model = new HoSoGiangVien();

        $v = new KiemTraDuLieu($_POST);
        $v->required('ho_ten', 'Họ tên')->length('ho_ten', 'Họ tên', 2, 100)->personName('ho_ten', 'Họ tên');
        $v->in('hoc_ham', 'Học hàm', ['', ...array_keys(HoSoGiangVien::HOC_HAM)]);
        $v->in('hoc_vi', 'Học vị', ['', ...array_keys(HoSoGiangVien::HOC_VI)]);
        $v->length('phong_lam_viec', 'Phòng làm việc', 0, 100)->length('gioi_thieu', 'Giới thiệu', 0, 1000);

        $phone = preg_replace('/[\s.\-]/', '', $v->value('so_dien_thoai')) ?? '';
        if ($phone !== '' && !preg_match('/^(?:\+84|0)\d{9,10}$/', $phone)) {
            $v->add('so_dien_thoai', 'Số điện thoại không hợp lệ (ví dụ 0901234567 hoặc +84901234567).');
        }

        $deptId = filter_var($v->value('ma_don_vi'), FILTER_VALIDATE_INT);
        if ($deptId === false || (new DanhMuc('departments'))->find($deptId) === null) {
            $v->add('ma_don_vi', 'Vui lòng chọn đơn vị công tác hợp lệ.');
        }

        // Lĩnh vực tư vấn: chỉ nhận id có thật trong bảng chuyen_mon (chống sửa HTML gửi id bậy).
        $valid = array_map('intval', array_column((new DanhMuc('specialties'))->all(), 'ma'));
        $posted = is_array($_POST['chuyen_mon'] ?? null) ? array_map('intval', $_POST['chuyen_mon']) : [];
        $specIds = array_values(array_unique(array_intersect($posted, $valid)));
        if ($specIds === []) {
            $v->add('chuyen_mon', 'Hãy chọn ít nhất một lĩnh vực tư vấn.');
        }

        if ($v->fails()) {
            http_response_code(422);
            $this->renderForm($model->find($id), $v->errors(), [
                'ho_ten' => $v->value('ho_ten'), 'ma_don_vi' => $v->value('ma_don_vi'), 'hoc_ham' => $v->value('hoc_ham'),
                'hoc_vi' => $v->value('hoc_vi'), 'so_dien_thoai' => $v->value('so_dien_thoai'),
                'phong_lam_viec' => $v->value('phong_lam_viec'), 'gioi_thieu' => $v->value('gioi_thieu'), 'chuyen_mon' => $specIds,
            ]);
            return;
        }

        $model->save($id, [
            'ho_ten'         => $v->value('ho_ten'),
            'ma_don_vi'      => (int) $deptId,
            'hoc_ham'        => $v->value('hoc_ham') ?: null,
            'hoc_vi'         => $v->value('hoc_vi') ?: null,
            'so_dien_thoai'  => $phone ?: null,
            'phong_lam_viec' => $v->value('phong_lam_viec') ?: null,
            'gioi_thieu'     => $v->value('gioi_thieu') ?: null,
        ], $specIds);
        flash('success', 'Đã cập nhật hồ sơ của bạn.');
        redirect('giang-vien/ho-so');
    }

    private function renderForm(array $profile, array $errors, array $old): void
    {
        $this->view('hoso/ho_so', [
            'title'       => 'Hồ sơ giảng viên',
            'profile'     => $profile,
            'errors'      => $errors,
            'old'         => $old,
            'departments' => (new DanhMuc('departments'))->all(),
            'specialties' => (new DanhMuc('specialties'))->all(),
        ]);
    }

    // ---- Sinh viên (và mọi người dùng đã đăng nhập) tìm giảng viên ----

    public function directory(): void
    {
        XacThuc::requireLogin();
        $filters = [
            'q'    => mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100),
            'dept' => max(0, (int) ($_GET['dept'] ?? 0)),
            'spec' => max(0, (int) ($_GET['spec'] ?? 0)),
        ];
        $model = new HoSoGiangVien();
        // Chọn khoa thì ô chuyên môn chỉ liệt kê chuyên môn có giảng viên của khoa đó
        $specialties = $filters['dept'] > 0 ? $model->specialtiesOfDepartment($filters['dept']) : (new DanhMuc('specialties'))->all();
        $specialtyIds = array_map('intval', array_column($specialties, 'ma'));
        if ($filters['spec'] > 0 && !in_array($filters['spec'], $specialtyIds, true)) {
            $filters['spec'] = 0;   // chuyên môn đang chọn không thuộc khoa mới -> bỏ lọc chuyên môn
        }
        $total = $model->countSearch($filters);
        $pages = max(1, (int) ceil($total / self::PER_PAGE));
        $page = min(max(1, (int) ($_GET['page'] ?? 1)), $pages);

        $this->view('hoso/danh_sach', [
            'title'       => 'Tìm giảng viên',
            'rows'        => $model->search($filters, self::PER_PAGE, ($page - 1) * self::PER_PAGE),
            'filters'     => $filters,
            'page'        => $page,
            'pages'       => $pages,
            'total'       => $total,
            'departments' => (new DanhMuc('departments'))->all(),
            'specialties' => $specialties,
        ]);
    }

    public function detail(): void
    {
        XacThuc::requireLogin();
        $profile = (new HoSoGiangVien())->find((int) ($_GET['id'] ?? 0), true);
        if ($profile === null) {
            abort(404, 'Không tìm thấy giảng viên này.');
        }
        $this->view('hoso/chi_tiet', ['title' => lecturer_title($profile), 'profile' => $profile]);
    }
}