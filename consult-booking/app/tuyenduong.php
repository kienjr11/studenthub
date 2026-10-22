<?php
declare(strict_types=1);

/** [phương thức, route, controller, action] — thêm route cho module mới ở đây. */
return [
    ['GET',  'home/index',        DieuKhienTrangChu::class,       'index'],

    // Xác thực
    ['GET',  'auth/login',        DieuKhienXacThuc::class,       'showLogin'],
    ['POST', 'auth/login',        DieuKhienXacThuc::class,       'login'],
    ['GET',  'auth/register',     DieuKhienXacThuc::class,       'showRegister'],
    ['POST', 'auth/register',     DieuKhienXacThuc::class,       'register'],
    ['POST', 'auth/logout',       DieuKhienXacThuc::class,       'logout'],
    ['GET',  'auth/check-email',  DieuKhienXacThuc::class,       'checkEmail'],   // endpoint JSON cho Fetch API

    // Khu vực theo vai trò
    ['GET',  'dashboard/index',   DieuKhienKhuVuc::class, 'index'],
    ['GET',  'dashboard/student',   DieuKhienKhuVuc::class, 'student'],
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
    
    // Hồ sơ & chuyên môn
    ['GET',  'giang-vien/ho-so',     DieuKhienHoSoGiangVien::class, 'profile'],
    ['POST', 'giang-vien/ho-so',     DieuKhienHoSoGiangVien::class, 'saveProfile'],
    ['GET',  'giang-vien/danh-sach', DieuKhienHoSoGiangVien::class, 'directory'],
    ['GET',  'giang-vien/chi-tiet',  DieuKhienHoSoGiangVien::class, 'detail'],

    // Đánh giá / Phản hồi của Sinh viên
    ['GET',  'sinh-vien/phan-hoi',   DieuKhienPhanHoi::class,       'index'],   // trang Lịch hẹn & Đánh giá
    ['POST', 'sinh-vien/phan-hoi',   DieuKhienPhanHoi::class,       'gui'],

    // Sinh viên đặt lịch hẹn
    ['GET',  'sinh-vien/dat-lich',   DieuKhienLichHenGiangVien::class, 'hienThiFormDatLich'],
    ['POST', 'sinh-vien/dat-lich',   DieuKhienLichHenGiangVien::class, 'xuLyDatLich'],
];