/* Trang sửa hồ sơ giảng viên: xem trước ảnh đại diện và chặn file quá lớn ngay trên trình duyệt. */
(function () {
  "use strict";
  var input = document.getElementById("anh_dai_dien");
  var khung = document.getElementById("anhXemTruoc");
  var xoa = document.getElementById("xoa_anh");
  if (!input || !khung) {
    return;
  }

  var htmlBanDau = khung.innerHTML;

  input.addEventListener("change", function () {
    var file = input.files && input.files[0];
    if (!file) {
      khung.innerHTML = htmlBanDau;
      return;
    }

    var toiDa = parseInt(input.getAttribute("data-toi-da") || "0", 10);
    if (!/^image\/(jpeg|png|webp)$/.test(file.type)) {
      alert("Chỉ chấp nhận ảnh JPG, PNG hoặc WebP.");
      input.value = "";
      khung.innerHTML = htmlBanDau;
      return;
    }
    if (toiDa && file.size > toiDa) {
      alert("Ảnh quá lớn, vui lòng chọn ảnh tối đa 2 MB.");
      input.value = "";
      khung.innerHTML = htmlBanDau;
      return;
    }

    // data: URL (FileReader), KHÔNG dùng URL.createObjectURL: blob: bị CSP img-src chặn
    var reader = new FileReader();
    reader.onload = function () {
      var img = document.createElement("img");
      img.className = "avatar avatar-giang_vien avatar-xl avatar-anh";
      img.alt = "Xem trước ảnh đại diện";
      img.src = reader.result;
      khung.innerHTML = "";
      khung.appendChild(img);
    };
    reader.readAsDataURL(file);
    if (xoa) {
      xoa.checked = false;
    }
  });

  if (xoa) {
    xoa.addEventListener("change", function () {
      if (xoa.checked) {
        input.value = "";
        khung.innerHTML = htmlBanDau;
      }
    });
  }
})();
