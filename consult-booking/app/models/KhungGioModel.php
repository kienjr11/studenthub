<?php
class KhungGioModel extends MoHinh {
    
    // Lấy danh sách khung giờ theo giảng viên và lọc theo ngày
    public function layTheoGiangVienVaNgay($maGiangVien, $ngay = null) {
        $sql = "SELECT * FROM khung_gio WHERE ma_giang_vien = ?";
        $params = [$maGiangVien];

        if (!empty($ngay)) {
            $sql .= " AND DATE(bat_dau) = ?";
            $params[] = $ngay;
        }

        $sql .= " ORDER BY bat_dau ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Thêm khung giờ rảnh mới
    public function them($maGiangVien, $batDau, $ketThuc, $diaDiem) {
        $sql = "INSERT INTO khung_gio (ma_giang_vien, bat_dau, ket_thuc, dia_dim) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$maGiangVien, $batDau, $ketThuc, $diaDiem]);
    }

    // Cập nhật (Sửa) khung giờ
    public function capNhat($maKhungGio, $maGiangVien, $batDau, $ketThuc, $diaDiem) {
        $sql = "UPDATE khung_gio SET bat_dau = ?, ket_thuc = ?, dia_dim = ? WHERE ma_khung_gio = ? AND ma_giang_vien = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$batDau, $ketThuc, $diaDiem, $maKhungGio, $maGiangVien]);
    }

    // Xóa khung giờ
    public function xoa($maKhungGio, $maGiangVien) {
        $sql = "DELETE FROM khung_gio WHERE ma_khung_gio = ? AND ma_giang_vien = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$maKhungGio, $maGiangVien]);
    }
}