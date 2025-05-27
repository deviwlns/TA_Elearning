<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'guru') {
    header("Location: ../login.php");
    exit;
}

include '../db.php';

$guru_id = $_SESSION['user']['id'];

// Ambil semua tugas yang diupload oleh guru ini
$stmt = $pdo->prepare("SELECT * FROM tugas WHERE id_guru = ? ORDER BY created_at DESC");
$stmt->execute([$guru_id]);
$tugas_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hapus tugas jika ada permintaan
if (isset($_GET['delete'])) {
    $id_tugas = (int)$_GET['delete'];
    
    $stmt = $pdo->prepare("SELECT file_path FROM tugas WHERE id_tugas = ? AND id_guru = ?");
    $stmt->execute([$id_tugas, $guru_id]);
    $tugas = $stmt->fetch();

    if ($tugas) {
        if (file_exists($tugas['file_path'])) {
            unlink($tugas['file_path']);
        }

        // Hapus relasi dulu
        $pdo->prepare("DELETE FROM tugas_siswa WHERE id_tugas = ?")->execute([$id_tugas]);

        // Baru hapus tugas
        $pdo->prepare("DELETE FROM tugas WHERE id_tugas = ?")->execute([$id_tugas]);

        header("Location: list_tugas.php");
        exit;
    } else {
        echo "Tugas tidak ditemukan.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daftar Tugas Saya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2>📘 Daftar Tugas Yang Diupload</h2>
    <p>Halo, <?= htmlspecialchars($_SESSION['user']['username']) ?>! Berikut adalah daftar tugas yang telah Anda upload:</p>

    <?php if ($tugas_list): ?>
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>Judul</th>
                    <th>Kelas</th>
                    <th>Mata Pelajaran</th>
                    <th>Deadline</th>
                    <th>Tanggal Upload</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tugas_list as $tugas): ?>
                    <tr>
                        <td><?= htmlspecialchars($tugas['judul']) ?></td>
                        <td>
                            <?php
                            $stmt_kelas = $pdo->prepare("SELECT nama_kelas FROM kelas WHERE id = ?");
                            $stmt_kelas->execute([$tugas['id_kelas']]);
                            echo $stmt_kelas->fetchColumn() ?: "Tidak Diketahui";
                            ?>
                        </td>
                        <td>
                            <?php
                            $stmt_pelajaran = $pdo->prepare("SELECT nama_pelajaran FROM subjects WHERE id = ?");
                            $stmt_pelajaran->execute([$tugas['id_pelajaran']]);
                            echo $stmt_pelajaran->fetchColumn() ?: "Tidak Diketahui";
                            ?>
                        </td>
                        <td><?= $tugas['deadline'] ? date('d M Y H:i', strtotime($tugas['deadline'])) : '-' ?></td>
                        <td><?= htmlspecialchars(date('d M Y H:i', strtotime($tugas['created_at']))) ?></td>
                        <td class="text-center">
                            <!-- Download -->
                            <?php if (!empty($tugas['file_path']) && file_exists($tugas['file_path'])): ?>
                                <a href="<?= htmlspecialchars($tugas['file_path']) ?>" class="btn btn-sm btn-success me-1" target="_blank">
                                    <i class="bi bi-download me-1"></i>Download
                                </a>
                            <?php else: ?>
                                <span class="badge bg-danger">File Hilang</span>
                            <?php endif; ?>

                            <!-- Edit -->
                            <a href="edit_tugas.php?id=<?= $tugas['id_tugas'] ?>" class="btn btn-sm btn-primary me-1">
                                <i class="bi bi-pencil-square me-1"></i>Edit
                            </a>

                            <!-- Delete -->
                            <a href="list_tugas.php?delete=<?= $tugas['id_tugas'] ?>" class="btn btn-sm btn-danger me-1" onclick="return confirm('Yakin ingin menghapus tugas ini?')">
                                <i class="bi bi-trash3"></i>Hapus
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">Belum ada tugas yang diupload.</div>
    <?php endif; ?>

    <a href="dashboard.php" class="btn btn-outline-secondary me-2">
        <i class="bi bi-arrow-left-circle me-1"></i>Kembali ke Dashboard
    </a>
    <a href="upload_tugas.php" class="btn btn-primary">
        <i class="bi bi-upload me-1"></i>Upload Tugas Baru
    </a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap @5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>