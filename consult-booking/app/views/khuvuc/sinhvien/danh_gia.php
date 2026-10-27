<?php
/** 
 * File view hiển thị nút và modal đánh giá / hủy cuộc hẹn dành cho Sinh viên.
 * Biến đầu vào: $item (mảng chứa thông tin cuộc hẹn)
 */
$maCuocHen = $item['ma_cuoc_hen'] ?? 0;
$trangThai = $item['trang_thai'] ?? '';
$diemCu = $item['diem_danh_gia'] ?? 5;
$nhanXetCu = $item['nhan_xet_sinh_vien'] ?? '';
?>

<?php if ($trangThai === 'hoan_thanh'): ?>
    <!-- LỊCH ĐÃ HOÀN THÀNH: GIỮ NGUYÊN LOGIC CŨ (Chỉ hiển thị Đánh giá / Sửa đánh giá) -->
    <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalDanhGia<?= $maCuocHen ?>">
        ⭐ <?= !empty($item['diem_danh_gia']) ? 'Sửa đánh giá' : 'Đánh giá' ?>
    </button>

    <!-- Modal Đánh giá -->
    <div class="modal fade text-start" id="modalDanhGia<?= $maCuocHen ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="<?= url('sinh-vien/phan-hoi') ?>" class="modal-content">
                <?= csrf_field() ?>
                <input type="hidden" name="ma_cuoc_hen" value="<?= $maCuocHen ?>">
                
                <div class="modal-header">
                    <h5 class="modal-title">Đánh giá chất lượng buổi tư vấn</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                
                <div class="modal-body">
                    <!-- Chọn số sao đánh giá -->
                    <div class="mb-3 text-center">
                        <label class="form-label fw-semibold d-block">Mức độ hài lòng của bạn:</label>
                        <div class="btn-group" role="group" aria-label="Chọn số sao">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <input type="radio" class="btn-check" name="diem_danh_gia" 
                                       id="dg<?= $maCuocHen ?>_<?= $i ?>" 
                                       value="<?= $i ?>" 
                                       <?= (int)$diemCu === $i ? 'checked' : '' ?>>
                                <label class="btn btn-outline-warning" for="dg<?= $maCuocHen ?>_<?= $i ?>">
                                    <?= $i ?> ⭐
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- Ô nhập nhận xét -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="nhanXet<?= $maCuocHen ?>">Nhận xét / Góp ý:</label>
                        <textarea id="nhanXet<?= $maCuocHen ?>" name="nhan_xet" class="form-control" rows="3" 
                                  placeholder="Giảng viên tư vấn có nhiệt tình và giải đáp đúng thắc mắc của bạn không?"><?= e($nhanXetCu) ?></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary btn-sm">Gửi đánh giá</button>
                </div>
            </form>
        </div>
    </div>

<?php elseif (in_array($trangThai, ['cho_duyet', 'da_duyet'])): ?>
    <!-- LỊCH CHƯA HOÀN THÀNH (Chờ duyệt / Đã duyệt): HIỂN THỊ NÚT HỦY LỊCH -->
    <form method="POST" action="<?= url('sinh-vien/huy-lich') ?>" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn hủy lịch hẹn này?');">
        <?= csrf_field() ?>
        <input type="hidden" name="ma_cuoc_hen" value="<?= $maCuocHen ?>">
        <button type="submit" class="btn btn-sm btn-outline-danger">
            Hủy lịch
        </button>
    </form>

<?php elseif ($trangThai === 'da_huy'): ?>
    <!-- LỊCH ĐÃ HỦY: DÙNG BTN NÊN KÍCH THƯỚC Ô SẼ RỘNG VÀ ĐỒNG BỘ VỚI CÁC NÚT KHÁC -->
    <button type="button" class="btn btn-sm btn-secondary" disabled>Đã hủy</button>

<?php else: ?>
    <!-- CÁC TRẠNG THÁI KHÁC (Đã từ chối) -->
    <button type="button" class="btn btn-sm btn-secondary" disabled>Đã từ chối</button>
<?php endif; ?>