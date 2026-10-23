-- =====================================================================
-- ConsultBook - Hệ thống đặt lịch tư vấn / hẹn gặp giảng viên
-- Chạy: mysql -u root -p < database/schema.sql   (hoặc Import trong phpMyAdmin / HeidiSQL)
-- Tên bảng và tên cột dùng tiếng Việt không dấu, viết thường, nối bằng dấu gạch dưới.
-- Quy ước: khóa chính của bảng X tên là ma_x; khóa ngoại trỏ tới X cũng tên ma_x.
-- File này XÓA và tạo lại các bảng -> chỉ dùng cho môi trường dev/demo.
-- Giá trị của các cột ENUM (student, lecturer, admin, pending...) là mã dùng trong code, giữ nguyên tiếng Anh.
-- =====================================================================
CREATE DATABASE IF NOT EXISTS consult_booking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE consult_booking;
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
-- Dọn cả các bảng mang tên tiếng Anh của phiên bản cũ (nếu có)
DROP TABLE IF EXISTS feedback, appointments, slots, lecturer_specialties, users, specialties, departments;
DROP TABLE IF EXISTS phan_hoi, cuoc_hen, khung_gio, giang_vien_chuyen_mon, nguoi_dung, chuyen_mon, don_vi;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- MODULE AUTH + ADMIN: don_vi, chuyen_mon, nguoi_dung, giang_vien_chuyen_mon
-- ---------------------------------------------------------------------
CREATE TABLE don_vi (
    ma_don_vi   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ten_don_vi  VARCHAR(100) NOT NULL,
    mo_ta       VARCHAR(255) NULL,
    ngay_tao    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_don_vi_ten (ten_don_vi)
) ENGINE=InnoDB;

CREATE TABLE chuyen_mon (
    ma_chuyen_mon  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ten_chuyen_mon VARCHAR(100) NOT NULL,
    mo_ta          VARCHAR(255) NULL,
    ngay_tao       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_chuyen_mon_ten (ten_chuyen_mon)
) ENGINE=InnoDB;

CREATE TABLE nguoi_dung (
    ma_nguoi_dung        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ho_ten               VARCHAR(100) NOT NULL,
    email                VARCHAR(190) NOT NULL,
    mat_khau_bam         VARCHAR(255) NOT NULL,                                   -- chuỗi băm password_hash(), không lưu mật khẩu gốc
    vai_tro              ENUM('student','lecturer','admin') NOT NULL DEFAULT 'student',
    trang_thai           ENUM('active','pending','locked')  NOT NULL DEFAULT 'active',
    ma_don_vi            INT UNSIGNED NULL,                                       -- chỉ giảng viên có đơn vị
    so_lan_dang_nhap_sai TINYINT UNSIGNED NOT NULL DEFAULT 0,
    khoa_den             DATETIME NULL,                                           -- khóa tạm do đăng nhập sai nhiều lần
    dang_nhap_lan_cuoi   DATETIME NULL,
    ngay_tao             DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ngay_cap_nhat        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_nguoi_dung_email (email),
    KEY idx_nguoi_dung_vai_tro_trang_thai (vai_tro, trang_thai),
    CONSTRAINT fk_nguoi_dung_don_vi FOREIGN KEY (ma_don_vi) REFERENCES don_vi(ma_don_vi)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE giang_vien_chuyen_mon (
    ma_giang_vien  INT UNSIGNED NOT NULL,                                         -- trỏ tới nguoi_dung (vai trò giảng viên)
    ma_chuyen_mon  INT UNSIGNED NOT NULL,
    PRIMARY KEY (ma_giang_vien, ma_chuyen_mon),
    CONSTRAINT fk_gvcm_giang_vien FOREIGN KEY (ma_giang_vien) REFERENCES nguoi_dung(ma_nguoi_dung) ON DELETE CASCADE,
    CONSTRAINT fk_gvcm_chuyen_mon FOREIGN KEY (ma_chuyen_mon) REFERENCES chuyen_mon(ma_chuyen_mon) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- KHUNG CHO CÁC MODULE KHÁC (khung giờ, cuộc hẹn, phản hồi) - chỉnh theo ERD của nhóm
-- ---------------------------------------------------------------------
CREATE TABLE khung_gio (
    ma_khung_gio  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ma_giang_vien INT UNSIGNED NOT NULL,
    bat_dau       DATETIME NOT NULL,
    ket_thuc      DATETIME NOT NULL,
    dia_diem      VARCHAR(150) NULL,
    ngay_tao      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_khung_gio_giang_vien_bat_dau (ma_giang_vien, bat_dau),
    CONSTRAINT fk_khung_gio_giang_vien FOREIGN KEY (ma_giang_vien) REFERENCES nguoi_dung(ma_nguoi_dung) ON DELETE RESTRICT,
    CONSTRAINT chk_khung_gio_thoi_gian CHECK (ket_thuc > bat_dau)
) ENGINE=InnoDB;

-- Quy tắc "một khung giờ chỉ có tối đa một cuộc hẹn đang hiệu lực" được ép ngay ở CSDL:
-- cột khung_gio_hieu_luc chỉ có giá trị khi cuộc hẹn còn hiệu lực, và UNIQUE trên cột đó chặn 2 người giữ cùng khung giờ.
CREATE TABLE cuoc_hen (
    ma_cuoc_hen        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ma_khung_gio       INT UNSIGNED NOT NULL,
    ma_sinh_vien       INT UNSIGNED NOT NULL,                                     -- trỏ tới nguoi_dung (vai trò sinh viên)
    chu_de             VARCHAR(255) NOT NULL,
    trang_thai         ENUM('pending','approved','rejected','cancelled','completed') NOT NULL DEFAULT 'pending',
    ghi_chu_giang_vien TEXT NULL,
    khung_gio_hieu_luc INT UNSIGNED GENERATED ALWAYS AS
                       (IF(trang_thai IN ('pending','approved','completed'), ma_khung_gio, NULL)) STORED,
    ngay_tao           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ngay_cap_nhat      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_mot_cuoc_hen_hieu_luc_moi_khung_gio (khung_gio_hieu_luc),
    KEY idx_cuoc_hen_sinh_vien (ma_sinh_vien, trang_thai),
    CONSTRAINT fk_cuoc_hen_khung_gio FOREIGN KEY (ma_khung_gio) REFERENCES khung_gio(ma_khung_gio) ON DELETE RESTRICT,
    CONSTRAINT fk_cuoc_hen_sinh_vien FOREIGN KEY (ma_sinh_vien) REFERENCES nguoi_dung(ma_nguoi_dung) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE phan_hoi (
    ma_phan_hoi   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ma_cuoc_hen   INT UNSIGNED NOT NULL,
    diem_danh_gia TINYINT UNSIGNED NOT NULL,
    nhan_xet      VARCHAR(500) NULL,
    ngay_tao      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_phan_hoi_cuoc_hen (ma_cuoc_hen),
    CONSTRAINT fk_phan_hoi_cuoc_hen FOREIGN KEY (ma_cuoc_hen) REFERENCES cuoc_hen(ma_cuoc_hen) ON DELETE CASCADE,
    CONSTRAINT chk_phan_hoi_diem CHECK (diem_danh_gia BETWEEN 1 AND 5)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- DỮ LIỆU MẪU (mật khẩu chung của mọi tài khoản demo: Demo@12345)
-- ---------------------------------------------------------------------
INSERT INTO don_vi (ten_don_vi, mo_ta) VALUES
 ('Khoa Công nghệ thông tin', 'Lập trình, mạng, dữ liệu, trí tuệ nhân tạo'),
 ('Khoa Kinh tế', 'Quản trị, marketing, khởi nghiệp'),
 ('Khoa Khoa học cơ bản', 'Toán, vật lý, kỹ năng nền tảng'),
 ('Phòng Công tác sinh viên', 'Hỗ trợ học vụ, học bổng, định hướng nghề nghiệp');

INSERT INTO chuyen_mon (ten_chuyen_mon, mo_ta) VALUES
 ('Lập trình Web', 'PHP, JavaScript, kiến trúc MVC'),
 ('Cơ sở dữ liệu', 'Thiết kế ERD, SQL, tối ưu truy vấn'),
 ('Trí tuệ nhân tạo', 'Học máy, xử lý dữ liệu'),
 ('Toán ứng dụng', 'Phương trình vi phân, mô hình hóa'),
 ('Khởi nghiệp', 'Kế hoạch kinh doanh, gọi vốn'),
 ('Hướng nghiệp', 'CV, phỏng vấn, lộ trình nghề nghiệp');

INSERT INTO nguoi_dung (ho_ten, email, mat_khau_bam, vai_tro, trang_thai, ma_don_vi) VALUES
 ('Quản trị hệ thống',  'admin@demo.test',      '$2y$10$1IgX5hYLu/gNcArsS9szWu/XI.VGmsJBIY7wtLJ6C9cVo5b4.1NRK', 'admin',    'active',  NULL),
 ('Nguyễn Văn Minh',    'gv.minh@demo.test',    '$2y$10$1IgX5hYLu/gNcArsS9szWu/XI.VGmsJBIY7wtLJ6C9cVo5b4.1NRK', 'lecturer', 'active',  1),
 ('Trần Thu Hà',        'gv.ha@demo.test',      '$2y$10$1IgX5hYLu/gNcArsS9szWu/XI.VGmsJBIY7wtLJ6C9cVo5b4.1NRK', 'lecturer', 'active',  2),
 ('Lê Quang Huy',       'gv.huy@demo.test',     '$2y$10$1IgX5hYLu/gNcArsS9szWu/XI.VGmsJBIY7wtLJ6C9cVo5b4.1NRK', 'lecturer', 'pending', 3),
 ('Phạm Thị An',        'sv.an@demo.test',      '$2y$10$1IgX5hYLu/gNcArsS9szWu/XI.VGmsJBIY7wtLJ6C9cVo5b4.1NRK', 'student',  'active',  NULL),
 ('Hoàng Gia Bảo',      'sv.bao@demo.test',     '$2y$10$1IgX5hYLu/gNcArsS9szWu/XI.VGmsJBIY7wtLJ6C9cVo5b4.1NRK', 'student',  'active',  NULL),
 ('Đỗ Minh Khôi',       'sv.khoi@demo.test',    '$2y$10$1IgX5hYLu/gNcArsS9szWu/XI.VGmsJBIY7wtLJ6C9cVo5b4.1NRK', 'student',  'locked',  NULL);

INSERT INTO giang_vien_chuyen_mon (ma_giang_vien, ma_chuyen_mon) VALUES (2, 1), (2, 2), (3, 5), (3, 6);
