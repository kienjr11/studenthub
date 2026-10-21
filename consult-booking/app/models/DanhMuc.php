<?php
declare(strict_types=1);

/**
 * Danh mục dùng chung cho 2 bảng có cùng hình dạng: don_vi (đơn vị) và chuyen_mon (chuyên môn).
 * Tên bảng/cột chỉ lấy từ danh sách trắng META nên an toàn khi nối vào câu SQL.
 * Kết quả trả về được chuẩn hóa thành các khóa chung: ma, ten, mo_ta, so_dang_dung.
 */
final class DanhMuc extends MoHinh
{
    public const LABELS = ['departments' => 'Đơn vị', 'specialties' => 'Chuyên môn'];

    private const META = [
        'departments' => ['bang' => 'don_vi',     'ma' => 'ma_don_vi',     'ten' => 'ten_don_vi'],
        'specialties' => ['bang' => 'chuyen_mon', 'ma' => 'ma_chuyen_mon', 'ten' => 'ten_chuyen_mon'],
    ];

    /** Đếm số bản ghi đang dùng mục đó (đơn vị: số người dùng; chuyên môn: số giảng viên). */
    private const USAGE_SQL = [
        'departments' => 'SELECT t.ma_don_vi AS ma, t.ten_don_vi AS ten, t.mo_ta, COUNT(x.ma_nguoi_dung) AS so_dang_dung
                          FROM don_vi t LEFT JOIN nguoi_dung x ON x.ma_don_vi = t.ma_don_vi
                          GROUP BY t.ma_don_vi, t.ten_don_vi, t.mo_ta ORDER BY t.ten_don_vi',
        'specialties' => 'SELECT t.ma_chuyen_mon AS ma, t.ten_chuyen_mon AS ten, t.mo_ta, COUNT(x.ma_giang_vien) AS so_dang_dung
                          FROM chuyen_mon t LEFT JOIN giang_vien_chuyen_mon x ON x.ma_chuyen_mon = t.ma_chuyen_mon
                          GROUP BY t.ma_chuyen_mon, t.ten_chuyen_mon, t.mo_ta ORDER BY t.ten_chuyen_mon',
    ];

    private string $bang;
    private string $ma;
    private string $ten;

    public function __construct(private string $type)
    {
        if (!isset(self::META[$type])) {
            throw new InvalidArgumentException('Loại danh mục không hợp lệ.');
        }
        ['bang' => $this->bang, 'ma' => $this->ma, 'ten' => $this->ten] = self::META[$type];
        parent::__construct();
    }

    public static function isValidType(string $type): bool
    {
        return isset(self::META[$type]);
    }

    public function all(): array
    {
        return $this->db->query(self::USAGE_SQL[$this->type])->fetchAll();
    }

    public function count(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM {$this->bang}")->fetchColumn();
    }

    public function find(int $id): ?array
    {
        $st = $this->db->prepare("SELECT {$this->ma} AS ma, {$this->ten} AS ten, mo_ta FROM {$this->bang} WHERE {$this->ma} = :id");
        $st->execute(['id' => $id]);
        return $st->fetch() ?: null;
    }

    public function nameExists(string $name, ?int $exceptId = null): bool
    {
        $st = $this->db->prepare("SELECT 1 FROM {$this->bang} WHERE {$this->ten} = :n AND {$this->ma} <> :id LIMIT 1");
        $st->execute(['n' => $name, 'id' => $exceptId ?? 0]);
        return (bool) $st->fetchColumn();
    }

    public function create(string $name, ?string $description): void
    {
        $st = $this->db->prepare("INSERT INTO {$this->bang} ({$this->ten}, mo_ta) VALUES (:n, :d)");
        $st->execute(['n' => $name, 'd' => $description]);
    }

    public function update(int $id, string $name, ?string $description): void
    {
        $st = $this->db->prepare("UPDATE {$this->bang} SET {$this->ten} = :n, mo_ta = :d WHERE {$this->ma} = :id");
        $st->execute(['n' => $name, 'd' => $description, 'id' => $id]);
    }

    /** @return bool false nếu mục đang được sử dụng (ràng buộc khóa ngoại RESTRICT chặn xóa). */
    public function delete(int $id): bool
    {
        try {
            $st = $this->db->prepare("DELETE FROM {$this->bang} WHERE {$this->ma} = :id");
            $st->execute(['id' => $id]);
            return true;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                return false;
            }
            throw $e;
        }
    }
}
