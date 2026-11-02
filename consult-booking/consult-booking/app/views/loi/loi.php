<div class="container py-5 text-center">
    <p class="display-3 fw-bold text-secondary mb-2"><?= e($code) ?></p>
    <h1 class="h3 mb-3"><?= e($heading) ?></h1>
    <?php if (!empty($message)): ?><p class="text-muted"><?= e($message) ?></p><?php endif; ?>
    <a href="<?= e(url('home/index')) ?>" class="btn btn-primary">Về trang chủ</a>
</div>
