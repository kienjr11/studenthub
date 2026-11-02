<?php /** @var int $maxRows */ ?>
<div class="container py-4 container-760">
    <div class="d-flex justify-content-between align-items-end mb-3">
        <h1 class="h3 mb-0 page-title">Nhập tài khoản từ file CSV</h1>
        <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('admin/users')) ?>">&larr; Quay lại danh sách</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h6">Định dạng file</h2>
            <p class="small mb-2">Dòng đầu tiên là tên cột. Mỗi dòng sau là một người (tối đa <?= e($maxRows) ?> dòng mỗi lần).</p>
            <table class="table table-sm small mb-2">
                <thead><tr><th>Cột</th><th>Bắt buộc</th><th>Giá trị</th></tr></thead>
                <tbody>
                    <tr><td><code>ho_ten</code></td><td>Có</td><td>Họ và tên</td></tr>
                    <tr><td><code>email</code></td><td>Có</td><td>Email đăng nhập, không được trùng</td></tr>
                    <tr><td><code>vai_tro</code></td><td>Không</td><td><code>sinh_vien</code> hoặc <code>giang_vien</code>; để trống thì dùng vai trò mặc định bên dưới</td></tr>
                    <tr><td><code>don_vi</code></td><td>Với giảng viên</td><td>Tên đơn vị, phải trùng với tên trong Danh mục đơn vị</td></tr>
                    <tr><td><code>mat_khau</code></td><td>Không</td><td>Để trống thì hệ thống tự tạo mật khẩu ngẫu nhiên riêng cho từng người</td></tr>
                </tbody>
            </table>
            <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('admin/user-import-template')) ?>">Tải file CSV mẫu</a>
            <span class="small text-muted ms-2">Lưu từ Excel bằng "CSV UTF-8 (Comma delimited)".</span>
        </div>
    </div>

    <form method="post" action="<?= e(url('admin/user-import')) ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label for="file" class="form-label">File CSV</label>
            <input class="form-control" type="file" id="file" name="file" accept=".csv,text/csv" required>
        </div>
        <div class="mb-4">
            <label class="form-label d-block">Vai trò mặc định (cho dòng không có cột <code>vai_tro</code>)</label>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="default_role" id="dr-sv" value="sinh_vien" checked>
                <label class="form-check-label" for="dr-sv">Sinh viên</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="default_role" id="dr-gv" value="giang_vien">
                <label class="form-check-label" for="dr-gv">Giảng viên</label>
            </div>
        </div>
        <button class="btn btn-primary" type="submit">Nhập tài khoản</button>
    </form>
</div>