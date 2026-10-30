<?php
/**
 * Trang "Lịch hẹn & Đánh giá" của sinh viên.
 * Biến đầu vào: $danh_sach (mảng cuộc hẹn), $trang_thai_loc (chuỗi)
 */
$danh_sach      = $danh_sach ?? [];
$trang_thai_loc = $trang_thai_loc ?? '';

$nhanTrangThai = [
    'cho_duyet'  => ['Chờ duyệt', 'warning text-dark'],
    'da_duyet'   => ['Đã duyệt', 'primary'],
    'hoan_thanh' => ['Hoàn thành', 'success'],
    'tu_choi'    => ['Đã từ chối', 'danger'],
    'da_huy'     => ['Đã hủy', 'secondary'],
];
?>
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 fw-bold mb-0">Lịch hẹn &amp; Đánh giá</h1>
    </div>

    <!-- Bộ lọc trạng thái -->
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body py-3">
            <form method="get" action="<?= e(url()) ?>" class="row g-2 align-items-center">
                <input type="hidden" name="r" value="sinh-vien/phan-hoi">
                <div class="col-auto">
                    <label class="form-label mb-0 fw-semibold" for="locTrangThai">Lọc theo trạng thái:</label>
                </div>
                <div class="col-auto">
                    <select id="locTrangThai" name="trang_thai" class="form-select form-select-sm">
                        <option value="">-- Tất cả trạng thái --</option>
                        <?php foreach ($nhanTrangThai as $gia_tri => [$nhan]): ?>
                            <option value="<?= e($gia_tri) ?>" <?= $trang_thai_loc === $gia_tri ? 'selected' : '' ?>><?= e($nhan) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary">Lọc</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Giảng viên</th>
                        <th>Môn</th>
                        <th>Thời gian &amp; Địa điểm</th>
                        <th>Chủ đề tư vấn</th>
                        <th>Trạng thái</th>
                        <th>Đánh giá của bạn</th>
                        <th class="text-end">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($danh_sach)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Bạn chưa có lịch hẹn nào phù hợp.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($danh_sach as $item): ?>
                        <?php [$nhan, $mau] = $nhanTrangThai[$item['trang_thai']] ?? [$item['trang_thai'], 'secondary']; ?>
                        <tr>
                            <td><strong><?= e($item['ten_giang_vien']) ?></strong></td>
                            <td><?= e($item['ten_chuyen_mon'] ?? '') !== '' ? e($item['ten_chuyen_mon']) : '—' ?></td>
                            <td>
                                <?= e(date('H:i d/m/Y', strtotime($item['bat_dau']))) ?>
                                – <?= e(date('H:i', strtotime($item['ket_thuc']))) ?><br>
                                <small class="text-muted"><?= e($item['dia_diem'] ?? 'Trực tuyến / Chưa có') ?></small>
                            </td>
                            <td><?= e($item['chu_de']) ?></td>
                            <td><span class="badge bg-<?= e($mau) ?>"><?= e($nhan) ?></span></td>
                            <td>
                                <?php if (!empty($item['diem_danh_gia'])): ?>
                                    <span class="text-warning" aria-label="<?= (int) $item['diem_danh_gia'] ?> trên 5 sao"><?= str_repeat('★', (int) $item['diem_danh_gia']) . str_repeat('☆', 5 - (int) $item['diem_danh_gia']) ?></span>
                                    <?php if (!empty($item['nhan_xet_sinh_vien'])): ?>
                                        <br><small class="text-muted"><?= e($item['nhan_xet_sinh_vien']) ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">Chưa đánh giá</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php require __DIR__ . '/danh_gia.php'; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>