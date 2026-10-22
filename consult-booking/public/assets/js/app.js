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
  var khoiMon = document.getElementById("khoi_chon_mon");
  if (selGv && selMon && khoiMon) {
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
})();