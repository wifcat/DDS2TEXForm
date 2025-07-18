<?php

include 'hashbrown-openvapi.php';

?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Konversi DDS → TEX</title>
  <link rel="icon" type="image/png" href="https://i.imgur.com/vOoPsoN.jpeg">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      margin: 0;
      height: 100vh;
      background: linear-gradient(-45deg,rgb(26, 26, 26),rgb(61, 41, 61),rgb(29, 42, 59),rgb(26, 26, 26));
      background-size: 400% 400%;
      animation: gradientFlow 30s ease infinite;
      color: white;
      font-family: 'Segoe UI', sans-serif;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    @keyframes gradientFlow {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    h3, label {
      color: #fff;
    }

    .card {
      background: rgba(40, 40, 40, 0.85);
      backdrop-filter: blur(10px);
      border: none;
      border-radius: 1rem;
      padding: 3rem;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.4);
    }

    .hidden-input {
      display: none;
    }

    .custom-upload-btn {
      display: inline-block;
      background-color: rgba(255, 255, 255, 0.05);
      color: #fff;
      border-radius: 8px;
      padding: 12px 20px;
      width: 50%;                 /* Lebarkan penuh container */
      max-width: 100%;  
      cursor: pointer;
      transition: background-color 0.3s, border-color 0.3s;
      backdrop-filter: blur(8px);
    }

    .custom-upload-btn:hover {
      background-color: rgba(255, 255, 255, 0.1);
      border-color: rgba(255, 255, 255, 0.3);
    }

    .btn-primary {
      background-color: #0d6efd;
      border: none;
      max-width: 100%;
      width: 50%;
      padding: 5px;
      border-radius: 10px;
    }

    .btn-primary:hover {
      background-color: #0b5ed7;
    }

    .text-muted {
      color: #aaa !important;
    }
    .modal.fade .modal-dialog {
    transform: scale(0.7);
    transition: transform 0.3s ease-out, opacity 0.3s ease-out;
    opacity: 0;
    }

    .modal.fade.show .modal-dialog {
      transform: scale(1);
      opacity: 1;
    }
    
    .modal-content {
      background: rgba(30, 30, 30, 0.9);
      backdrop-filter: blur(14px);
      border: none;
      border-radius: 1rem;
      color: #fff;
    }

    .btn-outline-light,
    .modal-content .btn {
      border-radius: 10px;
    }

    /* Nama file setelah dipilih */
    #file-name {
      margin-top: 10px;
      font-size: 0.9rem;
      color: #ccc;
      word-break: break-all;
    }
  </style>
  
</head>
<body>
  <div class="container text-center">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow">
          <h3 class="mb-4">Konversi File DDS ke TEX</h3>
          <form method="post" enctype="multipart/form-data" onsubmit="showModal()">
            <div class="mb-3 text-center">
              <label for="ddsfile" class="custom-upload-btn">Pilih File DDS</label>
              <input type="file" name="ddsfile" id="ddsfile" accept=".dds" required class="hidden-input" onchange="showFileName(this)">
              <div id="file-name"></div>
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-primary mx-auto">Konversi Sekarang</button>
            </div>
          </form>
        </div>
        <p class="mt-4 text-muted">© <?= date('Y') ?> Dibuat oleh <strong style="color:rgb(108, 91, 148);"><a href="https://youtube.com/c/zenichen">Ateris</a></strong></p>
        <p class="mt-4 text-muted"> Thanks to <a href="https://github.com/EdnessP" class="link">EdnessP</a> for his python script</p>
      </div>
    </div>
  </div>


  <!-- Modal Sukses -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center p-4">
      <div class="modal-body">
        <i class="fa-solid fa-circle-check fa-3x mb-3 text-success"></i>
        <h5 class="modal-title mb-2" id="successModalLabel">Konversi Berhasil!</h5>
        <p>File DDS telah berhasil dikonversi ke TEX. File akan terunduh otomatis!</p>
        <button id="mantapBtn" type="button" class="btn btn-outline-light rounded-pill mt-2" data-bs-dismiss="modal" disabled>
          <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
          Tunggu sebentar...
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Proyek Tidak Dilanjutkan -->
<div class="modal fade" id="cancelledModal" tabindex="-1" aria-labelledby="cancelledModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center p-4">
      <div class="modal-body"> 
        <i class="fa-solid fa-circle-exclamation fa-3x mb-3 text-danger"></i>
        <h5 class="modal-title mb-2" id="cancelledModalLabel">Proyek Dibatalkan</h5>
        <p>Maaf, Proyek ini tidak akan dilanjutkan. Terima kasih, Versi lain software ini => <a href="dds2tex-bae-up.railway.app"> Klik disini</a></p>
        <button type="button" class="btn btn-outline-danger rounded-pill mt-2" data-bs-dismiss="modal">
          Saya mengerti
        </button>
      </div>
    </div>
  </div>
</div>
  

<!-- Modal Kedua -->
<div class="modal fade" id="secondModal" tabindex="-1" aria-labelledby="secondModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center p-4" style="background: rgba(30, 30, 30, 0.85); backdrop-filter: blur(12px); border: none; border-radius: 1rem; color: #fff;">
      <div class="modal-body">
        <h5 class="modal-title mb-3 fw-bold" id="secondModalLabel">🍦 Bantu gw beli eskrim, yo!</h5>
        <p class="mb-4">💖 Terima kasih telah pake software gw!</p>
        
        <div class="d-flex justify-content-center gap-4 mb-4">
          <a href="https://www.youtube.com/c/zenichen" target="_blank" title="YouTube" class="text-danger" style="text-decoration: none;">
            <i class="fa-brands fa-youtube fa-2x"></i>
          </a>
          <a href="https://aterisdoc.ct.ws" target="_blank" title="Website" class="text-info" style="text-decoration: none;">
            <i class="fa-solid fa-globe fa-2x"></i>
          </a>
          <a href="https://saweria.co/ateris" target="_blank" title="Donasi" style="color: #ff69b4; text-decoration: none;">
            <i class="fa-solid fa-heart fa-2x"></i>
          </a>
        </div>

        <button type="button" class="btn btn-outline-light px-4 py-2 rounded-pill" data-bs-dismiss="modal">
          Lain kali deh say 🥺
        </button>
      </div>
    </div>
  </div>
</div>



<!-- Modal Gagal -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center p-4">
      <div class="modal-body">
        <i class="fa-solid fa-circle-xmark fa-3x mb-3 text-danger"></i>
        <h5 class="modal-title text-danger mb-2" id="errorModalLabel">Konversi Gagal!</h5>
        <p class="text-muted"><?= htmlspecialchars($errorMsg ?? "Terjadi kesalahan saat mengonversi file.") ?></p>
        <button type="button" class="btn btn-danger rounded-pill mt-2" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function showModal() {
    const modal = new bootstrap.Modal(document.getElementById('successModal'));
    const button = document.getElementById('mantapBtn');
    button.disabled = true;
    button.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Tunggu sebentar...`;
    modal.show();
    setTimeout(() => {
        button.disabled = false;
        button.innerHTML = 'Mantap';
        button.addEventListener('click', function() {
            const secondModal = new bootstrap.Modal(document.getElementById('secondModal'));
            secondModal.show();
        });
    }, 4610);
}

  document.addEventListener('DOMContentLoaded', () => {
  const success = <?= isset($success) ? json_encode($success) : 'null' ?>;

  if (success === true) {
    const modal = new bootstrap.Modal(document.getElementById('successModal'));
    modal.show();

    const button = document.querySelector('#successModal .btn');
    button.style.display = 'none';
    setTimeout(() => {
      button.style.display = 'inline-block';
    }, 4610);
  } else if (success === false) {
    const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
    errorModal.show();
  }
});
    document.addEventListener("DOMContentLoaded", function() {
    // Tampilkan modal saat halaman selesai dimuat
    var myModal = new bootstrap.Modal(document.getElementById('cancelledModal'));
    myModal.show();
    });


</script>
</body>
</html>
</script>
</body>
</html>
