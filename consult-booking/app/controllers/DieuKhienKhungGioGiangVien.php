<?php
class DieuKhienKhungGioGiangVien extends BoDieuKhien {
    private $khungGioModel;

    public function __construct() {
        // Kiểm tra đăng nhập theo chuẩn hệ thống
        $me = XacThuc::user();
        if (!$me) {
            header('Location: ' . url('auth/login'));
            exit;
        }
        // Khởi tạo model trực tiếp bằng từ khóa new
        $this->khungGioModel = new KhungGioModel();
    }

    // Hiển thị giao diện chính khi bấm vào menu
    public function index() {
        $me = XacThuc::user();
        $maGiangVien = $me['ma_nguoi_dung'] ?? PhienLamViec::lay('user_id');
        $this->view('khuvuc/giangvien/quan_ly_khung_gio', ['maGiangVien' => $maGiangVien]);
    }

    // API: Lấy danh sách slot tự động theo ngày (JSON)
    public function apiLayDanhSach() {
        header('Content-Type: application/json');
        $me = XacThuc::user();
        $maGiangVien = $_GET['ma_giang_vien'] ?? ($me['ma_nguoi_dung'] ?? PhienLamViec::lay('user_id'));
        $ngay = $_GET['ngay'] ?? date('Y-m-d');

        try {
            $danhSach = $this->khungGioModel->layTheoGiangVienVaNgay($maGiangVien, $ngay);
            echo json_encode(['success' => true, 'data' => $danhSach]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // API: Thêm slot
    public function apiThem() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $me = XacThuc::user();
            $maGiangVien = $me['ma_nguoi_dung'] ?? PhienLamViec::lay('user_id');
            $batDau = $_POST['bat_dau'] ?? '';
            $ketThuc = $_POST['ket_thuc'] ?? '';
            $diaDiem = $_POST['dia_diem'] ?? '';

            if ($this->khungGioModel->them($maGiangVien, $batDau, $ketThuc, $diaDiem)) {
                echo json_encode(['success' => true, 'message' => 'Thêm khung giờ thành công!']);
                return;
            }
        }
        echo json_encode(['success' => false, 'message' => 'Thêm thất bại!']);
    }

    // API: Sửa slot
    public function apiSua() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $me = XacThuc::user();
            $maGiangVien = $me['ma_nguoi_dung'] ?? PhienLamViec::lay('user_id');
            $maKhungGio = $_POST['ma_khung_gio'] ?? 0;
            $batDau = $_POST['bat_dau'] ?? '';
            $ketThuc = $_POST['ket_thuc'] ?? '';
            $diaDiem = $_POST['dia_diem'] ?? '';

            if ($this->khungGioModel->capNhat($maKhungGio, $maGiangVien, $batDau, $ketThuc, $diaDiem)) {
                echo json_encode(['success' => true, 'message' => 'Cập nhật thành công!']);
                return;
            }
        }
        echo json_encode(['success' => false, 'message' => 'Cập nhật thất bại!']);
    }

    // API: Xóa slot
    public function apiXoa() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $me = XacThuc::user();
            $maGiangVien = $me['ma_nguoi_dung'] ?? PhienLamViec::lay('user_id');
            $maKhungGio = $_POST['ma_khung_gio'] ?? 0;

            if ($this->khungGioModel->xoa($maKhungGio, $maGiangVien)) {
                echo json_encode(['success' => true, 'message' => 'Xóa thành công!']);
                return;
            }
        }
        echo json_encode(['success' => false, 'message' => 'Xóa thất bại!']);
    }
}