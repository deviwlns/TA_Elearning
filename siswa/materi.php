<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: ../login.php");
    exit;
}

include '../db.php';

$siswa_id = $_SESSION['user']['id'];

// Ambil data kelas siswa
$stmt_kelas = $pdo->prepare("
    SELECT k.id, k.nama_kelas 
    FROM kelas_siswa ks
    JOIN kelas k ON ks.id_kelas = k.id
    WHERE ks.id_siswa = ?
");

$stmt_kelas->execute([$siswa_id]);
$kelas_data = $stmt_kelas->fetch(PDO::FETCH_ASSOC);

$materi_list = [];
$error = '';

if (!$kelas_data) {
    $error = "Anda belum tergabung dalam kelas apapun.";
} else {
    // Ambil materi berdasarkan kelas
    $kelas_id = $kelas_data['id'];
    $stmt_materi = $pdo->prepare("
        SELECT m.id_materi, m.judul, m.file_path, m.created_at, u.username AS nama_guru, s.nama_pelajaran
        FROM materi m
        JOIN users u ON m.id_guru = u.id
        JOIN subjects s ON m.id_pelajaran = s.id
        WHERE m.id_kelas = ?
        ORDER BY m.created_at DESC
    ");
    $stmt_materi->execute([$kelas_id]);
    $materi_list = $stmt_materi->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daftar Materi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .card-subjek {
            transition: transform 0.2s ease;
        }
        .card-subjek:hover {
            transform: scale(1.02);
        }
        .table a {
            text-decoration: none;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container-fluid">
        <span class="navbar-brand">E-Learning - <?= ucfirst($_SESSION['user']['role']) ?></span>
        <div class="ms-auto">
            <a href="dashboard.php" class="btn btn-light me-2">Dashboard</a>
            <a href="../logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <?php if ($error): ?>
        <div class="alert alert-warning"><?= $error ?></div>
    <?php else: ?>
        <h4>Materi Pelajaran untuk Kelas <?= htmlspecialchars($kelas_data['nama_kelas']) ?></h4>

        <?php if ($materi_list && count($materi_list) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle bg-white shadow-sm rounded">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Judul Materi</th>
                            <th>Mata Pelajaran</th>
                            <th>Guru Pengajar</th>
                            <th>Tanggal Upload</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($materi_list as $key => $materi): ?>
                            <tr>
                                <td><?= $key + 1 ?></td>
                                <td><?= htmlspecialchars($materi['judul']) ?></td>
                                <td><?= htmlspecialchars($materi['nama_pelajaran']) ?></td>
                                <td><?= htmlspecialchars($materi['nama_guru']) ?></td>
                                <td><?= date('d M Y', strtotime($materi['created_at'])) ?></td>
                                <td class="text-center">
                                    <?php if (!empty($materi['file_path']) && file_exists($materi['file_path'])): ?>
                                        <a href="<?= $materi['file_path'] ?>" class="btn btn-success btn-sm" target="_blank">
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                    <?php else: ?>
                                        <span class="small text-muted">File tidak ditemukan</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">Belum ada materi untuk kelas ini.</div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>