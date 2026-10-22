<?php /** @var string $portal @var array $me */ ?>
<div class="container py-4">
    <div class="d-flex align-items-center gap-3 mb-4">
        <span class="avatar avatar-lg avatar-<?= e($me['vai_tro']) ?>" aria-hidden="true"><?= e(initial($me['ho_ten'])) ?></span>
        <div>
            <h1 class="h3 mb-0">Xin chào, <?= e($me['ho_ten']) ?></h1>
            <p class="text-muted mb-0">Bạn đang ở khu vực dành cho <?= e(mb_strtolower(role_label($portal))) ?>.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-5">
            <h2 class="h5">Thông tin tài khoản</h2>
            <dl class="info-list">
                <div><dt>Email</dt><dd><?= e($me['email']) ?></dd></div>
                <div><dt>Vai trò</dt><dd><?= e(role_label($me['vai_tro'])) ?></dd></div>
                <?php if ($me['vai_tro'] === 'giang_vien'): ?>
                    <div><dt>Đơn vị</dt><dd><?= e($me['ten_don_vi'] ?? 'Chưa gán đơn vị') ?></dd></div>
                <?php endif; ?>
                <div><dt>Đăng nhập gần nhất</dt><dd><?= $me['dang_nhap_lan_cuoi'] ? e(date('d/m/Y H:i', strtotime($me['dang_nhap_lan_cuoi']))) : 'Lần đầu' ?></dd></div>
            </dl>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-outline-primary btn-sm" href="<?= e(url('tai-khoan/ho-so')) ?>">Chỉnh sửa hồ sơ</a>
                <a class="btn btn-outline-secondary btn-sm" href="<?= e(url('tai-khoan/doi-mat-khau')) ?>">Đổi mật khẩu</a>
            </div>
        </div>
        <div class="col-md-7">
            <h2 class="h5">Chức năng</h2>
            <?php if ($portal === 'sinh_vien'): ?>
                <p>Tìm giảng viên, xem slot trống, đặt/hủy lịch và gửi phản hồi sẽ được gắn vào đây bởi các module đặt lịch.</p>
            <?php else: ?>
                <p>Quản lý khung giờ, duyệt/từ chối cuộc hẹn và xem thống kê sẽ được gắn vào đây bởi các module quản lý slot.</p>
            <?php endif; ?>
            <p class="text-muted mb-0">Trang này dùng để kiểm tra phân quyền: tài khoản không đúng vai trò sẽ nhận lỗi 403 khi truy cập.</p>
        </div>
    </div>
</div>
