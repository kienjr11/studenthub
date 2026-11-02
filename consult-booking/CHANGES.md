# Các lỗi nghiêm trọng đã sửa (lần rà soát 2)
1. Chỗ trống khung giờ: `CuocHen::datVaoKhungGio` đếm cả yêu cầu chờ duyệt, trong khi danh sách/duyệt đếm chỉ đã duyệt/hoàn thành -> lệch quy tắc. Nay thống nhất: chỉ đã duyệt/hoàn thành chiếm chỗ; chặn khung giờ ẩn (`la_tu_nhap=1`) và giảng viên đã bị khóa.
2. `KhungGioModel`: danh sách khung giờ ẩn `la_tu_nhap`; `biTrung` bỏ qua khung ẩn không còn cuộc hẹn.
3. `hoanThanhTatCa`: một cuộc hẹn lỗi không còn làm văng 500 cả loạt.
4. `slotTrong`/`slotSapToi`: chỉ trả lịch của giảng viên còn hoạt động.
5. `luuPhanHoi`: chỉ ghi chú cho cuộc hẹn đã duyệt/hoàn thành, tối đa 1000 ký tự.
6. Admin: khóa tài khoản sẽ đóng lịch sắp diễn ra; đổi vai trò bị chặn khi còn lịch hiệu lực.
7. CSP: font Bootstrap Icons, bỏ `onclick`/`style` inline, ảnh preview dùng data URL; `display_errors` theo chế độ debug.
8. `schema.sql` bổ sung `ma_chuyen_mon`, `la_tu_nhap`; README sửa tên file SQL và mô tả đúng.
