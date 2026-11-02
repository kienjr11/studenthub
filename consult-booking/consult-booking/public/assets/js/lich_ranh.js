/* Trang Tìm giảng viên: nút "Lịch rảnh" mở/đóng danh sách khung giờ còn chỗ ngay trên thẻ giảng viên (Fetch API).
   JS thuần, không inline script (tương thích CSP). */
(function () {
  "use strict";

  var app = document.getElementById("lichRanhApp");
  if (!app) return;

  var URL_DS = app.dataset.url;
  var URL_DAT = app.dataset.urlDat;   // rỗng nếu người xem không phải sinh viên -> chỉ xem, không có link đặt
  var THU = ["CN", "T2", "T3", "T4", "T5", "T6", "T7"];

  function el(tag, cls, text) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text !== undefined) n.textContent = text;
    return n;
  }

  function tach(s) {
    return { ngay: s.slice(0, 10), gio: s.slice(11, 16) };
  }

  function nhanNgay(ngay) {
    var p = ngay.split("-");
    var d = new Date(+p[0], +p[1] - 1, +p[2]);
    return THU[d.getDay()] + ", " + p[2] + "/" + p[1] + "/" + p[0];
  }

  function thongBao(panel, text, cls) {
    panel.replaceChildren(el("div", cls || "small text-muted", text));
  }

  function ve(panel, gv, slots) {
    if (!slots.length) {
      thongBao(panel, "Giảng viên này chưa có khung giờ rảnh nào trong thời gian tới.");
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
    frag.appendChild(el("div", "small fw-semibold mb-1", "Khung giờ rảnh"));
    thuTu.forEach(function (ngay) {
      frag.appendChild(el("div", "small text-muted mt-2 mb-1", nhanNgay(ngay)));
      var hang = el("div", "d-flex flex-wrap gap-1");
      nhom[ngay].forEach(function (s) {
        var bd = tach(s.bat_dau);
        var kt = tach(s.ket_thuc);
        var nhan = bd.gio + " – " + kt.gio;
        var tip = (s.ten_chuyen_mon ? s.ten_chuyen_mon + " · " : "") + (s.dia_diem || "Chưa cập nhật địa điểm") + " · còn " + s.con_lai + "/" + s.suc_chua + " chỗ";
        var chip;
        if (URL_DAT) {
          var q = new URLSearchParams({ giang_vien_id: gv, ma_khung_gio: s.ma_khung_gio });
          chip = el("a", "btn btn-sm btn-outline-primary", nhan);
          chip.href = URL_DAT + (URL_DAT.indexOf("?") === -1 ? "?" : "&") + q.toString();
        } else {
          chip = el("span", "pill", nhan);
        }
        chip.title = tip;
        hang.appendChild(chip);
      });
      frag.appendChild(hang);
    });
    if (URL_DAT) frag.appendChild(el("div", "form-text mt-2", "Bấm vào một khung giờ để đặt lịch."));
    panel.replaceChildren(frag);
  }

  document.querySelectorAll(".btn-lich-ranh").forEach(function (btn) {
    var panel = btn.closest(".role-block").querySelector(".lich-ranh-panel");
    var daNap = false;

    btn.addEventListener("click", function () {
      var mo = panel.classList.contains("d-none");
      panel.classList.toggle("d-none", !mo);
      btn.setAttribute("aria-expanded", mo ? "true" : "false");
      if (!mo || daNap) return;

      daNap = true;
      thongBao(panel, "Đang tải khung giờ...");
      var gv = btn.getAttribute("data-gv");
      fetch(URL_DS + (URL_DS.indexOf("?") === -1 ? "?" : "&") + "ma_giang_vien=" + encodeURIComponent(gv), {
        headers: { Accept: "application/json" },
        credentials: "same-origin",
      })
        .then(function (res) {
          return res.json();
        })
        .then(function (json) {
          if (!json.success) throw new Error(json.message || "Không tải được lịch rảnh.");
          ve(panel, gv, json.data || []);
        })
        .catch(function (err) {
          daNap = false;   // cho phép bấm lại để thử tải lại
          thongBao(panel, err.message || "Không tải được lịch rảnh. Vui lòng thử lại.", "small text-danger");
        });
    });
  });
})();