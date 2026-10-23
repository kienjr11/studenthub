<?php
declare(strict_types=1);

final class View
{
    /** Render view vào chuỗi, rồi (tùy chọn) bọc trong layout. */
    public static function render(string $view, array $data = [], ?string $layout = 'main'): string
    {
        $content = self::include(APP_PATH . "/views/$view.php", $data);
        if ($layout === null) {
            return $content;
        }
        return self::include(APP_PATH . "/views/layouts/$layout.php", $data + ['content' => $content]);
    }

    private static function include(string $file, array $data): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        try {
            require $file;
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        return (string) ob_get_clean();
    }
}
