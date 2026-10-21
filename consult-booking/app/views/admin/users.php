<?php
/** @var array $rows @var array $filters @var int $page @var int $pages @var int $total @var string $ret @var array $departments @var ?int $meId */
$roles = ['student' => 'Sinh viên', 'lecturer' => 'Giảng viên', 'admin' => 'Quản trị viên'];
$statuses = ['active' => 'Hoạt động', 'pending' => 'Chờ duyệt', 'locked' => 'Đã khóa'];
$pageUrl = static fn (int $p): string => url('admin/users', array_filter($filters + ['page' => $p > 1 ? $p : ''], static fn ($v) => $v !== ''));
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-end mb-3">
        <h1 class="h3 mb-0">Quản lý tài khoản</h1>
        <span class="text-muted"><?= e($total) ?> tài khoản</span>
    </div>

    <form method="get" action="<?= e(url()) ?>" class="row g-2 mb-3">
        <input type="hidden" name="r" value="admin/users">
        <div class="col-md-5"><input type="search" class="form-control" name="q" value="<?= e($filters['q']) ?>" placeholder="Tìm theo họ tên hoặc email" maxlength="100" aria-label="Tìm kiếm"></div>
        <div class="col-6 col-md-2">
            <select class="form-select" name="role" aria-label="Lọc theo vai trò">
                <option value="">Mọi vai trò</option>
                <?php foreach ($roles as $k => $l): ?><option value="<?= e($k) ?>" <?= $filters['role'] === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <select class="form-select" name="status" aria-label="Lọc theo trạng thái">
                <option value="">Mọi trạng thái</option>
                <?php foreach ($statuses as $k => $l): ?><option value="<?= e($k) ?>" <?= $filters['status'] === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-primary" type="submit">Lọc</button>
            <a class="btn btn-outline-secondary" href="<?= e(url('admin/users')) ?>">Xóa lọc</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table align-middle app-table">
            <thead><tr><th>Người dùng</th><th>Đơn vị</th><th>Vai trò</th><th>Trạng thái</th><th>Đăng nhập gần nhất</th><th>Thao tác</th></tr></thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Không có tài khoản nào khớp bộ lọc.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $u):
                [$stText, $stColor] = status_label($u['trang_thai']);
                $isMe = (int) $u['ma_nguoi_dung'] === $meId;
                $tempLock = $u['khoa_den'] && strtotime($u['khoa_den']) > time();
            ?>
                <tr>
                    <td><div class="d-flex align-items-center gap-2"><span class="avatar avatar-<?= e($u['vai_tro']) ?>" aria-hidden="true"><?= e(initial($u['ho_ten'])) ?></span><div><div class="fw-semibold"><?= e($u['ho_ten']) ?></div><div class="small text-muted"><?= e($u['email']) ?></div></div></div></td>
                    <td><?= e($u['ten_don_vi'] ?? '—') ?></td>
                    <td><?= e(role_label($u['vai_tro'])) ?></td>
                    <td>
                        <span class="badge text-bg-<?= e($stColor) ?>"><?= e($stText) ?></span>
                        <?php if ($tempLock): ?><div class="small text-muted">Tạm khóa đến <?= e(date('H:i d/m', strtotime($u['khoa_den']))) ?></div><?php endif; ?>
                    </td>
                    <td class="small"><?= $u['dang_nhap_lan_cuoi'] ? e(date('d/m/Y H:i', strtotime($u['dang_nhap_lan_cuoi']))) : 'Chưa đăng nhập' ?></td>
                    <td>
                        <?php if ($isMe): ?>
                            <span class="small text-muted">Tài khoản của bạn</span>
                        <?php else: ?>
                            <form method="post" action="<?= e(url('admin/user-status')) ?>" class="d-inline"
                                  <?= $u['trang_thai'] === 'active' ? 'data-confirm="Khóa tài khoản này? Người dùng sẽ bị đăng xuất ngay."' : '' ?>>
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= e($u['ma_nguoi_dung']) ?>">
                                <input type="hidden" name="ret" value="<?= e($ret) ?>">
                                <?php if ($u['trang_thai'] === 'pending'): ?>
                                    <input type="hidden" name="action" value="approve"><button class="btn btn-sm btn-success" type="submit">Phê duyệt</button>
                                <?php elseif ($u['trang_thai'] === 'locked'): ?>
                                    <input type="hidden" name="action" value="unlock"><button class="btn btn-sm btn-outline-success" type="submit">Mở khóa</button>
                                <?php else: ?>
                                    <input type="hidden" name="action" value="lock"><button class="btn btn-sm btn-outline-danger" type="submit">Khóa</button>
                                <?php endif; ?>
                            </form>
                            <details class="role-edit d-inline-block ms-1">
                                <summary class="btn btn-sm btn-outline-secondary">Phân quyền</summary>
                                <form method="post" action="<?= e(url('admin/user-role')) ?>" class="role-edit-form">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= e($u['ma_nguoi_dung']) ?>">
                                    <input type="hidden" name="ret" value="<?= e($ret) ?>">
                                    <label class="form-label small mb-1">Vai trò</label>
                                    <select class="form-select form-select-sm mb-2" name="role">
                                        <?php foreach ($roles as $k => $l): ?><option value="<?= e($k) ?>" <?= $u['vai_tro'] === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
                                    </select>
                                    <label class="form-label small mb-1">Đơn vị (bắt buộc với giảng viên)</label>
                                    <select class="form-select form-select-sm mb-2" name="department_id">
                                        <option value="">Không có</option>
                                        <?php foreach ($departments as $d): ?><option value="<?= e($d['ma']) ?>" <?= (int) $u['ma_don_vi'] === (int) $d['ma'] ? 'selected' : '' ?>><?= e($d['ten']) ?></option><?php endforeach; ?>
                                    </select>
                                    <button class="btn btn-sm btn-primary w-100" type="submit">Lưu thay đổi</button>
                                </form>
                            </details>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pages > 1): ?>
        <nav aria-label="Phân trang">
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
