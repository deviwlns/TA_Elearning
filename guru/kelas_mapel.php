<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'guru') {
    header("Location: ../index.html");
    exit;
}
include '../db.php';

$guru_id = $_SESSION['user']['id'];

// Ambil daftar kelas dan mata pelajaran yang diampu oleh guru
$stmt_combined = $pdo->prepare("
    SELECT k.nama_kelas, s.nama_pelajaran 
    FROM guru_kelas_pelajaran gkp
    JOIN kelas k ON gkp.kelas_id = k.id
    JOIN subjects s ON gkp.pelajaran_id = s.id
    WHERE gkp.guru_id = ?
");
$stmt_combined->execute([$guru_id]);
$combined_list = $stmt_combined->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kelas & Mata Pelajaran - E-Learning</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #0f0f1c;
            color: #f8f9fa;
        }
        .main-content {
            padding: 30px;
        }
        table {
            color: #ccc;
        }
        table th {
            background-color: #212529;
            color: #fff;
        }
        .card {
            background-color: #161a2b;
            border: none;
        }
        .btn-back {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container main-content">
    <a href="dashboard.php" class="btn btn-outline-primary btn-back">&laquo; Kembali ke Dashboard</a>
    
    <div class="card shadow-sm">
        <div class="card-header fw-bold fs-5 bg-light border-0">📚 Kelas & Mata Pelajaran yang Diampu</div>
        <div class="card-body">
            <?php if ($combined_list): ?>
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kelas</th>
                            <th>Mata Pelajaran</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($combined_list as $no => $item): ?>
                            <tr>
                                <td><?= $no + 1 ?></td>
                                <td><?= htmlspecialchars($item['nama_kelas']) ?></td>
                                <td><?= htmlspecialchars($item['nama_pelajaran']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">Belum ada kelas atau mata pelajaran yang diampu.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>