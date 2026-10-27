<?php
// Khai báo giá trị mặc định để tránh hoàn toàn lỗi cảnh báo Undefined variable trong IDE/Linter
$tieu_de          = $tieu_de ?? 'Quản lý lịch hẹn tư vấn';
$trang_thai_loc   = $trang_thai_loc ?? '';
$danh_sach        = $danh_sach ?? [];
$tong_trang       = $tong_trang ?? 1;
$trang_hien_tai   = $trang_hien_tai ?? 1;
?>


<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 fw-bold text-primary mb-0">
            <i class="bi bi-calendar-check"></i> Quản lý lịch hẹn tư vấn
        </h2>
    </div>


    <!-- Bộ lọc trạng thái có nút Lọc -->
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body py-3">
            <form method="GET" action="<?= url('giang-vien/lich-hen') ?>" class="row g-2 align-items-center">
                <input type="hidden" name="r" value="giang-vien/lich-hen">
                <div class="col-auto">
                    <label class="form-label mb-0 fw-semibold">Lọc theo trạng thái:</label>
                </div>
                <div class="col-auto">
                    <select name="trang_thai" class="form-select form-select-sm">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="cho_duyet" <?= $trang_thai_loc === 'cho_duyet' ? 'selected' : '' ?>>Chờ duyệt</option>
                        <option value="da_duyet" <?= $trang_thai_loc === 'da_duyet' ? 'selected' : '' ?>>Đã duyệt</option>
                        <option value="hoan_thanh" <?= $trang_thai_loc === 'hoan_thanh' ? 'selected' : '' ?>>Hoàn thành</option>
                        <option value="tu_choi" <?= $trang_thai_loc === 'tu_choi' ? 'selected' : '' ?>>Đã từ chối</option>
                        <option value="da_huy" <?= $trang_thai_loc === 'da_huy' ? 'selected' : '' ?>>Đã hủy</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-funnel"></i> Lọc
                    </button>
                    <?php if (!empty($trang_thai_loc)): ?>
                        <a href="<?= url('giang-vien/lich-hen') ?>" class="btn btn-outline-secondary btn-sm" title="Xóa bộ lọc">
                            <i class="bi bi-arrow-counterclockwise"></i> Đặt lại
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>


    <!-- Danh sách cuộc hẹn -->
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Sinh viên</th>
                        <th>Thời gian & Địa điểm</th>
                        <th>Chủ đề tư vấn</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($danh_sach)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Không có lịch hẹn nào phù hợp.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($danh_sach as $item): ?>
                            <tr>
                                <td>
                                    <strong><?= e($item['ten_sinh_vien']) ?></strong><br>
                                    <small class="text-muted"><?= e($item['email_sinh_vien']) ?></small>
                                </td>
                                <td>
                                    <i class="bi bi-clock text-secondary"></i> <?= date('H:i - d/m/Y', strtotime($item['bat_dau'])) ?><br>
                                    <small class="text-muted"><i class="bi bi-geo-alt"></i> <?= e($item['dia_diem'] ?? 'Trực tuyến / Chưa có') ?></small>
                                </td>
                                <td>
                                    <span class="d-inline-block text-truncate" style="max-width: 250px;" title="<?= e($item['chu_de']) ?>">
                                        <?= e($item['chu_de']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($item['trang_thai'] === 'cho_duyet'): ?>
                                        <span class="badge bg-warning text-dark">Chờ duyệt</span>
                                    <?php elseif ($item['trang_thai'] === 'da_duyet'): ?>
                                        <span class="badge bg-primary">Đã duyệt</span>
                                    <?php elseif ($item['trang_thai'] === 'hoan_thanh'): ?>
                                        <span class="badge bg-success">Hoàn thành</span>
                                    <?php elseif ($item['trang_thai'] === 'tu_choi'): ?>
                                        <span class="badge bg-danger">Đã từ chối</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Đã hủy</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <!-- Nút duyệt / từ chối khi đang chờ duyệt -->
                                    <?php if ($item['trang_thai'] === 'cho_duyet'): ?>
                                        <form method="POST" action="<?= url('giang-vien/lich-hen/cap-nhat') ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="ma_cuoc_hen" value="<?= $item['ma_cuoc_hen'] ?>">
                                            <input type="hidden" name="hanh_dong" value="duyet">
                                            <button type="submit" class="btn btn-sm btn-success" title="Phê duyệt cuộc hẹn">Duyệt</button>
                                        </form>


                                        <form method="POST" action="<?= url('giang-vien/lich-hen/cap-nhat') ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="ma_cuoc_hen" value="<?= $item['ma_cuoc_hen'] ?>">
                                            <input type="hidden" name="hanh_dong" value="tu_choi">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc muốn từ chối lịch hẹn này?')" title="Từ chối cuộc hẹn">Từ chối</button>
                                        </form>
                                    <?php endif; ?>


                                    <!-- Nút hoàn thành và nút hủy kèm Modal nhập lý do khi đã duyệt -->
                                    <?php if ($item['trang_thai'] === 'da_duyet'): ?>
                                        <form method="POST" action="<?= url('giang-vien/lich-hen/cap-nhat') ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="ma_cuoc_hen" value="<?= $item['ma_cuoc_hen'] ?>">
                                            <input type="hidden" name="hanh_dong" value="hoan_thanh">
                                            <button type="submit" class="btn btn-sm btn-outline-primary" title="Đánh dấu đã hoàn thành tư vấn">Hoàn thành</button>
                                        </form>


                                        <!-- Nút mở modal hủy lịch hẹn -->
                                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalHuy<?= $item['ma_cuoc_hen'] ?>" title="Hủy cuộc hẹn">
                                            Hủy
                                        </button>


                                        <!-- Modal Nhập lý do hủy -->
                                        <div class="modal fade text-start" id="modalHuy<?= $item['ma_cuoc_hen'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <form method="POST" action="<?= url('giang-vien/lich-hen/cap-nhat') ?>" class="modal-content">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="ma_cuoc_hen" value="<?= $item['ma_cuoc_hen'] ?>">
                                                    <input type="hidden" name="hanh_dong" value="da_huy">
                                                   
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title">Xác nhận hủy lịch hẹn</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                   
                                                    <div class="modal-body">
                                                        <p class="text-muted small">Vui lòng nêu rõ lý do hủy để sinh viên nắm bắt thông tin qua hệ thống:</p>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Lý do hủy lịch hẹn <span class="text-danger">*</span>:</label>
                                                            <textarea name="ly_do_huy" class="form-control" rows="3" placeholder="Nhập lý do hủy (VD: Giảng viên bận công tác đột xuất...)" required></textarea>
                                                        </div>
                                                    </div>
                                                   
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                                                        <button type="submit" class="btn btn-danger btn-sm">Xác nhận hủy lịch</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endif; ?>


                                    <!-- Nút ghi chú phản hồi (hiển thị khi đã duyệt hoặc hoàn thành) -->
                                    <?php if (in_array($item['trang_thai'], ['da_duyet', 'hoan_thanh'])): ?>
                                        <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#modalGhiChu<?= $item['ma_cuoc_hen'] ?>">
                                            <i class="bi bi-chat-left-text"></i> Ghi chú
                                        </button>


                                        <!-- Modal Ghi chú & Phản hồi -->
                                        <div class="modal fade text-start" id="modalGhiChu<?= $item['ma_cuoc_hen'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <form method="POST" action="<?= url('giang-vien/lich-hen/phan-hoi') ?>" class="modal-content">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="ma_cuoc_hen" value="<?= $item['ma_cuoc_hen'] ?>">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Ghi chú & Phản hồi buổi tư vấn</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <?php if (!empty($item['diem_danh_gia'])): ?>
                                                            <div class="alert alert-light border mb-3">
                                                                <strong>Đánh giá từ sinh viên:</strong> <?= $item['diem_danh_gia'] ?>/5 ⭐<br>
                                                                <em>"<?= e($item['nhan_xet_sinh_vien']) ?>"</em>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Ghi chú của giảng viên:</label>
                                                            <textarea name="ghi_chu_giang_vien" class="form-control" rows="4" placeholder="Nhập nội dung trao đổi, dặn dò hoặc kết quả tư vấn..."><?= e($item['ghi_chu_giang_vien'] ?? '') ?></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                                                        <button type="submit" class="btn btn-primary btn-sm">Lưu ghi chú</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>


    <!-- Phân trang -->
    <?php if ($tong_trang > 1): ?>
        <nav class="mt-4">
            <ul class="pagination pagination-sm justify-content-center">
                <?php for ($i = 1; $i <= $tong_trang; $i++): ?>
                    <li class="page-item <?= $i === $trang_hien_tai ? 'active' : '' ?>">
                        <a class="page-link" href="<?= url('giang-vien/lich-hen', ['trang' => $i, 'trang_thai' => $trang_thai_loc]) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

