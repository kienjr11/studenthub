<?php
declare(strict_types=1);

/**
 * Hồ sơ giảng viên: nguoi_dung (nd) + ho_so_giang_vien (hs) + don_vi (dv) + giang_vien_chuyen_mon.
 * Phục vụ 3 chức năng: trang hồ sơ cá nhân, danh sách tìm kiếm cho sinh viên, dashboard của giảng viên.
 */
final class HoSoGiangVien extends MoHinh
{
    public const HOC_HAM = ['giao_su' => 'Giáo sư', 'pho_giao_su' => 'Phó giáo sư'];
    public const HOC_VI = ['cu_nhan' => 'Cử nhân', 'ky_su' => 'Kỹ sư', 'thac_si' => 'Thạc sĩ', 'tien_si' => 'Tiến sĩ'];
    public const HOC_HAM_NGAN = ['giao_su' => 'GS.', 'pho_giao_su' => 'PGS.'];
    public const HOC_VI_NGAN = ['cu_nhan' => 'CN.', 'ky_su' => 'KS.', 'thac_si' => 'ThS.', 'tien_si' => 'TS.'];

    /** Hồ sơ đầy đủ của một giảng viên (kể cả khi chưa có dòng trong ho_so_giang_vien). */
    public function find(int $id, bool $onlyActive = false): ?array
    {
        $sql = "SELECT nd.ma_nguoi_dung, nd.ho_ten, nd.email, nd.ma_don_vi, dv.ten_don_vi,
                       hs.hoc_ham, hs.hoc_vi, hs.so_dien_thoai, hs.phong_lam_viec, hs.gioi_thieu
                FROM nguoi_dung nd
                LEFT JOIN don_vi dv ON dv.ma_don_vi = nd.ma_don_vi
                LEFT JOIN ho_so_giang_vien hs ON hs.ma_giang_vien = nd.ma_nguoi_dung
                WHERE nd.ma_nguoi_dung = :id AND nd.vai_tro = 'giang_vien'"
            . ($onlyActive ? " AND nd.trang_thai = 'hoat_dong'" : '');
        $st = $this->db->prepare($sql);
        $st->execute(['id' => $id]);
        $row = $st->fetch();
        if (!$row) {
            return null;
        }
        $st = $this->db->prepare('SELECT cm.ma_chuyen_mon, cm.ten_chuyen_mon FROM giang_vien_chuyen_mon g
            JOIN chuyen_mon cm ON cm.ma_chuyen_mon = g.ma_chuyen_mon WHERE g.ma_giang_vien = :id ORDER BY cm.ten_chuyen_mon');
        $st->execute(['id' => $id]);
        $row['chuyen_mon'] = $st->fetchAll();
        return $row;
    }

    /**
     * Lưu hồ sơ trong MỘT giao dịch: hoặc cập nhật hết, hoặc không đổi gì.
     * @param array{ho_ten:string,ma_don_vi:int,hoc_ham:?string,hoc_vi:?string,so_dien_thoai:?string,phong_lam_viec:?string,gioi_thieu:?string} $d
     * @param int[] $specialtyIds
     */
    public function save(int $id, array $d, array $specialtyIds): void
    {
        $this->db->beginTransaction();
        try {
            $st = $this->db->prepare('UPDATE nguoi_dung SET ho_ten = :ten, ma_don_vi = :dv WHERE ma_nguoi_dung = :id');
            $st->execute(['ten' => $d['ho_ten'], 'dv' => $d['ma_don_vi'], 'id' => $id]);

            $st = $this->db->prepare('INSERT INTO ho_so_giang_vien (ma_giang_vien, hoc_ham, hoc_vi, so_dien_thoai, phong_lam_viec, gioi_thieu)
                VALUES (:id, :hh, :hv, :sdt, :phong, :gt)
                ON DUPLICATE KEY UPDATE hoc_ham = VALUES(hoc_ham), hoc_vi = VALUES(hoc_vi), so_dien_thoai = VALUES(so_dien_thoai),
                                        phong_lam_viec = VALUES(phong_lam_viec), gioi_thieu = VALUES(gioi_thieu)');
            $st->execute([
                'id' => $id, 'hh' => $d['hoc_ham'], 'hv' => $d['hoc_vi'], 'sdt' => $d['so_dien_thoai'],
                'phong' => $d['phong_lam_viec'], 'gt' => $d['gioi_thieu'],
            ]);

            $this->db->prepare('DELETE FROM giang_vien_chuyen_mon WHERE ma_giang_vien = :id')->execute(['id' => $id]);
            $ins = $this->db->prepare('INSERT INTO giang_vien_chuyen_mon (ma_giang_vien, ma_chuyen_mon) VALUES (:g, :c)');
            foreach ($specialtyIds as $cid) {
                $ins->execute(['g' => $id, 'c' => $cid]);
            }
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ---- Danh sách giảng viên cho sinh viên tìm kiếm ----

    private function where(array $f, array &$p): string
    {
        $w = ["nd.vai_tro = 'giang_vien'", "nd.trang_thai = 'hoat_dong'"];   // chỉ giảng viên đã được duyệt, không bị khóa
        if (($f['q'] ?? '') !== '') {
            $w[] = "nd.ho_ten LIKE :q ESCAPE '|'";
            $p['q'] = '%' . str_replace(['|', '%', '_'], ['||', '|%', '|_'], $f['q']) . '%';
        }
        if (($f['dept'] ?? 0) > 0) {
            $w[] = 'nd.ma_don_vi = :dept';
            $p['dept'] = $f['dept'];
        }
        if (($f['spec'] ?? 0) > 0) {
            $w[] = 'EXISTS (SELECT 1 FROM giang_vien_chuyen_mon x WHERE x.ma_giang_vien = nd.ma_nguoi_dung AND x.ma_chuyen_mon = :spec)';
            $p['spec'] = $f['spec'];
        }
        return implode(' AND ', $w);
    }

    public function countSearch(array $filters): int
    {
        $p = [];
        $st = $this->db->prepare('SELECT COUNT(*) FROM nguoi_dung nd WHERE ' . $this->where($filters, $p));
        $st->execute($p);
        return (int) $st->fetchColumn();
    }

    /** Mỗi dòng có thêm 'chuyen_mon' là mảng tên chuyên môn. */
    public function search(array $filters, int $limit, int $offset): array
    {
        $p = [];
        $sql = "SELECT nd.ma_nguoi_dung, nd.ho_ten, dv.ten_don_vi, hs.hoc_ham, hs.hoc_vi, hs.phong_lam_viec,
                       (SELECT GROUP_CONCAT(cm.ten_chuyen_mon ORDER BY cm.ten_chuyen_mon SEPARATOR '||')
                          FROM giang_vien_chuyen_mon g JOIN chuyen_mon cm ON cm.ma_chuyen_mon = g.ma_chuyen_mon
                         WHERE g.ma_giang_vien = nd.ma_nguoi_dung) AS chuyen_mon_ds
                FROM nguoi_dung nd
                LEFT JOIN don_vi dv ON dv.ma_don_vi = nd.ma_don_vi
                LEFT JOIN ho_so_giang_vien hs ON hs.ma_giang_vien = nd.ma_nguoi_dung
                WHERE " . $this->where($filters, $p) . ' ORDER BY nd.ho_ten LIMIT :lim OFFSET :off';
        $st = $this->db->prepare($sql);
        foreach ($p as $k => $v) {
            $st->bindValue(':' . $k, $v);
        }
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->bindValue(':off', $offset, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll();
        foreach ($rows as &$r) {
            $r['chuyen_mon'] = $r['chuyen_mon_ds'] !== null ? explode('||', $r['chuyen_mon_ds']) : [];
        }
        return $rows;
    }

    // ---- Dashboard cá nhân ----

    /** Mức độ hoàn thiện hồ sơ: phần trăm + danh sách mục còn thiếu. */
    public static function completeness(array $profile): array
    {
        $checks = [
            'Đơn vị công tác'       => !empty($profile['ma_don_vi']),
            'Học vị'                => !empty($profile['hoc_vi']),
            'Số điện thoại'         => !empty($profile['so_dien_thoai']),
            'Phòng làm việc'        => !empty($profile['phong_lam_viec']),
            'Giới thiệu bản thân'   => !empty($profile['gioi_thieu']),
            'Lĩnh vực tư vấn'       => !empty($profile['chuyen_mon']),
        ];
        $missing = array_keys(array_filter($checks, static fn (bool $ok) => !$ok));
        return ['percent' => (int) round((count($checks) - count($missing)) * 100 / count($checks)), 'missing' => $missing];
    }

    /** Số liệu cho các ô thống kê trên dashboard. Dùng giờ của PHP (đã set múi giờ) để khớp với dữ liệu đã lưu. */
    public function stats(int $id): array
    {
        $now = date('Y-m-d H:i:s');
        $today = date('Y-m-d');
        $join = 'FROM cuoc_hen ch JOIN khung_gio kg ON kg.ma_khung_gio = ch.ma_khung_gio';
        $sql = "SELECT
            (SELECT COUNT(*) FROM khung_gio WHERE ma_giang_vien = :g1 AND bat_dau > :now1) AS slot_sap_toi,
            (SELECT COUNT(*) $join WHERE kg.ma_giang_vien = :g2 AND ch.trang_thai = 'cho_duyet') AS cho_duyet,
            (SELECT COUNT(*) $join WHERE kg.ma_giang_vien = :g3 AND ch.trang_thai = 'da_duyet' AND DATE(kg.bat_dau) = :today) AS hom_nay,
            (SELECT COUNT(*) $join WHERE kg.ma_giang_vien = :g4 AND ch.trang_thai = 'hoan_thanh') AS hoan_thanh,
            (SELECT AVG(ph.diem_danh_gia) $join JOIN phan_hoi ph ON ph.ma_cuoc_hen = ch.ma_cuoc_hen WHERE kg.ma_giang_vien = :g5) AS diem_tb,
            (SELECT COUNT(*) $join JOIN phan_hoi ph ON ph.ma_cuoc_hen = ch.ma_cuoc_hen WHERE kg.ma_giang_vien = :g6) AS so_danh_gia";
        $st = $this->db->prepare($sql);
        $st->execute(['g1' => $id, 'g2' => $id, 'g3' => $id, 'g4' => $id, 'g5' => $id, 'g6' => $id, 'now1' => $now, 'today' => $today]);
        $row = $st->fetch();
        $row['diem_tb'] = $row['diem_tb'] !== null ? round((float) $row['diem_tb'], 1) : null;
        foreach (['slot_sap_toi', 'cho_duyet', 'hom_nay', 'hoan_thanh', 'so_danh_gia'] as $k) {
            $row[$k] = (int) $row[$k];
        }
        return $row;
    }

    /** Các cuộc hẹn sắp tới (đang chờ duyệt hoặc đã duyệt), gần nhất trước. */
    public function upcoming(int $id, int $limit = 5): array
    {
        $st = $this->db->prepare("SELECT ch.ma_cuoc_hen, ch.chu_de, ch.trang_thai, sv.ho_ten AS ten_sinh_vien, kg.bat_dau, kg.ket_thuc, kg.dia_diem
            FROM cuoc_hen ch
            JOIN khung_gio kg ON kg.ma_khung_gio = ch.ma_khung_gio
            JOIN nguoi_dung sv ON sv.ma_nguoi_dung = ch.ma_sinh_vien
            WHERE kg.ma_giang_vien = :id AND ch.trang_thai IN ('cho_duyet', 'da_duyet') AND kg.bat_dau >= :now
            ORDER BY kg.bat_dau ASC LIMIT :lim");
        $st->bindValue(':id', $id, PDO::PARAM_INT);
        $st->bindValue(':now', date('Y-m-d H:i:s'));
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }
}