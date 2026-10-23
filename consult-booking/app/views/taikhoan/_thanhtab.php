<?php /** @var string $tabHienTai */ ?>
<ul class="nav nav-tabs mb-4">
    <li class="nav-item"><a class="nav-link<?= $tabHienTai === 'ho-so' ? ' active' : '' ?>" href="<?= e(url('tai-khoan/ho-so')) ?>">Hồ sơ cá nhân</a></li>
    <li class="nav-item"><a class="nav-link<?= $tabHienTai === 'doi-mat-khau' ? ' active' : '' ?>" href="<?= e(url('tai-khoan/doi-mat-khau')) ?>">Đổi mật khẩu</a></li>
</ul>
