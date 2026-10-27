<?php
declare(strict_types=1);

/**
 * Module khung giờ tư vấn (slot) phía giảng viên.
 *  - index():          trang quản lý (HTML)
 *  - danhSach():       JSON, slot của giảng viên đăng nhập theo ngày (kèm suc_chua / so_dang_ky)
 *  - them/sua/xoa():   JSON, CRUD slot (POST, CSRF kiểm tra tập trung ở DinhTuyen)
 *  - sinhVienDangKy(): JSON, danh sách sinh viên đã đăng ký 1 slot (nút "con mắt")
 *  - slotTrong():      JSON, slot CÒN CHỖ theo ngày + theo giảng viên (cho trang đặt lịch của sinh viên)
 */
class DieuKhienKhungGioGiangVien extends BoDieuKhien
{
    private const SUC_CHUA_TOI_DA = 50;

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
        $sucChua = $this->sucChuaHopLe(1);

        $id = $this->khungGio->them($maGv, $bd, $kt, $dd, $sucChua);
        $this->json(['success' => true, 'message' => 'Thêm khung giờ thành công.', 'ma_khung_gio' => $id]);
    }

    public function sua(): void
    {
        $maGv = $this->maGiangVienJson();
        $id   = (int) ($_POST['ma_khung_gio'] ?? 0);

        $slot = $this->khungGio->timTheoId($id, $maGv);
        if ($slot === null) {
            $this->json(['success' => false, 'message' => 'Khung giờ không tồn tại hoặc không thuộc về bạn.'], 404);
        }
        if (strtotime((string) $slot['bat_dau']) <= time()) {
            $this->json(['success' => false, 'message' => 'Khung giờ đã qua, không thể sửa.'], 409);
        }

        $daDangKy = $this->khungGio->demDangKy($id);
        $sucChua  = $this->sucChuaHopLe(max(1, $daDangKy));

        if ($daDangKy > 0) {
            // Đã có sinh viên đăng ký: GIỮ NGUYÊN giờ, chỉ cho đổi địa điểm và số lượng tối đa.
            $dd = mb_substr($this->input('dia_diem'), 0, 150);
            $this->khungGio->capNhat($id, $maGv, (string) $slot['bat_dau'], (string) $slot['ket_thuc'], $dd === '' ? null : $dd, $sucChua);
        } else {
            [$bd, $kt, $dd] = $this->doiTuongHopLe($maGv, $id);
            $this->khungGio->capNhat($id, $maGv, $bd, $kt, $dd, $sucChua);
        }
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

    /** GET ?r=giang-vien/khung-gio/sinh-vien&ma_khung_gio=5 — chỉ giảng viên SỞ HỮU slot mới xem được. */
    public function sinhVienDangKy(): void
    {
        $maGv = $this->maGiangVienJson();
        $id   = (int) ($_GET['ma_khung_gio'] ?? 0);

        $slot = $this->khungGio->timTheoId($id, $maGv);
        if ($slot === null) {
            $this->json(['success' => false, 'message' => 'Khung giờ không tồn tại hoặc không thuộc về bạn.'], 404);
        }
        $this->json([
            'success'    => true,
            'slot'       => [
                'bat_dau'  => $slot['bat_dau'],
                'ket_thuc' => $slot['ket_thuc'],
                'dia_diem' => $slot['dia_diem'],
                'suc_chua' => (int) $slot['suc_chua'],
            ],
            'so_dang_ky' => $this->khungGio->demDangKy($id),
            'data'       => $this->khungGio->layDanhSachDangKy($id),
        ]);
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

    /** Số lượng sinh viên tối đa: số nguyên 1..50 và không nhỏ hơn số sinh viên đã đăng ký ($toiThieu). */
    private function sucChuaHopLe(int $toiThieu): int
    {
        $v = filter_var($this->input('suc_chua'), FILTER_VALIDATE_INT);
        if ($v === false || $v < 1 || $v > self::SUC_CHUA_TOI_DA) {
            $this->json(['success' => false, 'message' => 'Số lượng sinh viên phải từ 1 đến ' . self::SUC_CHUA_TOI_DA . '.'], 422);
        }
        if ($v < $toiThieu) {
            $this->json(['success' => false, 'message' => "Đã có $toiThieu sinh viên đăng ký, số lượng tối đa không được nhỏ hơn $toiThieu."], 422);
        }
        return $v;
    }

    /** Đọc "2026-10-27T10:31" (hoặc có giây / dùng dấu cách thay chữ T); trả null nếu sai định dạng. */
    private function docThoiGian(string $chuoi): ?DateTime
    {
        foreach (['Y-m-d\TH:i', 'Y-m-d\TH:i:s', 'Y-m-d H:i', 'Y-m-d H:i:s'] as $dinhDang) {
            $d = DateTime::createFromFormat($dinhDang, $chuoi);
            if ($d !== false && $d->format($dinhDang) === $chuoi) {
                return $d;
            }
        }
        return null;
    }

    /** Kiểm tra dữ liệu form (JS gửi "Y-m-dTH:i"). Trả [bắt đầu, kết thúc, địa điểm] hoặc kết thúc bằng JSON 422. */
    private function doiTuongHopLe(int $maGv, int $boQua): array
    {
        $loi = fn(string $m) => $this->json(['success' => false, 'message' => $m], 422);

        $bd = $this->docThoiGian($this->input('bat_dau'));
        $kt = $this->docThoiGian($this->input('ket_thuc'));
        if (!$bd || !$kt) {
            // Kèm giá trị server nhận được để dễ tìm nguyên nhân khi gặp lỗi định dạng
            $loi('Thời gian bắt đầu / kết thúc không hợp lệ (server nhận: "' . $this->input('bat_dau') . '" → "' . $this->input('ket_thuc') . '").');
        }
        if ($kt <= $bd) {
            $loi('Thời gian kết thúc phải sau thời gian bắt đầu.');
        }
        if ($bd <= new DateTime()) {
            $loi('Giờ bắt đầu phải sau thời điểm hiện tại (giờ máy chủ: ' . (new DateTime())->format('H:i d/m/Y') . ').');
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