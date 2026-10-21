# Checklist kiểm thử – Module Hệ thống chung & Xác thực (Auth) + Admin

Tài khoản demo dùng mật khẩu `Demo@12345` (xem README). Cột **Kết quả thực tế** để bạn điền khi tự chạy lại trước buổi bảo vệ (Đạt / Không đạt + ghi chú).
Loại: **H** = dữ liệu hợp lệ, **S** = dữ liệu sai, **Q** = truy cập sai quyền.

| STT | Loại | Tình huống | Các bước | Kết quả mong đợi | Kết quả thực tế |
|---|---|---|---|---|---|
| 1 | H | Đăng ký sinh viên hợp lệ | Vào Đăng ký, chọn Sinh viên, nhập họ tên, email mới, mật khẩu `Abcdef12` (2 lần) | Chuyển về trang đăng nhập, có thông báo thành công; tài khoản `hoat_dong`, mật khẩu trong CSDL là chuỗi băm `$2y$...` | |
| 2 | H | Đăng ký giảng viên hợp lệ | Chọn Giảng viên, chọn đơn vị, nhập thông tin hợp lệ | Thông báo cần admin phê duyệt; tài khoản `cho_duyet`; chưa đăng nhập được | |
| 3 | S | Tự đăng ký quyền admin | Sửa HTML hoặc gửi POST với `role=quan_tri` | Bị từ chối "Vai trò không hợp lệ"; không có tài khoản nào được tạo | |
| 4 | S | Email sai định dạng / để trống | Nhập `abc`, rồi bỏ trống | Báo lỗi dưới ô email; dữ liệu đã nhập (trừ mật khẩu) được giữ lại | |
| 5 | S | Email đã tồn tại (khác hoa/thường) | Đăng ký với `SV.AN@demo.test` | Báo "Email này đã được đăng ký" | |
| 6 | S | Mật khẩu yếu | Nhập `abcdefgh` (thiếu số) và `Ab1` (quá ngắn) | Báo lỗi yêu cầu mật khẩu; không tạo tài khoản | |
| 7 | S | Xác nhận mật khẩu không khớp | Nhập hai mật khẩu khác nhau | Báo "Mật khẩu xác nhận không khớp" | |
| 8 | S | Họ tên chứa thẻ HTML | Nhập `<script>alert(1)</script>` | Bị từ chối do chứa ký tự không hợp lệ; không có script nào chạy | |
| 9 | H | Đăng nhập đúng ba vai trò | Đăng nhập `sv.an@`, `gv.minh@`, `admin@` | Lần lượt vào khu vực sinh viên, giảng viên, bảng điều khiển quản trị | |
| 10 | S | Đăng nhập sai mật khẩu / email không tồn tại | Nhập sai mật khẩu; nhập email lạ | Cùng một thông báo "Email hoặc mật khẩu không đúng" (không lộ email có tồn tại hay không) | |
| 11 | S | Brute-force | Sai mật khẩu 5 lần liên tiếp với một email, sau đó nhập đúng | Lần thứ 6 báo bị khóa tạm 10 phút, kể cả khi nhập đúng mật khẩu | |
| 12 | S | Tài khoản chờ duyệt | Đăng nhập `gv.huy@demo.test` (trước khi admin duyệt) | Thông báo đang chờ phê duyệt; không vào được hệ thống | |
| 13 | S | Tài khoản bị khóa | Đăng nhập `sv.khoi@demo.test` | Thông báo tài khoản bị khóa | |
| 14 | S | Thiếu/sai CSRF token | Gửi POST đăng nhập (hoặc khóa tài khoản) không có `_csrf` hoặc sai token | Lỗi 419; dữ liệu không thay đổi | |
| 15 | Q | Chưa đăng nhập vào trang quản trị | Mở thẳng `?r=admin/users` | Chuyển về trang đăng nhập kèm thông báo | |
| 16 | Q | Sinh viên vào khu vực không thuộc quyền | Đăng nhập sinh viên, mở `?r=admin/users` và `?r=dashboard/lecturer` | Cả hai đều trả lỗi 403 | |
| 17 | Q | Sinh viên tự nâng quyền bằng POST | Đăng nhập sinh viên, gửi POST `admin/user-role` (kèm CSRF hợp lệ) | Lỗi 403; vai trò không đổi (quyền được kiểm tra ở server, không chỉ ẩn nút) | |
| 18 | H | Admin khóa tài khoản đang đăng nhập | Sinh viên A đang đăng nhập; admin khóa A; A tải lại trang | A bị đăng xuất ngay ở request kế tiếp | |
| 19 | H | Admin phê duyệt giảng viên | Admin bấm Phê duyệt `gv.huy@demo.test`, sau đó đăng nhập tài khoản này | Đăng nhập thành công vào khu vực giảng viên | |
| 20 | S | Admin tự khóa / tự đổi vai trò | Admin thao tác trên chính tài khoản mình | Bị chặn bằng thông báo; không mất quyền quản trị | |
| 21 | S | Đổi thành giảng viên nhưng không chọn đơn vị | Trong Phân quyền, chọn Giảng viên, đơn vị "Không có" | Báo lỗi, vai trò không đổi | |
| 22 | S | Danh mục: trùng tên và xóa mục đang dùng | Thêm chuyên môn trùng tên (khác hoa/thường); xóa đơn vị đã có giảng viên | Báo trùng tên; nút Xóa bị vô hiệu và server cũng từ chối | |
| 23 | S | Tìm kiếm chứa ký tự đặc biệt | Ô tìm kiếm nhập `' OR 1=1 --`, `%`, `<script>alert(1)</script>` | Không lỗi SQL, không trả toàn bộ bảng, không chạy script (kết quả được mã hóa) | |
| 24 | H | Đăng xuất | Bấm Đăng xuất, sau đó bấm Back và mở lại trang quản trị | Bị chuyển về đăng nhập; mở `?r=auth/logout` bằng URL (GET) không đăng xuất được | |
| 25 | H | Fetch kiểm tra email (JSON) | Ở trang Đăng ký gõ email đã có / chưa có / sai dạng | Hiện lần lượt "đã được sử dụng" / "có thể sử dụng" / "chưa đúng định dạng" mà không tải lại trang | |
| 26 | H | Hết phiên do không thao tác | Đặt `idle_timeout` = 10 trong config, đăng nhập, chờ 15 giây rồi tải lại | Bị đăng xuất kèm thông báo phiên hết hạn | |
