<?php
/**
 * Bảng thống kê số cuộc hẹn THEO NGÀY HẸN (dùng chung cho Admin và Giảng viên).
 * Biến đầu vào: $thongKeNgay (mảng từng ngày, xem CuocHen::thongKeTheoNgay), $tuNgay / $denNgay (Y-m-d),
 *               $routeLoc  (route của chính trang đang hiển thị, để form lọc gửi về),
 *               $routeNgay (tùy chọn: route danh sách cuộc hẹn; nếu có thì ô ngày là link xem chi tiết ngày đó).
 */
/** @var array $thongKeNgay */
/** @var string $tuNgay */
/** @var string $denNgay */
/** @var string $routeLoc */
/** @var string|null $routeNgay */

$thongKeNgay = $thongKeNgay ?? [];
$tuNgay      = $tuNgay ?? date('Y-m-d', strtotime('-14 days'));
$denNgay     = $denNgay ?? date('Y-m-d');
$routeLoc    = $routeLoc ?? '';
$routeNgay = $routeNgay ?? null;
$thuTrongTuan = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
$homNay = date('Y-m-d');
$cotTrangThai = ['cho_duyet' => 'Chờ duyệt', 'da_duyet' => 'Đã duyệt', 'hoan_thanh' => 'Hoàn thành', 'tu_choi' => 'Từ chối', 'da_huy' => 'Đã hủy'];
$cong = array_fill_keys(array_merge(['tong'], array_keys($cotTrangThai)), 0);
foreach ($thongKeNgay as $dong) {
    foreach ($cong as $khoa => $_) {
        $cong[$khoa] += (int) $dong[$khoa];
    }
}
?>
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
            <h2 class="h5 mb-0">Thống kê cuộc hẹn theo ngày</h2>
            <form method="GET" action="<?= e(url($routeLoc)) ?>" class="d-flex flex-wrap align-items-end gap-2">
                <input type="hidden" name="r" value="<?= e($routeLoc) ?>">
                <div>
                    <label class="form-label small mb-0" for="tkTuNgay">Từ ngày</label>
                    <input type="date" id="tkTuNgay" name="tu_ngay" value="<?= e($tuNgay) ?>" class="form-control form-control-sm" required>
                </div>
                <div>
                    <label class="form-label small mb-0" for="tkDenNgay">Đến ngày</label>
                    <input type="date" id="tkDenNgay" name="den_ngay" value="<?= e($denNgay) ?>" class="form-control form-control-sm" required>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Xem</button>
                <a href="<?= e(url($routeLoc)) ?>" class="btn btn-outline-secondary btn-sm" title="Quay về 14 ngày quanh hôm nay">Đặt lại</a>
            </form>
        </div>
        <p class="small text-muted mb-2">Tính theo ngày diễn ra buổi hẹn, tối đa 62 ngày. Ngày không có lịch hiển thị dấu "–".</p>

        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle text-center mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-start">Ngày</th>
                        <th>Tổng</th>
                        <?php foreach ($cotTrangThai as $nhan): ?>
                            <th><?= e($nhan) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($thongKeNgay as $dong): ?>
                        <?php $ts = strtotime($dong['ngay']); ?>
                        <tr class="<?= $dong['ngay'] === $homNay ? 'table-primary' : '' ?><?= $dong['tong'] === 0 ? ' text-muted' : '' ?>">
                            <td class="text-start text-nowrap">
                                <?php $nhanNgay = $thuTrongTuan[(int) date('w', $ts)] . ', ' . date('d/m/Y', $ts); ?>
                                <?php if ($routeNgay !== null && $dong['tong'] > 0): ?>
                                    <a href="<?= e(url($routeNgay, ['ngay' => $dong['ngay']])) ?>"><?= e($nhanNgay) ?></a>
                                <?php else: ?>
                                    <?= e($nhanNgay) ?>
                                <?php endif; ?>
                                <?php if ($dong['ngay'] === $homNay): ?><span class="badge text-bg-primary ms-1">Hôm nay</span><?php endif; ?>
                            </td>
                            <td class="fw-semibold"><?= $dong['tong'] > 0 ? e($dong['tong']) : '–' ?></td>
                            <?php foreach ($cotTrangThai as $khoa => $_): ?>
                                <td><?= $dong[$khoa] > 0 ? e($dong[$khoa]) : '–' ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light fw-semibold">
                    <tr>
                        <td class="text-start">Cộng (<?= e(count($thongKeNgay)) ?> ngày)</td>
                        <td><?= e($cong['tong']) ?></td>
                        <?php foreach ($cotTrangThai as $khoa => $_): ?>
                            <td><?= e($cong[$khoa]) ?></td>
                        <?php endforeach; ?>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
