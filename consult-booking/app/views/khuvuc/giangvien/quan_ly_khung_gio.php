<div class="container my-4" id="khung-gio-app"
     data-bay-gio="<?= e(date('Y-m-d\TH:i:s')) ?>"
     data-url-danh-sach="<?= e(url('giang-vien/khung-gio/danh-sach')) ?>"
     data-url-sinh-vien="<?= e(url('giang-vien/khung-gio/sinh-vien')) ?>"
     data-url-them="<?= e(url('giang-vien/khung-gio/them')) ?>"
     data-url-sua="<?= e(url('giang-vien/khung-gio/sua')) ?>"
     data-url-xoa="<?= e(url('giang-vien/khung-gio/xoa')) ?>">

    <h2 class="mb-4">Quản lý khung giờ tư vấn</h2>

    <div id="thongBao" class="alert d-none" role="alert"></div>

    <!-- Form Thêm / Sửa -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header fw-semibold" id="tieuDeForm">Thêm khung giờ rảnh mới</div>
        <div class="card-body">
            <form id="formKhungGio" novalidate>
                <input type="hidden" id="ma_khung_gio" value="">
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label" for="ngay">Ngày</label>
                        <input type="date" class="form-control" id="ngay" required>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label" for="gio_bat_dau">Từ</label>
                        <input type="time" class="form-control" id="gio_bat_dau" step="300" required>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label" for="gio_ket_thuc">Đến</label>
                        <input type="time" class="form-control" id="gio_ket_thuc" step="300" required>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label" for="suc_chua">Số sinh viên tối đa</label>
                        <input type="number" class="form-control" id="suc_chua" min="1" max="50" step="1" value="1" required>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label" for="dia_diem">Địa điểm / Phòng</label>
                        <input type="text" class="form-control" id="dia_diem" maxlength="150" placeholder="P.306 nhà B / Online">
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <span class="small text-secondary">Thời lượng nhanh:</span>
                    <button type="button" class="btn btn-outline-secondary btn-sm btn-phut" data-phut="30">30 phút</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm btn-phut" data-phut="60">1 giờ</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm btn-phut" data-phut="90">1 giờ 30</button>
                </div>

                <div id="ghiChuKhoa" class="form-text text-warning mb-3 d-none">
                    Khung giờ này đã có sinh viên đăng ký nên không đổi được ngày giờ. Bạn chỉ sửa được địa điểm và số lượng tối đa (không nhỏ hơn số sinh viên đã đăng ký).
                </div>

                <button type="submit" class="btn btn-primary" id="btnLuu">Lưu khung giờ</button>
                <button type="button" class="btn btn-outline-secondary d-none" id="btnHuy">Hủy sửa</button>
            </form>
        </div>
    </div>

    <!-- Danh sách theo ngày (tải bằng Fetch API, không reload trang) -->
    <div class="card shadow-sm">
        <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <h5 class="mb-0">Danh sách khung giờ</h5>
            <div class="d-flex align-items-center gap-2">
                <label for="ngayLoc" class="mb-0 small">Ngày:</label>
                <input type="date" id="ngayLoc" class="form-control form-control-sm" value="<?= e(date('Y-m-d')) ?>">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th><th>Bắt đầu</th><th>Kết thúc</th><th>Địa điểm</th>
                        <th class="text-center">Sinh viên</th><th>Trạng thái</th><th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="bangSlot"></tbody>
            </table>
        </div>
    </div>

    <!-- Modal: sinh viên đã đăng ký khung giờ (nút con mắt) -->
    <div class="modal fade" id="modalSinhVien" tabindex="-1" aria-labelledby="modalSinhVienTieuDe" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="modalSinhVienTieuDe">Sinh viên đã đăng ký</h5>
                        <div class="small text-secondary" id="modalSinhVienPhu"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr><th>#</th><th>Họ tên</th><th>Email</th><th>Chủ đề</th><th>Trạng thái</th><th>Đăng ký lúc</th></tr>
                            </thead>
                            <tbody id="bangSinhVien"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <span class="small text-secondary" id="modalSinhVienDem"></span>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
</div>