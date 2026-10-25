<div class="container my-4" id="khung-gio-app"
     data-url-danh-sach="<?= e(url('giang-vien/khung-gio/danh-sach')) ?>"
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
                <input type="hidden" id="ma_khung_gio" name="ma_khung_gio" value="">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label" for="bat_dau">Bắt đầu</label>
                        <input type="datetime-local" class="form-control" id="bat_dau" name="bat_dau" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="ket_thuc">Kết thúc</label>
                        <input type="datetime-local" class="form-control" id="ket_thuc" name="ket_thuc" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="dia_diem">Địa điểm / Phòng</label>
                        <input type="text" class="form-control" id="dia_diem" name="dia_diem" maxlength="150" placeholder="P.306 nhà B / Online">
                    </div>
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
                    <tr><th>#</th><th>Bắt đầu</th><th>Kết thúc</th><th>Địa điểm</th><th>Trạng thái</th><th class="text-end">Thao tác</th></tr>
                </thead>
                <tbody id="bangSlot"></tbody>
            </table>
        </div>
    </div>
</div>