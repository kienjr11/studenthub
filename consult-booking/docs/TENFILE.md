# Quy ước đặt tên file và bảng đối chiếu tên cũ – mới

## Quy ước

- **File chứa class** (thư mục `core/`, `models/`, `controllers/`): tên file trùng tên class, viết kiểu PascalCase, ví dụ `NguoiDung.php` chứa `class NguoiDung`. Trình tự nạp class tự động của `public/index.php` dựa vào quy ước này nên **không đổi tên một file mà không đổi tên class bên trong**.
- **File không chứa class** (view, bố cục, hàm trợ giúp, bảng route, cấu hình): chữ thường viết liền, ví dụ `formdangnhap.php`, `tuyenduong.php`.
- **Controller** đặt theo dạng `DieuKhien` + chức năng, ví dụ `DieuKhienXacThuc`.
- **Giữ nguyên:** `public/index.php` (máy chủ web tìm file này làm trang mặc định), cùng tên các hàm, phương thức, biến, route (`auth/login`...), tên trường trong form HTML và class CSS vẫn là tiếng Anh.
- Giá trị ENUM trong CSDL (`sinh_vien`, `hoat_dong`...) là tiếng Việt không dấu, khớp với `database/dat_lich_tu_van.sql`.

## Bảng đối chiếu

| Tên cũ | Tên mới | Ghi chú |
|---|---|---|
| `app/helpers.php` | `app/hamtro.php` | Hàm trợ giúp `e()`, `url()`, `config()`... |
| `app/routes.php` | `app/tuyenduong.php` | Bảng route |
| `app/config/config.example.php` | `app/config/cauhinh.mau.php` | Cấu hình mẫu (commit) |
| `app/config/config.local.php` | `app/config/cauhinh.local.php` | Cấu hình thật (không commit) |
| `core/Database.php` | `core/CoSoDuLieu.php` | Kết nối PDO |
| `core/Session.php` | `core/PhienLamViec.php` | Bảo mật phiên |
| `core/Csrf.php` | `core/ChongCsrf.php` | Chống CSRF |
| `core/Auth.php` | `core/XacThuc.php` | Đăng nhập và phân quyền |
| `core/Validator.php` | `core/KiemTraDuLieu.php` | Kiểm tra dữ liệu |
| `core/Router.php` | `core/DinhTuyen.php` | Định tuyến |
| `core/View.php` | `core/GiaoDien.php` | Hiển thị view |
| `core/Controller.php` | `core/BoDieuKhien.php` | Lớp cha của controller |
| `core/Model.php` | `core/MoHinh.php` | Lớp cha của model |
| `models/User.php` | `models/NguoiDung.php` | Bảng `nguoi_dung` |
| `models/Catalog.php` | `models/DanhMuc.php` | Bảng `don_vi` và `chuyen_mon` |
| `controllers/HomeController.php` | `controllers/DieuKhienTrangChu.php` | Trang chủ |
| `controllers/AuthController.php` | `controllers/DieuKhienXacThuc.php` | Đăng ký, đăng nhập, đăng xuất |
| `controllers/DashboardController.php` | `controllers/DieuKhienKhuVuc.php` | Khu vực sinh viên và giảng viên |
| `controllers/AdminController.php` | `controllers/DieuKhienQuanTri.php` | Tổng quan, tài khoản |
| `controllers/CatalogController.php` | `controllers/DieuKhienDanhMuc.php` | Danh mục đơn vị, chuyên môn |
| `views/layouts/main.php` | `views/bocuc/chinh.php` | Bố cục chính |
| `views/layouts/bare.php` | `views/bocuc/trong.php` | Bố cục tối giản (trang lỗi hệ thống) |
| `views/home/index.php` | `views/trangchu/trangchu.php` | Trang chủ |
| `views/auth/login.php` | `views/xacthuc/formdangnhap.php` | Form đăng nhập |
| `views/auth/register.php` | `views/xacthuc/formdangky.php` | Form đăng ký |
| `views/auth/_aside.php` | `views/xacthuc/_cotthuonghieu.php` | Cột thương hiệu bên trái |
| `views/admin/dashboard.php` | `views/quantri/bangdieukhien.php` | Bảng điều khiển admin |
| `views/admin/users.php` | `views/quantri/quanlynguoidung.php` | Quản lý tài khoản |
| `views/admin/catalog.php` | `views/quantri/quanlydanhmuc.php` | Quản lý danh mục |
| `views/dashboard/portal.php` | `views/khuvuc/trangcanhan.php` | Trang cá nhân theo vai trò |
| `views/errors/error.php` | `views/loi/loi.php` | Trang lỗi |
| `database/schema.sql` | `database/dat_lich_tu_van.sql` | Tên CSDL đổi từ `consult_booking` thành `dat_lich_tu_van` |
