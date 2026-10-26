<?php /** @var array $stats @var array $pending @var int $deptCount @var int $specCount @var array $thongKeCuocHen */ ?>
<div class="container py-4">
    <h1 class="h3 mb-4 page-title">Bảng điều khiển quản trị</h1>

    <!-- Thống kê tổng quan cuộc hẹn toàn hệ thống -->
    <div class="row mb-4">
        <div class="col-md-4 col-xl-2 mb-3">
            <div class="card bg-primary text-white shadow-sm h-100">
                <div class="card-body text-center p-3">
                    <h6 class="text-uppercase small font-weight-bold mb-1">Tổng cuộc hẹn</h6>
                    <h4 class="mb-0"><?= e($thongKeCuocHen['tong_so'] ?? 0) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2 mb-3">
            <div class="card bg-warning text-dark shadow-sm h-100">
                <div class="card-body text-center p-3">
                    <h6 class="text-uppercase small font-weight-bold mb-1">Chờ duyệt</h6>
                    <h4 class="mb-0"><?= e($thongKeCuocHen['cho_duyet'] ?? 0) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2 mb-3">
            <div class="card bg-success text-white shadow-sm h-100">
                <div class="card-body text-center p-3">
                    <h6 class="text-uppercase small font-weight-bold mb-1">Đã duyệt</h6>
                    <h4 class="mb-0"><?= e($thongKeCuocHen['da_duyet'] ?? 0) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2 mb-3">
            <div class="card bg-info text-white shadow-sm h-100">
                <div class="card-body text-center p-3">
                    <h6 class="text-uppercase small font-weight-bold mb-1">Hoàn thành</h6>
                    <h4 class="mb-0"><?= e($thongKeCuocHen['hoan_thanh'] ?? 0) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2 mb-3">
            <div class="card bg-danger text-white shadow-sm h-100">
                <div class="card-body text-center p-3">
                    <h6 class="text-uppercase small font-weight-bold mb-1">Từ chối</h6>
                    <h4 class="mb-0"><?= e($thongKeCuocHen['tu_choi'] ?? 0) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2 mb-3">
            <div class="card bg-secondary text-white shadow-sm h-100">
                <div class="card-body text-center p-3">
                    <h6 class="text-uppercase small font-weight-bold mb-1">Đã hủy</h6>
                    <h4 class="mb-0"><?= e($thongKeCuocHen['da_huy'] ?? 0) ?></h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <h2 class="h5">Giảng viên chờ phê duyệt</h2>
            <?php if ($pending): ?>
                <ul class="list-group">
                    <?php foreach ($pending as $u): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center gap-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="avatar avatar-giang_vien" aria-hidden="true"><?= e(initial($u['ho_ten'] ?? 'GV')) ?></span>
                                <div>
                                    <div class="fw-semibold"><?= e($u['ho_ten'] ?? 'Chưa cập nhật tên') ?></div>
                                    <div class="small text-muted"><?= e($u['email']) ?> – <?= e($u['ten_don_vi'] ?? 'Chưa chọn đơn vị') ?></div>
                                </div>
                            </div>
                            <form method="post" action="<?= e(url('admin/user-status')) ?>" class="m-0">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= e($u['ma_nguoi_dung']) ?>">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="back" value="dashboard">
                                <button class="btn btn-sm btn-success" type="submit">Phê duyệt</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php if ($stats['cho_duyet'] > count($pending)): ?>
                    <a class="d-inline-block mt-2" href="<?= e(url('admin/users', ['status' => 'cho_duyet'])) ?>">Xem tất cả <?= e($stats['cho_duyet']) ?> tài khoản đang chờ</a>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-muted">Không có giảng viên nào đang chờ duyệt.</p>
            <?php endif; ?>
        </div>

        <div class="col-lg-5">
            <h2 class="h5">Số liệu hiện tại</h2>
            <dl class="info-list">
                <div><dt>Tổng tài khoản</dt><dd><?= e($stats['tong']) ?></dd></div>
                <div><dt>Sinh viên</dt><dd><?= e($stats['sinh_vien']) ?></dd></div>
                <div><dt>Giảng viên (đang hoạt động / tổng)</dt><dd><?= e($stats['giang_vien_hoat_dong']) ?> / <?= e($stats['giang_vien']) ?></dd></div>
                <div><dt>Quản trị viên</dt><dd><?= e($stats['quan_tri']) ?></dd></div>
                <div><dt>Đang bị khóa</dt><dd><?= e($stats['bi_khoa']) ?></dd></div>
                <div><dt>Đơn vị</dt><dd><?= e($deptCount) ?></dd></div>
                <div><dt>Chuyên môn</dt><dd><?= e($specCount) ?></dd></div>
            </dl>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-primary" href="<?= e(url('admin/users')) ?>">Quản lý tài khoản</a>
                <a class="btn btn-outline-primary" href="<?= e(url('admin/catalog', ['type' => 'departments'])) ?>">Quản lý danh mục</a>
            </div>
        </div>
    </div>
</div>