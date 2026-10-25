<?php
declare(strict_types=1);

/**
 * Module khung giờ tư vấn (slot) phía giảng viên.
 *  - index():     trang quản lý (HTML)
 *  - danhSach():  JSON, slot của chính giảng viên đăng nhập theo ngày (kèm cờ da_dat)
 *  - them/sua/xoa(): JSON, CRUD slot (POST, CSRF kiểm tra tập trung ở DinhTuyen)
 *  - slotTrong(): JSON, slot CÒN TRỐNG theo ngày + theo giảng viên (dành cho trang đặt lịch của sinh viên)
 */
class DieuKhienKhungGioGiangVien extends BoDieuKhien
{
    private KhungGioModel $khungGio;

    public function __construct()
    {
        $this->khungGio = new KhungGioModel();
    }

    public function index(): void
    {
        XacThuc::requireRole('giang_vien');
        $this->view('khuvuc/giangvien/quan_ly_khung_gio', [
            'title'   => 'Quản lý khung giờ tư vấn',
            'scripts' => ['js/khung-gio.js'],
        ]);
    }

    public function danhSach(): void
    {
        $maGv = $this->maGiangVienJson();
        $ngay = $this->ngayHopLe((string) ($_GET['ngay'] ?? ''));
        $this->json(['success' => true, 'data' => $this->khungGio->layTheoGiangVienVaNgay($maGv, $ngay)]);
    }

    public function them(): void
    {
        $maGv = $this->maGiangVienJson();
        [$bd, $kt, $dd] = $this->doiTuongHopLe($maGv, 0);

        $id = $this->khungGio->them($maGv, $bd, $kt, $dd);
        $this->json(['success' => true, 'message' => 'Thêm khung giờ thành công.', 'ma_khung_gio' => $id]);
    }

    public function sua(): void
    {
        $maGv = $this->maGiangVienJson();
        $id   = (int) ($_POST['ma_khung_gio'] ?? 0);

        if ($this->khungGio->timTheoId($id, $maGv) === null) {
            $this->json(['success' => false, 'message' => 'Khung giờ không tồn tại hoặc không thuộc về bạn.'], 404);
        }
        if ($this->khungGio->daCoNguoiDat($id)) {
            $this->json(['success' => false, 'message' => 'Khung giờ đã có sinh viên đặt, không thể sửa.'], 409);
        }
        [$bd, $kt, $dd] = $this->doiTuongHopLe($maGv, $id);

        $this->khungGio->capNhat($id, $maGv, $bd, $kt, $dd);
        $this->json(['success' => true, 'message' => 'Cập nhật khung giờ thành công.']);
    }

    public function xoa(): void
    {
        $maGv = $this->maGiangVienJson();
        $id   = (int) ($_POST['ma_khung_gio'] ?? 0);

        if ($this->khungGio->timTheoId($id, $maGv) === null) {
            $this->json(['success' => false, 'message' => 'Khung giờ không tồn tại hoặc không thuộc về bạn.'], 404);
        }
        if ($this->khungGio->coLichSuCuocHen($id)) {
            $this->json(['success' => false, 'message' => 'Khung giờ này đã gắn với cuộc hẹn nên không thể xóa.'], 409);
        }
        $this->khungGio->xoa($id, $maGv);
        $this->json(['success' => true, 'message' => 'Đã xóa khung giờ.']);
    }

    /** GET ?r=khung-gio/trong&ma_giang_vien=4&ngay=2027-02-04 — sinh viên hoặc giảng viên đã đăng nhập đều gọi được. */
    public function slotTrong(): void
    {
        if (!XacThuc::check()) {
            $this->json(['success' => false, 'message' => 'Vui lòng đăng nhập.'], 401);
        }
        $maGv = (int) ($_GET['ma_giang_vien'] ?? 0);
        if ($maGv <= 0) {
            $this->json(['success' => false, 'message' => 'Thiếu mã giảng viên.'], 422);
        }
        $ngay = $this->ngayHopLe((string) ($_GET['ngay'] ?? ''));
        $this->json(['success' => true, 'data' => $this->khungGio->layTheoGiangVienVaNgay($maGv, $ngay, true)]);
    }

    // ----- hàm phụ -----

    /** Endpoint JSON: trả 401/403 dạng JSON thay vì redirect HTML như requireRole(). */
    private function maGiangVienJson(): int
    {
        $me = XacThuc::user();
        if ($me === null) {
            $this->json(['success' => false, 'message' => 'Phiên đăng nhập đã hết hạn.'], 401);
        }
        if ($me['vai_tro'] !== 'giang_vien') {
            $this->json(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này.'], 403);
        }
        return (int) $me['ma_nguoi_dung'];
    }

    private function ngayHopLe(string $ngay): string
    {
        $d = DateTime::createFromFormat('Y-m-d', $ngay);
        return ($d && $d->format('Y-m-d') === $ngay) ? $ngay : date('Y-m-d');
    }

    /** Kiểm tra dữ liệu form (datetime-local gửi "Y-m-dTH:i"). Trả [bắt đầu, kết thúc, địa điểm] hoặc kết thúc bằng JSON 422. */
    private function doiTuongHopLe(int $maGv, int $boQua): array
    {
        $loi = fn(string $m) => $this->json(['success' => false, 'message' => $m], 422);

        $bd = DateTime::createFromFormat('Y-m-d\TH:i', $this->input('bat_dau'));
        $kt = DateTime::createFromFormat('Y-m-d\TH:i', $this->input('ket_thuc'));
        if (!$bd || !$kt) {
            $loi('Thời gian bắt đầu / kết thúc không hợp lệ.');
        }
        if ($kt <= $bd) {
            $loi('Thời gian kết thúc phải sau thời gian bắt đầu.');
        }
        if ($bd <= new DateTime()) {
            $loi('Khung giờ phải nằm trong tương lai.');
        }
        if ($bd->format('Y-m-d') !== $kt->format('Y-m-d')) {
            $loi('Khung giờ phải bắt đầu và kết thúc trong cùng một ngày.');
        }
        $diaDiem = mb_substr($this->input('dia_diem'), 0, 150);   // cột dia_diem là VARCHAR(150)

        $bdStr = $bd->format('Y-m-d H:i:s');
        $ktStr = $kt->format('Y-m-d H:i:s');
        if ($this->khungGio->biTrung($maGv, $bdStr, $ktStr, $boQua)) {
            $loi('Khung giờ này bị trùng với một khung giờ khác của bạn.');
        }
        return [$bdStr, $ktStr, $diaDiem === '' ? null : $diaDiem];
    }
}