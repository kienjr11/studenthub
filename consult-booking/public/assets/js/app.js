/* JavaScript thuần, không dùng inline script nên tương thích Content-Security-Policy. */
(function () {
  "use strict";

  var csrf = document.querySelector('meta[name="csrf-token"]');
  // Dùng cho các request Fetch thay đổi dữ liệu (POST): gửi kèm header X-CSRF-Token.
  window.csrfHeaders = function (extra) {
    return Object.assign(
      { "X-CSRF-Token": csrf ? csrf.content : "" },
      extra || {},
    );
  };

  // Hộp xác nhận cho form có data-confirm
  document.addEventListener("submit", function (ev) {
    var msg = ev.target.getAttribute && ev.target.getAttribute("data-confirm");
    if (msg && !window.confirm(msg)) {
      ev.preventDefault();
    }
  });

  // Ô lọc có data-autosubmit: đổi lựa chọn thì gửi form ngay. data-reset="tên" đặt lại ô khác về giá trị đầu tiên
  // (đổi khoa thì bỏ chuyên môn đang chọn vì danh sách chuyên môn thay đổi theo khoa).
  document.querySelectorAll("select[data-autosubmit]").forEach(function (sel) {
    sel.addEventListener("change", function () {
      var other = sel.getAttribute("data-reset");
      if (other && sel.form.elements[other]) {
        sel.form.elements[other].selectedIndex = 0;
      }
      sel.form.submit();
    });
  });

  // Hiện/ẩn mật khẩu
  document.querySelectorAll("[data-toggle-password]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var input = document.querySelector(
        btn.getAttribute("data-toggle-password"),
      );
      var show = input.type === "password";
      input.type = show ? "text" : "password";
      btn.textContent = show ? "Ẩn" : "Hiện";
    });
  });

  // Trang đăng ký: chỉ hiện ô "Đơn vị" khi chọn vai trò giảng viên
  var deptGroup = document.getElementById("dept-group");
  var roleInputs = document.querySelectorAll('input[name="role"]');
  function syncDept() {
    if (!deptGroup) {
      return;
    }
    var checked = document.querySelector('input[name="role"]:checked');
    deptGroup.classList.toggle(
      "d-none",
      !checked || checked.value !== "giang_vien",
    );
  }
  roleInputs.forEach(function (r) {
    r.addEventListener("change", syncDept);
  });
  syncDept();

  // Trang đăng ký: kiểm tra email đã tồn tại chưa bằng Fetch API + endpoint JSON (có debounce)
  var emailInput = document.querySelector("[data-check-email]");
  var feedback = document.getElementById("email-feedback");
  if (emailInput && feedback) {
    var timer = null;
    var lastController = null;
    emailInput.addEventListener("input", function () {
      clearTimeout(timer);
      feedback.textContent = "";
      feedback.className = "form-text";
      var value = emailInput.value.trim();
      if (value.length < 5 || value.indexOf("@") === -1) {
        return;
      }
      timer = setTimeout(function () {
        if (lastController) {
          lastController.abort();
        }
        lastController = new AbortController();
        var url = emailInput.getAttribute("data-check-email");
        url +=
          (url.indexOf("?") === -1 ? "?" : "&") +
          "email=" +
          encodeURIComponent(value);
        fetch(url, {
          headers: { Accept: "application/json" },
          credentials: "same-origin",
          signal: lastController.signal,
        })
          .then(function (res) {
            return res.json();
          })
          .then(function (data) {
            feedback.textContent = data.message || "";
            feedback.className =
              "form-text " +
              (data.available === true
                ? "text-success"
                : data.available === false
                  ? "text-danger"
                  : "text-muted");
          })
          .catch(function (err) {
            if (err.name !== "AbortError") {
              feedback.textContent = "";
            }
          });
      }, 400);
    });
  }

  // Trang đặt lịch: chọn giảng viên thì nạp danh sách môn học (chuyên môn) của giảng viên đó
  var selGv = document.getElementById("giang_vien_id");
  var selMon = document.getElementById("mon_hoc");
  if (selGv && selMon) {
    var capNhatMon = function () {
      var opt = selGv.options[selGv.selectedIndex];
      var raw = opt ? opt.getAttribute("data-chuyenmon") || "" : "";
      var ds = raw.split(/[,;]+/).map(function (x) { return x.trim(); }).filter(Boolean);
      selMon.innerHTML = "";
      var first = document.createElement("option");
      first.value = "";
      first.textContent = "-- Chọn môn học --";
      selMon.appendChild(first);
      ds.forEach(function (mon) {
        var o = document.createElement("option");
        o.value = mon;
        o.textContent = mon;
        selMon.appendChild(o);
      });
      selMon.required = ds.length > 0;
      if (ds.length === 1) {
        selMon.value = ds[0];
      }
    };
    selGv.addEventListener("change", capNhatMon);
    capNhatMon();
  }

  // Trang đặt lịch: hiện thẻ xem trước giảng viên đang chọn (họ tên, khoa, chuyên môn)
  var xemTruoc = document.getElementById("xem_truoc_gv");
  if (selGv && xemTruoc) {
    var veXemTruoc = function () {
      var opt = selGv.options[selGv.selectedIndex];
      if (!opt || !opt.value) {
        xemTruoc.classList.add("d-none");
        return;
      }
      var ten = opt.getAttribute("data-ten") || "";
      var tu = ten.trim().split(/\s+/);
      xemTruoc.querySelector('[data-o="chu-cai"]').textContent = (tu[tu.length - 1] || "?").charAt(0).toUpperCase();
      xemTruoc.querySelector('[data-o="ten"]').textContent = ten;
      xemTruoc.querySelector('[data-o="khoa"]').textContent = opt.getAttribute("data-khoa") || "Chưa gán đơn vị";
      var khungMon = xemTruoc.querySelector('[data-o="mon"]');
      khungMon.textContent = "";
      (opt.getAttribute("data-chuyenmon") || "").split(/[,;]+/).map(function (x) { return x.trim(); }).filter(Boolean).forEach(function (mon) {
        var the = document.createElement("span");
        the.className = "pill";
        the.textContent = mon;
        khungMon.appendChild(the);
      });
      xemTruoc.classList.remove("d-none");
    };
    selGv.addEventListener("change", veXemTruoc);
    veXemTruoc();
  }

  // ---------- Ô nhập giờ 24h (gõ hoặc chọn) cho <input type="time" data-gio24> ----------
  // <input type="time"> hiển thị SA/CH theo hệ điều hành và không ép được -> thay bằng 2 ô Giờ (00–23) / Phút (00–59).
  // Mỗi ô vừa gõ số trực tiếp được, vừa bấm để chọn từ danh sách gợi ý (datalist).
  // Giá trị gửi lên server vẫn là "HH:MM" qua một input ẩn mang đúng name cũ.
  Array.prototype.forEach.call(document.querySelectorAll('input[type="time"][data-gio24]'), function (input) {
    function p2(n) { return (n < 10 ? "0" : "") + n; }

    var goc = input.id || input.name;
    function taoDanhSach(id, max, buoc) {
      var dl = document.createElement("datalist");
      dl.id = id;
      for (var i = 0; i <= max; i += buoc) {
        var o = document.createElement("option");
        o.value = p2(i);
        dl.appendChild(o);
      }
      return dl;
    }
    function taoO(nhan, dsId) {
      var e = document.createElement("input");
      e.type = "text";
      e.className = "form-control text-center";
      e.inputMode = "numeric";
      e.maxLength = 2;
      e.placeholder = "--";
      e.autocomplete = "off";
      e.required = input.required;
      e.setAttribute("list", dsId);
      e.setAttribute("aria-label", nhan);
      return e;
    }

    var dsH = taoDanhSach(goc + "_ds_gio", 23, 1);
    var dsM = taoDanhSach(goc + "_ds_phut", 59, 5);
    var h = taoO("Giờ", dsH.id);
    var m = taoO("Phút", dsM.id);
    h.id = input.id;                                       // <label for> trỏ vào ô Giờ

    var an = document.createElement("input");
    an.type = "hidden";
    an.name = input.name;

    var sep = document.createElement("span");
    sep.textContent = ":";
    var wrap = document.createElement("div");
    wrap.className = "d-flex align-items-center gap-2";
    [h, sep, m, an, dsH, dsM].forEach(function (x) { wrap.appendChild(x); });

    function dongBo() {
      var ok = h.value !== "" && m.value !== "" && +h.value <= 23 && +m.value <= 59;
      an.value = ok ? p2(+h.value) + ":" + p2(+m.value) : "";
    }

    // Chuẩn hóa một ô: "7" -> "07"; vượt giới hạn thì báo lỗi ngay trên ô
    function chuanHoa(e, max, ten) {
      if (e.value === "") { e.setCustomValidity(""); return; }
      var n = +e.value;
      if (n > max) { e.setCustomValidity(ten + " phải từ 00 đến " + p2(max) + "."); return; }
      e.value = p2(n);
      e.setCustomValidity("");
    }

    function ganSuKien(e, max, ten, ke, soDau) {
      e.addEventListener("input", function () {
        e.value = e.value.replace(/\D/g, "");               // chỉ giữ chữ số
        // Đủ 2 số, hoặc số đầu lớn đến mức không thể thành số 2 chữ số hợp lệ -> chuẩn hóa và nhảy sang ô kế
        if (e.value.length === 2 || (e.value.length === 1 && +e.value > soDau)) {
          chuanHoa(e, max, ten);
          if (ke && e.validity.valid) { ke.focus(); ke.select(); }
        }
        dongBo();
      });
      e.addEventListener("change", function () {
        chuanHoa(e, max, ten);
        if (e === h && h.value !== "" && h.validity.valid && m.value === "") m.value = "00";  // chọn giờ xong là có giá trị đầy đủ
        dongBo();
      });
      e.addEventListener("blur", function () { chuanHoa(e, max, ten); dongBo(); });
      e.addEventListener("focus", function () { e.select(); });
    }
    ganSuKien(h, 23, "Giờ", m, 2);
    ganSuKien(m, 59, "Phút", null, 5);

    var v = /^(\d{2}):(\d{2})/.exec(input.value || "");     // nạp lại giá trị cũ khi form báo lỗi
    if (v) { h.value = v[1]; m.value = v[2]; dongBo(); }

    input.replaceWith(wrap);
  });
})();