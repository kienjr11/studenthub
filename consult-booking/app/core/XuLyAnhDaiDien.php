<?php
declare(strict_types=1);

/**
 * Nhận ảnh đại diện tải lên: kiểm tra thật sự là ảnh (không tin đuôi file hay Content-Type do trình duyệt gửi),
 * cắt vuông, thu nhỏ 400x400 và LƯU LẠI bằng GD dưới dạng JPEG với tên ngẫu nhiên.
 * Mã hóa lại ảnh làm mất mọi dữ liệu thừa (EXIF/GPS, mã độc nhúng trong ảnh).
 */
final class XuLyAnhDaiDien
{
    public const MAX_BYTES = 2 * 1024 * 1024;   // 2 MB
    private const MAX_PIXELS = 25_000_000;       // chặn ảnh "bom giải nén"
    private const SIZE = 400;

    public static function thuMuc(): string
    {
        return ROOT_PATH . '/public/uploads/avatars';
    }

    /** @return array{0:?string,1:?string} [tên file đã lưu, thông báo lỗi]. Không chọn file thì cả hai đều null. */
    public static function luu(?array $file, int $userId): array
    {
        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return [null, null];
        }
        $err = $file['error'];
        if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) {
            return [null, 'Ảnh quá lớn, tối đa 2 MB.'];
        }
        if ($err !== UPLOAD_ERR_OK || !is_uploaded_file((string) $file['tmp_name'])) {
            return [null, 'Tải ảnh lên không thành công, vui lòng thử lại.'];
        }
        if ((int) $file['size'] > self::MAX_BYTES) {
            return [null, 'Ảnh quá lớn, tối đa 2 MB.'];
        }
        if (!function_exists('imagecreatefromstring')) {
            return [null, 'Máy chủ chưa bật PHP GD nên chưa xử lý được ảnh.'];
        }

        $tmp = (string) $file['tmp_name'];
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
        $info = @getimagesize($tmp);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true) || $info === false) {
            return [null, 'Chỉ nhận ảnh JPG, PNG hoặc WebP.'];
        }
        if ($info[0] < 1 || $info[1] < 1 || $info[0] * $info[1] > self::MAX_PIXELS) {
            return [null, 'Kích thước ảnh không hợp lệ (quá lớn).'];
        }

        $src = @imagecreatefromstring((string) file_get_contents($tmp));
        if ($src === false) {
            return [null, 'Không đọc được ảnh này, hãy thử ảnh khác.'];
        }
        // Xoay theo EXIF (ảnh chụp bằng điện thoại hay bị nằm ngang)
        if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($tmp);
            $rot = [3 => 180, 6 => -90, 8 => 90][$exif['Orientation'] ?? 1] ?? 0;
            if ($rot !== 0 && ($r = imagerotate($src, $rot, 0)) !== false) {
                $src = $r;
            }
        }

        $w = imagesx($src);
        $h = imagesy($src);
        $side = min($w, $h);
        $dst = imagecreatetruecolor(self::SIZE, self::SIZE);
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));   // nền trắng cho PNG/WebP trong suốt
        imagecopyresampled($dst, $src, 0, 0, intdiv($w - $side, 2), intdiv($h - $side, 2), self::SIZE, self::SIZE, $side, $side);

        $dir = self::thuMuc();
        if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
            return [null, 'Không tạo được thư mục lưu ảnh trên máy chủ.'];
        }
        $name = 'gv' . $userId . '_' . bin2hex(random_bytes(8)) . '.jpg';
        if (!imagejpeg($dst, $dir . '/' . $name, 85)) {
            return [null, 'Không lưu được ảnh, vui lòng thử lại.'];
        }
        return [$name, null];
    }

    /** Xóa file ảnh cũ. Chỉ xóa tên đúng định dạng do chính hệ thống sinh ra (chống ghi tên file bậy). */
    public static function xoa(?string $name): void
    {
        if ($name !== null && preg_match('/^gv\d+_[a-f0-9]{16}\.jpg$/', $name)) {
            @unlink(self::thuMuc() . '/' . $name);
        }
    }
}