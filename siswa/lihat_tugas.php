<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: ../login.php");
    exit;
}

include '../db.php';

$siswa_id = $_SESSION['user']['id'];

// Ambil kelas siswa
$stmt_kelas = $pdo->prepare("
    SELECT ks.id_kelas, k.nama_kelas 
    FROM kelas_siswa ks
    JOIN kelas k ON ks.id_kelas = k.id
    WHERE ks.id_siswa = ?
");
$stmt_kelas->execute([$siswa_id]);
$kelas_data = $stmt_kelas->fetch(PDO::FETCH_ASSOC);

if (!$kelas_data) {
    die("Anda belum tergabung dalam kelas apapun.");
}
$kelas_id = $kelas_data['id_kelas'];

// Ambil semua tugas berdasarkan kelas dan pelajaran
$stmt_tugas = $pdo->prepare("
    SELECT 
        t.id_tugas,
        t.judul AS judul_tugas,
        t.deadline,
        s.nama_pelajaran,
        t.file_path AS file_tugas, -- File tugas dari guru
        ts.file_path AS file_jawaban,
        ts.submitted_at
    FROM tugas t
    JOIN subjects s ON t.id_pelajaran = s.id
    LEFT JOIN tugas_siswa ts 
        ON t.id_tugas = ts.id_tugas AND ts.id_siswa = ?
    WHERE t.id_kelas = ?
    ORDER BY t.deadline ASC
");

$stmt_tugas->execute([$siswa_id, $kelas_id]);
$tugas_list = $stmt_tugas->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daftar Tugas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card-task { border-left: 5px solid #0d6efd; transition: transform 0.2s; }
        .card-task:hover { transform: scale(1.01); }
    </style>
</head>
<body>

<div class="container mt-5">
    <h2>📋 Daftar Tugas Anda</h2>
    <p>Kelas: <strong><?= htmlspecialchars($kelas_data['nama_kelas']) ?></strong></p>

    <?php if (empty($tugas_list)): ?>
        <div class="alert alert-info">Belum ada tugas dari guru.</div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($tugas_list as $tugas): 

                $deadline = is_null($tugas['deadline']) ? '-' : date('d M Y H:i', strtotime($tugas['deadline']));
                
                if (!empty($tugas['file_jawaban'])) {
                    $status_badge = '<span class="badge bg-primary">Dikumpulkan</span>';
                    $jawaban_url = htmlspecialchars($tugas['file_jawaban']);
                } else {
                    $status_badge = '<span class="badge bg-warning text-dark">Belum Dikumpulkan</span>';
                    $jawaban_url = '#';
                }

            ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card card-task p-3 shadow-sm">
                        <h5><?= htmlspecialchars($tugas['judul_tugas']) ?></h5>
                        <p class="text-muted"><?= htmlspecialchars($tugas['nama_pelajaran']) ?></p>
                        <p><strong>Deadline:</strong> <?= $deadline ?></p>
                        <p>Status: <?= $status_badge ?></p>

                        <!-- File tugas dari guru -->
                        <?php if (!empty($tugas['file_tugas'])): ?>
                            <a href="<?= htmlspecialchars($tugas['file_tugas']) ?>" target="_blank" class="btn btn-sm btn-outline-primary mb-2">
                                📁 Lihat File Tugas
                            </a>
                        <?php else: ?>
                            <p class="text-muted mb-2">Tidak ada file tugas dari guru.</p>
                        <?php endif; ?>

                        <!-- Upload jawaban atau lihat jawaban -->
                        <?php if (!empty($tugas['file_jawaban'])): ?>
                            <a href="<?= $jawaban_url ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                📄 Lihat Jawaban
                            </a>
                        <?php else: ?>
                            <a href="upload_tugas.php?id=<?= $tugas['id_tugas'] ?>" class="btn btn-sm btn-success">
                                ➕ Upload Jawaban
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <a href="dashboard.php" class="btn btn-secondary mt-4">⬅️ Kembali</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>