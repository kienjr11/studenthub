<?php
/** @var string $type @var array $items */
$label = Catalog::LABELS[$type];
$usedText = $type === 'departments' ? 'tài khoản' : 'giảng viên';
?>
<div class="container py-4">
    <h1 class="h3 mb-3">Danh mục đơn vị và chuyên môn</h1>

    <ul class="nav nav-tabs mb-4">
        <?php foreach (Catalog::LABELS as $key => $l): ?>
            <li class="nav-item"><a class="nav-link<?= $key === $type ? ' active' : '' ?>" href="<?= e(url('admin/catalog', ['type' => $key])) ?>"><?= e($l) ?></a></li>
        <?php endforeach; ?>
    </ul>

    <form method="post" action="<?= e(url('admin/catalog-save')) ?>" class="row g-2 mb-4">
        <?= csrf_field() ?>
        <input type="hidden" name="type" value="<?= e($type) ?>">
        <input type="hidden" name="id" value="0">
        <div class="col-md-4"><input type="text" class="form-control" name="name" placeholder="Tên <?= e(mb_strtolower($label)) ?> mới" maxlength="100" required aria-label="Tên <?= e(mb_strtolower($label)) ?> mới"></div>
        <div class="col-md-6"><input type="text" class="form-control" name="description" placeholder="Mô tả ngắn (không bắt buộc)" maxlength="255" aria-label="Mô tả"></div>
        <div class="col-md-2"><button class="btn btn-primary w-100" type="submit">Thêm <?= e(mb_strtolower($label)) ?></button></div>
    </form>

    <div class="table-responsive">
        <table class="table align-middle app-table">
            <thead><tr><th>Tên</th><th>Mô tả</th><th>Đang dùng</th><th>Thao tác</th></tr></thead>
            <tbody>
            <?php if (!$items): ?>
                <tr><td colspan="4" class="text-center text-muted py-4">Chưa có <?= e(mb_strtolower($label)) ?> nào. Hãy thêm mục đầu tiên ở biểu mẫu phía trên.</td></tr>
            <?php endif; ?>
            <?php foreach ($items as $it): $fid = 'f-' . (int) $it['ma']; ?>
                <tr>
                    <td><input type="text" class="form-control form-control-sm" name="name" form="<?= e($fid) ?>" value="<?= e($it['ten']) ?>" maxlength="100" required aria-label="Tên"></td>
                    <td><input type="text" class="form-control form-control-sm" name="description" form="<?= e($fid) ?>" value="<?= e($it['mo_ta'] ?? '') ?>" maxlength="255" aria-label="Mô tả"></td>
                    <td class="text-nowrap"><?= e($it['so_dang_dung']) ?> <?= e($usedText) ?></td>
                    <td class="text-nowrap">
                        <form id="<?= e($fid) ?>" method="post" action="<?= e(url('admin/catalog-save')) ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="type" value="<?= e($type) ?>">
                            <input type="hidden" name="id" value="<?= e($it['ma']) ?>">
                            <button class="btn btn-sm btn-outline-primary" type="submit">Lưu</button>
                        </form>
                        <form method="post" action="<?= e(url('admin/catalog-delete')) ?>" class="d-inline" data-confirm="Xóa <?= e(mb_strtolower($label)) ?> này?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="type" value="<?= e($type) ?>">
                            <input type="hidden" name="id" value="<?= e($it['ma']) ?>">
                            <button class="btn btn-sm btn-outline-danger" type="submit" <?= (int) $it['so_dang_dung'] > 0 ? 'disabled title="Đang được sử dụng, không thể xóa"' : '' ?>>Xóa</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
