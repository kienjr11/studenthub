<?php
/** 
 * @var string $title 
 * @var array $danh_sach 
 * @var int $tong_trang 
 * @var int $trang_hien_tai 
 * @var ?string $trang_thai_loc 
 */
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 page-title"><?= e($title) ?></h1>
        <a href="<?= e(url('admin/dashboard')) ?>" class="btn btn-outline-secondary btn-sm">Quay lại Bảng điều khiển</a>
    </div>

    <!-- Bộ lọc trạng thái nhanh -->
    <div class="mb-3 d-flex flex-wrap gap-2">
        <a href="<?= e(url('admin/lich-hen')) ?>" class="btn btn-sm <?= empty($trang_thai_loc) ? 'btn-primary' : 'btn-outline-primary' ?>">Tất cả</a>
        <a href="<?= e(url('admin/lich-hen', ['trang_thai' => 'cho_duyet'])) ?>" class="btn btn-sm <?= $trang_thai_loc === 'cho_duyet' ? 'btn-warning text-dark fw-bold' : 'btn-outline-warning text-warning' ?>">Chờ duyệt</a>
        <a href="<?= e(url('admin/lich-hen', ['trang_thai' => 'da_duyet'])) ?>" class="btn btn-sm <?= $trang_thai_loc === 'da_duyet' ? 'btn-success' : 'btn-outline-success' ?>">Đã duyệt</a>
        <a href="<?= e(url('admin/lich-hen', ['trang_thai' => 'hoan_thanh'])) ?>" class="btn btn-sm <?= $trang_thai_loc === 'hoan_thanh' ? 'btn-info text-white' : 'btn-outline-info' ?>">Hoàn thành</a>
        <a href="<?= e(url('admin/lich-hen', ['trang_thai' => 'tu_choi'])) ?>" class="btn btn-sm <?= $trang_thai_loc === 'tu_choi' ? 'btn-danger' : 'btn-outline-danger' ?>">Từ chối</a>
        <a href="<?= e(url('admin/lich-hen', ['trang_thai' => 'da_huy'])) ?>" class="btn btn-sm <?= $trang_thai_loc === 'da_huy' ? 'btn-secondary' : 'btn-outline-secondary' ?>">Đã hủy</a>
    </div>

    <!-- Bảng danh sách cuộc hẹn -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Mã</th>
                            <th>Sinh viên</th>
                            <th>Giảng viên</th>
                            <th>Chủ đề</th>
                            <th>Thời gian</th>
                            <th>Địa điểm</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($danh_sach)): ?>
                            <?php foreach ($danh_sach as $item): ?>
                                <tr>
                                    <td>#<?= e($item['ma_cuoc_hen']) ?></td>
                                    <td>
                                        <div class="fw-semibold"><?= e($item['ten_sinh_vien']) ?></div>
                                        <div class="small text-muted"><?= e($item['email_sinh_vien']) ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= e($item['ten_giang_vien']) ?></div>
                                        <div class="small text-muted"><?= e($item['email_giang_vien']) ?></div>
                                    </td>
                                    <td><?= e($item['chu_de']) ?></td>
                                    <td>
                                        <div><?= e(date('d/m/Y H:i', strtotime($item['bat_dau']))) ?></div>
                                        <small class="text-muted">đến <?= e(date('H:i', strtotime($item['ket_thuc']))) ?></small>
                                    </td>
                                    <td><?= e($item['dia_diem'] ?? 'Trực tuyến / Chưa cập nhật') ?></td>
                                    <td>
                                        <?php
                                            $badges = [
                                                'cho_duyet'  => ['warning text-dark', 'Chờ duyệt'],
                                                'da_duyet'   => ['success', 'Đã duyệt'],
                                                'hoan_thanh' => ['info', 'Hoàn thành'],
                                                'tu_choi'    => ['danger', 'Từ chối'],
                                                'da_huy'     => ['secondary', 'Đã hủy']
                                            ];
                                            $st = $badges[$item['trang_thai']] ?? ['light', $item['trang_thai']];
                                        ?>
                                        <span class="badge bg-<?= $st[0] ?>"><?= $st[1] ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Không tìm thấy cuộc hẹn nào.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Phân trang -->
    <?php if ($tong_trang > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php 
                    $queryParams = [];
                    if (!empty($trang_thai_loc)) $queryParams['trang_thai'] = $trang_thai_loc;
                ?>
                <?php for ($i = 1; $i <= $tong_trang; $i++): ?>
                    <li class="page-item <?= $i === $trang_hien_tai ? 'active' : '' ?>">
                        <a class="page-link" href="<?= e(url('admin/lich-hen') . '?' . http_build_query(array_merge($queryParams, ['trang' => $i]))) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>