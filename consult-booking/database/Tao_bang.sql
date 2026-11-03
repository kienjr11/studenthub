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

-- Data exporting was unselected.

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

-- Data exporting was unselected.

-- Dumping structure for table dat_lich_tu_van.don_vi
CREATE TABLE IF NOT EXISTS `don_vi` (
  `ma_don_vi` int unsigned NOT NULL AUTO_INCREMENT,
  `ten_don_vi` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mo_ta` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ngay_tao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ma_don_vi`),
  UNIQUE KEY `uq_don_vi_ten` (`ten_don_vi`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table dat_lich_tu_van.giang_vien_chuyen_mon
CREATE TABLE IF NOT EXISTS `giang_vien_chuyen_mon` (
  `ma_giang_vien` int unsigned NOT NULL,
  `ma_chuyen_mon` int unsigned NOT NULL,
  PRIMARY KEY (`ma_giang_vien`,`ma_chuyen_mon`),
  KEY `fk_gvcm_chuyen_mon` (`ma_chuyen_mon`),
  CONSTRAINT `fk_gvcm_chuyen_mon` FOREIGN KEY (`ma_chuyen_mon`) REFERENCES `chuyen_mon` (`ma_chuyen_mon`) ON DELETE RESTRICT,
  CONSTRAINT `fk_gvcm_giang_vien` FOREIGN KEY (`ma_giang_vien`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

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

-- Data exporting was unselected.

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

-- Data exporting was unselected.

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

-- Data exporting was unselected.

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

-- Data exporting was unselected.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
