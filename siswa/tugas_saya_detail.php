<?php
session_start();
include '../db.php';

if ($_SESSION['user']['role'] !== 'siswa') {
    header("Location: ../login.php");
    exit;
}

$submit_id = $_GET['id'];
$siswa_id = $_SESSION['user']['id'];

// Ambil data tugas siswa beserta nilai
$stmt = $pdo->prepare("
    SELECT sa.id, sa.file_path, sa.nilai, sa.is_checked, a.judul
    FROM submitted_assignments sa
    JOIN assignments a ON sa.assignment_id = a.id
    WHERE sa.id = ? AND sa.siswa_id = ?
");
$stmt->execute([$submit_id, $siswa_id]);
$tugas = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tugas) {
    die("Tugas tidak ditemukan atau Anda tidak berhak mengakses.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Detail Tugas Saya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h3>Detail Tugas: <?= htmlspecialchars($tugas['judul']) ?></h3>
    <p>File: <a href="../uploads/<?= $tugas['file_path'] ?>" target="_blank">Download Jawaban</a></p>
    <p>Status: <?= $tugas['is_checked'] ? '<span class="badge bg-success">Dinilai</span>' : '<span class="badge bg-warning">Belum Dinilai</span>' ?></p>
    <p>Nilai: <strong><?= $tugas['nilai'] ?? '--' ?></strong></p>

    <a href="dashboard_siswa.php" class="btn btn-primary mt-3">Kembali</a>
</div>
</body>
</html>