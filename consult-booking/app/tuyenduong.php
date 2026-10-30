<?php
declare(strict_types=1);

/** [phương thức, route, controller, action] — thêm route cho module mới ở đây. */
return [
    ['GET',  'home/index',         DieuKhienTrangChu::class,       'index'],

    // Xác thực (Đã bỏ đăng ký)
    ['GET',  'auth/login',         DieuKhienXacThuc::class,       'showLogin'],
    ['POST', 'auth/login',         DieuKhienXacThuc::class,       'login'],
    ['POST', 'auth/logout',        DieuKhienXacThuc::class,       'logout'],
    ['GET',  'auth/check-email',   DieuKhienXacThuc::class,       'checkEmail'],   // endpoint JSON cho Fetch API

    // Khu vực theo vai trò
    ['GET',  'dashboard/index',    DieuKhienKhuVuc::class, 'index'],
    ['GET',  'dashboard/student',  DieuKhienKhuVuc::class, 'student'],
    ['GET',  'dashboard/lecturer', DieuKhienKhuVuc::class, 'lecturer'],

    // Quản trị
    ['GET',  'admin/dashboard',    DieuKhienQuanTri::class,     'dashboard'],
    ['GET',  'admin/users',        DieuKhienQuanTri::class,     'users'],
    ['POST', 'admin/user-status',  DieuKhienQuanTri::class,     'updateStatus'],
    ['POST', 'admin/user-role',    DieuKhienQuanTri::class,     'updateRole'],
    ['GET',  'admin/catalog',      DieuKhienDanhMuc::class,   'index'],
    ['POST', 'admin/catalog-save', DieuKhienDanhMuc::class,   'save'],
    ['POST', 'admin/catalog-delete', DieuKhienDanhMuc::class, 'delete'],

    // Quản lý lịch hẹn của Giảng viên
    ['GET',  'giang-vien/lich-hen',         DieuKhienLichHenGiangVien::class, 'index'],
    ['POST', 'giang-vien/lich-hen/cap-nhat', DieuKhienLichHenGiangVien::class, 'capNhatTrangThai'],
    ['POST', 'giang-vien/lich-hen/phan-hoi',  DieuKhienLichHenGiangVien::class, 'luuPhanHoi'],

    // Khung giờ tư vấn (slot) của Giảng viên: trang quản lý + các endpoint JSON cho Fetch API
    ['GET',  'giang-vien/khung-gio',              DieuKhienKhungGioGiangVien::class, 'index'],
    ['GET',  'giang-vien/khung-gio/danh-sach',    DieuKhienKhungGioGiangVien::class, 'danhSach'],
    ['GET',  'giang-vien/khung-gio/sinh-vien',    DieuKhienKhungGioGiangVien::class, 'sinhVienDangKy'],   // nút "con mắt": SV đã đăng ký slot
    ['POST', 'giang-vien/khung-gio/them',         DieuKhienKhungGioGiangVien::class, 'them'],
    ['POST', 'giang-vien/khung-gio/sua',          DieuKhienKhungGioGiangVien::class, 'sua'],
    ['POST', 'giang-vien/khung-gio/xoa',          DieuKhienKhungGioGiangVien::class, 'xoa'],
    ['GET',  'khung-gio/trong',                   DieuKhienKhungGioGiangVien::class, 'slotTrong'],        // slot còn chỗ theo ngày + giảng viên

    // Hồ sơ & chuyên môn
    ['GET',  'giang-vien/ho-so',    DieuKhienHoSoGiangVien::class, 'profile'],        // chế độ xem
    ['GET',  'giang-vien/ho-so/sua', DieuKhienHoSoGiangVien::class, 'editProfile'],    // form sửa (nút "Chỉnh sửa")
    ['POST', 'giang-vien/ho-so',    DieuKhienHoSoGiangVien::class, 'saveProfile'],
    ['GET',  'giang-vien/danh-sach', DieuKhienHoSoGiangVien::class, 'directory'],
    ['GET',  'giang-vien/chi-tiet',  DieuKhienHoSoGiangVien::class, 'detail'],

    // Tài khoản của tôi (mọi vai trò đã đăng nhập)
    ['GET',  'tai-khoan/ho-so',            DieuKhienTaiKhoan::class, 'hoSo'],
    ['POST', 'tai-khoan/ho-so/cap-nhat',   DieuKhienTaiKhoan::class, 'capNhatHoSo'],
    ['POST', 'tai-khoan/chuyen-mon',       DieuKhienTaiKhoan::class, 'capNhatChuyenMon'],
    ['GET',  'tai-khoan/doi-mat-khau',     DieuKhienTaiKhoan::class, 'formDoiMatKhau'],
    ['POST', 'tai-khoan/doi-mat-khau',     DieuKhienTaiKhoan::class, 'doiMatKhau'],

    // Đánh giá / Phản hồi của Sinh viên
    ['GET',  'sinh-vien/phan-hoi',   DieuKhienPhanHoi::class,       'index'],   // trang Lịch hẹn & Đánh giá
    ['POST', 'sinh-vien/phan-hoi',   DieuKhienPhanHoi::class,       'gui'],

    // Sinh viên đặt lịch hẹn & Hủy lịch hẹn
    ['GET',  'sinh-vien/dat-lich',   DieuKhienLichHenGiangVien::class, 'hienThiFormDatLich'],
    ['POST', 'sinh-vien/dat-lich',   DieuKhienLichHenGiangVien::class, 'xuLyDatLich'],
    ['POST', 'sinh-vien/huy-lich',   DieuKhienLichHenGiangVien::class, 'huyLichSinhVien'],
];