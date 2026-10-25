<div class="container mt-4 mb-5">
    <h2 class="mb-4">Quản lý Khung Giờ Tư Vấn (Slots)</h2>

    <!-- Form Thêm / Sửa Slot -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white" id="tieuDeForm">Thêm Khung Giờ Rảnh Mới</div>
        <div class="card-body">
            <form id="formKhungGio">
                <input type="hidden" id="ma_khung_gio" name="ma_khung_gio">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Thời gian bắt đầu:</label>
                        <input type="datetime-local" class="form-control" id="bat_dau" name="bat_dau" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Thời gian kết thúc:</label>
                        <input type="datetime-local" class="form-control" id="ket_thuc" name="ket_thuc" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Địa điểm / Phòng:</label>
                        <input type="text" class="form-control" id="dia_diem" name="dia_diem" placeholder="Phòng học / Online">
                    </div>
                </div>
                <button type="submit" class="btn btn-success" id="btnLuu">Lưu Khung Giờ</button>
                <button type="button" class="btn btn-secondary" id="btnHuy" style="display:none;">Hủy</button>
            </form>
        </div>
    </div>

    <!-- Danh sách Slot theo ngày -->
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center bg-light">
            <h5 class="mb-0">Danh sách Khung Giờ</h5>
            <div class="w-25">
                <input type="date" id="ngayLoc" class="form-control form-control-sm" value="<?php echo date('Y-m-d'); ?>">
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>STT</th>
                        <th>Bắt đầu</th>
                        <th>Kết thúc</th>
                        <th>Địa điểm</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody id="bangDanhSachSlot">
                    <!-- Dữ liệu được nạp tự động bằng Fetch API -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
(function() {
    const maGiangVien = "<?php echo isset($data['maGiangVien']) ? $data['maGiangVien'] : ''; ?>";
    
    // 1. Tải danh sách tự động dùng Fetch API
    function taiDanhSachSlot() {
        const ngayLoc = document.getElementById("ngayLoc");
        const bangDanhSachSlot = document.getElementById("bangDanhSachSlot");
        if (!ngayLoc || !bangDanhSachSlot) return;

        const ngay = ngayLoc.value;
        fetch(`index.php?r=giang-vien/khung-gio/api-danh-sach&ma_giang_vien=${maGiangVien}&ngay=${ngay}`)
            .then(response => response.json())
            .then(res => {
                if (res && res.success) {
                    let html = "";
                    if (!res.data || res.data.length === 0) {
                        html = `<tr><td colspan="5" class="text-center text-muted">Không có khung giờ rảnh nào trong ngày này.</td></tr>`;
                    } else {
                        res.data.forEach((item, index) => {
                            let batDauFormatted = item.bat_dau ? item.bat_dau.replace(' ', 'T') : '';
                            let ketThucFormatted = item.ket_thuc ? item.ket_thuc.replace(' ', 'T') : '';

                            html += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${item.bat_dau}</td>
                                    <td>${item.ket_thuc}</td>
                                    <td>${item.dia_dim ? item.dia_dim : 'Chưa cập nhật'}</td>
                                    <td>
                                        <button type="button" class="btn btn-warning btn-sm btn-sua" 
                                            data-id="${item.ma_khung_gio}" 
                                            data-batdau="${batDauFormatted}" 
                                            data-ketthuc="${ketThucFormatted}" 
                                            data-diadiem="${item.dia_dim || ''}">Sửa</button>
                                        <button type="button" class="btn btn-danger btn-sm btn-xoa" data-id="${item.ma_khung_gio}">Xóa</button>
                                    </td>
                                </tr>
                            `;
                        });
                    }
                    bangDanhSachSlot.innerHTML = html;
                }
            })
            .catch(err => console.error("Lỗi tải dữ liệu:", err));
    }

    // Gắn sự kiện khi DOM đã sẵn sàng hoặc thực thi trực tiếp
    document.addEventListener("DOMContentLoaded", initApp);
    // Dự phòng trường hợp DOM đã load xong từ trước
    if (document.readyState === "interactive" || document.readyState === "complete") {
        initApp();
    }

    function initApp() {
        const ngayLoc = document.getElementById("ngayLoc");
        const formKhungGio = document.getElementById("formKhungGio");
        const bangDanhSachSlot = document.getElementById("bangDanhSachSlot");
        const tieuDeForm = document.getElementById("tieuDeForm");
        const btnLuu = document.getElementById("btnLuu");
        const btnHuy = document.getElementById("btnHuy");

        if (ngayLoc) {
            ngayLoc.addEventListener("change", taiDanhSachSlot);
        }

        // 2. Bắt sự kiện Submit form - CHẶN TUYỆT ĐỐI NHẢY TRANG
        if (formKhungGio) {
            formKhungGio.onsubmit = function(e) {
                e.preventDefault(); // Ngăn chặn hoàn toàn việc load lại trang

                const maKhungGioVal = document.getElementById("ma_khung_gio").value;
                const actionUrl = maKhungGioVal ? "index.php?r=giang-vien/khung-gio/api-sua" : "index.php?r=giang-vien/khung-gio/api-them";
                const formData = new FormData(formKhungGio);

                fetch(actionUrl, {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(res => {
                    if (res) {
                        alert(res.message);
                        if (res.success) {
                            formKhungGio.reset();
                            document.getElementById("ma_khung_gio").value = "";
                            if (tieuDeForm) tieuDeForm.innerText = "Thêm Khung Giờ Rảnh Mới";
                            if (btnLuu) btnLuu.innerText = "Lưu Khung Giờ";
                            if (btnHuy) btnHuy.style.display = "none";
                            taiDanhSachSlot();
                        }
                    }
                })
                .catch(err => {
                    console.error("Lỗi:", err);
                    alert("Có lỗi xảy ra khi kết nối máy chủ!");
                });

                return false;
            };
        }

        // 3. Xử lý Sửa / Xóa trong bảng
        if (bangDanhSachSlot) {
            bangDanhSachSlot.addEventListener("click", function(e) {
                if (e.target.classList.contains("btn-xoa")) {
                    if (confirm("Bạn có chắc chắn muốn xóa khung giờ này không?")) {
                        const id = e.target.getAttribute("data-id");
                        const formData = new URLSearchParams();
                        formData.append("ma_khung_gio", id);

                        fetch("index.php?r=giang-vien/khung-gio/api-xoa", {
                            method: "POST",
                            body: formData
                        })
                        .then(response => response.json())
                        .then(res => {
                            if (res) {
                                alert(res.message);
                                if (res.success) taiDanhSachSlot();
                            }
                        });
                    }
                }

                if (e.target.classList.contains("btn-sua")) {
                    document.getElementById("ma_khung_gio").value = e.target.getAttribute("data-id");
                    document.getElementById("bat_dau").value = e.target.getAttribute("data-batdau");
                    document.getElementById("ket_thuc").value = e.target.getAttribute("data-ketthuc");
                    document.getElementById("dia_diem").value = e.target.getAttribute("data-diadiem");

                    if (tieuDeForm) tieuDeForm.innerText = "Cập Nhật Khung Giờ Tư Vấn";
                    if (btnLuu) btnLuu.innerText = "Cập Nhật";
                    if (btnHuy) btnHuy.style.display = "inline-block";
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            });
        }

        if (btnHuy) {
            btnHuy.addEventListener("click", function() {
                formKhungGio.reset();
                document.getElementById("ma_khung_gio").value = "";
                if (tieuDeForm) tieuDeForm.innerText = "Thêm Khung Giờ Rảnh Mới";
                if (btnLuu) btnLuu.innerText = "Lưu Khung Giờ";
                btnHuy.style.display = "none";
            });
        }

        // Tải danh sách lần đầu
        taiDanhSachSlot();
    }
})();
</script>