CHỦ ĐỀ 5: Hệ thống đặt lịch tư vấn/hẹn gặp giảng viên 

Danh sách thành viên
  1. Lê Trần Ngọc Anh - 224001708
  2. Lê Tuấn Anh - 224001709
  3. Đào Quang Dương - 224001714
  4. Đỗ Minh Hằng - 224001716
  5. Đỗ Trung Kiên - 224001727

Phân công công việc 
    1. Lê Tuấn Anh: Module Hệ thống chung & Xác thực (Auth)
      - Trang chủ website, giới thiệu, điều hướng chung.
      - Chức năng Đăng nhập, Đăng ký tài khoản (Phân biệt vai trò Sinh viên, Giảng viên, Admin).
      - Xây dựng bộ khung kết nối PDO và cơ chế bảo mật Session, chống CSRF, băm mật khẩu.
      - Bổ sung Admin Dashboard: Giao diện quản lý tài khoản người dùng (khóa/mở tài khoản, phân quyền) và cấu hình danh mục chuyên môn/đơn vị. 
    2. Đỗ Minh Hằng: Module Quản lý Hồ sơ & Chuyên môn
      - Trang quản lý hồ sơ cá nhân của Giảng viên (Cập nhật thông tin, học hàm, học vị, lĩnh vực tư vấn).
      - Trang danh sách giảng viên để sinh viên tìm kiếm theo tên, khoa/đơn vị.
      - Giao diện dashboard cá nhân hóa cho từng giảng viên.
    3. Lê Trần Ngọc Anh: Module Khung giờ tư vấn (Slots - Phía Giảng viên)
      - Chức năng CRUD (Thêm, Sửa, Xóa) các khung giờ rảnh (slot) của giảng viên.
      - Lập trình API/Endpoint JSON kết hợp Fetch API để hệ thống tự động tải danh sách slot trống theo ngày và theo giảng viên mà không cần load lại trang. 
    4. Đào Quang Dương: Module Đặt lịch & Quản lý lịch hẹn (Phía Sinh viên)
      - Giao diện đặt lịch hẹn kèm mô tả chủ đề cần tư vấn.
      - Trang lịch cá nhân của sinh viên (xem danh sách lịch đã đặt, trạng thái duyệt, hủy lịch trong hạn).
      - Xử lý quy tắc nghiệp vụ chặn trùng lịch ở phía Server. 
    5. Đỗ Trung Kiên: Module Duyệt lịch, Trạng thái & Phản hồi (Feedback)
      - Trang quản lý lịch hẹn của Giảng viên (Thực hiện duyệt approve, từ chối reject, hoặc đánh dấu hoàn thành completed).
      - Chức năng gửi phản hồi (Feedback/Đánh giá sao) của sinh viên sau khi cuộc hẹn kết thúc.
      - Trang thống kê số lượng cuộc hẹn cho Admin/Giảng viên. 
