<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card bg-dark text-white border-secondary shadow">
                <div class="card-header bg-primary text-white py-3">
                    <h4 class="card-title mb-0 font-weight-bold">
                        <i class="bi bi-calendar-plus me-2"></i>Đặt Lịch Hẹn Tư Vấn Giảng Viên
                    </h4>
                </div>
                <div class="card-body p-4">
                    <p class="text-white-50 mb-4">
                        Vui lòng điền đầy đủ các thông tin chi tiết dưới đây để gửi yêu cầu đặt lịch tư vấn đến giảng viên.
                    </p>

                    <form action="<?= e(url('sinh-vien/dat-lich')) ?>" method="POST" class="booking-form">
                        <?= csrf_field() ?>

                        <!-- Chọn Giảng viên (Hiển thị kèm Chuyên môn và Khoa) -->
                        <div class="mb-3">
                            <label for="giang_vien_id" class="form-label text-white fw-semibold">
                                Giảng viên hướng dẫn / tư vấn <span class="text-danger">*</span>
                            </label>
                            <select id="giang_vien_id" name="giang_vien_id" class="form-select bg-dark text-white border-secondary" required>
                                <option value="">-- Chọn giảng viên --</option>
                                <?php if (!empty($danhSachGiangVien)): ?>
                                    <?php foreach ($danhSachGiangVien as $gv): ?>
                                        <?php
                                            $nhanHienThi = e($gv['ho_ten']);
                                            if (!empty($gv['ten_khoa'])) {
                                                $nhanHienThi .= ' - ' . e($gv['ten_khoa']);
                                            }
                                        ?>
                                        <option value="<?= e($gv['ma_nguoi_dung']) ?>" 
                                                data-chuyenmon="<?= e($gv['chuyen_mon'] ?? '') ?>"
                                                <?= (int) ($cu['giang_vien_id'] ?? 0) === (int) $gv['ma_nguoi_dung'] ? ' selected' : '' ?>>
                                            <?= $nhanHienThi ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Chọn Môn học / Chuyên đề (Hiển thị động khi chọn GV có nhiều môn) -->
                        <div class="mb-3" id="khoi_chon_mon">
                            <label for="mon_hoc" class="form-label text-white fw-semibold">
                                Môn học / Chuyên đề tư vấn <span class="text-danger">*</span>
                            </label>
                            <select id="mon_hoc" name="mon_hoc" class="form-select bg-dark text-white border-secondary">
                                <option value="">-- Chọn môn học --</option>
                            </select>
                        </div>

                        <!-- Ngày & Thời gian -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="ngay_hen" class="form-label text-white fw-semibold">
                                    Ngày hẹn <span class="text-danger">*</span>
                                </label>
                                <input type="date" id="ngay_hen" name="ngay_hen" class="form-control bg-dark text-white border-secondary" min="<?= date('Y-m-d') ?>" value="<?= e($cu['ngay_hen'] ?? '') ?>" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="gio_bat_dau" class="form-label text-white fw-semibold">
                                    Giờ bắt đầu <span class="text-danger">*</span>
                                </label>
                                <input type="time" id="gio_bat_dau" name="gio_bat_dau" class="form-control bg-dark text-white border-secondary" value="<?= e($cu['gio_bat_dau'] ?? '') ?>" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="gio_ket_thuc" class="form-label text-white fw-semibold">
                                    Giờ kết thúc <span class="text-danger">*</span>
                                </label>
                                <input type="time" id="gio_ket_thuc" name="gio_ket_thuc" class="form-control bg-dark text-white border-secondary" value="<?= e($cu['gio_ket_thuc'] ?? '') ?>" required>
                            </div>
                        </div>

                        <!-- Tiêu đề cuộc hẹn -->
                        <div class="mb-3">
                            <label for="chu_de" class="form-label text-white fw-semibold">
                                Tiêu đề cuộc hẹn <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="chu_de" name="chu_de" class="form-control bg-dark text-white border-secondary" placeholder="Ví dụ: Hướng dẫn Đồ án tốt nghiệp, Hỏi bài môn C#..." maxlength="255" value="<?= e($cu['chu_de'] ?? '') ?>" required>
                        </div>

                        <!-- Thao tác nút bấm -->
                        <div class="d-flex justify-content-between align-items-center pt-2">
                            <a href="<?= e(url('sinh-vien/phan-hoi')) ?>" class="btn btn-outline-secondary px-4">
                                Quay lại
                            </a>
                            <button type="submit" class="btn btn-primary px-4 fw-bold">
                                Xác nhận đặt lịch
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>