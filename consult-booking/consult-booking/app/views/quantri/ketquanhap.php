<?php /** @var array $created @var array $errors */ ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-end mb-3">
        <h1 class="h3 mb-0 page-title">Kết quả nhập tài khoản</h1>
        <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('admin/users')) ?>">&larr; Về danh sách tài khoản</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3"><div class="card"><div class="card-body py-3"><div class="small text-muted">Đã tạo</div><div class="h4 mb-0 text-success"><?= e(count($created)) ?></div></div></div></div>
        <div class="col-6 col-md-3"><div class="card"><div class="card-body py-3"><div class="small text-muted">Bị bỏ qua (lỗi)</div><div class="h4 mb-0 <?= $errors ? 'text-danger' : '' ?>"><?= e(count($errors)) ?></div></div></div></div>
    </div>

    <?php if ($created): ?>
        <div class="alert alert-warning d-flex flex-wrap justify-content-between align-items-center gap-2" role="alert">
            <div>
                <strong>Lưu lại mật khẩu tạm ngay bây giờ.</strong> Mật khẩu chỉ lưu trong phiên làm việc này (tối đa 30 phút) và không xem lại được sau đó.
                Hãy tải file, gửi cho từng người và nhắc họ vào "Đổi mật khẩu" sau khi đăng nhập.
            </div>
            <a class="btn btn-primary btn-sm" href="<?= e(url('admin/user-import-download')) ?>">Tải CSV tài khoản + mật khẩu tạm</a>
        </div>

        <h2 class="h5">Tài khoản đã tạo</h2>
        <div class="table-responsive mb-4">
            <table class="table table-sm align-middle app-table">
                <thead><tr><th>Họ tên</th><th>Email</th><th>Vai trò</th><th>Đơn vị</th><th>Mật khẩu tạm</th></tr></thead>
                <tbody>
                <?php foreach ($created as $c): ?>
                    <tr>
                        <td><?= e($c['ho_ten']) ?></td>
                        <td><?= e($c['email']) ?></td>
                        <td><?= e(role_label($c['vai_tro'])) ?></td>
                        <td><?= e($c['don_vi'] !== '' ? $c['don_vi'] : '—') ?></td>
                        <td><code><?= e($c['mat_khau']) ?></code><?= $c['tu_tao'] ? '' : ' <span class="small text-muted">(từ file)</span>' ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <h2 class="h5">Dòng bị bỏ qua</h2>
        <p class="small text-muted">Sửa các dòng này trong file rồi nhập lại (chỉ cần giữ lại những dòng lỗi).</p>
        <div class="table-responsive">
            <table class="table table-sm align-middle app-table">
                <thead><tr><th>Dòng</th><th>Họ tên</th><th>Email</th><th>Lý do</th></tr></thead>
                <tbody>
                <?php foreach ($errors as $er): ?>
                    <tr>
                        <td><?= e($er['dong']) ?></td>
                        <td><?= e($er['ho_ten'] !== '' ? $er['ho_ten'] : '—') ?></td>
                        <td><?= e($er['email'] !== '' ? $er['email'] : '—') ?></td>
                        <td class="text-danger"><?= e($er['ly_do']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <a class="btn btn-outline-primary mt-3" href="<?= e(url('admin/user-import')) ?>">Nhập thêm file khác</a>
</div>