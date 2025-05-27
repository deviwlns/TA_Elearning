<?php
session_start();
include '../db.php';

if ($_SESSION['user']['role'] !== 'siswa') {
    header("Location: ../login.php");
    exit;
}

$assignment_id = $_GET['id'];
$siswa_id = $_SESSION['user']['id'];

$stmt = $pdo->prepare("
    SELECT sa.*, a.judul
    FROM submitted_assignments sa
    JOIN assignments a ON sa.assignment_id = a.id
    WHERE sa.assignment_id = ? AND sa.siswa_id = ?
");
$stmt->execute([$assignment_id, $siswa_id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    die("Data tidak ditemukan.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Detail Tugas - <?= htmlspecialchars($data['judul']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h3><?= htmlspecialchars($data['judul']) ?></h3>
    <p>File: <a href="../uploads/<?= $data['file_path'] ?>" target="_blank">Download Jawaban</a></p>
    <p>Diupload pada: <?= date('d M Y H:i', strtotime($data['submitted_at'])) ?></p>
    <p>Status: <?= $data['is_checked'] ? '<span class="badge bg-success">Dinilai</span>' : '<span class="badge bg-secondary">Belum Dinilai</span>' ?></p>
    <p>Nilai: <strong><?= $data['nilai'] !== null ? number_format($data['nilai'], 2) : '--' ?></strong></p>

    <a href="dashboard_siswa.php" class="btn btn-primary mt-3">Kembali ke Dashboard</a>
</div>
</body>
</html>