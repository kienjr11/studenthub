<?php /** @var string $asideTitle @var string $asideText */ ?>
<aside class="auth-aside d-none d-lg-flex">
    <div>
        <span class="brand-mark brand-mark-lg" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
        <h2><?= e($asideTitle) ?></h2>
        <p class="mb-0"><?= e($asideText) ?></p>
    </div>
    <ul class="aside-list">
        <li><span class="slot slot-free">Còn trống</span> Sinh viên chọn giờ phù hợp</li>
        <li><span class="slot slot-pending">Chờ duyệt</span> Giảng viên xác nhận yêu cầu</li>
        <li><span class="slot slot-booked">Đã xác nhận</span> Giờ được giữ cho đúng một người</li>
    </ul>
</aside>
