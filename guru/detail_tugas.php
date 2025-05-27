<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'guru') {
    header("Location: ../login.php");
    exit;
}

include '../db.php';

$guru_id = $_SESSION['user']['id'];
$kelas_id = isset($_GET['kelas']) ? $_GET['kelas'] : null;

// Validasi jika kelas bukan kelas yang diampu guru
$stmt_valid_kelas = $pdo->prepare("
    SELECT COUNT(*) FROM guru_kelas_pelajaran 
    WHERE guru_id = ? AND kelas_id = ?
");

$stmt_valid_kelas->execute([$guru_id, $kelas_id]);

if ($stmt_valid_kelas->fetchColumn() == 0 || !$kelas_id) {
    header("Location: lihat_tugas.php");
    exit;
}

// Ambil nama kelas
$stmt_kelas = $pdo->prepare("SELECT nama_kelas FROM kelas WHERE id_kelas = ?");
$stmt_kelas->execute([$kelas_id]);
$kelas_info = $stmt_kelas->fetch(PDO::FETCH_ASSOC);

// Ambil semua tugas dalam kelas ini
$stmt_tugas = $pdo->prepare("
    SELECT ts.id_tugas, ts.file_path, ts.submitted_at, u.username AS nama_siswa, m.judul AS judul_materi 
    FROM tugas_siswa ts
    JOIN users u ON ts.id_siswa = u.id
    JOIN materi m ON ts.id_materi = m.id_materi
    WHERE m.id_kelas = ?
    ORDER BY m.judul, ts.submitted_at DESC
");

$stmt_tugas->execute([$kelas_id]);
$tugas_list = $stmt_tugas->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lihat Tugas - <?= $kelas_info['nama_kelas'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Sidebar / Menu Guru -->
<div class="d-flex">
    <div class="bg-dark text-white p-3" style="width: 250px;">
        <h4>Guru Panel</h4>
        <hr>
        <ul class="nav flex-column">
            <li class="nav-item"><a href="dashboard.php" class="nav-link text-white">Dashboard</a></li>
            <li class="nav-item"><a href="#" class="nav-link active bg-primary text-white">Lihat Tugas Siswa</a></li>
            <li class="nav-item"><a href="../logout.php" class="nav-link text-danger">Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="p-4 flex-grow-1">
        <h2>Tugas Siswa - Kelas <?= htmlspecialchars($kelas_info['nama_kelas']) ?></h2>

        <?php if ($tugas_list): ?>
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Siswa</th>
                        <th>Judul Tugas</th>
                        <th>Tanggal Upload</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($tugas_list as $tugas): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($tugas['nama_siswa']) ?></td>
                            <td><?= htmlspecialchars($tugas['judul_materi']) ?></td>
                            <td><?= date('d M Y H:i', strtotime($tugas['submitted_at'])) ?></td>
                            <td>
                                <?php if (file_exists($tugas['file_path'])): ?>
                                    <a href="<?= $tugas['file_path'] ?>" target="_blank" class="btn btn-sm btn-success">📥 Download</a>
                                    <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#nilaiModal<?= $tugas['id_tugas'] ?>">📝 Nilai</button>
                                <?php else: ?>
                                    <span class="text-muted">Tidak tersedia</span>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <!-- Modal Beri Nilai -->
                        <div class="modal fade" id="nilaiModal<?= $tugas['id_tugas'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <form method="POST" class="modal-content">
                                    <input type="hidden" name="id_tugas" value="<?= $tugas['id_tugas'] ?>">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Beri Nilai untuk Siswa</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="nilai" class="form-label">Nilai</label>
                                            <input type="number" step="0.01" name="nilai" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="komentar" class="form-label">Komentar (Opsional)</label>
                                            <textarea name="komentar" rows="3" class="form-control"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">Belum ada tugas dari siswa di kelas ini.</div>
        <?php endif; ?>

        <a href="lihat_tugas.php" class="btn btn-outline-secondary">Kembali</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Proses input nilai
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_tugas = $_POST['id_tugas'];
    $nilai = filter_input(INPUT_POST, 'nilai', FILTER_VALIDATE_FLOAT);
    $komentar = $_POST['komentar'] ?? '';
    
    try {
        // Simpan ke tabel nilai_tugas
        $pdo->beginTransaction();
        
        $stmt_nilai = $pdo->prepare("
            INSERT INTO nilai_tugas (id_tugas, nilai, komentar, diberikan_oleh)
            VALUES (?, ?, ?, ?)
        ");
        $stmt_nilai->execute([$id_tugas, $nilai, $komentar, $guru_id]);

        $pdo->commit();
        echo "<div class='alert alert-success'>Nilai berhasil disimpan</div>";
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "<div class='alert alert-danger'>Gagal menyimpan nilai: " . $e->getMessage() . "</div>";
    }
}
?>