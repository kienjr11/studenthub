<?php
declare(strict_types=1);

/** Quản lý danh mục đơn vị / chuyên môn. */
final class CatalogController extends Controller
{
    public function __construct()
    {
        Auth::requireRole('admin');
    }

    public function index(): void
    {
        $type = $this->type((string) ($_GET['type'] ?? 'departments'));
        $this->view('admin/catalog', [
            'title' => 'Danh mục ' . mb_strtolower(Catalog::LABELS[$type]),
            'type'  => $type,
            'items' => (new Catalog($type))->all(),
        ]);
    }

    /** id = 0 -> thêm mới; id > 0 -> cập nhật. */
    public function save(): void
    {
        $type = $this->type($this->input('type'));
        $catalog = new Catalog($type);
        $id = (int) ($_POST['id'] ?? 0);
        $label = Catalog::LABELS[$type];
        $lower = mb_strtolower($label);

        $v = new Validator($_POST);
        $v->required('name', "Tên $lower")->length('name', "Tên $lower", 2, 100)->length('description', 'Mô tả', 0, 255);
        if (!$v->fails() && $catalog->nameExists($v->value('name'), $id ?: null)) {
            $v->add('name', "Tên $lower đã tồn tại.");
        }
        if (!$v->fails() && $id > 0 && $catalog->find($id) === null) {
            abort(404, 'Không tìm thấy mục cần sửa.');
        }
        if ($v->fails()) {
            flash('danger', implode(' ', $v->errors()));
            redirect('admin/catalog', ['type' => $type]);
        }

        $description = $v->value('description') ?: null;
        if ($id > 0) {
            $catalog->update($id, $v->value('name'), $description);
            flash('success', 'Đã cập nhật ' . mb_strtolower($label) . '.');
        } else {
            $catalog->create($v->value('name'), $description);
            flash('success', 'Đã thêm ' . mb_strtolower($label) . ' mới.');
        }
        redirect('admin/catalog', ['type' => $type]);
    }

    public function delete(): void
    {
        $type = $this->type($this->input('type'));
        if (!(new Catalog($type))->delete((int) ($_POST['id'] ?? 0))) {
            flash('danger', 'Không thể xóa vì mục này đang được sử dụng. Hãy chuyển các bản ghi liên quan sang mục khác trước.');
        } else {
            flash('success', 'Đã xóa ' . mb_strtolower(Catalog::LABELS[$type]) . '.');
        }
        redirect('admin/catalog', ['type' => $type]);
    }

    private function type(string $type): string
    {
        if (!Catalog::isValidType($type)) {
            abort(404, 'Loại danh mục không tồn tại.');
        }
        return $type;
    }
}
