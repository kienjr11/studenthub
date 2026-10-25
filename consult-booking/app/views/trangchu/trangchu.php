<?php
/** @var array $stats @var array $departments @var array $specialties */
$me = XacThuc::user();
?>
<section class="hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <h1 class="hero-title">Đặt lịch tư vấn giảng viên <span class="hl">nhanh chóng, chủ động.</span></h1>
                <p class="lead mb-4">Hệ thống kết nối sinh viên với giảng viên, giúp việc đặt lịch tư vấn học tập diễn ra thuận tiện, đúng hẹn và không trùng lịch.</p>
                <div class="d-flex flex-wrap gap-2">
                    <?php if ($me): ?>
                        <a class="btn btn-primary btn-lg" href="<?= e(url('dashboard/index')) ?>">Vào khu vực của tôi</a>
                    <?php else: ?>
                        <a class="btn btn-primary btn-lg" href="<?= e(url('auth/register')) ?>">Tạo tài khoản sinh viên</a>
                        <a class="btn btn-outline-light btn-lg" href="<?= e(url('auth/login')) ?>">Đăng nhập</a>
                    <?php endif; ?>
                </div>
                <ul class="hero-points">
                    <li>Không trùng lịch</li><li>Giảng viên duyệt từng yêu cầu</li><li>Phản hồi sau buổi hẹn</li>
                </ul>
                <p class="mt-3 mb-0 text-muted"><?= e($stats['giang_vien_hoat_dong']) ?> giảng viên đang nhận lịch tư vấn, thuộc <?= e(count($departments)) ?> đơn vị.</p>
            </div>
            <div class="col-lg-6">
                <div class="slot-board" role="img" aria-label="Minh họa bảng khung giờ tư vấn với ba trạng thái: còn trống, chờ duyệt, đã xác nhận">
                    <div class="slot-board-head">
                        <strong>Khung giờ tư vấn trong tuần</strong>
                        <span class="text-muted small">Minh họa</span>
                    </div>
                    <div class="slot-day">Thứ Hai</div>
                    <div class="slot-row"><span class="slot-who">Nguyễn Văn Minh</span>
                        <span class="slot slot-free">08:00</span><span class="slot slot-booked">09:30</span><span class="slot slot-pending">14:00</span><span class="slot slot-free">15:30</span></div>
                    <div class="slot-row"><span class="slot-who">Trần Thu Hà</span>
                        <span class="slot slot-booked">08:30</span><span class="slot slot-free">10:00</span><span class="slot slot-free">13:30</span><span class="slot slot-pending">15:00</span></div>
                    <div class="slot-day">Thứ Tư</div>
                    <div class="slot-row"><span class="slot-who">Nguyễn Văn Minh</span>
                        <span class="slot slot-pending">09:00</span><span class="slot slot-free">10:30</span><span class="slot slot-free">14:30</span><span class="slot slot-booked">16:00</span></div>
                    <div class="slot-legend">
                        <span><i class="slot slot-free"></i>Còn trống</span>
                        <span><i class="slot slot-pending"></i>Chờ giảng viên duyệt</span>
                        <span><i class="slot slot-booked"></i>Đã xác nhận</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="container py-5">
    <h2 class="h3 mb-4 section-title">Ai dùng hệ thống và làm được gì</h2>
    <div class="row g-4">
        <div class="col-md-4"><div class="role-block role-student">
            <h3 class="h5 d-flex align-items-center gap-2"><span class="avatar avatar-sinh_vien" aria-hidden="true">S</span>Sinh viên</h3>
            <p class="mb-0">Tìm giảng viên theo đơn vị hoặc chuyên môn, xem các khung giờ còn trống, đặt hoặc hủy lịch và gửi phản hồi sau buổi gặp.</p></div></div>
        <div class="col-md-4"><div class="role-block role-lecturer">
            <h3 class="h5 d-flex align-items-center gap-2"><span class="avatar avatar-giang_vien" aria-hidden="true">G</span>Giảng viên</h3>
            <p class="mb-0">Tạo khung giờ tư vấn, duyệt hoặc từ chối yêu cầu, đánh dấu cuộc hẹn đã hoàn thành. Tài khoản giảng viên cần quản trị viên phê duyệt trước khi dùng.</p></div></div>
        <div class="col-md-4"><div class="role-block role-admin">
            <h3 class="h5 d-flex align-items-center gap-2"><span class="avatar avatar-quan_tri" aria-hidden="true">Q</span>Quản trị viên</h3>
            <p class="mb-0">Khóa hoặc mở khóa tài khoản, phân quyền, và cập nhật danh mục đơn vị, chuyên môn mà giảng viên thuộc về.</p></div></div>
    </div>
</section>

<section class="container pb-4">
    <h2 class="h3 mb-4 section-title">Một buổi tư vấn đi qua bốn bước</h2>
    <ol class="steps">
        <li><strong>Giảng viên mở khung giờ.</strong> Chọn ngày, giờ bắt đầu và kết thúc.</li>
        <li><strong>Sinh viên chọn giờ còn trống.</strong> Ghi chủ đề muốn trao đổi rồi gửi yêu cầu.</li>
        <li><strong>Giảng viên duyệt hoặc từ chối.</strong> Sinh viên thấy kết quả ngay trong lịch của mình.</li>
        <li><strong>Hoàn thành và phản hồi.</strong> Sau buổi gặp, sinh viên đánh giá và góp ý.</li>
    </ol>
</section>

<section class="container py-4">
    <div class="row g-4">
        <div class="col-md-6">
            <h2 class="h5">Đơn vị</h2>
            <?php if ($departments): ?>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($departments as $d): ?><span class="pill"><?= e($d['ten']) ?></span><?php endforeach; ?>
                </div>
            <?php else: ?><p class="text-muted mb-0">Quản trị viên chưa tạo đơn vị nào.</p><?php endif; ?>
        </div>
        <div class="col-md-6">
            <h2 class="h5">Chuyên môn tư vấn</h2>
            <?php if ($specialties): ?>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($specialties as $s): ?><span class="pill"><?= e($s['ten']) ?></span><?php endforeach; ?>
                </div>
            <?php else: ?><p class="text-muted mb-0">Quản trị viên chưa tạo chuyên môn nào.</p><?php endif; ?>
        </div>
    </div>
</section>

<?php if (!$me): ?>
<section class="cta-band">
    <div class="container d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h2 class="h4 mb-1">Bắt đầu đặt lịch với giảng viên của bạn</h2>
            <p class="mb-0">Tài khoản sinh viên dùng được ngay sau khi đăng ký, không cần chờ duyệt.</p>
        </div>
        <a class="btn btn-light btn-lg" href="<?= e(url('auth/register')) ?>">Tạo tài khoản</a>
    </div>
</section>
<?php endif; ?>
