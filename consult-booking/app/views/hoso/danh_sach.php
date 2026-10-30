<?php
/** @var array $rows @var array $filters @var int $page @var int $pages @var int $total @var array $departments @var array $specialties */
$nguoiXem = XacThuc::user();
$pageUrl = static fn (int $p): string => url('giang-vien/danh-sach', array_filter($filters + ['page' => $p > 1 ? $p : 0], static fn ($v) => $v !== '' && $v !== 0));
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-end mb-3">
        <h1 class="h3 mb-0 page-title">Tìm giảng viên</h1>
        <span class="text-muted"><?= e($total) ?> giảng viên</span>
    </div>

    <form method="get" action="<?= e(url()) ?>" class="row g-2 mb-4">
        <input type="hidden" name="r" value="giang-vien/danh-sach">
        <div class="col-md-4"><input type="search" class="form-control" name="q" value="<?= e($filters['q']) ?>" placeholder="Tìm theo tên giảng viên" maxlength="100" aria-label="Tìm theo tên"></div>
        <div class="col-6 col-md-3">
            <select class="form-select" name="dept" aria-label="Lọc theo khoa/đơn vị" data-autosubmit data-reset="spec">
                <option value="0">Mọi khoa / đơn vị</option>
                <?php foreach ($departments as $d): ?><option value="<?= e($d['ma']) ?>" <?= $filters['dept'] === (int) $d['ma'] ? 'selected' : '' ?>><?= e($d['ten']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-3">
            <select class="form-select" name="spec" aria-label="Lọc theo chuyên môn" data-autosubmit>
                <option value="0">Mọi chuyên môn</option>
                <?php foreach ($specialties as $s): ?><option value="<?= e($s['ma']) ?>" <?= $filters['spec'] === (int) $s['ma'] ? 'selected' : '' ?>><?= e($s['ten']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button class="btn btn-primary" type="submit">Tìm</button>
            <a class="btn btn-outline-secondary" href="<?= e(url('giang-vien/danh-sach')) ?>">Xóa lọc</a>
        </div>
    </form>

    <?php if (!$rows): ?>
        <p class="text-center text-muted py-5">Không tìm thấy giảng viên nào khớp bộ lọc.</p>
    <?php endif; ?>

    <div class="row g-3">
        <?php foreach ($rows as $r): ?>
            <div class="col-md-6 col-lg-4">
                <div class="role-block d-flex flex-column">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <?= lecturer_avatar($r) ?>
                        <div>
                            <div class="fw-semibold"><?= e(lecturer_title($r)) ?></div>
                            <div class="small text-muted"><?= e($r['ten_don_vi'] ?? 'Chưa gán đơn vị') ?></div>
                        </div>
                    </div>
                    <?php if ($r['phong_lam_viec']): ?><div class="small mb-2">Phòng: <?= e($r['phong_lam_viec']) ?></div><?php endif; ?>
                    <div class="d-flex flex-wrap gap-1 mb-3">
                        <?php foreach ($r['chuyen_mon'] as $cm): ?><span class="pill"><?= e($cm) ?></span><?php endforeach; ?>
                    </div>
                    <div class="d-flex gap-2 mt-auto">
                        <a class="btn btn-sm btn-outline-primary" href="<?= e(url('giang-vien/chi-tiet', ['id' => $r['ma_nguoi_dung']])) ?>">Xem hồ sơ</a>
                        <?php if (($nguoiXem['vai_tro'] ?? '') === 'sinh_vien'): ?>
                            <a class="btn btn-sm btn-primary" href="<?= e(url('sinh-vien/dat-lich', ['giang_vien_id' => $r['ma_nguoi_dung']])) ?>">Đặt lịch</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($pages > 1): ?>
        <nav class="mt-4" aria-label="Phân trang">
            <ul class="pagination pagination-sm justify-content-center">
                <li class="page-item<?= $page <= 1 ? ' disabled' : '' ?>"><a class="page-link" href="<?= e($pageUrl($page - 1)) ?>">Trước</a></li>
                <?php for ($p = max(1, $page - 2); $p <= min($pages, $page + 2); $p++): ?>
                    <li class="page-item<?= $p === $page ? ' active' : '' ?>"><a class="page-link" href="<?= e($pageUrl($p)) ?>"><?= e($p) ?></a></li>
                <?php endfor; ?>
                <li class="page-item<?= $page >= $pages ? ' disabled' : '' ?>"><a class="page-link" href="<?= e($pageUrl($page + 1)) ?>">Sau</a></li>
            </ul>
        </nav>
    <?php endif; ?>
</div>