<?php
declare(strict_types=1);

/** Bảng nguoi_dung (alias nd), liên kết don_vi (alias dv). */
final class NguoiDung extends MoHinh
{
    private const COT_CONG_KHAI = 'nd.ma_nguoi_dung, nd.ho_ten, nd.email, nd.vai_tro, nd.trang_thai, nd.ma_don_vi,
        nd.so_lan_dang_nhap_sai, nd.khoa_den, nd.dang_nhap_lan_cuoi, nd.ngay_tao, dv.ten_don_vi';

    /** Không trả mat_khau_bam để tránh vô tình đưa ra view. */
    public function find(int $id): ?array
    {
        $st = $this->db->prepare('SELECT ' . self::COT_CONG_KHAI . ', LEFT(SHA2(nd.mat_khau_bam, 256), 16) AS dau_van_tay_mk
            FROM nguoi_dung nd
            LEFT JOIN don_vi dv ON dv.ma_don_vi = nd.ma_don_vi WHERE nd.ma_nguoi_dung = :id');
        $st->execute(['id' => $id]);
        return $st->fetch() ?: null;
    }

    /** Dùng riêng cho đăng nhập: có mat_khau_bam. */
    public function findByEmail(string $email): ?array
    {
        $st = $this->db->prepare('SELECT * FROM nguoi_dung WHERE email = :email');
        $st->execute(['email' => $email]);
        return $st->fetch() ?: null;
    }

    public function emailExists(string $email): bool
    {
        $st = $this->db->prepare('SELECT 1 FROM nguoi_dung WHERE email = :email LIMIT 1');
        $st->execute(['email' => $email]);
        return (bool) $st->fetchColumn();
    }

    /** @param array{ho_ten:string,email:string,mat_khau_bam:string,vai_tro:string,trang_thai:string,ma_don_vi:?int} $d */
    public function create(array $d): int
    {
        $st = $this->db->prepare('INSERT INTO nguoi_dung (ho_ten, email, mat_khau_bam, vai_tro, trang_thai, ma_don_vi)
            VALUES (:ho_ten, :email, :mat_khau_bam, :vai_tro, :trang_thai, :ma_don_vi)');
        $st->execute([
            'ho_ten' => $d['ho_ten'], 'email' => $d['email'], 'mat_khau_bam' => $d['mat_khau_bam'],
            'vai_tro' => $d['vai_tro'], 'trang_thai' => $d['trang_thai'], 'ma_don_vi' => $d['ma_don_vi'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    /** Ghi nhận đăng nhập sai; đủ số lần thì khóa tạm và đặt lại bộ đếm. */
    public function recordFailedLogin(array $user, int $max, int $lockMinutes): void
    {
        $attempts = (int) $user['so_lan_dang_nhap_sai'] + 1;
        $lockUntil = null;
        if ($attempts >= $max) {
            $lockUntil = date('Y-m-d H:i:s', time() + $lockMinutes * 60);
            $attempts = 0;
        }
        $st = $this->db->prepare('UPDATE nguoi_dung SET so_lan_dang_nhap_sai = :a, khoa_den = :l WHERE ma_nguoi_dung = :id');
        $st->execute(['a' => $attempts, 'l' => $lockUntil, 'id' => $user['ma_nguoi_dung']]);
    }

    public function markLoggedIn(int $id): void
    {
        $st = $this->db->prepare('UPDATE nguoi_dung SET so_lan_dang_nhap_sai = 0, khoa_den = NULL, dang_nhap_lan_cuoi = :now
            WHERE ma_nguoi_dung = :id');
        $st->execute(['now' => date('Y-m-d H:i:s'), 'id' => $id]);
    }

    public function updatePasswordHash(int $id, string $hash): void
    {
        $st = $this->db->prepare('UPDATE nguoi_dung SET mat_khau_bam = :h WHERE ma_nguoi_dung = :id');
        $st->execute(['h' => $hash, 'id' => $id]);
    }

    // ---- Tài khoản của tôi ----

    public function capNhatHoTen(int $id, string $hoTen): void
    {
        $st = $this->db->prepare('UPDATE nguoi_dung SET ho_ten = :t WHERE ma_nguoi_dung = :id');
        $st->execute(['t' => $hoTen, 'id' => $id]);
    }

    public function layMatKhauBam(int $id): ?string
    {
        $st = $this->db->prepare('SELECT mat_khau_bam FROM nguoi_dung WHERE ma_nguoi_dung = :id');
        $st->execute(['id' => $id]);
        $bam = $st->fetchColumn();
        return $bam === false ? null : (string) $bam;
    }

    /** Lưu mật khẩu mới (đã băm) và xóa trạng thái khóa tạm do đăng nhập sai. */
    public function capNhatMatKhau(int $id, string $matKhauBam): void
    {
        $st = $this->db->prepare('UPDATE nguoi_dung SET mat_khau_bam = :h, so_lan_dang_nhap_sai = 0, khoa_den = NULL WHERE ma_nguoi_dung = :id');
        $st->execute(['h' => $matKhauBam, 'id' => $id]);
    }

    /** @return int[] mã các chuyên môn của giảng viên */
    public function layChuyenMon(int $maGiangVien): array
    {
        $st = $this->db->prepare('SELECT ma_chuyen_mon FROM giang_vien_chuyen_mon WHERE ma_giang_vien = :id');
        $st->execute(['id' => $maGiangVien]);
        return array_map('intval', $st->fetchAll(PDO::FETCH_COLUMN));
    }

    /** Thay toàn bộ chuyên môn của giảng viên bằng danh sách mới (trong một giao dịch). */
    public function datChuyenMon(int $maGiangVien, array $maChuyenMon): void
    {
        $this->db->beginTransaction();
        try {
            $xoa = $this->db->prepare('DELETE FROM giang_vien_chuyen_mon WHERE ma_giang_vien = :id');
            $xoa->execute(['id' => $maGiangVien]);
            $them = $this->db->prepare('INSERT INTO giang_vien_chuyen_mon (ma_giang_vien, ma_chuyen_mon) VALUES (:gv, :cm)');
            foreach ($maChuyenMon as $cm) {
                $them->execute(['gv' => $maGiangVien, 'cm' => (int) $cm]);
            }
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ---- Quản trị ----

    private function where(array $f, array &$p): string
    {
        $w = ['1 = 1'];
        if (($f['q'] ?? '') !== '') {
            // '|' làm ký tự escape để người dùng gõ % hoặc _ không bị hiểu thành wildcard
            $like = '%' . str_replace(['|', '%', '_'], ['||', '|%', '|_'], $f['q']) . '%';
            $w[] = "(nd.ho_ten LIKE :q1 ESCAPE '|' OR nd.email LIKE :q2 ESCAPE '|')";
            $p['q1'] = $p['q2'] = $like;
        }
        if (($f['role'] ?? '') !== '') {
            $w[] = 'nd.vai_tro = :role';
            $p['role'] = $f['role'];
        }
        if (($f['status'] ?? '') !== '') {
            $w[] = 'nd.trang_thai = :status';
            $p['status'] = $f['status'];
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

    public function search(array $filters, int $limit, int $offset): array
    {
        $p = [];
        $sql = 'SELECT ' . self::COT_CONG_KHAI . ' FROM nguoi_dung nd LEFT JOIN don_vi dv ON dv.ma_don_vi = nd.ma_don_vi
                WHERE ' . $this->where($filters, $p) . ' ORDER BY nd.ma_nguoi_dung DESC LIMIT :lim OFFSET :off';
        $st = $this->db->prepare($sql);
        foreach ($p as $k => $v) {
            $st->bindValue(':' . $k, $v);
        }
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->bindValue(':off', $offset, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public function setStatus(int $id, string $status): void
    {
        $st = $this->db->prepare('UPDATE nguoi_dung SET trang_thai = :s, so_lan_dang_nhap_sai = 0, khoa_den = NULL WHERE ma_nguoi_dung = :id');
        $st->execute(['s' => $status, 'id' => $id]);
    }

    public function setRoleAndDepartment(int $id, string $role, ?int $departmentId): void
    {
        $st = $this->db->prepare('UPDATE nguoi_dung SET vai_tro = :r, ma_don_vi = :d WHERE ma_nguoi_dung = :id');
        $st->execute(['r' => $role, 'd' => $departmentId, 'id' => $id]);
    }

    /** Giảng viên đã có khung giờ thì không được đổi vai trò (tránh mồ côi dữ liệu). */
    public function hasSlots(int $lecturerId): bool
    {
        $st = $this->db->prepare('SELECT 1 FROM khung_gio WHERE ma_giang_vien = :id LIMIT 1');
        $st->execute(['id' => $lecturerId]);
        return (bool) $st->fetchColumn();
    }

    /** Khóa kết quả bằng tiếng Việt: tong, sinh_vien, giang_vien, quan_tri, cho_duyet, bi_khoa, giang_vien_hoat_dong. */
    public function stats(): array
    {
        $row = $this->db->query("SELECT COUNT(*) AS tong,
                COALESCE(SUM(vai_tro = 'sinh_vien'), 0)  AS sinh_vien,
                COALESCE(SUM(vai_tro = 'giang_vien'), 0) AS giang_vien,
                COALESCE(SUM(vai_tro = 'quan_tri'), 0)    AS quan_tri,
                COALESCE(SUM(trang_thai = 'cho_duyet'), 0) AS cho_duyet,
                COALESCE(SUM(trang_thai = 'da_khoa'), 0)  AS bi_khoa,
                COALESCE(SUM(vai_tro = 'giang_vien' AND trang_thai = 'hoat_dong'), 0) AS giang_vien_hoat_dong
            FROM nguoi_dung")->fetch();
        return array_map('intval', $row);
    }

    public function pendingLecturers(int $limit): array
    {
        $st = $this->db->prepare("SELECT " . self::COT_CONG_KHAI . " FROM nguoi_dung nd LEFT JOIN don_vi dv ON dv.ma_don_vi = nd.ma_don_vi
            WHERE nd.vai_tro = 'giang_vien' AND nd.trang_thai = 'cho_duyet' ORDER BY nd.ngay_tao ASC LIMIT :lim");
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    /** Danh sách người dùng đang hoạt động theo vai trò (dùng cho ô chọn giảng viên khi đặt lịch). */
    public function layTheoVaiTro(string $vaiTro): array
    {
        $st = $this->db->prepare(
            "SELECT ma_nguoi_dung, ho_ten, email
               FROM nguoi_dung
              WHERE vai_tro = :vai_tro AND trang_thai = 'hoat_dong'
              ORDER BY ho_ten"
        );
        $st->execute([':vai_tro' => $vaiTro]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
}
