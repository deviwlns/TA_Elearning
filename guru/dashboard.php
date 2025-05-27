<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'guru') {
    header("Location: ../index.html");
    exit;
}
include '../db.php';
$guru_id = $_SESSION['user']['id'];

// Ambil daftar kelas yang diampu oleh guru
$stmt_kelas = $pdo->prepare("
    SELECT k.nama_kelas 
    FROM kelas k
    JOIN guru_kelas_pelajaran gkp ON k.id = gkp.kelas_id
    WHERE gkp.guru_id = ?
");
$stmt_kelas->execute([$guru_id]);
$kelas_list = $stmt_kelas->fetchAll(PDO::FETCH_ASSOC);

// Ambil daftar pelajaran yang diajarkan
$stmt_pelajaran = $pdo->prepare("
    SELECT s.nama_pelajaran 
    FROM subjects s
    JOIN guru_kelas_pelajaran gkp ON s.id = gkp.pelajaran_id
    WHERE gkp.guru_id = ?
");
$stmt_pelajaran->execute([$guru_id]);
$pelajaran_list = $stmt_pelajaran->fetchAll(PDO::FETCH_ASSOC);

// Hitung jumlah materi yang diupload
$stmt_materi = $pdo->prepare("SELECT COUNT(*) FROM materi WHERE id_guru = ?");
$stmt_materi->execute([$guru_id]);
$total_materi = $stmt_materi->fetchColumn();

// Hitung jumlah tugas yang dibuat
$stmt_tugas = $pdo->prepare("SELECT COUNT(*) FROM tugas WHERE id_guru = ?");
$stmt_tugas->execute([$guru_id]);
$total_tugas = $stmt_tugas->fetchColumn();

// Ambil semua tugas yang dibuat oleh guru ini
$stmt_tugas = $pdo->prepare("
    SELECT t.id_tugas, t.judul AS judul_tugas, k.nama_kelas 
    FROM tugas t
    JOIN kelas k ON t.id_kelas = k.id
    WHERE t.id_guru = ?
");
$stmt_tugas->execute([$guru_id]);
$tugas_list = $stmt_tugas->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Guru - E-Learning</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css " rel="stylesheet">
    <style>
        /* Dark Mode */
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #0f0f1c;
            color: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(145deg, #1a1a2e, #16213e);
            color: white;
            padding: 20px;
        }
        .sidebar h4 {
            text-align: center;
            font-weight: bold;
            margin-bottom: 2rem;
        }
        .nav-link {
            border-radius: 10px;
            transition: all 0.3s ease;
            color: #dcdcdc;
        }
        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }
        .nav-link.active {
            background-color: #4e73df;
            color: white !important;
        }
        .main-content {
            background-color: #161a2b;
            padding: 30px;
            min-height: 100vh;
        }
        .card-clickable {
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: none;
        }
        .card-clickable:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 25px rgba(0, 0, 255, 0.2);
        }
        .icon-bg {
            font-size: 2rem;
            border-radius: 50%;
            padding: 15px;
            display: inline-block;
            transition: background 0.3s ease;
        }
        .bg-info {
            background-color: #36b9cc !important;
        }
        .bg-success {
            background-color: #1cc88a !important;
        }
        .bg-warning {
            background-color: #f6c23e !important;
        }
        .bg-secondary {
            background-color: #858796 !important;
        }

        /* Welcome Message */
        .welcome-box {
            background: rgba(255, 255, 255, 0.05);
            border-left: 4px solid #4e73df;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(78, 115, 223, 0.2);
            color: #e9ecef;
            position: relative;
            overflow: hidden;
        }
        .welcome-box::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background-image: linear-gradient(45deg, rgba(255,255,255,0.05) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.05) 50%, rgba(255,255,255,0.05) 75%, transparent 75%, transparent);
            background-size: 50px 50px;
            animation: shine 8s infinite linear;
            z-index: 0;
        }
        .welcome-box > * {
            position: relative;
            z-index: 1;
        }
        @keyframes shine {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Card Putih */
        .card-white {
            background-color: #ffffff !important;
            color: #000;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        .card-white .card-header {
            font-weight: bold;
            border-bottom: 1px solid #ddd;
        }
        .card-white .list-group-item {
            background-color: #fff;
            border: none;
        }

        /* Background Animation */
        #particles-js {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: -1;
        }
        table {
            color: #ccc;
        }
        table th {
            background-color: #212529;
            color: #fff;
        }
        .form-select {
            background-color: #212529;
            color: #fff;
            border: 1px solid #444;
        }
        .btn-info {
            background-color: #36b9cc;
            border: none;
        }
        .btn-info:hover {
            background-color: #2aa5b6;
        }
    </style>
</head>
<body>

<!-- Particles Background -->
<div id="particles-js"></div>

<div class="d-flex">
    <!-- Sidebar -->
    <nav class="sidebar">
        <h4>E-Learning Guru</h4>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item"><a href="dashboard.php" class="nav-link active"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
            <li class="nav-item"><a href="upload_materi.php" class="nav-link"><i class="bi bi-file-earmark-arrow-up me-2"></i>Upload Materi</a></li>
            <li class="nav-item"><a href="list_materi.php" class="nav-link"><i class="bi bi-journal-text me-2"></i>Daftar Materi</a></li>
            <li class="nav-item"><a href="upload_tugas.php" class="nav-link"><i class="bi bi-file-earmark-plus me-2"></i>Tambah Tugas</a></li>
            <li class="nav-item"><a href="list_tugas.php" class="nav-link"><i class="bi bi-list-task me-2"></i>Daftar Tugas</a></li>
            <li class="nav-item mt-auto">
                <a href="../logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="main-content animate__animated animate__fadeInLeft">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="animate__animated animate__bounceIn">Selamat Datang, <?= htmlspecialchars($_SESSION['user']['username']) ?> 👋</h2>
            <span class="badge bg-primary fs-6"><?= ucfirst($_SESSION['user']['role']) ?></span>
        </div>

        <!-- Stat Cards -->
        <div class="row g-4 mb-4">
            <!-- Kelas yang Diampu -->
            <div class="col-md-3">
                <a href="kelas_mapel.php" class="text-decoration-none card-clickable animate__animated animate__zoomIn animate__delay-1s">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-info icon-bg text-white me-3">
                                <i class="bi bi-building"></i>
                            </div>
                            <div>
                                <p class="mb-0 small text-muted">Kelas Diampu</p>
                                <h5 class="mb-0"><?= count($kelas_list) ?></h5>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Pelajaran yang Diajar -->
            <div class="col-md-3">
                <a href="kelas_mapel.php" class="text-decoration-none card-clickable animate__animated animate__zoomIn animate__delay-2s">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-success icon-bg text-white me-3">
                                <i class="bi bi-book-half"></i>
                            </div>
                            <div>
                                <p class="mb-0 small text-muted">Pelajaran</p>
                                <h5 class="mb-0"><?= count($pelajaran_list) ?></h5>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Materi Diupload -->
            <div class="col-md-3">
                <a href="list_materi.php" class="text-decoration-none card-clickable animate__animated animate__zoomIn animate__delay-3s">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-warning icon-bg text-white me-3">
                                <i class="bi bi-journal-arrow-down"></i>
                            </div>
                            <div>
                                <p class="mb-0 small text-muted">Materi Diupload</p>
                                <h5 class="mb-0"><?= $total_materi ?></h5>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Tugas Dikirim -->
            <div class="col-md-3">
                <a href="list_tugas.php" class="text-decoration-none card-clickable animate__animated animate__zoomIn animate__delay-4s">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-secondary icon-bg text-white me-3">
                                <i class="bi bi-journal-check"></i>
                            </div>
                            <div>
                                <p class="mb-0 small text-muted">Tugas Dikirim</p>
                                <h5 class="mb-0"><?= $total_tugas ?></h5>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Welcome Box -->
        <div class="mt-4 welcome-box">
            <h5 class="mb-2">👋 Hai, <?= htmlspecialchars($_SESSION['user']['username']) ?></h5>
            <p class="mb-0">
                Anda login sebagai 
                <strong class="text-primary"><?= ucfirst($_SESSION['user']['role']) ?></strong>. 
                Gunakan panel ini untuk mengelola sistem e-learning kelas Anda.
            </p>
        </div>

        <!-- Daftar Kelas & Mata Pelajaran -->
        <div class="row mt-4">
            <!-- Kelas Yang Diampu -->
           

        <!-- Rekap Pengumpulan Tugas -->
        <div class="col-md-12 mt-4">
            <div class="card shadow-sm border-0 animate__animated animate__fadeInUp animate__delay-7s">
                <div class="card-header fw-bold bg-transparent border-0">📚 Rekap Pengumpulan Tugas</div>
                <div class="card-body">
                    <?php if ($tugas_list): ?>
                        <form method="GET" class="mb-3">
                            <label for="id_tugas" class="form-label">Pilih Tugas</label>
                            <select name="id_tugas" class="form-select" required onchange="this.form.submit()">
                                <option value="">-- Pilih Tugas --</option>
                                <?php foreach ($tugas_list as $t): ?>
                                    <option value="<?= $t['id_tugas'] ?>" <?= (isset($_GET['id_tugas']) && $_GET['id_tugas'] == $t['id_tugas']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($t['judul_tugas']) ?> - <?= htmlspecialchars($t['nama_kelas']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>

                        <?php if (isset($_GET['id_tugas'])): ?>
                            <?php
                            $id_tugas = $_GET['id_tugas'];

                            // Ambil detail tugas
                            $stmt_detail = $pdo->prepare("SELECT * FROM tugas WHERE id_tugas = ?");
                            $stmt_detail->execute([$id_tugas]);
                            $tugas = $stmt_detail->fetch(PDO::FETCH_ASSOC);

                            // Ambil semua siswa di kelas tersebut
                            $stmt_siswa = $pdo->prepare("
                                SELECT u.id, u.username 
                                FROM users u
                                JOIN kelas_siswa ks ON u.id = ks.id_siswa 
                                WHERE ks.id_kelas = ?
                            ");
                            $stmt_siswa->execute([$tugas['id_kelas']]);
                            $siswa_list = $stmt_siswa->fetchAll(PDO::FETCH_ASSOC);

                            // Ambil siapa saja yang sudah upload jawaban
                            $stmt_upload = $pdo->prepare("SELECT id_siswa FROM tugas_siswa WHERE id_tugas = ?");
                            $stmt_upload->execute([$id_tugas]);
                            $upload_list = $stmt_upload->fetchAll(PDO::FETCH_COLUMN, 0);
                            ?>
                            <h6>Tugas: <?= htmlspecialchars($tugas['judul']) ?></h6>
                            <p>Kelas: <?= htmlspecialchars($tugas['id_kelas']) ?></p>

                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Siswa</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($siswa_list as $no => $s): ?>
                                        <tr>
                                            <td><?= ++$no ?></td>
                                            <td><?= htmlspecialchars($s['username']) ?></td>
                                            <td>
                                                <?php if (in_array($s['id'], $upload_list)): ?>
                                                    <span class="badge bg-success">Sudah Upload</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Belum Upload</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (in_array($s['id'], $upload_list)): ?>
                                                    <?php
                                                        $stmt_file = $pdo->prepare("SELECT file_path FROM tugas_siswa WHERE id_tugas = ? AND id_siswa = ?");
                                                        $stmt_file->execute([$id_tugas, $s['id']]);
                                                        $file = $stmt_file->fetchColumn();
                                                    ?>
                                                    <a href="<?= htmlspecialchars($file) ?>" target="_blank" class="btn btn-sm btn-info">Download Jawaban</a>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="text-muted">Silakan pilih tugas untuk melihat rekap.</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-muted">Belum ada tugas yang dibuat.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Particles JS -->
<script src="https://cdn.jsdelivr.net/npm/particles.js @2.0.0/particles.min.js"></script>
<script>
    particlesJS("particles-js", {
        "particles": {
            "number": {"value": 80, "density": {"enable": true, "value_area": 800}},
            "color": {"value": "#ffffff"},
            "shape": {"type": "circle"},
            "opacity": {"value": 0.5},
            "size": {"value": 3, "random": true},
            "line_linked": {"enable": true, "distance": 150, "color": "#ffffff", "opacity": 0.4, "width": 1},
            "move": {"enable": true, "speed": 1.5}
        },
        "interactivity": {
            "detect_on": "canvas",
            "events": {
                "onhover": {"enable": true, "mode": "grab"},
                "onclick": {"enable": true, "mode": "push"}
            }
        },
        "retina_detect": true
    });
</script>

<!-- Bootstrap Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>