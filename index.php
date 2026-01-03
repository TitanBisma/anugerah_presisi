<?php

require_once __DIR__ . '/config/config.php'; // beracu pada percakapanmu sebelumnya
// Ambil data katalog
$sql  = "SELECT id_brg, nama_brg, urlfoto, deskripsi, harga FROM tb_barang ORDER BY id_brg DESC";
$res  = db()->query($sql);

// Helper aman & format rupiah
function esc($s)
{
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
function formatRupiah($n)
{
  return 'Rp ' . number_format((float)$n, 0, ',', '.');
}

// --- Ambil semua paket untuk pilihan select ---
$katalogList = [];
$opt = db()->query("SELECT id_brg, nama_brg, harga FROM tb_barang ORDER BY nama_brg ASC");
if ($opt) {
  while ($r = $opt->fetch_assoc()) {
    $katalogList[] = $r;
  }
}

// --- Jika datang dari katalog ?id_brg=XX, set pilihan awal & harga ---
$id_brgDipilih = isset($_GET['id_brg']) ? (int)$_GET['id_brg'] : 0;
$selectedRow   = null;
foreach ($katalogList as $r) {
  if ((int)$r['id_brg'] === $id_brgDipilih) {
    $selectedRow = $r;
    break;
  }
}
$namaBarang  = $selectedRow ? $selectedRow['nama_brg'] : '';
$hargaAsli = $selectedRow ? (float)$selectedRow['harga'] : 0;
?>

<!DOCTYPE html>
<html lang="en">

<style>
  /* Agar gambar proporsional di dalam ratio */
  .object-fit-cover {
    object-fit: cover;
    width: 100%;
    height: 100%;
  }

  /* ===== Rules (Aturan Pemesanan) Cards ===== */
  .rules-area .rule-card {
    position: relative;
    background: #fff;
    border-radius: 18px;
    padding: 22px 22px 20px;
    border: 1px solid rgba(0, 0, 0, .06);
    box-shadow: 0 6px 20px rgba(0, 0, 0, .05);
    transition: transform .35s ease, box-shadow .35s ease, border-color .35s ease;
    overflow: hidden;
  }

  /* subtle highlight sweep */
  .rules-area .rule-card::after {
    content: "";
    position: absolute;
    inset: -40%;
    background: radial-gradient(80% 60% at 20% 0%,
        rgba(99, 102, 241, .08), transparent 60%);
    transform: translateX(-20%);
    transition: transform .6s ease;
    pointer-events: none;
  }

  .rules-area .rule-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 14px 40px rgba(0, 0, 0, .10);
    border-color: rgba(99, 102, 241, .25);
    /* ungu lembut */
  }

  .rules-area .rule-card:hover::after {
    transform: translateX(10%);
  }

  .rules-area .rule-icon {
    width: 48px;
    height: 48px;
    display: grid;
    place-items: center;
    border-radius: 12px;
    background: linear-gradient(135deg, rgba(99, 102, 241, .15), rgba(59, 130, 246, .15));
    color: #4f46e5;
    /* fallback icon color */
    margin-bottom: 14px;
    font-size: 22px;
  }

  .rules-area .rule-title {
    font-size: 1.15rem;
    font-weight: 700;
    margin-bottom: 8px;
  }


  body .scroll-top {
    left: 16px !important;
    right: auto !important;
  }

  .rules-area p {
    color: #444;
  }
</style>

<head>
  <!--====== Required meta tags ======-->
  <meta charset="utf-8" />
  <meta http-equiv="x-ua-compatible" content="ie=edge" />
  <meta name="description" content="" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">


  <!-- Swiper -->
  <link rel="stylesheet" href="https://unpkg.com/swiper@11/swiper-bundle.min.css">

  <!--====== Title ======-->
  <title>CV. Anugerah Presisi</title>

  <!--====== Favicon Icon ======-->
  <link rel="shortcut icon" href="../assets/images/favicon.svg" type="image/svg" />

  <!--====== Bootstrap css ======-->
  <link rel="stylesheet" href="assets/css/bootstrap.min.css" />

  <!--====== Line Icons css ======-->
  <link rel="stylesheet" href="assets/css/lineicons.css" />

  <!--====== Tiny Slider css ======-->
  <link rel="stylesheet" href="assets/css/tiny-slider.css" />

  <!--====== gLightBox css ======-->
  <link rel="stylesheet" href="assets/css/glightbox.min.css" />

  <link rel="stylesheet" href="style.css" />
</head>

<body data-bs-spy="scroll" data-bs-target="#navbarNine" data-bs-offset="120" tabindex="0">

  <!--====== NAVBAR NINE PART START ======-->

  <section class="navbar-area navbar-nine">
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <nav class="navbar navbar-expand-lg">
            <a class="navbar-brand" href="index.php">
              <img src="assets/images/logo_presisi.png" width="200px" />
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNine"
              aria-controls="navbarNine" aria-expanded="false" aria-label="Toggle navigation">
              <span class="toggler-icon"></span>
              <span class="toggler-icon"></span>
              <span class="toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse sub-menu-bar" id="navbarNine">
              <ul class="navbar-nav me-auto">
                <li class="nav-item">
                  <a class="page-scroll" href="#hero-area">Home</a>
                </li>
                <li class="nav-item">
                  <a class="page-scroll" href="#katalog">Katalog</a>
                </li>

                <li class="nav-item">
                  <a class="page-scroll" href="#pricing">Pesan</a>
                </li>
                <li class="nav-item">
                  <a class="page-scroll" href="#contact">Kontak</a>
                </li>
              </ul>
            </div>
          </nav>
          <!-- navbar -->
        </div>
      </div>
      <!-- row -->
    </div>
    <!-- container -->
  </section>

  <!--====== NAVBAR NINE PART ENDS ======-->

  <!-- Start header Area -->
  <section id="hero-area" class="header-area header-eight">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-6 col-md-12 col-12">
          <div class="header-content">
            <h1>Selamat datang di CV. Anugerah Presisi.</h1>
            <p>
              CV. Anugerah Presisi menghadirkan solusi manufaktur logam berkualitas,
              memproduksi berbagai produk fungsional seperti rak kue, rak telur, dan kebutuhan logam lainnya dengan presisi dan daya tahan tinggi.
            </p>
            <div class="button">
              <a href="#pricing" class="btn primary-btn">Mulai Pesan</a>
              <a href="https://www.youtube.com/watch?v=Zl-N-bTOKvQ"
                class="glightbox video-button">
                <span class="btn icon-btn rounded-full">
                  <i class="lni lni-play"></i>
                </span>
                <span class="text">Lihat Intro</span>
              </a>
            </div>
          </div>
        </div>
        <div class="col-lg-6 col-md-12 col-12">
          <div class="header-image">
            <img src="assets/images/gambar6.jpg" alt="#" />
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- End header Area -->

  <!--====== ABOUT FIVE PART START ======-->

  <section class="about-area about-five">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-6 col-12">
          <div class="about-image-five">
            <svg class="shape" width="106" height="134" viewBox="0 0 106 134" fill="none"
              xmlns="http://www.w3.org/2000/svg">
              <circle cx="1.66654" cy="1.66679" r="1.66667" fill="#DADADA" />
              <circle cx="1.66654" cy="16.3335" r="1.66667" fill="#DADADA" />
              <circle cx="1.66654" cy="31.0001" r="1.66667" fill="#DADADA" />
              <circle cx="1.66654" cy="45.6668" r="1.66667" fill="#DADADA" />
              <circle cx="1.66654" cy="60.3335" r="1.66667" fill="#DADADA" />
              <circle cx="1.66654" cy="88.6668" r="1.66667" fill="#DADADA" />
              <circle cx="1.66654" cy="117.667" r="1.66667" fill="#DADADA" />
              <circle cx="1.66654" cy="74.6668" r="1.66667" fill="#DADADA" />
              <circle cx="1.66654" cy="103" r="1.66667" fill="#DADADA" />
              <circle cx="1.66654" cy="132" r="1.66667" fill="#DADADA" />
              <circle cx="16.3333" cy="1.66679" r="1.66667" fill="#DADADA" />
              <circle cx="16.3333" cy="16.3335" r="1.66667" fill="#DADADA" />
              <circle cx="16.3333" cy="31.0001" r="1.66667" fill="#DADADA" />
              <circle cx="16.3333" cy="45.6668" r="1.66667" fill="#DADADA" />
              <circle cx="16.333" cy="60.3335" r="1.66667" fill="#DADADA" />
              <circle cx="16.333" cy="88.6668" r="1.66667" fill="#DADADA" />
              <circle cx="16.333" cy="117.667" r="1.66667" fill="#DADADA" />
              <circle cx="16.333" cy="74.6668" r="1.66667" fill="#DADADA" />
              <circle cx="16.333" cy="103" r="1.66667" fill="#DADADA" />
              <circle cx="16.333" cy="132" r="1.66667" fill="#DADADA" />
              <circle cx="30.9998" cy="1.66679" r="1.66667" fill="#DADADA" />
              <circle cx="74.6665" cy="1.66679" r="1.66667" fill="#DADADA" />
              <circle cx="30.9998" cy="16.3335" r="1.66667" fill="#DADADA" />
              <circle cx="74.6665" cy="16.3335" r="1.66667" fill="#DADADA" />
              <circle cx="30.9998" cy="31.0001" r="1.66667" fill="#DADADA" />
              <circle cx="74.6665" cy="31.0001" r="1.66667" fill="#DADADA" />
              <circle cx="30.9998" cy="45.6668" r="1.66667" fill="#DADADA" />
              <circle cx="74.6665" cy="45.6668" r="1.66667" fill="#DADADA" />
              <circle cx="31" cy="60.3335" r="1.66667" fill="#DADADA" />
              <circle cx="74.6668" cy="60.3335" r="1.66667" fill="#DADADA" />
              <circle cx="31" cy="88.6668" r="1.66667" fill="#DADADA" />
              <circle cx="74.6668" cy="88.6668" r="1.66667" fill="#DADADA" />
              <circle cx="31" cy="117.667" r="1.66667" fill="#DADADA" />
              <circle cx="74.6668" cy="117.667" r="1.66667" fill="#DADADA" />
              <circle cx="31" cy="74.6668" r="1.66667" fill="#DADADA" />
              <circle cx="74.6668" cy="74.6668" r="1.66667" fill="#DADADA" />
              <circle cx="31" cy="103" r="1.66667" fill="#DADADA" />
              <circle cx="74.6668" cy="103" r="1.66667" fill="#DADADA" />
              <circle cx="31" cy="132" r="1.66667" fill="#DADADA" />
              <circle cx="74.6668" cy="132" r="1.66667" fill="#DADADA" />
              <circle cx="45.6665" cy="1.66679" r="1.66667" fill="#DADADA" />
              <circle cx="89.3333" cy="1.66679" r="1.66667" fill="#DADADA" />
              <circle cx="45.6665" cy="16.3335" r="1.66667" fill="#DADADA" />
              <circle cx="89.3333" cy="16.3335" r="1.66667" fill="#DADADA" />
              <circle cx="45.6665" cy="31.0001" r="1.66667" fill="#DADADA" />
              <circle cx="89.3333" cy="31.0001" r="1.66667" fill="#DADADA" />
              <circle cx="45.6665" cy="45.6668" r="1.66667" fill="#DADADA" />
              <circle cx="89.3333" cy="45.6668" r="1.66667" fill="#DADADA" />
              <circle cx="45.6665" cy="60.3335" r="1.66667" fill="#DADADA" />
              <circle cx="89.3333" cy="60.3335" r="1.66667" fill="#DADADA" />
              <circle cx="45.6665" cy="88.6668" r="1.66667" fill="#DADADA" />
              <circle cx="89.3333" cy="88.6668" r="1.66667" fill="#DADADA" />
              <circle cx="45.6665" cy="117.667" r="1.66667" fill="#DADADA" />
              <circle cx="89.3333" cy="117.667" r="1.66667" fill="#DADADA" />
              <circle cx="45.6665" cy="74.6668" r="1.66667" fill="#DADADA" />
              <circle cx="89.3333" cy="74.6668" r="1.66667" fill="#DADADA" />
              <circle cx="45.6665" cy="103" r="1.66667" fill="#DADADA" />
              <circle cx="89.3333" cy="103" r="1.66667" fill="#DADADA" />
              <circle cx="45.6665" cy="132" r="1.66667" fill="#DADADA" />
              <circle cx="89.3333" cy="132" r="1.66667" fill="#DADADA" />
              <circle cx="60.3333" cy="1.66679" r="1.66667" fill="#DADADA" />
              <circle cx="104" cy="1.66679" r="1.66667" fill="#DADADA" />
              <circle cx="60.3333" cy="16.3335" r="1.66667" fill="#DADADA" />
              <circle cx="104" cy="16.3335" r="1.66667" fill="#DADADA" />
              <circle cx="60.3333" cy="31.0001" r="1.66667" fill="#DADADA" />
              <circle cx="104" cy="31.0001" r="1.66667" fill="#DADADA" />
              <circle cx="60.3333" cy="45.6668" r="1.66667" fill="#DADADA" />
              <circle cx="104" cy="45.6668" r="1.66667" fill="#DADADA" />
              <circle cx="60.333" cy="60.3335" r="1.66667" fill="#DADADA" />
              <circle cx="104" cy="60.3335" r="1.66667" fill="#DADADA" />
              <circle cx="60.333" cy="88.6668" r="1.66667" fill="#DADADA" />
              <circle cx="104" cy="88.6668" r="1.66667" fill="#DADADA" />
              <circle cx="60.333" cy="117.667" r="1.66667" fill="#DADADA" />
              <circle cx="104" cy="117.667" r="1.66667" fill="#DADADA" />
              <circle cx="60.333" cy="74.6668" r="1.66667" fill="#DADADA" />
              <circle cx="104" cy="74.6668" r="1.66667" fill="#DADADA" />
              <circle cx="60.333" cy="103" r="1.66667" fill="#DADADA" />
              <circle cx="104" cy="103" r="1.66667" fill="#DADADA" />
              <circle cx="60.333" cy="132" r="1.66667" fill="#DADADA" />
              <circle cx="104" cy="132" r="1.66667" fill="#DADADA" />
            </svg>
            <!-- SLIDER -->
            <div class="swiper about-swiper">
              <div class="swiper-wrapper">
                <!-- Tambahkan/duplikasi slide sesuai jumlah foto -->
                <div class="swiper-slide">
                  <img src="assets/images/tofflon.jpg" alt="about" />
                </div>
                <div class="swiper-slide">
                  <img src="assets/images/traytelor.jpg" alt="about">
                </div>
                <div class="swiper-slide">
                  <img src="assets/images/tray-vaksin.jpg" alt="about">
                </div>
              </div>

              <!-- Navigasi & pagination -->
              <div class="swiper-button-prev"></div>
              <div class="swiper-button-next"></div>
              <div class="swiper-pagination"></div>
            </div>

          </div>
        </div>
        <div class="col-lg-6 col-12">
          <div class="about-five-content">
            <h2 class="main-title fw-bold">Tentang Kami:</h2>
            <div class="about-five-tab">
              <nav>
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                  <button class="nav-link active" id="nav-who-tab" data-bs-toggle="tab" data-bs-target="#nav-who"
                    type="button" role="tab" aria-controls="nav-who" aria-selected="true">Apa itu CV. Anugerah Presisi?</button>
                  <button class="nav-link" id="nav-vision-tab" data-bs-toggle="tab" data-bs-target="#nav-vision"
                    type="button" role="tab" aria-controls="nav-vision" aria-selected="false">Visi</button>
                  <button class="nav-link" id="nav-history-tab" data-bs-toggle="tab" data-bs-target="#nav-history"
                    type="button" role="tab" aria-controls="nav-history" aria-selected="false">Misi</button>
                </div>
              </nav>
              <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active" id="nav-who" role="tabpanel" aria-labelledby="nav-who-tab">
                  <p>CV. Anugerah Presisi merupakan perusahaan yang bergerak di bidang manufaktur logam dengan fokus pada pembuatan berbagai produk berbahan logam berkualitas tinggi.
                    Kami memproduksi beragam kebutuhan industri dan usaha, seperti rak kue, rak telur, serta produk logam fungsional lainnya yang dirancang dengan presisi dan ketahanan optimal. </p>
                  <p>Dengan mengutamakan kualitas material, ketepatan proses produksi, dan kerapihan hasil akhir, CV. Anugerah Presisi berkomitmen untuk menghadirkan produk yang andal, efisien, dan sesuai dengan kebutuhan pelanggan.</p>
                </div>
                <div class="tab-pane fade" id="nav-vision" role="tabpanel" aria-labelledby="nav-vision-tab">
                  <p>Menjadi perusahaan manufaktur logam yang unggul, terpercaya, dan berdaya saing tinggi dengan mengutamakan kualitas bahan, ketepatan proses, serta kepuasan pelanggan sebagai fondasi utama dalam setiap produk yang dihasilkan.</p>

                </div>
                <div class="tab-pane fade" id="nav-history" role="tabpanel" aria-labelledby="nav-history-tab">
                  <p>CV. Anugerah Presisi berkomitmen untuk menghasilkan produk logam berkualitas tinggi melalui penggunaan material pilihan dan proses produksi yang presisi, memberikan pelayanan profesional
                    dan responsif kepada pelanggan, serta terus meningkatkan efisiensi, inovasi, dan standar mutu guna memenuhi kebutuhan industri secara berkelanjutan.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- container -->
  </section>

  <!--====== ABOUT FIVE PART ENDS ======-->

  <section id="katalog" class="services-area services-eight">
    <!-- judul/teks pengantar -->
    <div class="section-title-five">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="content">
              <h6>Katalog</h6>
              <h2 class="fw-bold">-Barang berkualitas dan Fungsional</h2>
              <p>
                Kami menyediakan berbagai pilihan alat kebutuhan yang dapat Anda pilih menyesuaikan dengan kebutuhan. Mulai dari Tofflon, Tray Telur dan Tray Vaksin.
              </p>
            </div>
          </div>
        </div>
        <!-- row -->
      </div>
    </div>


    <!-- bagian daftar kartu katalog -->
    <section class="py-5" id="katalog-list"> <!-- ganti/hapus id -->
      <div class="container">
        <div class="row g-4">
          <?php if ($res && $res->num_rows): ?>
            <?php while ($row = $res->fetch_assoc()):
              $img = !empty($row['urlfoto']) ? esc($row['urlfoto']) : 'assets/img/placeholder.jpg';
            ?>
              <div class="col-lg-4 col-md-6">
                <div class="card h-100 shadow-sm border-0">
                  <div class="ratio ratio-4x3">
                    <img src="<?= $img ?>" alt="<?= esc($row['nama_brg']) ?>" class="card-img-top object-fit-cover">
                  </div>
                  <div class="card-body d-flex flex-column">
                    <h5 class="card-title mb-1"><?= esc($row['nama_brg']) ?></h5>
                    <p class="card-text small text-muted flex-grow-1"><?= nl2br(esc($row['deskripsi'])) ?></p>
                    <div class="d-flex align-items-center justify-content-between">
                      <span class="badge bg-dark fs-6"><?= formatRupiah($row['harga']) ?></span>
                      <!-- urutan benar: query dulu, lalu hash -->
                      <a href="index.php?id_brg=<?= (int)$row['id_brg'] ?>#pricing" class="btn btn-primary btn-sm">
                        Pesan
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="col-12">
              <div class="alert alert-warning mb-0">Belum ada data katalog.</div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>
  </section>

  <!-- Start Aturan Pemesanan Area -->
  <div id="aturan-pesan" class="rules-area section">
    <!--======  Start Section Title Five ======-->
    <div class="section-title-five">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="content text-center">
              <h6>Aturan Pemesanan</h6>
              <h2 class="fw-bold">Mohon dibaca sebelum memesan</h2>
              <p>
                Pahami dan ikuti aturan dari CV. Anugerah Presisi sebelum melakukan pemesanan. Anda setuju dengan poin-poin berikut:
              </p>
            </div>
          </div>
        </div>
        <!-- row -->
      </div>
      <!-- container -->
    </div>
    <!--======  End Section Title Five ======-->

    <div class="container">
      <div class="row g-4">
        <!-- Card 1 -->
        <div class="col-lg-4 col-md-6 col-12">
          <div class="rule-card h-100">
            <div class="rule-icon"><i class="bi bi-clipboard-check"></i></div>
            <h4 class="rule-title">DP & Pelunasan</h4>
            <p class="mb-0">
              DP minimal 50% untuk tiap pemesanan. Sisa pembayaran wajib
              <strong>lunas H-1</strong> setelah invoice pelunasan dikirim. DP tidak dapat dikembalikan
              (non-refundable).
            </p>
          </div>
        </div>

        <!-- Card 2 -->
        <div class="col-lg-4 col-md-6 col-12">
          <div class="rule-card h-100">
            <div class="rule-icon"><i class="bi bi-calendar-check"></i></div>
            <h4 class="rule-title">Perubahan Jadwal</h4>
            <p class="mb-0">
              Reschedule pengiriman dapat dilakukan dan mengikuti waktu operasional toko.
              Beritahu maksimal <strong>1 hari</strong> setelah mendapat email pelunasan pembayaran.
            </p>
          </div>
        </div>

        <!-- Card 3 -->
        <div class="col-lg-4 col-md-6 col-12">
          <div class="rule-card h-100">
            <div class="rule-icon"><i class="bi bi bi-truck"></i></div>
            <h4 class="rule-title">Pengiriman</h4>
            <p class="mb-0">
              Pengiriman menggunakan ekspedisi Kargo. Dengan estimasi waktu kedatangan 2-4 hari tergantung daerah
            </p>
          </div>
        </div>

        <!-- Card 4 -->
        <div class="col-lg-4 col-md-6 col-12">
          <div class="rule-card h-100">
            <div class="rule-icon"><i class="bi bi-geo-alt"></i></div>
            <h4 class="rule-title">Lokasi & Transport</h4>
            <p class="mb-0">
              Biaya transport/akomodasi ditanggung pemesan setelah melakukan pelunasan barang.
              Pastikan lokasi mudah diakses & aman untuk peralatan.
            </p>
          </div>
        </div>

        <!-- Card 5 -->
        <div class="col-lg-4 col-md-6 col-12">
          <div class="rule-card h-100">
            <div class="rule-icon"><i class="bi bi-lock"></i></div>
            <h4 class="rule-title">Jumlah Minimal Pembelian</h4>
            <p class="mb-0">
              Batas minimal pembelian barang adalah
              <strong>10pcs</strong>. Kami tidak melayani pembelian di bawah 10pcs.
            </p>
          </div>
        </div>

        <!-- Card 6 -->
        <div class="col-lg-4 col-md-6 col-12">
          <div class="rule-card h-100">
            <div class="rule-icon"><i class="bi bi-shield-check"></i></div>
            <h4 class="rule-title">Asuransi Penuh</h4>
            <p class="mb-0">
              Kami akan menjamin garansi penuh barang yang kami kirimkan, jika mengalami kerusakan maka bisa dilakukan refund penuh
            </p>
          </div>
        </div>

        <!-- Card 7 -->
        <div class="col-lg-4 col-md-6 col-12">
          <div class="rule-card h-100">
            <div class="rule-icon"><i class="bi bi-wrench-adjustable"></i></div>
            <h4 class="rule-title">Hargai Waktu Pengerjaan</h4>
            <p class="mb-0">
              Barang yang kami buat membutuhkan waktu yang optimal agar kualitas terjamin, sehingga mohon ditunggu
            </p>
          </div>
        </div>

        <!-- Card 8 -->
        <div class="col-lg-4 col-md-6 col-12">
          <div class="rule-card h-100">
            <div class="rule-icon"><i class="bi bi-camera"></i></div>
            <h4 class="rule-title">Dokumentasi</h4>
            <p class="mb-0">
              Lakukan video unboxing ketika barang telah tiba. Agar jika tejadi masalah, dapat diajukan pengembalian
              penyewa meminta <em>opt-out</em> saat pemesanan.
            </p>
          </div>
        </div>

        <!-- Card 9 -->
        <div class="col-lg-4 col-md-6 col-12">
          <div class="rule-card h-100">
            <div class="rule-icon"><i class="bi bi-chat-dots"></i></div>
            <h4 class="rule-title">Konsultasi</h4>
            <p class="mb-0">
              Konsultasi terkait produk dan perubahan jumlah pesanan dapat dilakukan selama dalam masa pembuatan
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- End Aturan Pemesanan Area -->

  <!-- Start Pricing  Area -->
  <section id="pricing" class="pricing-area pricing-fourteen">
    <div class="section-title-five">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <div class="content">
              <h6>Pesan</h6>
              <h2 class="fw-bold">Pengisian Data Pembelian</h2>
              <p>Silakan isi formulir di bawah ini untuk melakukan pemesanan barang:</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10 col-12">
          <form id="formJual" action="logic_admin/jual_process.php" method="POST" class="card shadow-sm border-0 p-4">

            <!-- PILIH Barang -->
            <div class="mb-3">
              <label for="id_brg" class="form-label">Pilih Barang</label>
              <select class="form-select form-control-lg" id="id_brg" name="id_brg" required>
                <option value="" disabled <?= $id_brgDipilih ? '' : 'selected' ?>>Pilih Barang yang diinginkan..</option>
                <?php foreach ($katalogList as $r): ?>
                  <option
                    value="<?= (int)$r['id_brg'] ?>"
                    data-harga="<?= (float)$r['harga'] ?>"
                    <?= ($id_brgDipilih === (int)$r['id_brg']) ? 'selected' : '' ?>>
                    <?= esc($r['nama_brg']) ?> - <?= formatRupiah($r['harga']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Harga Satuan (readonly) -->
            <div class="mb-3">
              <label for="harga_satuan_view" class="form-label">Harga Satuan</label>
              <input type="text" class="form-control" id="harga_satuan_view" readonly value="<?= $hargaAsli ? formatRupiah($hargaAsli) : '' ?>">
              <input type="hidden" name="harga_satuan" id="harga_satuan" value="<?= $hargaAsli ?>">
            </div>

            <!-- Jumlah -->
            <div class="mb-3">
              <label for="qty" class="form-label">Jumlah Barang</label>
              <input
                type="number"
                class="form-control"
                id="qty"
                name="qty"
                required
                min="10"
                value="10">
              <div class="form-text">Minimal pemesanan 10 pcs</div>
            </div>

            <!-- Harga Total -->
            <div class="mb-4">
              <label for="harga_total_view" class="form-label">Harga Total</label>
              <input type="text" class="form-control" id="harga_total_view" readonly placeholder="Rp -">
              <input type="hidden" id="harga_total" name="harga_total">
              <div class="form-text">Harga total dihitung otomatis dari jumlah & harga satuan.</div>
            </div>

            <!-- Nama Customer -->
            <div class="mb-3">
              <label for="nama_cust" class="form-label">Nama Pembeli</label>
              <input type="text" class="form-control" id="nama_cust" name="nama_cust" required>
            </div>

            <!-- Email -->
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control" id="email" name="email" required>
            </div>

            <!-- No Telepon -->
            <div class="mb-3">
              <label for="notelp" class="form-label">Nomor Telepon</label>
              <input type="tel" class="form-control" id="notelp" name="notelp" required>
            </div>

            <!-- Alamat -->
            <div class="mb-3">
              <label for="alamat" class="form-label">Alamat Lengkap</label>
              <textarea class="form-control" id="alamat" name="alamat" rows="2" required></textarea>
            </div>

            <!-- Kota -->
            <div class="mb-3">
              <label for="kota" class="form-label">Kota</label>
              <input type="text" class="form-control" id="kota" name="kota" required>
            </div>

            <!-- Tanggal -->
            <div class="mb-3">
              <label for="tgl_beli" class="form-label">Tanggal Pembelian</label>
              <input type="date" class="form-control" id="tgl_beli" name="tgl_beli" required value="<?= date('Y-m-d') ?>">
            </div>

            <!-- Jenis Pembayaran -->
            <div class="mb-4">
              <label for="jenisbayar" class="form-label">Jenis Pembayaran</label>
              <select class="form-select" id="jenisbayar" name="jenisbayar" required>
                <option value="" selected disabled>Pilih jenis pembayaran</option>
                <option value="Full">Bayar Full</option>
                <option value="DP">DP (2 Tahap)</option>
              </select>
            </div>

            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary" id="btnKirim">Kirim Pemesanan</button>
              <a href="#katalog" class="btn btn-outline-secondary">Kembali ke Katalog</a>
            </div>
          </form>

          <!--/ End Pricing  Area -->

          <br>
        </div>


        <br>
        <!-- Start Cta Area -->
        <section id="call-action" class="call-action">
          <div class="container">
            <div class="row justify-content-center">
              <div class="col-xxl-6 col-xl-7 col-lg-8 col-md-9">
                <div class="inner-content">
                  <h2>Kami menyediakan bahan yang berkualitas.</h2>
                  <p>
                    CV. Anugerah Presisi berkomitmen menyediakan bahan berkualitas tinggi sebagai fondasi utama dalam setiap proses produksi.
                    Kami menggunakan material pilihan yang telah melalui proses seleksi dan pengendalian mutu secara ketat, sehingga menghasilkan produk baja yang kuat, presisi, dan tahan lama.
                    Dengan standar kualitas yang konsisten, kami memastikan setiap produk mampu memenuhi kebutuhan industri serta memberikan nilai jangka panjang bagi pelanggan.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </section>
        <!-- End Cta Area -->

        <!-- ========================= contact-section start ========================= -->
        <section id="contact" class="contact-section">
          <div class="container">
            <div class="row">
              <div class="col-xl-4">
                <div class="contact-item-wrapper">
                  <div class="row">
                    <div class="col-12 col-md-6 col-xl-12">
                      <div class="contact-item">
                        <div class="contact-icon">
                          <i class="lni lni-phone"></i>
                        </div>
                        <div class="contact-content">
                          <h4>Kontak</h4>
                          <p>+62 878-8617-0407</p>
                          <p>anugerah_presisi@gmail.com</p>
                        </div>
                      </div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-12">
                      <div class="contact-item">
                        <div class="contact-icon">
                          <i class="lni lni-alarm-clock"></i>
                        </div>
                        <div class="contact-content">
                          <h4>Jadwal Layanan</h4>
                          <p>06.00-20.00 WIB</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-xl-8">
                <div class="contact-form-wrapper">
                  <div class="row">
                    <div class="col-xl-10 col-lg-8 mx-auto">
                      <div class="section-title text-center">
                        <span> Get in Touch </span>
                        <h2>
                          Ready to Get Started
                        </h2>
                        <!-- <p>
                          At vero eos et accusamus et iusto odio dignissimos ducimus
                          quiblanditiis praesentium
                        </p> -->
                      </div>
                    </div>
                  </div>
                  <form action="#" class="contact-form">
                    <div class="row">
                      <div class="col-md-6">
                        <input type="text" name="name" id="name" placeholder="Name" required />
                      </div>
                      <div class="col-md-6">
                        <input type="email" name="email" id="email" placeholder="Email" required />
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <input type="text" name="phone" id="phone" placeholder="Phone" required />
                      </div>
                      <div class="col-md-6">
                        <input type="text" name="subject" id="email" placeholder="Subject" required />
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-12">
                        <textarea name="message" id="message" placeholder="Type Message" rows="5"></textarea>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-12">
                        <div class="button text-center rounded-buttons">
                          <button type="submit" class="btn primary-btn rounded-full">
                            Send Message
                          </button>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </section>
        <!-- ========================= contact-section end ========================= -->

        <!-- ========================= map-section end ========================= -->
        <section class="map-section map-style-9">
          <div class="map-container">
            <iframe
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3964.0517308583558!2d106.80879704339263!3d-6.5151368496517925!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69c3cd1263e429%3A0x74f80e1849d02bfa!2sPuri%20Nirwana%203!5e0!3m2!1sen!2sid!4v1767422410830!5m2!1sen!2sid"
              style="border:0; width:100%; height:500px;"
              allowfullscreen
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade">
            </iframe>

            <p style="margin-top:8px; text-align:center;">
              <a href="https://maps.app.goo.gl/3rEfH8YYLTxGJLN67"
                target="_blank" rel="noopener">
                Buka lokasi di Google Maps
              </a>
            </p>
          </div>
        </section>



        <!-- ========================= map-section end ========================= -->

        <!-- Start Footer Area -->
        <footer class="footer-area footer-eleven">
          <!-- Start Footer Top -->
          <div class="footer-top">
            <div class="container">
              <div class="inner-content">
                <div class="row">
                  <div class="col-lg-4 col-md-6 col-12">
                    <!-- Single Widget -->
                    <div class="footer-widget f-about">
                      <div class="logo">
                        <a href="index.php">
                          <img src="assets/images/logo_presisi.png" alt="#" class="img-fluid" width="140px" />
                        </a>
                      </div>
                      <p>
                        Making the world a better place through constructing elegant
                        hierarchies.
                      </p>
                      <p class="copyright-text">
                        <span>© 2025 CV. Anugerah Presisi</span>
                      </p>
                    </div>
                    <!-- End Single Widget -->
                  </div>
                  <div class="col-lg-2 col-md-6 col-12">
                    <!-- Single Widget -->
                    <div class="footer-widget f-link">
                      <h5>Solutions</h5>
                      <ul>
                        <li><a href="javascript:void(0)">Marketing</a></li>
                        <li><a href="javascript:void(0)">Analytics</a></li>
                        <li><a href="javascript:void(0)">Commerce</a></li>
                        <li><a href="javascript:void(0)">Insights</a></li>
                      </ul>
                    </div>
                    <!-- End Single Widget -->
                  </div>
                  <div class="col-lg-2 col-md-6 col-12">
                    <!-- Single Widget -->
                    <div class="footer-widget f-link">
                      <h5>Support</h5>
                      <ul>
                        <li><a href="javascript:void(0)">Pricing</a></li>
                        <li><a href="javascript:void(0)">Documentation</a></li>
                        <li><a href="javascript:void(0)">Guides</a></li>
                        <li><a href="javascript:void(0)">API Status</a></li>
                      </ul>
                    </div>
                    <!-- End Single Widget -->
                  </div>
                  <div class="col-lg-4 col-md-6 col-12">
                    <!-- Single Widget -->
                    <div class="footer-widget newsletter">
                      <h5>Subscribe</h5>
                      <p>Subscribe to our newsletter for the latest updates</p>
                      <form action="#" method="get" target="_blank" class="newsletter-form">
                        <input name="EMAIL" placeholder="Email address" required="required" type="email" />
                        <div class="button">
                          <button class="sub-btn">
                            <i class="lni lni-envelope"></i>
                          </button>
                        </div>
                      </form>
                    </div>
                    <!-- End Single Widget -->
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!--/ End Footer Top -->
        </footer>
        <!--/ End Footer Area -->



        <script src="https://files.bpcontent.cloud/2025/10/25/06/20251025063605-3G0PLVYY.js" defer></script>


        <a href="#" class="scroll-top btn-hover">
          <i class="lni lni-chevron-up"></i>
        </a>

        <!--====== js ======-->
        <script src="assets/js/bootstrap.bundle.min.js"></script>
        <script src="assets/js/glightbox.min.js"></script>
        <script src="assets/js/main.js"></script>
        <script src="assets/js/tiny-slider.js"></script>

        <script>
          //===== close navbar-collapse when a  clicked
          let navbarTogglerNine = document.querySelector(
            ".navbar-nine .navbar-toggler"
          );
          navbarTogglerNine.addEventListener("click", function() {
            navbarTogglerNine.classList.toggle("active");
          });

          // ==== left sidebar toggle
          let sidebarLeft = document.querySelector(".sidebar-left");
          let overlayLeft = document.querySelector(".overlay-left");
          let sidebarClose = document.querySelector(".sidebar-close .close");

          overlayLeft.addEventListener("click", function() {
            sidebarLeft.classList.toggle("open");
            overlayLeft.classList.toggle("open");
          });
          sidebarClose.addEventListener("click", function() {
            sidebarLeft.classList.remove("open");
            overlayLeft.classList.remove("open");
          });

          // ===== navbar nine sideMenu
          let sideMenuLeftNine = document.querySelector(".navbar-nine .menu-bar");

          sideMenuLeftNine.addEventListener("click", function() {
            sidebarLeft.classList.add("open");
            overlayLeft.classList.add("open");
          });

          //========= glightbox
          GLightbox({
            'href': 'https://www.youtube.com/watch?v=CGoxTMkNbmQ',
            'type': 'video',
            'source': 'youtube', //vimeo, youtube or local
            'width': 900,
            'autoplayVideos': true,
          });
        </script>

        <script src="https://unpkg.com/swiper@11/swiper-bundle.min.js"></script>
        <script>
          new Swiper('.about-swiper', {
            loop: true,
            autoplay: {
              delay: 4000,
              disableOnInteraction: false
            },
            navigation: {
              nextEl: '.about-image-five .swiper-button-next',
              prevEl: '.about-image-five .swiper-button-prev',
            },
            pagination: {
              el: '.about-image-five .swiper-pagination',
              clickable: true
            },
            keyboard: true,
            effect: 'slide', // bisa diganti 'fade','cube','coverflow'
            speed: 600
          });
        </script>

        <script>
          const hargaMap = <?= json_encode(array_column($katalogList, 'harga', 'id_brg'), JSON_UNESCAPED_UNICODE); ?>;

          const selectBarang = document.getElementById('id_brg');
          const qtyInput = document.getElementById('qty');
          const hargaSatuanInput = document.getElementById('harga_satuan');
          const hargaSatuanView = document.getElementById('harga_satuan_view');
          const hargaTotalInput = document.getElementById('harga_total');
          const hargaTotalView = document.getElementById('harga_total_view');

          function formatRupiah(num) {
            return new Intl.NumberFormat('id-ID', {
              style: 'currency',
              currency: 'IDR',
              maximumFractionDigits: 0
            }).format(num || 0);
          }

          function updateHarga() {
            const id = selectBarang.value;
            let qty = parseInt(qtyInput.value || '10', 10);

            // paksa minimal 10
            if (qty < 10) {
              qty = 10;
              qtyInput.value = 10;
            }

            const hargaSatuan = parseFloat(hargaMap[id] || 0);
            const total = hargaSatuan * qty;

            hargaSatuanInput.value = hargaSatuan;
            hargaSatuanView.value = formatRupiah(hargaSatuan);
            hargaTotalInput.value = total;
            hargaTotalView.value = formatRupiah(total);
          }

          // Inisialisasi
          updateHarga();

          // Event listener
          selectBarang.addEventListener('change', updateHarga);
          qtyInput.addEventListener('input', updateHarga);
        </script>


        <script>
          document.getElementById('formJual')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.currentTarget;

            const btn = form.querySelector('#btnKirim') || form.querySelector('[type=submit]');
            const old = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Mengirim...';

            try {
              const res = await fetch('logic_admin/jual_process.php?ajax=1', {
                method: 'POST',
                body: new FormData(form),
                headers: {
                  'Accept': 'application/json'
                }
              });

              // Jangan .json() langsung; aman-kan dulu:
              const text = await res.text();
              let out;
              try {
                out = JSON.parse(text);
              } catch {
                throw new Error('Server mengirim respons non-JSON: ' + text.slice(0, 200));
              }

              if (!res.ok || !out.ok) throw new Error(out.message || 'Gagal menyimpan pesanan.');

              await Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Pesanan tersimpan!',
                timer: 1600,
                showConfirmButton: false
              });
              form.reset();

            } catch (err) {
              Swal.fire({
                icon: 'error',
                title: 'Gagal menyimpan',
                text: String(err.message || err)
              });
            } finally {
              btn.disabled = false;
              btn.textContent = old;
            }
          });
        </script>

</body>

</html>