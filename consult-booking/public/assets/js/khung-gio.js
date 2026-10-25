/* Trang "Khung giờ tư vấn" của giảng viên. JS thuần, không inline script (tương thích CSP). */
(function () {
  "use strict";

  var app = document.getElementById("khung-gio-app");
  if (!app) return;

  var URL_DS = app.dataset.urlDanhSach;
  var URL_THEM = app.dataset.urlThem;
  var URL_SUA = app.dataset.urlSua;
  var URL_XOA = app.dataset.urlXoa;

  var form = document.getElementById("formKhungGio");
  var ngayLoc = document.getElementById("ngayLoc");
  var tbody = document.getElementById("bangSlot");
  var tieuDe = document.getElementById("tieuDeForm");
  var btnLuu = document.getElementById("btnLuu");
  var btnHuy = document.getElementById("btnHuy");
  var thongBao = document.getElementById("thongBao");
  var f = {
    id: document.getElementById("ma_khung_gio"),
    bd: document.getElementById("bat_dau"),
    kt: document.getElementById("ket_thuc"),
    dd: document.getElementById("dia_diem"),
  };

  function bao(ok, msg) {
    thongBao.textContent = msg;
    thongBao.className = "alert " + (ok ? "alert-success" : "alert-danger");
  }

  // Gọi API: GET lấy JSON; POST gửi kèm header X-CSRF-Token (window.csrfHeaders từ app.js).
  function goi(url, body) {
    var opt = { credentials: "same-origin", headers: { Accept: "application/json" } };
    if (body) {
      opt.method = "POST";
      opt.body = body;
      opt.headers = window.csrfHeaders({ Accept: "application/json" });
    }
    return fetch(url, opt).then(function (res) {
      return res.json().catch(function () {
        return { success: false, message: "Phản hồi không hợp lệ từ máy chủ." };
      });
    });
  }

  // "2027-02-04 12:12:00" -> "04/02/2027 12:12"
  function hienThi(s) {
    var m = /^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2})/.exec(s || "");
    return m ? m[3] + "/" + m[2] + "/" + m[1] + " " + m[4] + ":" + m[5] : s;
  }
  // "2027-02-04 12:12:00" -> "2027-02-04T12:12" (cho input datetime-local)
  function chonInput(s) {
    return (s || "").replace(" ", "T").slice(0, 16);
  }

  function o(tag, text, cls) {
    var el = document.createElement(tag);
    if (text !== undefined) el.textContent = text; // textContent: chống XSS với dữ liệu người dùng
    if (cls) el.className = cls;
    return el;
  }

  function veBang(ds) {
    tbody.replaceChildren();
    if (!ds.length) {
      var tr = o("tr");
      var td = o("td", "Chưa có khung giờ nào trong ngày này.", "text-center text-muted py-4");
      td.colSpan = 6;
      tr.appendChild(td);
      tbody.appendChild(tr);
      return;
    }
    ds.forEach(function (it, i) {
      var tr = o("tr");
      tr.appendChild(o("td", String(i + 1)));
      tr.appendChild(o("td", hienThi(it.bat_dau)));
      tr.appendChild(o("td", hienThi(it.ket_thuc)));
      tr.appendChild(o("td", it.dia_diem || "Chưa cập nhật"));

      var tt = o("td");
      tt.appendChild(it.da_dat ? o("span", "Đã có người đặt", "badge text-bg-warning") : o("span", "Còn trống", "badge text-bg-success"));
      tr.appendChild(tt);

      var act = o("td", undefined, "text-end");
      var sua = o("button", "Sửa", "btn btn-outline-primary btn-sm me-1");
      sua.type = "button";
      sua.disabled = it.da_dat;
      sua.addEventListener("click", function () { batDauSua(it); });
      var xoa = o("button", "Xóa", "btn btn-outline-danger btn-sm");
      xoa.type = "button";
      xoa.addEventListener("click", function () { xoaSlot(it); });
      act.appendChild(sua);
      act.appendChild(xoa);
      tr.appendChild(act);
      tbody.appendChild(tr);
    });
  }

  var daiTai = 0;
  function taiDanhSach() {
    var lan = ++daiTai; // bỏ qua kết quả cũ nếu người dùng đổi ngày liên tục
    goi(URL_DS + "&ngay=" + encodeURIComponent(ngayLoc.value)).then(function (res) {
      if (lan !== daiTai) return;
      if (res.success) veBang(res.data);
      else bao(false, res.message || "Không tải được danh sách.");
    }).catch(function () { bao(false, "Lỗi kết nối máy chủ."); });
  }

  function datLaiForm() {
    form.reset();
    f.id.value = "";
    tieuDe.textContent = "Thêm khung giờ rảnh mới";
    btnLuu.textContent = "Lưu khung giờ";
    btnHuy.classList.add("d-none");
  }

  function batDauSua(it) {
    f.id.value = it.ma_khung_gio;
    f.bd.value = chonInput(it.bat_dau);
    f.kt.value = chonInput(it.ket_thuc);
    f.dd.value = it.dia_diem || "";
    tieuDe.textContent = "Cập nhật khung giờ";
    btnLuu.textContent = "Cập nhật";
    btnHuy.classList.remove("d-none");
    window.scrollTo({ top: 0, behavior: "smooth" });
  }

  function xoaSlot(it) {
    if (!window.confirm("Xóa khung giờ " + hienThi(it.bat_dau) + "?")) return;
    goi(URL_XOA, new URLSearchParams({ ma_khung_gio: it.ma_khung_gio })).then(function (res) {
      bao(!!res.success, res.message || "");
      if (res.success) taiDanhSach();
    });
  }

  form.addEventListener("submit", function (ev) {
    ev.preventDefault(); // không reload trang
    var sua = f.id.value !== "";
    btnLuu.disabled = true;
    goi(sua ? URL_SUA : URL_THEM, new URLSearchParams(new FormData(form)))
      .then(function (res) {
        bao(!!res.success, res.message || "");
        if (res.success) {
          // Chuyển bảng sang đúng ngày của slot vừa lưu để người dùng thấy ngay kết quả
          if (f.bd.value) ngayLoc.value = f.bd.value.slice(0, 10);
          datLaiForm();
          taiDanhSach();
        }
      })
      .catch(function () { bao(false, "Lỗi kết nối máy chủ."); })
      .then(function () { btnLuu.disabled = false; });
  });

  btnHuy.addEventListener("click", datLaiForm);
  ngayLoc.addEventListener("change", taiDanhSach);
  taiDanhSach();
})();