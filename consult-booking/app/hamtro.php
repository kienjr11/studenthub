<?php
declare(strict_types=1);

/** Đọc cấu hình, hỗ trợ khóa dạng "db.host". Ưu tiên cauhinh.local.php, nếu chưa có thì dùng cauhinh.mau.php. */
function config(string $key, mixed $default = null): mixed
{
    static $cfg = null;
    if ($cfg === null) {
        $dir = APP_PATH . '/config';
        $cfg = require is_file("$dir/cauhinh.local.php") ? "$dir/cauhinh.local.php" : "$dir/cauhinh.mau.php";
    }
    $value = $cfg;
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}

/** Mã hóa output (chống XSS). Mọi dữ liệu người dùng in ra HTML đều phải đi qua hàm này. */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * In địa điểm ra HTML. Đoạn nào là link (http://, https://, www.) được xử lý theo $che_do:
 *  - 'bam' : thẻ <a> bấm được (mở tab mới);
 *  - 'cho' : ẩn link, hiện nhãn "link sẽ hiện sau khi giảng viên duyệt" (cuộc hẹn đang chờ duyệt);
 *  - 'mo'  : link bị vô hiệu — hiện mờ, gạch ngang, KHÔNG bấm được và KHÔNG lộ đường dẫn (cuộc hẹn bị từ chối / hủy).
 * Phần chữ thường (tên phòng...) luôn hiện bình thường và được mã hóa an toàn. Rỗng thì in $fallback.
 */
function lien_ket_dia_diem(?string $text, string $fallback = '', string $che_do = 'bam', string $ly_do_mo = 'Link không còn truy cập được'): string
{
    $text = trim((string) $text);
    if ($text === '') {
        return e($fallback);
    }
    $parts = preg_split('~((?:https?://|www\.)[^\s<>"\']+)~iu', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    $out = '';
    foreach ($parts as $i => $p) {
        if ($i % 2 === 0) {                      // đoạn chữ thường
            $out .= e($p);
            continue;
        }
        $duoi = '';                              // dấu câu dính cuối link (vd "...abc.") không thuộc link
        if (preg_match('~[.,;:!?)\]]+$~', $p, $m)) {
            $duoi = $m[0];
            $p    = substr($p, 0, -strlen($duoi));
        }
        if ($che_do === 'cho') {
            $out .= '<span class="link-cho-duyet text-muted"><i class="bi bi-lock"></i> Link online sẽ hiện sau khi giảng viên duyệt</span>';
        } elseif ($che_do === 'mo') {
            $out .= '<span class="link-vo-hieu text-muted" aria-disabled="true" title="' . e($ly_do_mo) . '"><i class="bi bi-link-45deg"></i> Link họp online</span>';
        } else {
            $href = stripos($p, 'www.') === 0 ? 'https://' . $p : $p;
            $out .= '<a href="' . e($href) . '" target="_blank" rel="noopener noreferrer">' . e($p) . '</a>';
        }
        $out .= e($duoi);
    }
    return $out;
}

/**
 * Địa điểm của 1 cuộc hẹn, tùy trạng thái:
 *  - đã duyệt / hoàn thành : link bấm được;
 *  - chờ duyệt             : sinh viên chưa thấy link (giảng viên chủ khung giờ thì vẫn bấm được);
 *  - bị từ chối / đã hủy   : link mờ đi, không bấm được (cả sinh viên lẫn giảng viên).
 */
function lien_ket_dia_diem_theo_trang_thai(?string $text, string $trang_thai, string $fallback = '', bool $la_giang_vien = false): string
{
    if ($trang_thai === 'tu_choi' || $trang_thai === 'da_huy') {
        $ly_do = $trang_thai === 'tu_choi'
            ? 'Giảng viên đã từ chối cuộc hẹn — không thể truy cập link'
            : 'Cuộc hẹn đã hủy — không thể truy cập link';
        return lien_ket_dia_diem($text, $fallback, 'mo', $ly_do);
    }
    if ($trang_thai === 'cho_duyet' && !$la_giang_vien) {
        return lien_ket_dia_diem($text, $fallback, 'cho');
    }
    return lien_ket_dia_diem($text, $fallback, 'bam');
}

/**
 * Thay link trong địa điểm bằng nhãn trung tính (trả về chuỗi thường, người gọi tự e()).
 * Dùng cho dữ liệu khung giờ hiển thị TRƯỚC khi đặt lịch, để sinh viên không lấy được link khi chưa được duyệt.
 */
function an_link_dia_diem(?string $text): ?string
{
    if ($text === null || trim($text) === '') {
        return $text;
    }
    return trim((string) preg_replace('~(?:https?://|www\.)[^\s<>"\']+~iu', 'Link online (hiện sau khi được duyệt)', $text));
}

/** Thư mục gốc của public/ tính từ URL (chạy được cả ở gốc domain lẫn thư mục con như /consult-booking/public). */
function base_path(): string
{
    return rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
}

function url(string $route = '', array $params = []): string
{
    $query = ($route === '' ? [] : ['r' => $route]) + $params;
    return base_path() . '/index.php' . ($query ? '?' . http_build_query($query) : '');
}

function asset(string $path): string
{
    return base_path() . '/assets/' . ltrim($path, '/');
}

function redirect(string $route, array $params = []): never
{
    header('Location: ' . url($route, $params));
    exit;
}

/** Flash message: lưu vào session, hiển thị một lần ở lần tải trang kế tiếp. */
function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'msg' => $message];
}

function pull_flashes(): array
{
    $flashes = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $flashes;
}

function csrf_field(): string
{
    return ChongCsrf::field();
}

function abort(int $code, string $message = ''): never
{
    $titles = [
        403 => 'Bạn không có quyền truy cập',
        404 => 'Không tìm thấy trang',
        405 => 'Phương thức không được hỗ trợ',
        419 => 'Phiên làm việc không hợp lệ',
    ];
    http_response_code($code);
    echo GiaoDien::render('loi/loi', [
        'code' => $code, 'heading' => $titles[$code] ?? 'Đã có lỗi xảy ra', 'message' => $message, 'title' => (string) $code,
    ]);
    exit;
}

function role_label(string $role): string
{
    return ['sinh_vien' => 'Sinh viên', 'giang_vien' => 'Giảng viên', 'quan_tri' => 'Quản trị viên'][$role] ?? $role;
}

/** @return array{0:string,1:string} [nhãn, màu Bootstrap] */
function status_label(string $status): array
{
    return ['hoat_dong' => ['Hoạt động', 'success'], 'cho_duyet' => ['Chờ duyệt', 'warning'], 'da_khoa' => ['Đã khóa', 'danger']][$status]
        ?? [$status, 'secondary'];
}

/** Chữ cái đầu của tên gọi (từ cuối trong họ tên theo cách gọi người Việt), dùng cho avatar. */
function initial(string $name): string
{
    $parts = preg_split('/\s+/u', trim($name)) ?: [];
    $last = (string) end($parts);
    return mb_strtoupper(mb_substr($last, 0, 1)) ?: '?';
}

/** URL ảnh đại diện giảng viên, hoặc null nếu chưa có. */
function anh_dai_dien_url(?string $tenFile): ?string
{
    return ($tenFile !== null && $tenFile !== '' && preg_match('/^[A-Za-z0-9_.\-]+$/', $tenFile))
        ? base_path() . '/uploads/avatars/' . $tenFile
        : null;
}
/**
 * Tương thích với code cũ gọi avatar(...). Trả về chuỗi HTML.
 * Dùng được các kiểu gọi: avatar('Nguyễn Văn A'), avatar('Nguyễn Văn A', 'giang_vien'),
 * avatar('Nguyễn Văn A', 'giang_vien', 'avatar-lg'), avatar($rowGiangVien, 'avatar-lg').
 * Truyền cả dòng giảng viên (có khóa ho_ten, anh_dai_dien) thì sẽ hiện ảnh nếu có.
 */
function avatar(array|string $nguoi, string ...$tuyChon): string
{
    $vaiTro = 'giang_vien';
    $kichCo = '';
    foreach ($tuyChon as $t) {
        if (in_array($t, ['sinh_vien', 'giang_vien', 'quan_tri'], true)) {
            $vaiTro = $t;
        } elseif (in_array($t, ['sm', 'lg', 'xl'], true)) {
            $kichCo = 'avatar-' . $t;
        } elseif (str_starts_with($t, 'avatar-')) {
            $kichCo = $t;
        }
    }

    $hang = is_array($nguoi) ? $nguoi : ['ho_ten' => $nguoi];
    $hang['ho_ten'] = (string) ($hang['ho_ten'] ?? '');

    if ($vaiTro === 'giang_vien') {
        return lecturer_avatar($hang, $kichCo);
    }
    return '<span class="' . e(trim('avatar avatar-' . $vaiTro . ' ' . $kichCo)) . '" aria-hidden="true">'
        . e(initial($hang['ho_ten'])) . '</span>';
}
/** Avatar giảng viên: ảnh nếu có, không thì chữ cái đầu. $size: '' | 'avatar-lg' | 'avatar-xl'. Cần khóa ho_ten, anh_dai_dien trong $row. */
function lecturer_avatar(array $row, string $size = ''): string
{
    $cls = trim('avatar avatar-giang_vien ' . $size);
    $src = anh_dai_dien_url($row['anh_dai_dien'] ?? null);
    if ($src !== null) {
        return '<img class="' . e($cls) . ' avatar-anh" src="' . e($src) . '" alt="Ảnh của ' . e($row['ho_ten']) . '">';
    }
    return '<span class="' . e($cls) . '" aria-hidden="true">' . e(initial($row['ho_ten'])) . '</span>';
}

/** Tên hiển thị kèm học hàm, học vị: "PGS. TS. Trần Thu Hà". Cần các khóa ho_ten, hoc_ham, hoc_vi trong $row. */
function lecturer_title(array $row): string
{
    $prefix = trim(
        (HoSoGiangVien::HOC_HAM_NGAN[$row['hoc_ham'] ?? ''] ?? '') . ' ' . (HoSoGiangVien::HOC_VI_NGAN[$row['hoc_vi'] ?? ''] ?? '')
    );
    return ($prefix !== '' ? $prefix . ' ' : '') . $row['ho_ten'];
}