<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'guru') {
    header("Location: ../login.php");
    exit;
}

include '../db.php';

$guru_id = $_SESSION['user']['id'];

// Ambil semua materi yang diupload oleh guru ini
$stmt = $pdo->prepare("SELECT * FROM materi WHERE id_guru = ? ORDER BY created_at DESC");
$stmt->execute([$guru_id]);
$materi_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['delete'])) {
    $id_materi = (int)$_GET['delete'];
    
    $stmt = $pdo->prepare("SELECT file_path FROM materi WHERE id_materi = ? AND id_guru = ?");
    $stmt->execute([$id_materi, $guru_id]);
    $materi = $stmt->fetch();

    if ($materi) {
        if (file_exists($materi['file_path'])) {
            unlink($materi['file_path']);
        }

        // Hapus relasi dulu
        $pdo->prepare("DELETE FROM tugas_siswa WHERE id_materi = ?")->execute([$id_materi]);

        // Baru hapus materi
        $pdo->prepare("DELETE FROM materi WHERE id_materi = ?")->execute([$id_materi]);

        header("Location: list_materi.php");
        exit;
    } else {
        echo "";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daftar Materi Saya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>

<!-- Navbar atau Sidebar -->
<div class="container mt-5">
    <h2>📚 Daftar Materi Yang Diupload</h2>
    <p>Halo, <?= htmlspecialchars($_SESSION['user']['username']) ?>! Berikut adalah daftar materi yang telah Anda upload:</p>

    <?php if ($materi_list): ?>
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>Judul</th>
                    <th>Kelas</th>
                    <th>Mata Pelajaran</th>
                    <th>Tanggal Upload</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($materi_list as $materi): ?>
                    <tr>
                        <td><?= htmlspecialchars($materi['judul']) ?></td>
                        <td>
                            <?php
                            $stmt_kelas = $pdo->prepare("SELECT nama_kelas FROM kelas WHERE id = ?");
                            $stmt_kelas->execute([$materi['id_kelas']]);
                            echo $stmt_kelas->fetchColumn() ?: "Tidak Diketahui";
                            ?>
                        </td>
                        <td>
                            <?php
                            $stmt_pelajaran = $pdo->prepare("SELECT nama_pelajaran FROM subjects WHERE id = ?");
                            $stmt_pelajaran->execute([$materi['id_pelajaran']]);
                            echo $stmt_pelajaran->fetchColumn() ?: "Tidak Diketahui";
                            ?>
                        </td>
                        <td><?= htmlspecialchars(date('d M Y H:i', strtotime($materi['created_at']))) ?></td>
                        <td class="text-center">
                            <!-- Download -->
                            <?php if (file_exists($materi['file_path'])): ?>
                                <a href="<?= htmlspecialchars($materi['file_path']) ?>" class="btn btn-sm btn-success me-1" target="_blank">
                                    <i class="bi bi-download me-1"></i>Download
                                </a>
                            <?php else: ?>
                                <span class="badge bg-danger">File Hilang</span>
                            <?php endif; ?>
                            
                            <!-- Edit -->
                            <a href="edit_materi.php?id=<?= $materi['id_materi'] ?>" class="btn btn-sm btn-primary me-1">
                                <i class="bi bi-pencil-square me-1"></i>Edit
                            </a>
                            
                            <!-- Delete -->
                            <a href="list_materi.php?delete=<?= $materi['id_materi'] ?>" class="btn btn-sm btn-danger me-1" onclick="return confirm('Yakin ingin menghapus materi ini?')"><i class="bi bi-trash3"></i>Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">Belum ada materi yang diupload.</div>
    <?php endif; ?>

    <a href="dashboard.php" class="btn btn-outline-secondary me-2">
        <i class="bi bi-arrow-left-circle me-1"></i>Kembali ke Dashboard
    </a>
    <a href="upload_materi.php" class="btn btn-primary">
        <i class="bi bi-upload me-1"></i>Upload Materi Baru
    </a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>