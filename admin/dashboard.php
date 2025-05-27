<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.html");
    exit;
}
include '../db.php';

// Hitung jumlah user berdasarkan role
$stmt = $pdo->query("SELECT role, COUNT(*) AS total FROM users GROUP BY role");
$user_stats = [];
foreach ($stmt as $row) {
    $user_stats[$row['role']] = $row['total'];
}

// Hitung jumlah mata pelajaran
$stmt = $pdo->query("SELECT COUNT(*) FROM subjects");
$total_subjects = $stmt->fetchColumn();

// Hitung jumlah kelas
$stmt = $pdo->query("SELECT COUNT(*) FROM kelas");
$total_kelas = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - E-Learning</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">

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

        .bg-primary {
            background-color: #4e73df !important;
        }

        .bg-success {
            background-color: #1cc88a !important;
        }

        .bg-warning {
            background-color: #f6c23e !important;
        }

        .bg-info {
            background-color: #36b9cc !important;
        }

        .badge {
            font-size: 1rem;
        }

        /* Welcome Message dengan animasi dan efek glowing */
        .welcome-box {
            background: rgba(255, 255, 255, 0.05);
            border-left: 4px solid #4e73df;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(78, 115, 223, 0.2);
            color: #e9ecef;
            position: relative;
            overflow: hidden;
            animation: fadeInUp 1s ease-in-out;
        }

        .welcome-box::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at center, rgba(78, 115, 223, 0.15), transparent 60%);
            animation: shine 8s infinite linear;
            z-index: 0;
        }

        .welcome-box > * {
            position: relative;
            z-index: 1;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes shine {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
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
    </style>
</head>
<body>

<!-- Particles Background -->
<div id="particles-js"></div>

<div class="d-flex">
    <!-- Sidebar -->
    <nav class="sidebar">
        <h4>E-Learning Admin</h4>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item"><a href="dashboard.php" class="nav-link active"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
            <li class="nav-item"><a href="users.php" class="nav-link"><i class="bi bi-people me-2"></i>Kelola Pengguna</a></li>
            <li class="nav-item"><a href="guru_kelas_pelajaran.php" class="nav-link"><i class="bi bi-person-video3 me-2"></i>Guru & Kelas</a></li>
            <li class="nav-item"><a href="kelas_siswa.php" class="nav-link"><i class="bi bi-people-fill me-2"></i>Siswa di Kelas</a></li>
            <li class="nav-item"><a href="subjects.php" class="nav-link"><i class="bi bi-book me-2"></i>Mata Pelajaran</a></li>
            <li class="nav-item"><a href="kelas.php" class="nav-link"><i class="bi bi-building me-2"></i>Kelas</a></li>
        </ul>
        <hr>
        <a href="../logout.php" class="nav-link text-danger mt-auto"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
    </nav>

    <!-- Main Content -->
    <div class="main-content animate__animated animate__fadeInLeft">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="animate__animated animate__bounceIn">Selamat Datang, <?= htmlspecialchars($_SESSION['user']['username']) ?> 👋</h2>
            <span class="badge bg-primary fs-6"><?= ucfirst($_SESSION['user']['role']) ?></span>
        </div>

        <!-- Stat Cards -->
        <div class="row g-4">
            <!-- Siswa -->
            <div class="col-md-3">
                <a href="users.php?role=siswa" class="text-decoration-none card-clickable animate__animated animate__zoomIn animate__delay-1s">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-primary icon-bg text-white me-3">
                                <i class="bi bi-person-lines-fill"></i>
                            </div>
                            <div>
                                <p class="mb-0 small text-muted">Siswa</p>
                                <h5 class="mb-0"><?= $user_stats['siswa'] ?? 0 ?></h5>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Guru -->
            <div class="col-md-3">
                <a href="users.php?role=guru" class="text-decoration-none card-clickable animate__animated animate__zoomIn animate__delay-2s">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-success icon-bg text-white me-3">
                                <i class="bi bi-person-badge"></i>
                            </div>
                            <div>
                                <p class="mb-0 small text-muted">Guru</p>
                                <h5 class="mb-0"><?= $user_stats['guru'] ?? 0 ?></h5>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Mata Pelajaran -->
            <div class="col-md-3">
                <a href="subjects.php" class="text-decoration-none card-clickable animate__animated animate__zoomIn animate__delay-3s">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-warning icon-bg text-white me-3">
                                <i class="bi bi-book-half"></i>
                            </div>
                            <div>
                                <p class="mb-0 small text-muted">Mata Pelajaran</p>
                                <h5 class="mb-0"><?= $total_subjects ?></h5>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Kelas -->
            <div class="col-md-3">
                <a href="kelas.php" class="text-decoration-none card-clickable animate__animated animate__zoomIn animate__delay-4s">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-info icon-bg text-white me-3">
                                <i class="bi bi-building"></i>
                            </div>
                            <div>
                                <p class="mb-0 small text-muted">Kelas</p>
                                <h5 class="mb-0"><?= $total_kelas ?></h5>
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
                Gunakan panel ini untuk mengelola sistem e-learning Anda.
            </p>
        </div>
    </div>
</div>

<!-- Particles JS -->
<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
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