-- --------------------------------------------------------
-- Máy chủ:                      127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Phiên bản:           12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for dat_lich_tu_van
CREATE DATABASE IF NOT EXISTS `dat_lich_tu_van` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `dat_lich_tu_van`;

-- Dumping structure for table dat_lich_tu_van.don_vi
CREATE TABLE IF NOT EXISTS `don_vi` (
  `ma_don_vi` int unsigned NOT NULL AUTO_INCREMENT,
  `ten_don_vi` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mo_ta` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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

-- Dumping structure for table dat_lich_tu_van.chuyen_mon
CREATE TABLE IF NOT EXISTS `chuyen_mon` (
  `ma_chuyen_mon` int unsigned NOT NULL AUTO_INCREMENT,
  `ten_chuyen_mon` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mo_ta` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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

-- Dumping structure for table dat_lich_tu_van.nguoi_dung
CREATE TABLE IF NOT EXISTS `nguoi_dung` (
  `ma_nguoi_dung` int unsigned NOT NULL AUTO_INCREMENT,
  `ho_ten` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mat_khau_bam` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `vai_tro` enum('sinh_vien','giang_vien','quan_tri') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sinh_vien',
  `trang_thai` enum('hoat_dong','cho_duyet','da_khoa') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'hoat_dong',
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table dat_lich_tu_van.nguoi_dung: ~7 rows (approximately)
INSERT INTO `nguoi_dung` (`ma_nguoi_dung`, `ho_ten`, `email`, `mat_khau_bam`, `vai_tro`, `trang_thai`, `ma_don_vi`, `so_lan_dang_nhap_sai`, `khoa_den`, `dang_nhap_lan_cuoi`, `ngay_tao`, `ngay_cap_nhat`) VALUES
	(1, 'Quản trị hệ thống', 'admin@demo.test', '$2y$10$1IgX5hYLu/gNcArsS9szWu/XI.VGmsJBIY7wtLJ6C9cVo5b4.1NRK', 'quan_tri', 'hoat_dong', NULL, 0, NULL, '2026-10-22 21:54:55', '2026-10-22 17:35:11', '2026-10-22 21:54:55'),
	(2, 'Nguyễn Văn Minh', 'gv.minh@demo.test', '$2y$10$IX3G7ohD8xf5tpPnDiiRcO14lXDVy1kuCvfDBB6wIcSbAnIXkzFL2', 'giang_vien', 'hoat_dong', 1, 0, NULL, '2026-10-23 13:16:06', '2026-10-22 17:35:11', '2026-10-23 13:16:06'),
	(3, 'Trần Thu Hà', 'gv.ha@demo.test', '$2y$10$1IgX5hYLu/gNcArsS9szWu/XI.VGmsJBIY7wtLJ6C9cVo5b4.1NRK', 'giang_vien', 'hoat_dong', 2, 0, NULL, NULL, '2026-10-22 17:35:11', '2026-10-22 17:35:11'),
	(4, 'Lê Quang Huy', 'gv.huy@demo.test', '$2y$10$1IgX5hYLu/gNcArsS9szWu/XI.VGmsJBIY7wtLJ6C9cVo5b4.1NRK', 'giang_vien', 'hoat_dong', 1, 0, NULL, '2026-10-23 13:14:31', '2026-10-22 17:35:11', '2026-10-23 13:14:31'),
	(5, 'Phạm Thị An', 'sv.an@demo.test', '$2y$10$1IgX5hYLu/gNcArsS9szWu/XI.VGmsJBIY7wtLJ6C9cVo5b4.1NRK', 'sinh_vien', 'hoat_dong', NULL, 0, NULL, NULL, '2026-10-22 17:35:11', '2026-10-22 17:35:11'),
	(6, 'Hoàng Gia Bảo', 'sv.bao@demo.test', '$2y$10$1IgX5hYLu/gNcArsS9szWu/XI.VGmsJBIY7wtLJ6C9cVo5b4.1NRK', 'sinh_vien', 'hoat_dong', NULL, 0, NULL, '2026-10-23 13:22:00', '2026-10-22 17:35:11', '2026-10-23 13:22:00'),
	(7, 'Đỗ Minh Khôi', 'sv.khoi@demo.test', '$2y$10$1IgX5hYLu/gNcArsS9szWu/XI.VGmsJBIY7wtLJ6C9cVo5b4.1NRK', 'sinh_vien', 'da_khoa', NULL, 0, NULL, NULL, '2026-10-22 17:35:11', '2026-10-22 17:35:11');

-- Dumping structure for table dat_lich_tu_van.ho_so_giang_vien
CREATE TABLE IF NOT EXISTS `ho_so_giang_vien` (
  `ma_giang_vien` int unsigned NOT NULL,
  `hoc_ham` enum('giao_su','pho_giao_su') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hoc_vi` enum('cu_nhan','ky_su','thac_si','tien_si') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `so_dien_thoai` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phong_lam_viec` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gioi_thieu` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `anh_dai_dien` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ngay_cap_nhat` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_giang_vien`),
  CONSTRAINT `fk_ho_so_giang_vien` FOREIGN KEY (`ma_giang_vien`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table dat_lich_tu_van.ho_so_giang_vien: ~3 rows (approximately)
INSERT INTO `ho_so_giang_vien` (`ma_giang_vien`, `hoc_ham`, `hoc_vi`, `so_dien_thoai`, `phong_lam_viec`, `gioi_thieu`, `anh_dai_dien`, `ngay_cap_nhat`) VALUES
	(2, NULL, 'tien_si', '0901234567', 'P.305 – Nhà A', 'Giảng dạy lập trình web và cơ sở dữ liệu; sẵn sàng tư vấn đồ án và định hướng nghề nghiệp lập trình viên.', 'uploads/avatars/gv_2.jpg', '2026-10-22 17:35:11'),
	(3, 'pho_giao_su', 'tien_si', NULL, 'P.210 – Nhà B', 'Tư vấn khởi nghiệp, xây dựng kế hoạch kinh doanh và kỹ năng xin việc.', NULL, '2026-10-22 17:35:11'),
	(4, 'pho_giao_su', 'thac_si', '0919337474', 'P.306 nhà B', 'đẹp trai', NULL, '2026-10-22 21:57:11');

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
	(2, 2),
	(3, 5),
	(3, 6),
	(4, 6);

-- Dumping structure for table dat_lich_tu_van.khung_gio
CREATE TABLE IF NOT EXISTS `khung_gio` (
  `ma_khung_gio` int unsigned NOT NULL AUTO_INCREMENT,
  `ma_giang_vien` int unsigned NOT NULL,
  `bat_dau` datetime NOT NULL,
  `ket_thuc` datetime NOT NULL,
  `dia_diem` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `suc_chua` tinyint unsigned NOT NULL DEFAULT '1',
  `ngay_tao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_khung_gio`),
  KEY `idx_khung_gio_giang_vien_bat_dau` (`ma_giang_vien`,`bat_dau`),
  CONSTRAINT `fk_khung_gio_giang_vien` FOREIGN KEY (`ma_giang_vien`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE RESTRICT,
  CONSTRAINT `chk_khung_gio_thoi_gian` CHECK ((`ket_thuc` > `bat_dau`)),
  CONSTRAINT `chk_khung_gio_suc_chua` CHECK ((`suc_chua` between 1 and 50))
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table dat_lich_tu_van.khung_gio: ~2 rows (approximately)
INSERT INTO `khung_gio` (`ma_khung_gio`, `ma_giang_vien`, `bat_dau`, `ket_thuc`, `dia_diem`, `suc_chua`, `ngay_tao`) VALUES
	(1, 4, '2027-02-04 12:12:00', '2027-02-04 13:13:00', NULL, 1, '2026-10-23 13:10:18'),
	(2, 2, '2027-02-04 12:31:00', '2027-02-04 13:31:00', NULL, 1, '2026-10-23 13:15:50');

-- Dumping structure for table dat_lich_tu_van.cuoc_hen
CREATE TABLE IF NOT EXISTS `cuoc_hen` (
  `ma_cuoc_hen` int unsigned NOT NULL AUTO_INCREMENT,
  `ma_khung_gio` int unsigned NOT NULL,
  `ma_sinh_vien` int unsigned NOT NULL,
  `chu_de` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trang_thai` enum('cho_duyet','da_duyet','tu_choi','da_huy','hoan_thanh') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cho_duyet',
  `ghi_chu_giang_vien` text COLLATE utf8mb4_unicode_ci,
  `khung_gio_hieu_luc` int unsigned GENERATED ALWAYS AS (if((`trang_thai` in (_utf8mb4'cho_duyet',_utf8mb4'da_duyet',_utf8mb4'hoan_thanh')),`ma_khung_gio`,NULL)) STORED,
  `ngay_tao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ngay_cap_nhat` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_cuoc_hen`),
  KEY `idx_cuoc_hen_khung_gio_hieu_luc` (`khung_gio_hieu_luc`),
  KEY `idx_cuoc_hen_sinh_vien` (`ma_sinh_vien`,`trang_thai`),
  KEY `fk_cuoc_hen_khung_gio` (`ma_khung_gio`),
  CONSTRAINT `fk_cuoc_hen_khung_gio` FOREIGN KEY (`ma_khung_gio`) REFERENCES `khung_gio` (`ma_khung_gio`) ON DELETE RESTRICT,
  CONSTRAINT `fk_cuoc_hen_sinh_vien` FOREIGN KEY (`ma_sinh_vien`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table dat_lich_tu_van.cuoc_hen: ~2 rows (approximately)
INSERT INTO `cuoc_hen` (`ma_cuoc_hen`, `ma_khung_gio`, `ma_sinh_vien`, `chu_de`, `trang_thai`, `ghi_chu_giang_vien`, `ngay_tao`, `ngay_cap_nhat`) VALUES
	(1, 1, 6, 'ád', 'hoan_thanh', 'oke', '2026-10-23 13:10:18', '2026-10-23 13:14:40'),
	(2, 2, 6, 'ádasd', 'da_duyet', NULL, '2026-10-23 13:15:50', '2026-10-23 13:16:11');

-- Dumping structure for table dat_lich_tu_van.phan_hoi
CREATE TABLE IF NOT EXISTS `phan_hoi` (
  `ma_phan_hoi` int unsigned NOT NULL AUTO_INCREMENT,
  `ma_cuoc_hen` int unsigned NOT NULL,
  `diem_danh_gia` tinyint unsigned NOT NULL,
  `nhan_xet` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ngay_tao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_phan_hoi`),
  UNIQUE KEY `uq_phan_hoi_cuoc_hen` (`ma_cuoc_hen`),
  CONSTRAINT `fk_phan_hoi_cuoc_hen` FOREIGN KEY (`ma_cuoc_hen`) REFERENCES `cuoc_hen` (`ma_cuoc_hen`) ON DELETE CASCADE,
  CONSTRAINT `chk_phan_hoi_diem` CHECK ((`diem_danh_gia` between 1 and 5))
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table dat_lich_tu_van.phan_hoi: ~1 rows (approximately)
INSERT INTO `phan_hoi` (`ma_phan_hoi`, `ma_cuoc_hen`, `diem_danh_gia`, `nhan_xet`, `ngay_tao`) VALUES
	(1, 1, 5, 'tốt', '2026-10-23 13:12:02');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;