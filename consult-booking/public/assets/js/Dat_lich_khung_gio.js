/* Trang đặt lịch của sinh viên: chọn giảng viên -> hiện các khung giờ rảnh (còn chỗ) của giảng viên đó để bấm chọn.
   JS thuần, không inline script (tương thích CSP). */
(function () {
  "use strict";

  var selGv = document.getElementById("giang_vien_id");
  var khoi = document.getElementById("khoi_khung_gio");
  var hidden = document.getElementById("ma_khung_gio");
  if (!selGv || !khoi || !hidden) return;

  var URL_DS = khoi.dataset.url;
  var THU = ["Chủ nhật", "Thứ hai", "Thứ ba", "Thứ tư", "Thứ năm", "Thứ sáu", "Thứ bảy"];
  var controller = null;
  var daChonBanDau = hidden.value;   // khung giờ được chọn sẵn (từ nút "Lịch rảnh" hoặc sau khi form báo lỗi)

  function el(tag, cls, text) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text !== undefined) n.textContent = text;
    return n;
  }

  function thongBao(text, cls) {
    khoi.replaceChildren(el("p", cls || "text-muted mb-0", text));
  }

  function tach(s) {
    return { ngay: s.slice(0, 10), gio: s.slice(11, 16) };
  }

  function nhanNgay(ngay) {
    var p = ngay.split("-");
    var d = new Date(+p[0], +p[1] - 1, +p[2]);
    return THU[d.getDay()] + ", " + p[2] + "/" + p[1] + "/" + p[0];
  }

  var selMon = document.getElementById("mon_hoc");
  var btnGui = selGv.closest("form") ? selGv.closest("form").querySelector('button[type="submit"]') : null;
  var guiBanDau = btnGui ? btnGui.disabled : false;
  var canhBao = null;

  // Môn mà giảng viên đã mở cho khung giờ đang chọn ("" nếu khung giờ không gắn môn hoặc chưa chọn khung giờ)
  function monCuaKhung() {
    var n = hidden.value ? khoi.querySelector('.nut-khung-gio[data-id="' + hidden.value + '"]') : null;
    return n ? n.getAttribute("data-mon") || "" : "";
  }

  // Sinh viên chọn môn khác với môn của khung giờ -> báo lỗi và khóa nút gửi
  function kiemTraMon() {
    if (!selMon) return;
    var mon = monCuaKhung();
    var sai = !!(mon && selMon.value && selMon.value !== mon);
    if (sai && !canhBao) {
      canhBao = el("div", "form-text text-danger");
      canhBao.setAttribute("role", "alert");
      selMon.parentElement.appendChild(canhBao);
    }
    if (canhBao) {
      canhBao.textContent = sai
        ? "Khung giờ này giảng viên mở cho môn “" + mon + "”, không thể đặt môn “" + selMon.value + "”. Hãy chọn lại môn “" + mon + "” hoặc chọn khung giờ khác."
        : "";
      canhBao.classList.toggle("d-none", !sai);
    }
    if (btnGui) btnGui.disabled = guiBanDau || sai;
  }

  // Điền môn của khung giờ lên ô "Môn học / Chuyên đề"
  function datMon(mon) {
    if (!selMon || !mon) return;
    var co = Array.prototype.some.call(selMon.options, function (o) { return o.value === mon; });
    if (!co) {
      var o = document.createElement("option");
      o.value = mon;
      o.textContent = mon;
      selMon.appendChild(o);
    }
    selMon.value = mon;
  }

  var oNgay = document.getElementById("ngay_hen");
  var oGioBd = document.getElementById("gio_bat_dau");    // sau khi app.js đổi ô giờ: đây là ô "Giờ" (text)
  var oGioKt = document.getElementById("gio_ket_thuc");

  // Ô giờ do app.js dựng gồm 2 ô text (Giờ, Phút) + 1 input ẩn giữ giá trị "HH:MM"; điền vào 2 ô text rồi báo change để nó tự đồng bộ.
  function datGio(oGio, hhmm) {
    if (!oGio) return;
    var o = oGio.parentElement.querySelectorAll('input[type="text"]');
    if (o.length >= 2) {
      o[0].value = hhmm.slice(0, 2);
      o[1].value = hhmm.slice(3, 5);
      o[0].dispatchEvent(new Event("change", { bubbles: true }));
      o[1].dispatchEvent(new Event("change", { bubbles: true }));
    } else {
      oGio.value = hhmm;   // phòng khi ô giờ chưa bị thay thế
    }
  }

  function boChon() {
    hidden.value = "";
    khoi.querySelectorAll(".nut-khung-gio").forEach(function (n) {
      n.classList.remove("btn-primary");
      n.classList.add("btn-outline-secondary");
      n.setAttribute("aria-pressed", "false");
    });
    kiemTraMon();
  }

  function chon(nut) {
    hidden.value = nut.getAttribute("data-id");
    if (oNgay) oNgay.value = nut.getAttribute("data-ngay");
    datGio(oGioBd, nut.getAttribute("data-bd"));
    datGio(oGioKt, nut.getAttribute("data-kt"));
    khoi.querySelectorAll(".nut-khung-gio").forEach(function (n) {
      var dangChon = n === nut;
      n.classList.toggle("btn-primary", dangChon);
      n.classList.toggle("btn-outline-secondary", !dangChon);
      n.setAttribute("aria-pressed", dangChon ? "true" : "false");
    });
    datMon(nut.getAttribute("data-mon"));   // bấm khung giờ -> hiện luôn môn của khung giờ lên form
    kiemTraMon();
  }

  function ve(slots) {
    if (!slots.length) {
      hidden.value = "";
      kiemTraMon();
      thongBao("Giảng viên này hiện chưa có khung giờ rảnh nào. Vui lòng chọn giảng viên khác hoặc quay lại sau.");
      return;
    }
    var nhom = {};
    var thuTu = [];
    slots.forEach(function (s) {
      var ngay = tach(s.bat_dau).ngay;
      if (!nhom[ngay]) {
        nhom[ngay] = [];
        thuTu.push(ngay);
      }
      nhom[ngay].push(s);
    });

    var frag = document.createDocumentFragment();
    var coKhungDaChon = false;
    thuTu.forEach(function (ngay) {
      frag.appendChild(el("div", "fw-semibold mt-3 mb-2", nhanNgay(ngay)));
      var hang = el("div", "d-flex flex-wrap gap-2");
      nhom[ngay].forEach(function (s) {
        var bd = tach(s.bat_dau);
        var kt = tach(s.ket_thuc);
        var nut = el("button", "nut-khung-gio btn btn-outline-secondary text-start");
        nut.type = "button";
        nut.setAttribute("data-id", s.ma_khung_gio);
        nut.setAttribute("aria-pressed", "false");
        nut.setAttribute("data-ngay", tach(s.bat_dau).ngay);
        nut.setAttribute("data-bd", bd.gio);
        nut.setAttribute("data-kt", kt.gio);
        if (s.ten_chuyen_mon) nut.setAttribute("data-mon", s.ten_chuyen_mon);
        nut.appendChild(el("div", "fw-semibold", bd.gio + " – " + kt.gio));
        nut.appendChild(
          el("div", "small opacity-75", (s.ten_chuyen_mon ? s.ten_chuyen_mon + " · " : "") + (s.dia_diem || "Chưa cập nhật địa điểm") + " · còn " + s.con_lai + "/" + s.suc_chua + " chỗ")
        );
        nut.addEventListener("click", function () {
          chon(nut);
        });
        if (String(s.ma_khung_gio) === String(hidden.value)) coKhungDaChon = true;
        hang.appendChild(nut);
      });
      frag.appendChild(hang);
    });
    khoi.replaceChildren(frag);

    if (coKhungDaChon) {
      chon(khoi.querySelector('.nut-khung-gio[data-id="' + hidden.value + '"]'));
    } else {
      hidden.value = "";   // khung giờ cũ không còn (đã đầy / đã qua) -> chuyển sang đặt theo ngày giờ đang hiển thị
    }
  }

  function nap() {
    var gv = selGv.value;
    if (controller) controller.abort();
    if (!gv) {
      hidden.value = "";
      thongBao("Hãy chọn giảng viên để xem các khung giờ rảnh.");
      return;
    }
    thongBao("Đang tải khung giờ rảnh...");
    controller = new AbortController();
    fetch(URL_DS + (URL_DS.indexOf("?") === -1 ? "?" : "&") + "ma_giang_vien=" + encodeURIComponent(gv), {
      headers: { Accept: "application/json" },
      credentials: "same-origin",
      signal: controller.signal,
    })
      .then(function (res) {
        return res.json();
      })
      .then(function (json) {
        if (!json.success) throw new Error(json.message || "Không tải được khung giờ.");
        ve(json.data || []);
      })
      .catch(function (err) {
        if (err.name === "AbortError") return;
        thongBao(err.message || "Không tải được khung giờ. Vui lòng thử lại.", "text-danger mb-0");
      });
  }

  selGv.addEventListener("change", function () {
    hidden.value = "";   // đổi giảng viên thì bỏ khung giờ đã chọn (ngày giờ đã điền giữ nguyên để sinh viên sửa)
    kiemTraMon();
    nap();
  });

  if (selMon) selMon.addEventListener("change", kiemTraMon);

  // Sinh viên tự sửa ngày/giờ -> không còn là "khung giờ có sẵn" nữa, bỏ chọn nút để tránh đặt nhầm khung giờ cũ
  [oNgay, oGioBd, oGioKt].forEach(function (o) {
    if (!o) return;
    var vung = o === oNgay ? o : o.parentElement;
    vung.addEventListener("input", boChon);
  });

  hidden.value = daChonBanDau;
  nap();
})();