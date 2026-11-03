-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for table dat_lich_tu_van.chuyen_mon
CREATE TABLE IF NOT EXISTS `chuyen_mon` (
  `ma_chuyen_mon` int unsigned NOT NULL AUTO_INCREMENT,
  `ten_chuyen_mon` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mo_ta` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ngay_tao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_chuyen_mon`),
  UNIQUE KEY `uq_chuyen_mon_ten` (`ten_chuyen_mon`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table dat_lich_tu_van.chuyen_mon: ~6 rows (approximately)
INSERT INTO `chuyen_mon` (`ma_chuyen_mon`, `ten_chuyen_mon`, `mo_ta`, `ngay_tao`) VALUES
	(1, 'Lập trình Web', 'PHP, JavaScript, kiến trúc MVC', '2026-10-22 17:35:11'),
	(2, 'Cơ sở dữ liệu', 'Thiết kế ERD, SQL, tối ưu truy vấn', '2026-10-22 17:35:11'),
	(3, 'Trí tuệ nhân tạo', 'Học máy, xử lý dữ liệu', '2026-10-22 17:35:11'),
	(4, 'Toán ứng dụng', 'Phương trình vi phân, mô hình hóa', '2026-10-22 17:35:11'),
	(5, 'Khởi nghiệp', 'Kế hoạch kinh doanh, gọi vốn', '2026-10-22 17:35:11'),
	(6, 'Hướng nghiệp', 'CV, phỏng vấn, lộ trình nghề nghiệp', '2026-10-22 17:35:11');

-- Dumping structure for table dat_lich_tu_van.cuoc_hen
CREATE TABLE IF NOT EXISTS `cuoc_hen` (
  `ma_cuoc_hen` int unsigned NOT NULL AUTO_INCREMENT,
  `ma_khung_gio` int unsigned NOT NULL,
  `ma_sinh_vien` int unsigned NOT NULL,
  `chu_de` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `trang_thai` enum('cho_duyet','da_duyet','tu_choi','da_huy','hoan_thanh') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cho_duyet',
  `ghi_chu_giang_vien` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ly_do_huy` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `huy_boi` enum('sinh_vien','giang_vien','he_thong') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `khung_gio_hieu_luc` int unsigned GENERATED ALWAYS AS (if((`trang_thai` in (_utf8mb4'cho_duyet',_utf8mb4'da_duyet',_utf8mb4'hoan_thanh')),`ma_khung_gio`,NULL)) STORED,
  `ngay_tao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ngay_cap_nhat` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_cuoc_hen`),
  KEY `idx_cuoc_hen_khung_gio_hieu_luc` (`khung_gio_hieu_luc`),
  KEY `idx_cuoc_hen_sinh_vien` (`ma_sinh_vien`,`trang_thai`),
  KEY `fk_cuoc_hen_khung_gio` (`ma_khung_gio`),
  CONSTRAINT `fk_cuoc_hen_khung_gio` FOREIGN KEY (`ma_khung_gio`) REFERENCES `khung_gio` (`ma_khung_gio`) ON DELETE RESTRICT,
  CONSTRAINT `fk_cuoc_hen_sinh_vien` FOREIGN KEY (`ma_sinh_vien`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table dat_lich_tu_van.cuoc_hen: ~30 rows (approximately)
INSERT INTO `cuoc_hen` (`ma_cuoc_hen`, `ma_khung_gio`, `ma_sinh_vien`, `chu_de`, `trang_thai`, `ghi_chu_giang_vien`, `ly_do_huy`, `huy_boi`, `ngay_tao`, `ngay_cap_nhat`) VALUES
	(1, 1, 6, 'ád', 'hoan_thanh', 'oke', NULL, NULL, '2026-10-23 13:10:18', '2026-10-23 13:14:40'),
	(2, 2, 6, 'ádasd', 'da_duyet', NULL, NULL, NULL, '2026-10-23 13:15:50', '2026-10-23 13:16:11'),
	(3, 3, 5, 'Hỏi đáp bài tập lớn Lập trình Web và kết nối MySQL', 'cho_duyet', NULL, NULL, NULL, '2026-10-29 14:39:48', '2026-10-29 14:39:48'),
	(4, 3, 10, 'Tư vấn tối ưu hóa truy vấn CSDL cho đồ án', 'da_duyet', 'Em nhớ chuẩn bị trước sơ đồ ERD nhé.', NULL, NULL, '2026-10-29 14:39:48', '2026-10-29 14:39:48'),
	(5, 4, 11, 'Định hướng đề tài nghiên cứu AI ứng dụng', 'da_duyet', NULL, NULL, NULL, '2026-10-29 14:39:48', '2026-10-29 14:39:48'),
	(6, 5, 12, 'Xin định hướng đề tài khóa luận tốt nghiệp', 'tu_choi', 'Thầy bận họp đột xuất khung giờ này, em đăng ký slot khác nhé.', NULL, NULL, '2026-10-29 14:39:48', '2026-10-29 14:39:48'),
	(7, 6, 10, 'Tư vấn kỹ năng viết CV và phỏng vấn xin việc', 'da_duyet', NULL, NULL, NULL, '2026-10-29 14:39:48', '2026-10-29 21:40:08'),
	(8, 7, 16, 'Xin tư vấn về sửa lỗi kết nối CSDL trong C# WinForms', 'da_duyet', 'Gợi ý em kiểm tra chuỗi Connection String.', NULL, NULL, '2026-10-29 14:41:01', '2026-10-29 14:41:01'),
	(9, 7, 17, 'Hỏi đáp bài tập phân quyền trong Lập trình Web', 'cho_duyet', NULL, NULL, NULL, '2026-10-29 14:41:01', '2026-10-29 14:41:01'),
	(10, 8, 18, 'Định hướng đề tài nghiên cứu khoa học cấp Trường', 'da_duyet', NULL, NULL, NULL, '2026-10-29 14:41:01', '2026-10-29 14:41:01'),
	(11, 8, 19, 'Tư vấn thiết kế chuẩn hóa cơ sở dữ liệu BTL', 'cho_duyet', NULL, NULL, NULL, '2026-10-29 14:41:01', '2026-10-29 14:41:01'),
	(12, 9, 20, 'Review báo cáo tiến độ thực tập doanh nghiệp', 'hoan_thanh', 'Sinh viên chuẩn bị báo cáo tốt.', NULL, NULL, '2026-10-29 14:41:01', '2026-10-29 14:41:01'),
	(13, 10, 21, 'Tư vấn thuật toán xử lý ảnh trong đồ án AI', 'cho_duyet', NULL, NULL, NULL, '2026-10-29 14:41:01', '2026-10-29 14:41:01'),
	(14, 11, 22, 'Góp ý mô hình Canvas cho ý tưởng khởi nghiệp', 'da_duyet', NULL, NULL, NULL, '2026-10-29 14:41:01', '2026-10-29 14:41:01'),
	(15, 12, 23, 'Xin hỗ trợ thủ tục đăng ký học bổng khuyến khích học tập', 'da_duyet', 'Đã duyệt, em mang hồ sơ giấy qua phòng 102.', NULL, NULL, '2026-10-29 14:41:01', '2026-10-29 14:41:01'),
	(16, 13, 16, 'Hỏi về cấu trúc Microservices cho đồ án tốt nghiệp', 'tu_choi', 'Kiến thức hơi quá sức cho đồ án môn học, nên làm Monolith trước.', NULL, NULL, '2026-10-29 14:41:01', '2026-10-29 14:41:01'),
	(17, 14, 17, 'Thắc mắc về quy trình kiểm thử phần mềm', 'da_huy', NULL, NULL, NULL, '2026-10-29 14:41:01', '2026-10-29 14:41:01'),
	(18, 15, 17, 'Xin tư vấn định hướng viết CV xin việc ngành Marketing', 'cho_duyet', NULL, NULL, NULL, '2026-10-29 15:00:16', '2026-10-29 15:00:16'),
	(19, 16, 18, 'Góp ý kế hoạch gọi vốn cho dự án khởi nghiệp môn học', 'da_duyet', 'Em nhớ chuẩn bị trước file slide thuyết trình nhé.', NULL, NULL, '2026-10-29 15:00:16', '2026-10-29 15:00:16'),
	(20, 6, 10, 'Tư vấn kỹ năng phỏng vấn tuyển dụng thực tập sinh', 'hoan_thanh', 'Sinh viên tiếp thu tốt, thái độ tích cực.', NULL, NULL, '2026-10-29 15:00:16', '2026-10-29 15:00:16'),
	(21, 14, 5, 'alo', 'da_duyet', NULL, NULL, NULL, '2026-11-03 08:15:51', '2026-11-03 08:17:46'),
	(22, 14, 6, 'hi', 'da_huy', NULL, 'Khung giờ đã đủ số sinh viên được duyệt trước bạn nên yêu cầu tự động hủy. Vui lòng chọn khung giờ khác.', 'he_thong', '2026-11-03 08:16:31', '2026-11-03 08:17:46'),
	(23, 18, 7, 'xin điểm cc', 'da_duyet', NULL, NULL, NULL, '2026-11-03 10:08:13', '2026-11-03 10:09:52'),
	(24, 18, 5, 'xin điểm 30%', 'da_duyet', NULL, NULL, NULL, '2026-11-03 10:08:40', '2026-11-03 10:09:52');

-- Dumping structure for table dat_lich_tu_van.don_vi
CREATE TABLE IF NOT EXISTS `don_vi` (
  `ma_don_vi` int unsigned NOT NULL AUTO_INCREMENT,
  `ten_don_vi` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mo_ta` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ngay_tao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_don_vi`),
  UNIQUE KEY `uq_don_vi_ten` (`ten_don_vi`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table dat_lich_tu_van.don_vi: ~4 rows (approximately)
INSERT INTO `don_vi` (`ma_don_vi`, `ten_don_vi`, `mo_ta`, `ngay_tao`) VALUES
	(1, 'Khoa Công nghệ thông tin', 'Lập trình, mạng, dữ liệu, trí tuệ nhân tạo', '2026-10-22 17:35:11'),
	(2, 'Khoa Kinh tế', 'Quản trị, marketing, khởi nghiệp', '2026-10-22 17:35:11'),
	(3, 'Khoa Khoa học cơ bản', 'Toán, vật lý, kỹ năng nền tảng', '2026-10-22 17:35:11'),
	(4, 'Phòng Công tác sinh viên', 'Hỗ trợ học vụ, học bổng, định hướng nghề nghiệp', '2026-10-22 17:35:11');

-- Dumping structure for table dat_lich_tu_van.giang_vien_chuyen_mon
CREATE TABLE IF NOT EXISTS `giang_vien_chuyen_mon` (
  `ma_giang_vien` int unsigned NOT NULL,
  `ma_chuyen_mon` int unsigned NOT NULL,
  PRIMARY KEY (`ma_giang_vien`,`ma_chuyen_mon`),
  KEY `fk_gvcm_chuyen_mon` (`ma_chuyen_mon`),
  CONSTRAINT `fk_gvcm_chuyen_mon` FOREIGN KEY (`ma_chuyen_mon`) REFERENCES `chuyen_mon` (`ma_chuyen_mon`) ON DELETE RESTRICT,
  CONSTRAINT `fk_gvcm_giang_vien` FOREIGN KEY (`ma_giang_vien`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table dat_lich_tu_van.giang_vien_chuyen_mon: ~5 rows (approximately)
INSERT INTO `giang_vien_chuyen_mon` (`ma_giang_vien`, `ma_chuyen_mon`) VALUES
	(2, 1),
	(15, 1),
	(2, 2),
	(15, 2),
	(2, 3),
	(8, 3),
	(9, 4),
	(3, 5),
	(13, 5),
	(3, 6),
	(4, 6),
	(14, 6);

-- Dumping structure for table dat_lich_tu_van.ho_so_giang_vien
CREATE TABLE IF NOT EXISTS `ho_so_giang_vien` (
  `ma_giang_vien` int unsigned NOT NULL,
  `hoc_ham` enum('giao_su','pho_giao_su') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hoc_vi` enum('cu_nhan','ky_su','thac_si','tien_si') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `so_dien_thoai` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phong_lam_viec` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gioi_thieu` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `anh_dai_dien` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ngay_cap_nhat` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_giang_vien`),
  CONSTRAINT `fk_ho_so_giang_vien` FOREIGN KEY (`ma_giang_vien`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table dat_lich_tu_van.ho_so_giang_vien: ~3 rows (approximately)
INSERT INTO `ho_so_giang_vien` (`ma_giang_vien`, `hoc_ham`, `hoc_vi`, `so_dien_thoai`, `phong_lam_viec`, `gioi_thieu`, `anh_dai_dien`, `ngay_cap_nhat`) VALUES
	(2, NULL, 'tien_si', '0901234567', 'P.305 – Nhà A', 'Giảng dạy lập trình web và cơ sở dữ liệu; sẵn sàng tư vấn đồ án và định hướng nghề nghiệp lập trình viên.', NULL, '2026-10-22 17:35:11'),
	(3, 'pho_giao_su', 'tien_si', '0982112233', 'P.210 – Nhà B', 'Phó Giáo sư, Tiến sĩ chuyên ngành Quản trị & Kinh tế. Chuyên tư vấn xây dựng kế hoạch kinh doanh, mô hình khởi nghiệp, kỹ năng viết CV chuyên nghiệp và định hướng nghề nghiệp cho sinh viên.', 'gv3_e9e363160059.jpg', '2026-11-01 22:49:44'),
	(4, 'pho_giao_su', 'thac_si', '0919337474', 'P.306 nhà B', 'đẹp trai', NULL, '2026-10-22 21:57:11'),
	(8, NULL, 'thac_si', '0988123456', 'P.402 – Nhà A1', 'Chuyên gia Trí tuệ nhân tạo và Học máy. Hỗ trợ sinh viên định hướng nghiên cứu khoa học.', NULL, '2026-10-29 14:39:48'),
	(9, 'pho_giao_su', 'tien_si', '0977654321', 'P.105 – Nhà B2', 'Giảng dạy Toán ứng dụng và Mô hình hóa toán học.', NULL, '2026-10-29 14:39:48'),
	(13, NULL, 'thac_si', '0912345678', 'P.201 – Nhà C', 'Tư vấn kế hoạch kinh doanh, mô hình khởi nghiệp cho sinh viên Khoa Kinh tế.', NULL, '2026-10-29 14:41:01'),
	(14, 'pho_giao_su', 'tien_si', '0934567890', 'P.102 – Nhà Hành chính', 'Giải đáp chính sách học vụ, học bổng và hỗ trợ kỹ năng phỏng vấn tuyển dụng.', NULL, '2026-10-29 14:41:01'),
	(15, NULL, 'tien_si', '0967890123', 'P.501 – Nhà A1', 'Nghiên cứu kiến trúc phần mềm, bảo mật hệ thống và lập trình ứng dụng.', NULL, '2026-10-29 14:41:01');

-- Dumping structure for table dat_lich_tu_van.khung_gio
CREATE TABLE IF NOT EXISTS `khung_gio` (
  `ma_khung_gio` int unsigned NOT NULL AUTO_INCREMENT,
  `ma_giang_vien` int unsigned NOT NULL,
  `bat_dau` datetime NOT NULL,
  `ket_thuc` datetime NOT NULL,
  `dia_diem` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ma_chuyen_mon` int unsigned DEFAULT NULL,
  `suc_chua` tinyint unsigned NOT NULL DEFAULT '1',
  `la_tu_nhap` tinyint(1) NOT NULL DEFAULT '0',
  `ngay_tao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_khung_gio`),
  KEY `idx_khung_gio_giang_vien_bat_dau` (`ma_giang_vien`,`bat_dau`),
  KEY `fk_khung_gio_chuyen_mon` (`ma_chuyen_mon`),
  CONSTRAINT `fk_khung_gio_chuyen_mon` FOREIGN KEY (`ma_chuyen_mon`) REFERENCES `chuyen_mon` (`ma_chuyen_mon`) ON DELETE RESTRICT,
  CONSTRAINT `fk_khung_gio_giang_vien` FOREIGN KEY (`ma_giang_vien`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE RESTRICT,
  CONSTRAINT `chk_khung_gio_suc_chua` CHECK ((`suc_chua` between 1 and 50)),
  CONSTRAINT `chk_khung_gio_thoi_gian` CHECK ((`ket_thuc` > `bat_dau`))
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table dat_lich_tu_van.khung_gio: ~18 rows (approximately)
INSERT INTO `khung_gio` (`ma_khung_gio`, `ma_giang_vien`, `bat_dau`, `ket_thuc`, `dia_diem`, `ma_chuyen_mon`, `suc_chua`, `la_tu_nhap`, `ngay_tao`) VALUES
	(1, 4, '2027-02-04 12:12:00', '2027-02-04 13:13:00', NULL, NULL, 1, 1, '2026-10-23 13:10:18'),
	(2, 2, '2027-02-04 12:31:00', '2027-02-04 13:31:00', NULL, NULL, 1, 1, '2026-10-23 13:15:50'),
	(3, 2, '2026-11-05 08:00:00', '2026-11-05 10:00:00', 'P.305 – Nhà A', 1, 3, 0, '2026-10-29 14:39:48'),
	(4, 2, '2026-11-06 14:00:00', '2026-11-06 16:00:00', 'Online via Google Meet', 3, 5, 0, '2026-10-29 14:39:48'),
	(5, 8, '2026-11-10 09:00:00', '2026-11-10 11:30:00', 'P.402 – Nhà A1', 3, 2, 0, '2026-10-29 14:39:48'),
	(6, 3, '2026-11-12 13:30:00', '2026-11-12 15:30:00', 'P.210 – Nhà B', 6, 1, 0, '2026-10-29 14:39:48'),
	(7, 2, '2026-11-15 08:30:00', '2026-11-15 10:30:00', 'P.305 – Nhà A', 1, 2, 0, '2026-10-29 14:41:01'),
	(8, 2, '2026-11-15 14:00:00', '2026-11-15 16:00:00', 'Google Meet: meet.google.com/abc-xyz', 2, 4, 0, '2026-10-29 14:41:01'),
	(9, 4, '2026-11-18 09:00:00', '2026-11-18 11:00:00', 'P.306 – Nhà B', 6, 1, 0, '2026-10-29 14:41:01'),
	(10, 8, '2026-11-20 13:30:00', '2026-11-20 15:30:00', 'P.402 – Nhà A1', 3, 3, 0, '2026-10-29 14:41:01'),
	(11, 13, '2026-11-22 08:00:00', '2026-11-22 10:00:00', 'P.201 – Nhà C', 5, 5, 0, '2026-10-29 14:41:01'),
	(12, 14, '2026-11-25 14:00:00', '2026-11-25 16:00:00', 'P.102 – Nhà Hành chính', 6, 10, 0, '2026-10-29 14:41:01'),
	(13, 15, '2026-12-01 09:00:00', '2026-12-01 11:00:00', 'P.501 – Nhà A1', 1, 2, 0, '2026-10-29 14:41:01'),
	(14, 2, '2026-12-05 10:00:00', '2026-12-05 11:30:00', 'P.305 – Nhà A', 2, 1, 0, '2026-10-29 14:41:01'),
	(15, 3, '2026-11-10 08:30:00', '2026-11-10 10:30:00', 'P.210 – Nhà B', 5, 2, 0, '2026-10-29 15:00:16'),
	(16, 3, '2026-11-12 14:00:00', '2026-11-12 16:00:00', 'Online via Google Meet (meet.google.com/ha-tutoring)', 6, 5, 0, '2026-10-29 15:00:16'),
	(17, 3, '2026-11-15 09:00:00', '2026-11-15 11:00:00', 'P.210 – Nhà B', 5, 1, 0, '2026-10-29 15:00:16'),
	(18, 4, '2026-11-04 09:00:00', '2026-11-04 10:00:00', 'online ggmeet', 6, 2, 0, '2026-11-03 10:07:41');

-- Dumping structure for table dat_lich_tu_van.nguoi_dung
CREATE TABLE IF NOT EXISTS `nguoi_dung` (
  `ma_nguoi_dung` int unsigned NOT NULL AUTO_INCREMENT,
  `ho_ten` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mat_khau_bam` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `vai_tro` enum('sinh_vien','giang_vien','quan_tri') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sinh_vien',
  `trang_thai` enum('hoat_dong','cho_duyet','da_khoa') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'hoat_dong',
  `ma_don_vi` int unsigned DEFAULT NULL,
  `so_lan_dang_nhap_sai` tinyint unsigned NOT NULL DEFAULT '0',
  `khoa_den` datetime DEFAULT NULL,
  `dang_nhap_lan_cuoi` datetime DEFAULT NULL,
  `ngay_tao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ngay_cap_nhat` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_nguoi_dung`),
  UNIQUE KEY `uq_nguoi_dung_email` (`email`),
  KEY `idx_nguoi_dung_vai_tro_trang_thai` (`vai_tro`,`trang_thai`),
  KEY `fk_nguoi_dung_don_vi` (`ma_don_vi`),
  CONSTRAINT `fk_nguoi_dung_don_vi` FOREIGN KEY (`ma_don_vi`) REFERENCES `don_vi` (`ma_don_vi`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table dat_lich_tu_van.nguoi_dung: ~7 rows (approximately)
INSERT INTO `nguoi_dung` (`ma_nguoi_dung`, `ho_ten`, `email`, `mat_khau_bam`, `vai_tro`, `trang_thai`, `ma_don_vi`, `so_lan_dang_nhap_sai`, `khoa_den`, `dang_nhap_lan_cuoi`, `ngay_tao`, `ngay_cap_nhat`) VALUES
	(1, 'Quản trị hệ thống', 'admin@demo.test', '$2y$10$1IgX5hYLu/gNcArsS9szWu/XI.VGmsJBIY7wtLJ6C9cVo5b4.1NRK', 'quan_tri', 'hoat_dong', NULL, 0, NULL, '2026-11-03 09:25:50', '2026-10-22 17:35:11', '2026-11-03 09:25:50'),
	(2, 'Nguyễn Văn Minh', 'gv.minh@demo.test', '$2y$10$IX3G7ohD8xf5tpPnDiiRcO14lXDVy1kuCvfDBB6wIcSbAnIXkzFL2', 'giang_vien', 'hoat_dong', 1, 0, NULL, '2026-11-03 08:17:15', '2026-10-22 17:35:11', '2026-11-03 08:17:15'),
	(3, 'Trần Thu Hà', 'gv.ha@demo.test', '$2y$10$1IgX5hYLu/gNcArsS9szWu/XI.VGmsJBIY7wtLJ6C9cVo5b4.1NRK', 'giang_vien', 'hoat_dong', 2, 0, NULL, '2026-11-03 08:41:00', '2026-10-22 17:35:11', '2026-11-03 08:41:00'),
	(4, 'Lê Quang Huy', 'gv.huy@demo.test', '$2y$10$1IgX5hYLu/gNcArsS9szWu/XI.VGmsJBIY7wtLJ6C9cVo5b4.1NRK', 'giang_vien', 'hoat_dong', 1, 0, NULL, '2026-11-03 10:09:40', '2026-10-22 17:35:11', '2026-11-03 10:09:40'),
	(5, 'Phạm Thị An', 'sv.an@demo.test', '$2y$10$1IgX5hYLu/gNcArsS9szWu/XI.VGmsJBIY7wtLJ6C9cVo5b4.1NRK', 'sinh_vien', 'hoat_dong', NULL, 0, NULL, '2026-11-03 10:08:25', '2026-10-22 17:35:11', '2026-11-03 10:08:25'),
	(6, 'Hoàng Gia Bảo', 'sv.bao@demo.test', '$2y$10$1IgX5hYLu/gNcArsS9szWu/XI.VGmsJBIY7wtLJ6C9cVo5b4.1NRK', 'sinh_vien', 'hoat_dong', NULL, 0, NULL, '2026-11-03 10:08:52', '2026-10-22 17:35:11', '2026-11-03 10:08:52'),
	(7, 'Đỗ Minh Khôi', 'sv.khoi@demo.test', '$2y$10$1IgX5hYLu/gNcArsS9szWu/XI.VGmsJBIY7wtLJ6C9cVo5b4.1NRK', 'sinh_vien', 'hoat_dong', NULL, 0, NULL, '2026-11-03 10:07:59', '2026-10-22 17:35:11', '2026-11-03 10:07:59'),
	(8, 'Phạm Hoàng Anh', 'gv.hanh@demo.test', '$2y$10$1IgX5hYLu/gNcArsS9szWu/XI.VGmsJBIY7wtLJ6C9cVo5b4.1NRK', 'giang_vien', 'hoat_dong', 1, 0, NULL, NULL, '2026-10-29 14:39:47', '2026-10-29 14:39:47'),
	(9, 'Vũ Thị Ngọc', 'gv.ngoc@demo.test', '$2y$10$1IgX5hYLu/gNcArsS9szWu/XI.VGmsJBIY7wtLJ6C9cVo5b4.1NRK', 'giang_vien', 'hoat_dong', 3, 0, NULL, NULL, '2026-10-29 14:39:47', '2026-10-29 14:39:47'),
	(10, 'Trần Đức Mạnh', 'sv.manh@demo.test', '$2y$10$1IgX5hYLu/gNcArsS9szWu/XI.VGmsJBIY7wtLJ6C9cVo5b4.1NRK', 'sinh_vien', 'hoat_dong', NULL, 0, NULL, NULL, '2026-10-29 14:39:47', '2026-10-29 14:39:47'),
	(11, 'Nguyễn Mai Phương', 'sv.phuong@demo.test', '$2y$10$1IgX5hYLu/gNcArsS9szWu/XI.VGmsJBIY7wtLJ6C9cVo5b4.1NRK', 'sinh_vien', 'hoat_dong', NULL, 0, NULL, NULL, '2026-10-29 14:39:47', '2026-10-29 14:39:47'),
	(12, 'Lê Tuấn Kiệt', 'sv.kiet@demo.test', '$2y$10$1IgX5hYLu/gNcArsS9szWu/XI.VGmsJBIY7wtLJ6C9cVo5b4.1NRK', 'sinh_vien', 'hoat_dong', NULL, 0, NULL, NULL, '2026-10-29 14:39:47', '2026-10-29 14:39:47'),
	(13, 'Đặng Văn Long', 'gv.long@demo.test', '$2y$10$1IgX5hYLu/gNcArsS9szWu/XI.VGmsJBIY7wtLJ6C9cVo5b4.1NRK', 'giang_vien', 'hoat_dong', 2, 0, NULL, NULL, '2026-10-29 14:41:01', '2026-10-29 14:41:01'),
	(14, 'Bùi Thị Dung', 'gv.dung@demo.test', '$2y$10$1IgX5hYLu/gNcArsS9szWu/XI.VGmsJBIY7wtLJ6C9cVo5b4.1NRK', 'giang_vien', 'hoat_dong', 4, 0, NULL, NULL, '2026-10-29 14:41:01', '2026-10-29 14:41:01'),
	(15, 'Ngô Quốc Khánh', 'gv.khanh@demo.test', '$2y$10$1IgX5hYLu/gNcArsS9szWu/XI.VGmsJBIY7wtLJ6C9cVo5b4.1NRK', 'giang_vien', 'hoat_dong', 1, 0, NULL, NULL, '2026-10-29 14:41:01', '2026-10-29 14:41:01'),
	(16, 'Phan Văn Hùng', 'sv.hung@demo.test', '$2y$10$1IgX5hYLu/gNcArsS9szWu/XI.VGmsJBIY7wtLJ6C9cVo5b4.1NRK', 'sinh_vien', 'hoat_dong', NULL, 0, NULL, NULL, '2026-10-29 14:41:01', '2026-10-29 14:41:01'),
	(17, 'Nguyễn Thùy Linh', 'sv.linh@demo.test', '$2y$10$1IgX5hYLu/gNcArsS9szWu/XI.VGmsJBIY7wtLJ6C9cVo5b4.1NRK', 'sinh_vien', 'hoat_dong', NULL, 0, NULL, NULL, '2026-10-29 14:41:01', '2026-10-29 14:41:01'),
	(18, 'Trịnh Tiến Đạt', 'sv.dat@demo.test', '$2y$10$1IgX5hYLu/gNcArsS9szWu/XI.VGmsJBIY7wtLJ6C9cVo5b4.1NRK', 'sinh_vien', 'hoat_dong', NULL, 0, NULL, NULL, '2026-10-29 14:41:01', '2026-10-29 14:41:01'),
	(19, 'Vũ Hoàng Nam', 'sv.nam@demo.test', '$2y$10$1IgX5hYLu/gNcArsS9szWu/XI.VGmsJBIY7wtLJ6C9cVo5b4.1NRK', 'sinh_vien', 'hoat_dong', NULL, 0, NULL, NULL, '2026-10-29 14:41:01', '2026-10-29 14:41:01'),
	(20, 'Đào Minh Thu', 'sv.thu@demo.test', '$2y$10$1IgX5hYLu/gNcArsS9szWu/XI.VGmsJBIY7wtLJ6C9cVo5b4.1NRK', 'sinh_vien', 'hoat_dong', NULL, 0, NULL, NULL, '2026-10-29 14:41:01', '2026-10-29 14:41:01'),
	(21, 'Lý Văn Hải', 'sv.hai@demo.test', '$2y$10$1IgX5hYLu/gNcArsS9szWu/XI.VGmsJBIY7wtLJ6C9cVo5b4.1NRK', 'sinh_vien', 'hoat_dong', NULL, 0, NULL, NULL, '2026-10-29 14:41:01', '2026-10-29 14:41:01'),
	(22, 'Cao Mỹ Duyên', 'sv.duyen@demo.test', '$2y$10$1IgX5hYLu/gNcArsS9szWu/XI.VGmsJBIY7wtLJ6C9cVo5b4.1NRK', 'sinh_vien', 'hoat_dong', NULL, 0, NULL, NULL, '2026-10-29 14:41:01', '2026-10-29 14:41:01'),
	(23, 'Đỗ Văn Thành', 'sv.thanh@demo.test', '$2y$10$1IgX5hYLu/gNcArsS9szWu/XI.VGmsJBIY7wtLJ6C9cVo5b4.1NRK', 'sinh_vien', 'hoat_dong', NULL, 0, NULL, NULL, '2026-10-29 14:41:01', '2026-10-29 14:41:01'),
	(24, 'Nguyễn Minh Anh', 'sv.minhanh@import.test', '$2y$10$2ZtSqi90taZE44b.tuYXr.7/JYuIcBR83vBlLCS8aQmAteLbvjDFm', 'sinh_vien', 'hoat_dong', NULL, 0, NULL, NULL, '2026-11-01 23:24:37', '2026-11-01 23:24:37'),
	(25, 'Trần Quốc Bảo', 'sv.quocbao@import.test', '$2y$10$eoEPAC2.grxR6ESi3GqKkOqKhvL1H8Atx54mm6LTPmh8RixuSgTGy', 'sinh_vien', 'hoat_dong', NULL, 0, NULL, NULL, '2026-11-01 23:24:37', '2026-11-01 23:24:37'),
	(26, 'Lê Thị Cẩm', 'sv.cam@import.test', '$2y$10$h8WHOmnFjRCjz0Gts3jETeJpSxeZJ.U2uJXIRuyTiuN5gw8n0zzbS', 'sinh_vien', 'hoat_dong', NULL, 0, NULL, NULL, '2026-11-01 23:24:37', '2026-11-01 23:24:37'),
	(27, 'Phạm Đức Dũng', 'sv.dung@import.test', '$2y$10$JvBBm473ysCUh1kxHw9Uh.OrGXQB55seydpiUbrd7h7hfcqLOKm.a', 'sinh_vien', 'hoat_dong', NULL, 0, NULL, NULL, '2026-11-01 23:24:37', '2026-11-01 23:24:37'),
	(28, 'Hoàng Thu Hà', 'sv.thuha@import.test', '$2y$10$xu7/0aps7YBDhW4EAs/ylOGr40xCYEu5rjtVqj4.icrTAgrf6XrKm', 'sinh_vien', 'hoat_dong', NULL, 0, NULL, NULL, '2026-11-01 23:24:37', '2026-11-01 23:24:37'),
	(29, 'Vũ Hải Long', 'gv.hailong@import.test', '$2y$10$fLIqm9SuXaX569V50DODD.6N1nOIfUvGv1Ad8eVdoOkRI9zyMDqKq', 'giang_vien', 'hoat_dong', 1, 0, NULL, NULL, '2026-11-01 23:24:37', '2026-11-01 23:24:37'),
	(30, 'Đặng Thanh Mai', 'gv.thanhmai@import.test', '$2y$10$ABlq48lanqKMzo3NmC368eLIMKXeG/U/DJesPT4wdmFjpuv7ieQXa', 'giang_vien', 'hoat_dong', 2, 0, NULL, NULL, '2026-11-01 23:24:37', '2026-11-01 23:24:37'),
	(31, 'Bùi Văn Nam', 'gv.vannam@import.test', '$2y$10$Jv0DSqEY/C8trTcCQxIWw.zWhi7Nz2gFTHTMYsMc8eQZQFUjGwCtu', 'giang_vien', 'hoat_dong', 4, 0, NULL, NULL, '2026-11-01 23:24:37', '2026-11-01 23:28:36'),
	(32, 'Nguyễn Hoàng Phúc', 'sv.hoangphuc@import.test', '$2y$10$eg0Iv5eML.Y2CybxkZlute3/bGNCJuVFJBu0TSK19MearKxZDX/em', 'sinh_vien', 'hoat_dong', NULL, 0, NULL, NULL, '2026-11-01 23:38:52', '2026-11-01 23:38:52'),
	(33, 'Trần Gia Hân', 'sv.giahan@import.test', '$2y$10$HeNkumdtwQk4DQ9oWMK.WOdEhBfm1Cg5xZc6M3Ts.nccAc5vY5wP6', 'sinh_vien', 'hoat_dong', NULL, 0, NULL, NULL, '2026-11-01 23:38:52', '2026-11-01 23:38:52'),
	(34, 'Lê Nhật Minh', 'sv.nhatminh@import.test', '$2y$10$ZQVTl4e1Nhj1zmgoOuK/f.HVao/JdWdHka1kdRBWTaUi5Tb/SBdx6', 'sinh_vien', 'hoat_dong', NULL, 0, NULL, NULL, '2026-11-01 23:38:52', '2026-11-01 23:38:52'),
	(35, 'Phạm Khánh Linh', 'sv.khanhlinh@import.test', '$2y$10$JWh4SXUFzsjoUMKqRMCYHOxqqnOpQTH8kZhPL/mKhwLzChUgj3h/C', 'sinh_vien', 'hoat_dong', NULL, 0, NULL, NULL, '2026-11-01 23:38:52', '2026-11-01 23:38:52'),
	(36, 'Hoàng Đức Thành', 'sv.ducthanh@import.test', '$2y$10$OlundolsOGMr1aacY.9UweSsNXZA4jXTUH.c6GzMdoZCV0io0XZLC', 'sinh_vien', 'hoat_dong', NULL, 0, NULL, NULL, '2026-11-01 23:38:52', '2026-11-01 23:38:52'),
	(37, 'Vũ Ngọc Ánh', 'gv.ngocanh@import.test', '$2y$10$WWyJcxD2UmiGDIozEh8eCueQMlWRdrPbjmEwIaoE.X5xVbEmZmFUe', 'giang_vien', 'hoat_dong', 1, 0, NULL, NULL, '2026-11-01 23:38:53', '2026-11-01 23:38:53'),
	(38, 'Đặng Minh Khoa', 'gv.minhkhoa@import.test', '$2y$10$9uRLTq4BWY2JBugl5OT6JuYG3nawTa2SEVrw.FctSuscc1/q8XfAK', 'giang_vien', 'hoat_dong', 2, 0, NULL, NULL, '2026-11-01 23:38:53', '2026-11-01 23:38:53'),
	(39, 'Bùi Thảo Vy', 'gv.thaovy@import.test', '$2y$10$f6HpezHkWqgXqCZmf72TY.XGkIP1hj9sJ2xS9ZdhatTgdPg0pqvTC', 'giang_vien', 'hoat_dong', 4, 0, NULL, NULL, '2026-11-01 23:38:53', '2026-11-01 23:38:53');

-- Dumping structure for table dat_lich_tu_van.phan_hoi
CREATE TABLE IF NOT EXISTS `phan_hoi` (
  `ma_phan_hoi` int unsigned NOT NULL AUTO_INCREMENT,
  `ma_cuoc_hen` int unsigned NOT NULL,
  `diem_danh_gia` tinyint unsigned NOT NULL,
  `nhan_xet` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ngay_tao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_phan_hoi`),
  UNIQUE KEY `uq_phan_hoi_cuoc_hen` (`ma_cuoc_hen`),
  CONSTRAINT `fk_phan_hoi_cuoc_hen` FOREIGN KEY (`ma_cuoc_hen`) REFERENCES `cuoc_hen` (`ma_cuoc_hen`) ON DELETE CASCADE,
  CONSTRAINT `chk_phan_hoi_diem` CHECK ((`diem_danh_gia` between 1 and 5))
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table dat_lich_tu_van.phan_hoi: ~0 rows (approximately)
INSERT INTO `phan_hoi` (`ma_phan_hoi`, `ma_cuoc_hen`, `diem_danh_gia`, `nhan_xet`, `ngay_tao`) VALUES
	(1, 1, 5, 'tốt', '2026-10-23 13:12:02'),
	(2, 2, 4, 'Thầy hướng dẫn rất nhiệt tình và chi tiết.', '2026-10-29 14:39:48'),
	(3, 12, 5, 'Thầy Huy hướng dẫn rất dễ hiểu, chỉ rõ các phần em làm chưa đúng.', '2026-10-29 14:41:01'),
	(4, 20, 5, 'Cô Hà tư vấn rất chi tiết, nhiệt tình và thực tế. Nhờ cô góp ý mà em đã sửa lại CV ấn tượng hơn rất nhiều!', '2026-10-29 15:00:16');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
