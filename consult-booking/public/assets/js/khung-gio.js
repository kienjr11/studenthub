/* Trang "Khung giờ tư vấn" của giảng viên. JS thuần, không inline script (tương thích CSP). */
/* PHIEN-BAN: v2.1 — gom ngay/gio, so sinh vien toi da, nut con mat, thoi luong nhanh */
(function () {
  "use strict";
 
  var app = document.getElementById("khung-gio-app");
  if (!app) return;
 
  var URL_DS = app.dataset.urlDanhSach;
  var URL_SV = app.dataset.urlSinhVien;
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
  var ghiChuKhoa = document.getElementById("ghiChuKhoa");
  var btnPhut = document.querySelectorAll(".btn-phut");
  // ---------- Ô chọn giờ 24h (00–23 : phút) ----------
  // <input type="time"> hiển thị AM/PM theo hệ điều hành và không ép được -> thay bằng 2 ô chọn Giờ / Phút.
  // Trả về đối tượng có value ("HH:MM" hoặc ""), min, disabled, focus, addEventListener như một input.
  function gioSelect(input) {
    var wrap = document.createElement("div");
    wrap.className = "d-flex align-items-center gap-2";
    var h = document.createElement("select");
    var m = document.createElement("select");
    h.className = m.className = "form-select";
    h.id = input.id;
    h.required = m.required = input.required;
    h.setAttribute("aria-label", "Giờ");
    m.setAttribute("aria-label", "Phút");
 
    function opt(sel, val, text) {
      var e = document.createElement("option");
      e.value = val;
      e.textContent = text;
      sel.appendChild(e);
      return e;
    }
    opt(h, "", "--");
    opt(m, "", "--");
    for (var i = 0; i < 24; i++) opt(h, p2(i), p2(i));
    for (var j = 0; j < 60; j += 5) opt(m, p2(j), p2(j));
    var sep = document.createElement("span");
    sep.textContent = ":";
    wrap.appendChild(h);
    wrap.appendChild(sep);
    wrap.appendChild(m);
    input.replaceWith(wrap);
 
    var minStr = "";
    function ganMin() {                                   // làm mờ (disable) các giờ/phút đã qua
      var mh = minStr ? +minStr.slice(0, 2) : -1;
      var mm = minStr ? +minStr.slice(3, 5) : -1;
      Array.prototype.forEach.call(h.options, function (e) { e.disabled = e.value !== "" && +e.value < mh; });
      Array.prototype.forEach.call(m.options, function (e) {
        e.disabled = e.value !== "" && minStr !== "" && h.value !== "" && +h.value === mh && +e.value < mm;
      });
    }
    h.addEventListener("change", function () {
      if (h.value !== "" && m.value === "") m.value = "00";   // chọn giờ xong là có giá trị đầy đủ
      ganMin();
    });
 
    return {
      get value() { return h.value !== "" && m.value !== "" ? h.value + ":" + m.value : ""; },
      set value(v) {
        if (!v) { h.value = ""; m.value = ""; ganMin(); return; }
        var x = v.split(":");
        h.value = x[0];
        if (!Array.prototype.some.call(m.options, function (e) { return e.value === x[1]; })) {
          // phút lẻ (vd 11:47 tạo từ trước) vẫn hiển thị đúng khi sửa
          var e = opt(m, x[1], x[1]);
          var cho = Array.prototype.find.call(m.options, function (z) { return z.value !== "" && +z.value > +x[1]; });
          if (cho) m.insertBefore(e, cho);
        }
        m.value = x[1];
        ganMin();
      },
      get min() { return minStr; },
      set min(v) { minStr = v || ""; ganMin(); },
      get disabled() { return h.disabled; },
      set disabled(v) { h.disabled = m.disabled = !!v; },
      focus: function () { h.focus(); },
      addEventListener: function (ev, fn) { h.addEventListener(ev, fn); m.addEventListener(ev, fn); },
    };
  }
 
  var f = {
    id: document.getElementById("ma_khung_gio"),
    ngay: document.getElementById("ngay"),
    bd: gioSelect(document.getElementById("gio_bat_dau")),
    kt: gioSelect(document.getElementById("gio_ket_thuc")),
    sl: document.getElementById("suc_chua"),
    dd: document.getElementById("dia_diem"),
  };
 
  // ---------- Đồng hồ: lấy theo giờ máy chủ (không tin đồng hồ máy người dùng) ----------
  var m = /^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})$/.exec(app.dataset.bayGio || "");
  var lech = m ? new Date(+m[1], +m[2] - 1, +m[3], +m[4], +m[5], +m[6]).getTime() - Date.now() : 0;
  function bayGio() { return new Date(Date.now() + lech); }
  function p2(n) { return (n < 10 ? "0" : "") + n; }
  function strNgay(d) { return d.getFullYear() + "-" + p2(d.getMonth() + 1) + "-" + p2(d.getDate()); }
  function strGio(d) { return p2(d.getHours()) + ":" + p2(d.getMinutes()); }
 
  // Mốc sớm nhất được phép chọn: bây giờ + 1 phút, làm tròn lên bội số 5 phút.
  function moc() {
    var d = new Date(bayGio().getTime() + 60000);
    d.setMinutes(Math.ceil(d.getMinutes() / 5) * 5, 0, 0);
    return d;
  }
 
  // "09:30" + 60 phút -> "10:30"; vượt 23:59 thì trả về "23:59"
  function congPhut(hhmm, phut) {
    var p = hhmm.split(":");
    var t = +p[0] * 60 + +p[1] + phut;
    if (t > 23 * 60 + 59) t = 23 * 60 + 59;
    return p2(Math.floor(t / 60)) + ":" + p2(t % 60);
  }
 
  function bao(ok, msg) {
    thongBao.textContent = msg;
    thongBao.className = "alert " + (ok ? "alert-success" : "alert-danger");
  }
 
  // ---------- Làm mờ / khóa quá khứ trên form ----------
  var daKhoaGio = false; // true khi đang sửa slot đã có sinh viên đăng ký (không đổi ngày giờ)
 
  function capNhatGioiHan() {
    if (daKhoaGio) return;
    var mo = moc();
    var ngayMin = strNgay(mo);
    f.ngay.min = ngayMin;                                  // ngày quá khứ bị mờ, không bấm được trong lịch
    if (!f.ngay.value || f.ngay.value < ngayMin) f.ngay.value = ngayMin;
 
    var homNay = f.ngay.value === ngayMin;
    f.bd.min = homNay ? strGio(mo) : "";                   // hôm nay: không cho chọn giờ đã qua
    if (homNay && f.bd.value && f.bd.value < f.bd.min) f.bd.value = f.bd.min;
 
    f.kt.min = f.bd.value || "";
    if (f.kt.value && f.bd.value && f.kt.value <= f.bd.value) f.kt.value = "";
  }
 
  function khoaGio(khoa) {
    daKhoaGio = khoa;
    f.ngay.disabled = f.bd.disabled = f.kt.disabled = khoa;
    btnPhut.forEach(function (b) { b.disabled = khoa; });
    ghiChuKhoa.classList.toggle("d-none", !khoa);
  }
 
  f.ngay.addEventListener("change", function () {
    var ngayMin = strNgay(moc());
    if (f.ngay.value && f.ngay.value < ngayMin) {
      bao(false, "Không thể chọn ngày đã qua. Đã chuyển về ngày gần nhất có thể đặt.");
      f.ngay.value = ngayMin;
    }
    capNhatGioiHan();
    if (f.ngay.value && !f.bd.value && !f.bd.disabled) f.bd.focus();   // bớt một lần bấm chuột
  });
 
  f.bd.addEventListener("change", function () {
    capNhatGioiHan();
    // Tự gợi ý giờ kết thúc = giờ bắt đầu + 1 giờ
    if (f.bd.value && (!f.kt.value || f.kt.value <= f.bd.value)) {
      f.kt.value = congPhut(f.bd.value, 60);
    }
    f.kt.min = f.bd.value || "";
  });
 
  f.kt.addEventListener("change", function () {
    if (f.bd.value && f.kt.value && f.kt.value <= f.bd.value) {
      bao(false, "Giờ kết thúc phải sau giờ bắt đầu.");
      f.kt.value = congPhut(f.bd.value, 60);
    }
  });
 
  btnPhut.forEach(function (b) {
    b.addEventListener("click", function () {
      capNhatGioiHan();
      if (!f.bd.value) f.bd.value = f.bd.min || strGio(moc());
      f.kt.value = congPhut(f.bd.value, +b.dataset.phut);
      capNhatGioiHan();
    });
  });
 
  setInterval(capNhatGioiHan, 30000); // trang để mở lâu thì giới hạn vẫn cập nhật theo thời gian
 
  // ---------- Gọi API ----------
  // GET lấy JSON; POST gửi kèm header X-CSRF-Token (window.csrfHeaders từ app.js).
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
    var x = /^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2})/.exec(s || "");
    return x ? x[3] + "/" + x[2] + "/" + x[1] + " " + x[4] + ":" + x[5] : s;
  }
 
  function o(tag, text, cls) {
    var el = document.createElement(tag);
    if (text !== undefined) el.textContent = text; // textContent: chống XSS với dữ liệu người dùng
    if (cls) el.className = cls;
    return el;
  }
 
  // Biểu tượng "con mắt" (SVG, không cần thư viện icon)
  function bieuTuongMat() {
    var ns = "http://www.w3.org/2000/svg";
    var svg = document.createElementNS(ns, "svg");
    svg.setAttribute("width", "16");
    svg.setAttribute("height", "16");
    svg.setAttribute("viewBox", "0 0 16 16");
    svg.setAttribute("fill", "currentColor");
    svg.setAttribute("aria-hidden", "true");
    [
      "M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z",
      "M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z",
    ].forEach(function (d) {
      var p = document.createElementNS(ns, "path");
      p.setAttribute("d", d);
      svg.appendChild(p);
    });
    return svg;
  }
 
  // ---------- Bảng danh sách ----------
  function veBang(ds) {
    tbody.replaceChildren();
    if (!ds.length) {
      var tr0 = o("tr");
      var td0 = o("td", "Chưa có khung giờ nào trong ngày này.", "text-center text-muted py-4");
      td0.colSpan = 7;
      tr0.appendChild(td0);
      tbody.appendChild(tr0);
      return;
    }
    var bayGioStr = strNgay(bayGio()) + " " + strGio(bayGio()) + ":00";
    ds.forEach(function (it, i) {
      var daQua = it.bat_dau <= bayGioStr;          // slot đã qua giờ: làm mờ, khóa nút Sửa
      var tr = o("tr", undefined, daQua ? "opacity-50" : "");
      tr.appendChild(o("td", String(i + 1)));
      tr.appendChild(o("td", hienThi(it.bat_dau)));
      tr.appendChild(o("td", hienThi(it.ket_thuc)));
      tr.appendChild(o("td", it.dia_diem || "Chưa cập nhật"));
      tr.appendChild(o("td", it.so_dang_ky + " / " + it.suc_chua, "text-center fw-semibold"));
 
      var tt = o("td");
      if (daQua) tt.appendChild(o("span", "Đã qua", "badge text-bg-secondary"));
      else if (it.da_day) tt.appendChild(o("span", "Đã đầy", "badge text-bg-warning"));
      else if (it.so_dang_ky > 0) tt.appendChild(o("span", "Còn " + it.con_lai + " chỗ", "badge text-bg-info"));
      else tt.appendChild(o("span", "Còn trống", "badge text-bg-success"));
      tr.appendChild(tt);
 
      var act = o("td", undefined, "text-end text-nowrap");
 
      var xem = o("button", undefined, "btn btn-outline-info btn-sm me-1");
      xem.type = "button";
      xem.title = "Xem sinh viên đăng ký";
      xem.setAttribute("aria-label", "Xem sinh viên đăng ký khung giờ " + hienThi(it.bat_dau));
      xem.appendChild(bieuTuongMat());
      xem.addEventListener("click", function () { xemSinhVien(it); });
 
      var sua = o("button", "Sửa", "btn btn-outline-primary btn-sm me-1");
      sua.type = "button";
      sua.disabled = daQua;
      if (daQua) sua.title = "Khung giờ đã qua, không thể sửa";
      sua.addEventListener("click", function () { batDauSua(it); });
 
      var xoa = o("button", "Xóa", "btn btn-outline-danger btn-sm");
      xoa.type = "button";
      if (it.so_dang_ky > 0) {
        xoa.disabled = true;                        // đã có sinh viên: server cũng từ chối xóa
        xoa.title = "Khung giờ đã có sinh viên đăng ký nên không thể xóa";
      }
      xoa.addEventListener("click", function () { xoaSlot(it); });
 
      act.appendChild(xem);
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
 
  // ---------- Modal: sinh viên đã đăng ký ----------
  var modalEl = document.getElementById("modalSinhVien");
  var modal = null;
  var bangSV = document.getElementById("bangSinhVien");
  var svPhu = document.getElementById("modalSinhVienPhu");
  var svDem = document.getElementById("modalSinhVienDem");
 
  var NHAN_TT = {
    cho_duyet: ["Chờ duyệt", "warning"],
    da_duyet: ["Đã duyệt", "success"],
    tu_choi: ["Từ chối", "danger"],
    da_huy: ["Đã hủy", "secondary"],
    hoan_thanh: ["Hoàn thành", "primary"],
  };
 
  function moModal() {
    if (!window.bootstrap) { bao(false, "Không tải được thư viện giao diện (Bootstrap)."); return false; }
    if (!modal) modal = new window.bootstrap.Modal(modalEl);
    modal.show();
    return true;
  }
 
  function veSinhVien(res) {
    var s = res.slot;
    svPhu.textContent = hienThi(s.bat_dau) + " – " + hienThi(s.ket_thuc).slice(-5) + (s.dia_diem ? " · " + s.dia_diem : "");
    svDem.textContent = "Đã đăng ký: " + res.so_dang_ky + " / " + s.suc_chua + " sinh viên";
    bangSV.replaceChildren();
 
    if (!res.data.length) {
      var tr0 = o("tr");
      var td0 = o("td", "Chưa có sinh viên nào đăng ký khung giờ này.", "text-center text-muted py-4");
      td0.colSpan = 6;
      tr0.appendChild(td0);
      bangSV.appendChild(tr0);
      return;
    }
    res.data.forEach(function (r, i) {
      var khongChiem = r.trang_thai === "tu_choi" || r.trang_thai === "da_huy"; // không chiếm chỗ -> làm mờ
      var tr = o("tr", undefined, khongChiem ? "opacity-50" : "");
      tr.appendChild(o("td", String(i + 1)));
      tr.appendChild(o("td", r.ho_ten, "fw-semibold"));
      tr.appendChild(o("td", r.email));
      tr.appendChild(o("td", r.chu_de));
      var td = o("td");
      var tt = NHAN_TT[r.trang_thai] || [r.trang_thai, "secondary"];
      td.appendChild(o("span", tt[0], "badge text-bg-" + tt[1]));
      tr.appendChild(td);
      tr.appendChild(o("td", hienThi(r.ngay_tao), "text-nowrap"));
      bangSV.appendChild(tr);
    });
  }
 
  function xemSinhVien(it) {
    goi(URL_SV + "&ma_khung_gio=" + encodeURIComponent(it.ma_khung_gio)).then(function (res) {
      if (!res.success) { bao(false, res.message || "Không tải được danh sách sinh viên."); return; }
      veSinhVien(res);
      moModal();
    }).catch(function () { bao(false, "Lỗi kết nối máy chủ."); });
  }
 
  // ---------- Form ----------
  function datLaiForm() {
    khoaGio(false);
    form.reset();                                   // suc_chua trở về 1 (thuộc tính value mặc định)
    f.sl.min = "1";
    f.id.value = "";
    // Gợi ý sẵn ngày đang xem trong bảng (nếu không phải quá khứ)
    f.ngay.value = ngayLoc.value && ngayLoc.value >= strNgay(moc()) ? ngayLoc.value : "";
    tieuDe.textContent = "Thêm khung giờ rảnh mới";
    btnLuu.textContent = "Lưu khung giờ";
    btnHuy.classList.add("d-none");
    capNhatGioiHan();
  }
 
  function batDauSua(it) {
    khoaGio(it.so_dang_ky > 0);                     // đã có sinh viên: khóa ngày giờ
    f.id.value = it.ma_khung_gio;
    f.ngay.value = it.bat_dau.slice(0, 10);
    f.bd.value = it.bat_dau.slice(11, 16);
    f.kt.value = it.ket_thuc.slice(11, 16);
    f.sl.value = it.suc_chua;
    f.sl.min = String(Math.max(1, it.so_dang_ky));  // không cho giảm xuống dưới số đã đăng ký
    f.dd.value = it.dia_diem || "";
    tieuDe.textContent = "Cập nhật khung giờ";
    btnLuu.textContent = "Cập nhật";
    btnHuy.classList.remove("d-none");
    capNhatGioiHan();
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
    capNhatGioiHan();
 
    if (!f.ngay.value || !f.bd.value || !f.kt.value) {
      bao(false, "Vui lòng chọn ngày, giờ bắt đầu và giờ kết thúc.");
      return;
    }
    if (f.kt.value <= f.bd.value) {
      bao(false, "Giờ kết thúc phải sau giờ bắt đầu.");
      return;
    }
    var sl = parseInt(f.sl.value, 10);
    var slMin = parseInt(f.sl.min || "1", 10);
    if (isNaN(sl) || sl < 1 || sl > 50) {
      bao(false, "Số sinh viên tối đa phải từ 1 đến 50.");
      return;
    }
    if (sl < slMin) {
      bao(false, "Đã có " + slMin + " sinh viên đăng ký, số lượng tối đa không được nhỏ hơn " + slMin + ".");
      return;
    }
 
    var sua = f.id.value !== "";
    var body = new URLSearchParams({
      ma_khung_gio: f.id.value,
      bat_dau: f.ngay.value + "T" + f.bd.value,    // gộp lại đúng định dạng backend đang nhận
      ket_thuc: f.ngay.value + "T" + f.kt.value,
      suc_chua: String(sl),
      dia_diem: f.dd.value,
    });
 
    btnLuu.disabled = true;
    goi(sua ? URL_SUA : URL_THEM, body)
      .then(function (res) {
        bao(!!res.success, res.message || "");
        if (res.success) {
          ngayLoc.value = f.ngay.value;              // chuyển bảng sang ngày vừa lưu để thấy ngay kết quả
          datLaiForm();
          taiDanhSach();
        }
      })
      .catch(function () { bao(false, "Lỗi kết nối máy chủ."); })
      .then(function () { btnLuu.disabled = false; });
  });
 
  btnHuy.addEventListener("click", datLaiForm);
  ngayLoc.addEventListener("change", function () {
    if (!f.id.value && ngayLoc.value >= strNgay(moc())) { f.ngay.value = ngayLoc.value; capNhatGioiHan(); }
    taiDanhSach();
  });
 
  capNhatGioiHan();
  taiDanhSach();
})();