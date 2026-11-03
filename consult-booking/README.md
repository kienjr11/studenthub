# ConsultBook – Hệ thống đặt lịch tư vấn / hẹn gặp giảng viên

Bài tập lớn học phần Lập trình Web (PHP 8, MySQL, PDO, kiến trúc MVC) – Chủ đề 5.

Hệ thống kết nối sinh viên với giảng viên: giảng viên mở khung giờ tư vấn, sinh viên đặt lịch, giảng viên duyệt và ghi chú, sinh viên đánh giá sau buổi tư vấn. Admin quản lý tài khoản, danh mục và theo dõi toàn bộ lịch hẹn.

## Chức năng

**Chung**
- Trang chủ giới thiệu, thanh điều hướng thay đổi theo vai trò.
- Đăng nhập / đăng xuất cho Sinh viên, Giảng viên, Admin. **Không có đăng ký công khai**: tài khoản do Admin tạo (từng người hoặc nhập hàng loạt từ CSV).
- Tài khoản của tôi (mọi vai trò): sửa họ tên, đổi mật khẩu (phải nhập lại mật khẩu hiện tại; đổi xong các thiết bị khác bị đăng xuất). Giảng viên chọn thêm lĩnh vực tư vấn.
- Tìm giảng viên (mọi người dùng đã đăng nhập): tìm theo tên, lọc theo đơn vị / lĩnh vực, phân trang, xem trang chi tiết và lịch rảnh sắp tới.

**Giảng viên**
- Hồ sơ: họ tên, đơn vị, học hàm, học vị, số điện thoại, phòng làm việc, giới thiệu, ảnh đại diện (được xử lý lại bằng GD: cắt vuông 400x400, lưu JPEG tên ngẫu nhiên).
- Bảng điều khiển: mức đầy đủ của hồ sơ, số yêu cầu cần duyệt, lịch hẹn hôm nay, số buổi đã hoàn thành, điểm đánh giá trung bình, lịch hẹn sắp tới.
- Quản lý khung giờ tư vấn: thêm / sửa / xóa khung giờ, mỗi khung giờ có địa điểm (hoặc link họp online), môn tư vấn và sức chứa từ 1 đến 50 sinh viên. Khung giờ đã có sinh viên đăng ký thì chỉ sửa được địa điểm và sức chứa. Bấm biểu tượng "con mắt" để xem danh sách sinh viên đăng ký, duyệt tất cả / hoàn thành tất cả.
- Quản lý lịch hẹn tư vấn: **lọc theo trạng thái và theo ngày**, phân trang; duyệt, từ chối, hoàn thành, hủy (bắt buộc nhập lý do), ghi chú phản hồi và xem đánh giá của sinh viên. Ô "Lịch hẹn hôm nay" trên bảng điều khiển dẫn thẳng tới danh sách đã lọc theo ngày.

**Sinh viên**
- Đặt lịch theo hai cách: chọn một khung giờ rảnh có sẵn của giảng viên, hoặc tự chọn ngày giờ (khi đó giảng viên nhập địa điểm lúc duyệt). Chặn giờ trong quá khứ, trùng lịch của sinh viên và trùng lịch của giảng viên; khung giờ đủ chỗ thì không đặt thêm được.
- Xem lịch hẹn của mình, lọc theo trạng thái, hủy lịch (bắt buộc nhập lý do), đánh giá 1–5 sao kèm nhận xét sau khi cuộc hẹn đã hoàn thành.
- Link họp online chỉ hiện sau khi giảng viên duyệt; lịch bị từ chối / hủy thì link bị vô hiệu.

**Admin**
- Bảng điều khiển: số liệu tài khoản, thống kê lịch hẹn (tổng theo trạng thái và **theo ngày hẹn**, chọn khoảng ngày tối đa 62 ngày), danh sách giảng viên chờ duyệt.
- Quản lý tài khoản: tìm kiếm, lọc theo vai trò / trạng thái, phân trang, khóa / mở khóa / phê duyệt, đổi vai trò và đơn vị. Khóa tài khoản sẽ tự đóng các lịch hẹn sắp diễn ra liên quan tới tài khoản đó.
- Thêm tài khoản thủ công, hoặc nhập hàng loạt từ file CSV (tối đa 200 dòng, 1 MB; có file mẫu; mật khẩu để trống thì hệ thống tự sinh; tải lại danh sách mật khẩu tạm trong 30 phút).
- Quản lý danh mục **Đơn vị** và **Chuyên môn** (thêm, sửa, xóa; không xóa được mục đang được sử dụng).
- Xem danh sách cuộc hẹn toàn hệ thống, lọc theo trạng thái.

**Quy tắc trạng thái cuộc hẹn**

| Trạng thái | Ý nghĩa và quy tắc |
|---|---|
| Chờ duyệt | Sinh viên vừa đặt. Yêu cầu chờ duyệt vẫn giữ chỗ. Nếu đã qua giờ bắt đầu mà giảng viên chưa duyệt thì hệ thống **tự động từ chối**. |
| Đã duyệt | Giảng viên chỉ hủy được **trước** giờ bắt đầu và phải nhập lý do. Chỉ bấm "Hoàn thành" được **sau** giờ kết thúc. Quá 24 giờ sau giờ kết thúc mà chưa bấm thì hệ thống **tự hoàn thành**. |
| Hoàn thành | Sinh viên được đánh giá; giảng viên vẫn ghi chú được. |
| Đã từ chối / Đã hủy | Trả lại chỗ cho khung giờ. Lý do hủy được lưu và hiển thị cho các bên liên quan. |

## Yêu cầu môi trường

- PHP 8.1 trở lên, bật các extension `pdo_mysql`, `mbstring`, `gd` và `fileinfo` (hai extension sau dùng để xử lý ảnh đại diện; `exif` là tùy chọn để xoay ảnh chụp bằng điện thoại). XAMPP / Laragon bản mới thường đã bật sẵn.
- MySQL 8.x (file `schema.sql` được xuất từ MySQL 8.4; các ràng buộc `CHECK` chỉ có hiệu lực từ MySQL 8.0.16).
- Trình duyệt có Internet khi demo (Bootstrap 5 và font Be Vietnam Pro tải từ CDN).

## Cài đặt

1. Tạo CSDL và dữ liệu mẫu. File `database/schema.sql` dùng `CREATE TABLE IF NOT EXISTS` và `INSERT` thường, **không tự xóa dữ liệu cũ**. Vì vậy:
   - Lần đầu: import trực tiếp.
     ```bash
     mysql -u root -p < database/schema.sql
     ```
     (hoặc dùng phpMyAdmin / HeidiSQL: Import → chọn `database/schema.sql`).
   - Muốn nạp lại từ đầu (chỉ dùng cho dev/demo, **mất toàn bộ dữ liệu hiện có**): xóa CSDL cũ trước rồi import lại.
     ```bash
     mysql -u root -p -e "DROP DATABASE IF EXISTS dat_lich_tu_van"
     mysql -u root -p < database/schema.sql
     ```
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

Tất cả tài khoản dưới đây đang ở trạng thái **Hoạt động**.

| Email | Vai trò | Dùng để kiểm thử |
|---|---|---|
| admin@demo.test | Admin | Quản trị, xem toàn bộ lịch hẹn, khóa / mở khóa tài khoản |
| gv.minh@demo.test | Giảng viên (CNTT) | Khung giờ nhiều sức chứa, lịch hẹn đã duyệt / chờ duyệt |
| gv.ha@demo.test | Giảng viên (Kinh tế) | Khung giờ, lịch hẹn đã hoàn thành có đánh giá |
| gv.huy@demo.test | Giảng viên (CNTT) | Màn hình "Quản lý lịch hẹn tư vấn": lọc theo trạng thái và ngày |
| sv.an@demo.test, sv.bao@demo.test, sv.khoi@demo.test | Sinh viên | Đặt lịch, hủy lịch, đánh giá |

Dữ liệu mẫu còn có thêm các sinh viên / giảng viên khác (`sv.*@demo.test`, `gv.*@demo.test`) để có sẵn lịch hẹn ở đủ các trạng thái. Các tài khoản `@import.test` là kết quả thử chức năng nhập CSV, mật khẩu do hệ thống sinh ngẫu nhiên nên không dùng để đăng nhập demo.

Muốn thử các luồng không có sẵn trong dữ liệu mẫu: đăng nhập admin, **khóa** một tài khoản rồi đăng nhập bằng tài khoản đó để thấy thông báo bị khóa, sau đó **mở khóa**.

Dữ liệu mẫu dùng ngày cố định (cuối tháng 10 đến tháng 12/2026) nên theo thời gian, các yêu cầu "chờ duyệt" quá hạn sẽ bị hệ thống tự từ chối. Muốn có dữ liệu mới, hãy nạp lại CSDL hoặc tạo khung giờ / đặt lịch mới.

Tài khoản demo chỉ dành cho môi trường dev/demo. Đừng dùng mật khẩu này ở nơi công khai.

## Cấu trúc thư mục

```
public/                 Thư mục gốc web: index.php (front controller, giữ nguyên tên) + assets/ (css, js) + uploads/avatars (ảnh đại diện)
app/
  config/               cauhinh.mau.php (commit), cauhinh.local.php (không commit)
  core/                 CoSoDuLieu, PhienLamViec, ChongCsrf, XacThuc, KiemTraDuLieu, DinhTuyen, GiaoDien, BoDieuKhien, MoHinh, XuLyAnhDaiDien
  models/               NguoiDung, DanhMuc (Đơn vị / Chuyên môn), CuocHen, KhungGioModel, HoSoGiangVien, PhanHoi
  controllers/          DieuKhienTrangChu, DieuKhienXacThuc, DieuKhienKhuVuc, DieuKhienQuanTri, DieuKhienDanhMuc, DieuKhienTaiKhoan, DieuKhienHoSoGiangVien, DieuKhienKhungGioGiangVien, DieuKhienLichHenGiangVien, DieuKhienPhanHoi
  views/                bocuc/, trangchu/, xacthuc/, quantri/, khuvuc/, taikhoan/, hoso/, loi/
  tuyenduong.php        Bảng route: [phương thức, route, controller, action]
  hamtro.php            e(), url(), redirect(), flash(), config() ...
database/
  schema.sql   Cấu trúc bảng + khóa ngoại + dữ liệu mẫu (tên bảng, cột, giá trị ENUM đều tiếng Việt không dấu)
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
| Băm mật khẩu | `password_hash(PASSWORD_DEFAULT)` khi tạo tài khoản và đổi mật khẩu, `password_verify()` khi đăng nhập, `password_needs_rehash()` để nâng cấp hash. |
| Validation server | Lớp `KiemTraDuLieu`: bắt buộc, độ dài, định dạng email, danh sách vai trò hợp lệ, độ mạnh mật khẩu (tối thiểu 8 ký tự, có chữ và số, tối đa 72 byte). Ngày lọc, ngày giờ đặt lịch, sức chứa, địa điểm đều được kiểm tra ở server. |
| Mã hóa output | Mọi giá trị in ra HTML đều qua `e()` (`htmlspecialchars`). Không có inline script/style. |
| Session | Cookie `HttpOnly`, `SameSite=Lax`, `Secure` khi chạy HTTPS, strict mode, đổi session id sau đăng nhập và đăng xuất, hết phiên sau 30 phút không thao tác. |
| CSRF | Token ngẫu nhiên theo phiên, kiểm tra tập trung trong `DinhTuyen` cho mọi POST (form dùng trường `_csrf`, Fetch dùng header `X-CSRF-Token`), đăng xuất bằng POST. |
| Kiểm soát truy cập | `XacThuc::requireRole()` ở server. Người dùng được đọc lại từ CSDL ở mỗi request nên khóa tài khoản hoặc đổi vai trò có hiệu lực ngay. Admin không tự khóa hoặc tự đổi vai trò của mình. Giảng viên chỉ thao tác được trên khung giờ và cuộc hẹn của chính mình; sinh viên chỉ hủy / đánh giá lịch của chính mình. |
| Đổi mật khẩu | Phải nhập lại mật khẩu hiện tại; sai 5 lần thì bị đăng xuất và khóa tạm (dùng chung bộ đếm với đăng nhập). Đổi xong, session id được cấp mới và mọi phiên khác của tài khoản bị vô hiệu nhờ "dấu vân tay" mật khẩu lưu trong phiên (không cần thêm cột CSDL). |
| Chống dò mật khẩu / email | Khóa tạm 10 phút sau 5 lần sai; thông báo lỗi chung cho email sai và mật khẩu sai; hash giả để thời gian phản hồi gần bằng nhau. |
| Chống đặt trùng | Mọi thao tác đặt lịch chạy trong transaction có khóa dòng (`FOR UPDATE`); cột sinh tự động `khung_gio_hieu_luc` hỗ trợ kiểm tra chỗ trống. |
| Tải file | Ảnh đại diện: kiểm tra loại thật bằng `finfo` + `getimagesize`, giới hạn 2 MB và số điểm ảnh, mã hóa lại bằng GD (xóa EXIF / mã nhúng), tên file ngẫu nhiên. File CSV nhập tài khoản: giới hạn 1 MB / 200 dòng; file CSV xuất ra có chống CSV injection. |
| Header | CSP, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`. |
| Quản lý mã | `cauhinh.local.php` và `.env` nằm trong `.gitignore`; repo chỉ chứa `cauhinh.mau.php`. |

## Mở rộng thêm chức năng

1. Thêm controller vào `app/controllers/`, trong `__construct()` hoặc từng action gọi `XacThuc::requireRole('sinh_vien')` (hoặc `'giang_vien'`).
2. Thêm dòng route vào `app/tuyenduong.php`. Mọi POST được kiểm tra CSRF tự động, trong form chỉ cần `<?= csrf_field() ?>`.
3. Thêm model vào `app/models/` (kế thừa `MoHinh`, dùng `$this->db`), view vào `app/views/<module>/`.
4. Khi sửa nghiệp vụ đặt lịch, lưu ý quy tắc "một khung giờ chỉ giữ chỗ cho tối đa `suc_chua` yêu cầu còn hiệu lực" được ép bằng cột sinh tự động `khung_gio_hieu_luc` của bảng `cuoc_hen` (chỉ có KEY thường, vì khung giờ có thể có sức chứa > 1) kết hợp kiểm tra trong transaction `FOR UPDATE` ở `CuocHen::datVaoKhungGio` và `CuocHen::taoCuocHen`. Mô tả đầy đủ ở `docs/DATABASE.md`.

## Gợi ý lịch sử Git

Mỗi tính năng một nhánh nhỏ và commit rõ nghĩa, ví dụ: `feat(auth): băm mật khẩu và khóa tạm sau 5 lần đăng nhập sai`, `feat(lich-hen): lọc lịch hẹn theo ngày`, `feat(admin): lọc và phân trang tài khoản`, `docs: checklist kiểm thử`. Rubric chấm cả lịch sử Git nên tránh một commit khổng lồ ngay trước hạn nộp.

## Giới hạn đã biết

- Chưa có quên mật khẩu, xác thực email, đăng ký công khai.
- Khi giảng viên từ chối một yêu cầu thì chưa bắt buộc / chưa có ô nhập lý do (khác với hủy lịch đã duyệt luôn phải có lý do).
- Lịch sinh viên tự chọn giờ chưa giới hạn thời lượng và khung giờ trong ngày.
- Phần "cấu hình hệ thống" của Admin mới có danh mục đơn vị / chuyên môn; chưa có bảng tham số (độ dài slot mặc định, hạn hủy lịch...). Mốc tự hoàn thành (24 giờ) đang là hằng số `CuocHen::GIO_TU_DONG_HOAN_THANH`.
- Chưa có thông báo (email / trong ứng dụng) khi lịch hẹn đổi trạng thái.
- Chưa có nhật ký thao tác của admin (audit log).
