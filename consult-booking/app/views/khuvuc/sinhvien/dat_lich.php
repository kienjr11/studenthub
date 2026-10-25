<?php
/** @var array $danhSachGiangVien @var array $cu */
$coGiangVien = !empty($danhSachGiangVien);
[$nhanChoDuyet, $mauChoDuyet] = status_label('cho_duyet');
?>
<section class="dat-lich-dau">
    <div class="container">
        <h1 class="h2 mb-2 page-title">Đặt lịch hẹn tư vấn</h1>
        <p class="text-muted mb-4">Gửi yêu cầu tới giảng viên bạn cần gặp. Giảng viên sẽ xem rồi duyệt hoặc từ chối yêu cầu của bạn.</p>
        <ol class="buoc-dat" aria-label="Các bước đặt lịch">
            <li><span class="buoc-so" aria-hidden="true">1</span><span><strong>Chọn giảng viên</strong><small>và chuyên đề cần tư vấn</small></span></li>
            <li><span class="buoc-so" aria-hidden="true">2</span><span><strong>Chọn thời gian</strong><small>ngày, giờ bắt đầu và kết thúc</small></span></li>
            <li><span class="buoc-so" aria-hidden="true">3</span><span><strong>Nêu chủ đề và gửi</strong><small>chờ giảng viên duyệt</small></span></li>
        </ol>
    </div>
</section>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-8">
            <form action="<?= e(url('sinh-vien/dat-lich')) ?>" method="post" class="auth-form dat-lich-form booking-form">
                <?= csrf_field() ?>

                <?php if (!$coGiangVien): ?>
                    <div class="alert alert-info" role="alert">Hiện chưa có giảng viên nào đang nhận lịch. Vui lòng quay lại sau.</div>
                <?php endif; ?>

                <!-- Bước 1: giảng viên và chuyên đề -->
                <div class="nhom-form">
                    <h2 class="nhom-tieu-de"><span class="buoc-so buoc-so-nho" aria-hidden="true">1</span>Giảng viên và chuyên đề</h2>

                    <div class="mb-3">
                        <label for="giang_vien_id" class="form-label">Giảng viên hướng dẫn / tư vấn <span class="bat-buoc" aria-hidden="true">*</span></label>
                        <select id="giang_vien_id" name="giang_vien_id" class="form-select" required<?= $coGiangVien ? '' : ' disabled' ?>>
                            <option value="">-- Chọn giảng viên --</option>
                            <?php foreach ($danhSachGiangVien as $gv): ?>
                                <?php
                                    $nhan = $gv['ho_ten'];
                                    if (!empty($gv['ten_khoa'])) {
                                        $nhan .= ' - ' . $gv['ten_khoa'];
                                    }
                                ?>
                                <option value="<?= e($gv['ma_nguoi_dung']) ?>"
                                        data-ten="<?= e($gv['ho_ten']) ?>"
                                        data-khoa="<?= e($gv['ten_khoa'] ?? '') ?>"
                                        data-chuyenmon="<?= e($gv['chuyen_mon'] ?? '') ?>"<?= (int) ($cu['giang_vien_id'] ?? 0) === (int) $gv['ma_nguoi_dung'] ? ' selected' : '' ?>>
                                    <?= e($nhan) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Thẻ xem trước: app.js điền nội dung khi chọn giảng viên -->
                    <div id="xem_truoc_gv" class="xem-truoc-gv d-none" aria-live="polite">
                        <span class="avatar avatar-lg avatar-giang_vien" data-o="chu-cai" aria-hidden="true">G</span>
                        <div>
                            <div class="xem-truoc-ten" data-o="ten"></div>
                            <div class="text-muted small" data-o="khoa"></div>
                            <div class="d-flex flex-wrap gap-1 mt-2" data-o="mon"></div>
                        </div>
                    </div>

                    <!-- Môn học / chuyên đề: app.js nạp theo giảng viên đang chọn -->
                    <div class="mt-3" id="khoi_chon_mon">
                        <label for="mon_hoc" class="form-label">Môn học / Chuyên đề tư vấn</label>
                        <select id="mon_hoc" name="mon_hoc" class="form-select">
                            <option value="">-- Chọn môn học --</option>
                        </select>
                    </div>
                </div>

                <!-- Bước 2: thời gian -->
                <div class="nhom-form">
                    <h2 class="nhom-tieu-de"><span class="buoc-so buoc-so-nho" aria-hidden="true">2</span>Thời gian</h2>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="ngay_hen" class="form-label">Ngày hẹn <span class="bat-buoc" aria-hidden="true">*</span></label>
                            <input type="date" id="ngay_hen" name="ngay_hen" class="form-control" min="<?= date('Y-m-d') ?>" value="<?= e($cu['ngay_hen'] ?? '') ?>" required>
                        </div>
                        <div class="col-6 col-md-4">
                            <label for="gio_bat_dau" class="form-label">Giờ bắt đầu <span class="bat-buoc" aria-hidden="true">*</span></label>
                            <input type="time" id="gio_bat_dau" name="gio_bat_dau" class="form-control" value="<?= e($cu['gio_bat_dau'] ?? '') ?>" required>
                        </div>
                        <div class="col-6 col-md-4">
                            <label for="gio_ket_thuc" class="form-label">Giờ kết thúc <span class="bat-buoc" aria-hidden="true">*</span></label>
                            <input type="time" id="gio_ket_thuc" name="gio_ket_thuc" class="form-control" value="<?= e($cu['gio_ket_thuc'] ?? '') ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Bước 3: chủ đề và gửi -->
                <div class="nhom-form nhom-form-cuoi">
                    <h2 class="nhom-tieu-de"><span class="buoc-so buoc-so-nho" aria-hidden="true">3</span>Chủ đề cuộc hẹn</h2>
                    <div class="mb-4">
                        <label for="chu_de" class="form-label">Tiêu đề cuộc hẹn <span class="bat-buoc" aria-hidden="true">*</span></label>
                        <input type="text" id="chu_de" name="chu_de" class="form-control" placeholder="Ví dụ: Hướng dẫn đồ án tốt nghiệp, hỏi bài môn C#..." maxlength="255" value="<?= e($cu['chu_de'] ?? '') ?>" required>
                        <div class="form-text">Nêu ngắn gọn nội dung muốn trao đổi để giảng viên chuẩn bị.</div>
                    </div>

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <a href="<?= e(url('sinh-vien/phan-hoi')) ?>" class="btn btn-outline-secondary px-4">Quay lại</a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold"<?= $coGiangVien ? '' : ' disabled' ?>>Xác nhận đặt lịch</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <aside class="luu-y-dat-lich" aria-labelledby="luu-y-tieu-de">
                <h2 id="luu-y-tieu-de" class="h6 mb-3">Trước khi gửi yêu cầu</h2>
                <ul class="danh-sach-luu-y">
                    <li>Thời gian hẹn phải ở tương lai, và giờ kết thúc phải sau giờ bắt đầu.</li>
                    <li>Nếu giảng viên đã có lịch hẹn trùng khoảng giờ đó, hệ thống sẽ báo để bạn chọn giờ khác.</li>
                    <li>Sau khi gửi, yêu cầu ở trạng thái <span class="badge text-bg-<?= e($mauChoDuyet) ?>"><?= e($nhanChoDuyet) ?></span> cho đến khi giảng viên duyệt hoặc từ chối.</li>
                </ul>
                <a class="btn btn-outline-primary btn-sm" href="<?= e(url('sinh-vien/phan-hoi')) ?>">Xem lịch hẹn của tôi</a>
            </aside>
        </div>
    </div>
</div>