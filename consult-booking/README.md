# ConsultBook – Hệ thống đặt lịch tư vấn / hẹn gặp giảng viên

Bài tập lớn học phần Lập trình Web (PHP 8, MySQL, PDO, kiến trúc MVC) – Chủ đề 5.

Repo này gồm **khung dự án** và **module Hệ thống chung & Xác thực (Auth) + Admin** đã hoàn chỉnh. Các module còn lại (khung giờ, đặt lịch, phản hồi, thống kê) gắn vào khung này, xem mục "Gắn module mới".

## Chức năng của module này

- Trang chủ giới thiệu, điều hướng thay đổi theo vai trò.
- Đăng ký / đăng nhập / đăng xuất cho Sinh viên, Giảng viên, Admin.
  - Sinh viên đăng ký xong dùng được ngay. Giảng viên đăng ký xong ở trạng thái **chờ duyệt** cho tới khi admin phê duyệt. Admin không đăng ký công khai (tạo bằng dữ liệu mẫu hoặc do admin khác phân quyền).
  - Kiểm tra email trùng ngay khi gõ bằng Fetch API (endpoint JSON `auth/check-email`).
- Tài khoản của tôi (mọi vai trò): sửa họ tên, giảng viên chọn lĩnh vực tư vấn, đổi mật khẩu (phải nhập lại mật khẩu hiện tại; đổi xong các thiết bị khác bị đăng xuất).
- Hồ sơ & chuyên môn của giảng viên: tự sửa họ tên, đơn vị, học hàm, học vị, số điện thoại, phòng làm việc, giới thiệu và lĩnh vực tư vấn; có bảng điều khiển (mức đầy đủ của hồ sơ, thống kê, lịch hẹn sắp tới).
- Tìm giảng viên (mọi người dùng đã đăng nhập): tìm theo tên, lọc theo đơn vị / lĩnh vực, phân trang, xem trang chi tiết.
- Đặt lịch và phản hồi: sinh viên chọn giảng viên và giờ hẹn (chặn giờ trong quá khứ và trùng lịch), xem lịch hẹn của mình và đánh giá sau khi cuộc hẹn được duyệt / hoàn thành; giảng viên duyệt, từ chối, hoàn thành và ghi chú phản hồi.
- Admin Dashboard:
  - Số liệu tổng quan và danh sách giảng viên chờ duyệt.
  - Quản lý tài khoản: tìm kiếm, lọc theo vai trò/trạng thái, phân trang, khóa / mở khóa / phê duyệt, đổi vai trò và đơn vị.
  - Quản lý danh mục **Đơn vị** và **Chuyên môn** (thêm, sửa, xóa; không xóa được mục đang được sử dụng).

## Yêu cầu môi trường

- PHP 8.1 trở lên, bật `pdo_mysql` và `mbstring` (XAMPP bản mới đã có sẵn).
- MySQL 5.7+ / 8.x hoặc MariaDB 10.4+.
- Trình duyệt có Internet khi demo (Bootstrap 5 và font Be Vietnam Pro tải từ CDN).

## Cài đặt

1. Tạo CSDL và dữ liệu mẫu (xóa và tạo lại các bảng, chỉ dùng cho dev/demo):
   ```bash
   mysql -u root -p < database/dat_lich_tu_van.sql
   ```
   Hoặc dùng phpMyAdmin: Import → chọn `database/dat_lich_tu_van.sql`.
2. Tạo file cấu hình riêng (file này đã nằm trong `.gitignore`, không bị đẩy lên repo):
   ```bash
   cp app/config/cauhinh.mau.php app/config/cauhinh.local.php
   ```
   Tên CSDL trong file này là `dat_lich_tu_van` (khớp với file SQL). Sửa `db.user`, `db.pass` cho khớp máy của bạn (Laragon: `root`, mật khẩu trống). Trên Windows có thể dùng `copy` thay cho `cp`. Khi đang code có thể đặt `app.debug` = `true` để xem chi tiết lỗi, nhớ đặt lại `false` khi demo.
3. Chạy:
   ```bash
   php -S localhost:8000 -t public
   ```
   rồi mở http://localhost:8000. Với XAMPP: đặt thư mục dự án trong `htdocs` và mở `http://localhost/consult-booking/public/`.

## Tài khoản demo (mật khẩu chung: `Demo@12345`)

| Email | Vai trò | Trạng thái | Dùng để kiểm thử |
|---|---|---|---|
| admin@demo.test | Admin | Hoạt động | Quản trị |
| gv.minh@demo.test, gv.ha@demo.test | Giảng viên | Hoạt động | Luồng giảng viên |
| gv.huy@demo.test | Giảng viên | Chờ duyệt | Admin phê duyệt |
| sv.an@demo.test, sv.bao@demo.test | Sinh viên | Hoạt động | Luồng sinh viên |
| sv.khoi@demo.test | Sinh viên | Đã khóa | Đăng nhập tài khoản bị khóa |

Tài khoản demo chỉ dành cho môi trường dev/demo. Đừng dùng mật khẩu này ở nơi công khai.

## Cấu trúc thư mục

```
public/                 Thư mục gốc web: index.php (front controller, giữ nguyên tên) + assets/
app/
  config/               cauhinh.mau.php (commit), cauhinh.local.php (không commit)
  core/                 CoSoDuLieu, PhienLamViec, ChongCsrf, XacThuc, KiemTraDuLieu, DinhTuyen, GiaoDien, BoDieuKhien, MoHinh
  models/               NguoiDung, DanhMuc (Đơn vị / Chuyên môn), CuocHen, HoSoGiangVien, PhanHoi
  controllers/          DieuKhienTrangChu, DieuKhienXacThuc, DieuKhienKhuVuc, DieuKhienQuanTri, DieuKhienDanhMuc, DieuKhienTaiKhoan, DieuKhienHoSoGiangVien, DieuKhienLichHenGiangVien, DieuKhienPhanHoi
  views/                bocuc/, trangchu/, xacthuc/, quantri/, khuvuc/, taikhoan/, hoso/, loi/
  tuyenduong.php        Bảng route: [phương thức, route, controller, action]
  hamtro.php            e(), url(), redirect(), flash(), config() ...
database/
  dat_lich_tu_van.sql   Cấu trúc bảng + khóa ngoại + dữ liệu mẫu (tên bảng, cột, giá trị ENUM đều tiếng Việt không dấu)
docs/
  DATABASE.md           Giải thích CSDL: ERD, từ điển dữ liệu, ràng buộc
  TENFILE.md            Bảng đối chiếu tên file/class cũ và mới, quy ước đặt tên
  TESTCASES.md          Checklist kiểm thử (37 trường hợp)
```

## Luồng route → controller → model → view

Ví dụ đăng nhập (`POST ?r=auth/login`):

1. `public/index.php` khởi tạo session, gọi `DinhTuyen::dispatch()`.
2. `DinhTuyen` tìm `POST auth/login` trong `tuyenduong.php`, kiểm tra CSRF token (mọi route POST đều bị kiểm tra ở đây), rồi gọi `DieuKhienXacThuc::login()`.
3. Controller nhận dữ liệu, gọi `NguoiDung::findByEmail()` (model, PDO prepared statement) và `password_verify()`.
4. Thành công: `XacThuc::login()` đổi session id rồi chuyển hướng tới `dashboard/index`. Thất bại: `GiaoDien::render('xacthuc/formdangnhap')` (file `app/views/xacthuc/formdangnhap.php`) hiển thị lại biểu mẫu kèm lỗi.

## Bảo mật (đối chiếu rubric)

| Yêu cầu | Cách làm trong code |
|---|---|
| Prepared statement | Mọi truy vấn trong `app/models/` dùng PDO prepared statement, `EMULATE_PREPARES = false`. Tên bảng của `DanhMuc` lấy từ danh sách trắng. |
| Băm mật khẩu | `password_hash(PASSWORD_DEFAULT)` khi đăng ký, `password_verify()` khi đăng nhập, `password_needs_rehash()` để nâng cấp hash. |
| Validation server | Lớp `KiemTraDuLieu`: bắt buộc, độ dài, định dạng email, danh sách vai trò hợp lệ, độ mạnh mật khẩu (tối thiểu 8 ký tự, có chữ và số, tối đa 72 byte). |
| Mã hóa output | Mọi giá trị in ra HTML đều qua `e()` (`htmlspecialchars`). Không có inline script/style. |
| Session | Cookie `HttpOnly`, `SameSite=Lax`, `Secure` khi chạy HTTPS, strict mode, đổi session id sau đăng nhập và đăng xuất, hết phiên sau 30 phút không thao tác. |
| CSRF | Token ngẫu nhiên theo phiên, kiểm tra tập trung trong `DinhTuyen` cho mọi POST (form dùng trường `_csrf`, Fetch dùng header `X-CSRF-Token`), đăng xuất bằng POST. |
| Kiểm soát truy cập | `XacThuc::requireRole()` ở server. Người dùng được đọc lại từ CSDL ở mỗi request nên khóa tài khoản hoặc đổi vai trò có hiệu lực ngay. Admin không tự khóa hoặc tự đổi vai trò của mình. |
| Đổi mật khẩu | Phải nhập lại mật khẩu hiện tại; sai 5 lần thì bị đăng xuất và khóa tạm (dùng chung bộ đếm với đăng nhập). Đổi xong, session id được cấp mới và mọi phiên khác của tài khoản bị vô hiệu nhờ \"dấu vân tay\" mật khẩu lưu trong phiên (không cần thêm cột CSDL). |
| Chống dò mật khẩu / email | Khóa tạm 10 phút sau 5 lần sai; thông báo lỗi chung cho email sai và mật khẩu sai; hash giả để thời gian phản hồi gần bằng nhau; endpoint check-email giới hạn 20 lượt/phút/phiên. |
| Header | CSP, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`. |
| Quản lý mã | `cauhinh.local.php` và `.env` nằm trong `.gitignore`; repo chỉ chứa `cauhinh.mau.php`. |

## Gắn module mới (cho các thành viên khác)

1. Thêm controller vào `app/controllers/`, trong `__construct()` hoặc từng action gọi `XacThuc::requireRole('sinh_vien')` (hoặc `'giang_vien'`).
2. Thêm dòng route vào `app/tuyenduong.php`. Mọi POST được kiểm tra CSRF tự động, trong form chỉ cần `<?= csrf_field() ?>`.
3. Thêm model vào `app/models/` (kế thừa `MoHinh`, dùng `$this->db`), view vào `app/views/<module>/`.
4. Các bảng `khung_gio`, `cuoc_hen`, `phan_hoi` trong `dat_lich_tu_van.sql` là khung tham khảo. Bảng `cuoc_hen` đã ép quy tắc "một khung giờ chỉ có một cuộc hẹn đang hiệu lực" bằng cột sinh tự động `khung_gio_hieu_luc` và `UNIQUE`, hãy chỉnh theo ERD nhóm bạn thống nhất. Mô tả đầy đủ ở `docs/DATABASE.md`.

## Gợi ý lịch sử Git

Mỗi tính năng một nhánh nhỏ và commit rõ nghĩa, ví dụ: `feat(auth): đăng ký và băm mật khẩu`, `feat(auth): khóa tạm sau 5 lần đăng nhập sai`, `feat(admin): lọc và phân trang tài khoản`, `docs: checklist kiểm thử`. Rubric chấm cả lịch sử Git nên tránh một commit khổng lồ ngay trước hạn nộp.

## Giới hạn đã biết

- Chưa có quên mật khẩu, xác thực email, hay trang sửa hồ sơ cá nhân.
- Phần "cấu hình hệ thống" của Admin mới có danh mục đơn vị/chuyên môn; chưa có bảng tham số (độ dài slot mặc định, hạn hủy lịch...).
- Việc gán chuyên môn cho giảng viên (`giang_vien_chuyen_mon`) thuộc module hồ sơ giảng viên; module này mới tạo bảng và hiển thị số lượng đang dùng.
- Chưa có nhật ký thao tác của admin (audit log).
