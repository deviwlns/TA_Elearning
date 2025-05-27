<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa') {
    header("Location: ../login.php");
    exit;
}

include '../db.php';

$success = '';
$error = '';
$siswa_id = $_SESSION['user']['id'];

// Ambil data kelas siswa
$stmt_kelas = $pdo->prepare("
    SELECT ks.id_kelas, k.nama_kelas 
    FROM kelas_siswa ks
    JOIN kelas k ON ks.id_kelas = k.id
    WHERE ks.id_siswa = ?
");
$stmt_kelas->execute([$siswa_id]);
$kelas_data = $stmt_kelas->fetch(PDO::FETCH_ASSOC);

if (!$kelas_data) {
    $error = "Anda belum tergabung dalam kelas apapun.";
} else {
    $kelas_id = $kelas_data['id_kelas'];

    // Ambil daftar tugas dari tabel `tugas`, bukan `materi`
    $stmt_tugas = $pdo->prepare("
        SELECT 
            t.id_tugas,
            t.judul AS judul_tugas,
            s.nama_pelajaran,
            t.deadline
        FROM tugas t
        JOIN subjects s ON t.id_pelajaran = s.id
        WHERE t.id_kelas = ?
          AND t.deadline > NOW()
          AND NOT EXISTS (
            SELECT 1 FROM tugas_siswa ts
            WHERE ts.id_tugas = t.id_tugas AND ts.id_siswa = ?
          )
    ");
    try {
        $stmt_tugas->execute([$kelas_id, $siswa_id]);
        $tugas_list = $stmt_tugas->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Gagal mengambil daftar tugas: " . $e->getMessage();
    }
}

// Proses Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$kelas_data) {
        $error = "Anda belum terdaftar di kelas apapun.";
    } else {
        if (!isset($_POST['id_tugas'])) {
            $error = "Silakan pilih tugas sebelum upload jawaban.";
        } else {
            $id_tugas = $_POST['id_tugas'];
            $upload_dir = "../uploads/tugas/";

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_name = $_FILES['file_tugas']['name'];
            $file_tmp = $_FILES['file_tugas']['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = ['pdf', 'doc', 'docx', 'xlsx', 'xls', 'pptx', 'ppt', 'txt'];

            if (!in_array($file_ext, $allowed_ext)) {
                $error = "Format file tidak diperbolehkan. Hanya PDF, Word, Excel, PowerPoint.";
            } else {
                $new_file_name = uniqid('jawaban_', true) . '.' . $file_ext;
                $file_path = $upload_dir . $new_file_name;

                if (move_uploaded_file($file_tmp, $file_path)) {
                   try {
    $submitted_at = date('Y-m-d H:i:s');

    // Cek apakah siswa ini sudah pernah upload tugas ini
    $checkStmt = $pdo->prepare("SELECT * FROM tugas_siswa WHERE id_tugas = ? AND id_siswa = ?");
    $checkStmt->execute([$id_tugas, $siswa_id]);
    $existing = $checkStmt->fetch();

    if ($existing) {
        // UPDATE jika sudah ada entri
        $stmt = $pdo->prepare("UPDATE tugas_siswa SET file_path = ?, submitted_at = ? WHERE id_tugas = ? AND id_siswa = ?");
        $stmt->execute([$file_path, $submitted_at, $id_tugas, $siswa_id]);
        $success = "Jawaban tugas berhasil diperbarui!";
    } else {
        // INSERT jika belum ada entri
        $stmt = $pdo->prepare("INSERT INTO tugas_siswa (id_siswa, id_tugas, file_path, submitted_at) VALUES (?, ?, ?, ?)");
        $stmt->execute([$siswa_id, $id_tugas, $file_path, $submitted_at]);
        $success = "Tugas berhasil diupload!";
    }

    header("Location: upload_tugas.php");
    exit;

} catch (PDOException $e) {
    $error = "Gagal menyimpan data: " . $e->getMessage();
}
                } else {
                    $error = "Gagal memindahkan file upload.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Jawaban Tugas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .card-task:hover { box-shadow: 0 0.25rem 1rem rgba(0,0,0,0.1); transition: transform 0.2s; }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container-fluid">
        <span class="navbar-brand">E-Learning - Upload Tugas</span>
        <a href="../logout.php" class="btn btn-danger ms-auto">Logout</a>
    </div>
</nav>

<div class="container mt-5">
    <h2>📤 Upload Jawaban Tugas</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!$kelas_data): ?>
        <div class="alert alert-warning">Anda belum tergabung di kelas apapun.</div>
    <?php else: ?>
        <p>Kelas Anda: <strong><?= htmlspecialchars($kelas_data['nama_kelas']) ?></strong></p>

        <!-- Form Upload Tugas -->
        <form method="POST" enctype="multipart/form-data" class="card card-body shadow-sm p-3 mb-4">
            <div class="mb-3">
                <label for="id_tugas" class="form-label">Pilih Tugas</label>
                <select name="id_tugas" class="form-select" required>
                    <option value="">-- Pilih Tugas --</option>
                    <?php if (!empty($tugas_list)): ?>
                        <?php foreach ($tugas_list as $tugas): ?>
                            <option value="<?= $tugas['id_tugas'] ?>">
                                <?= htmlspecialchars($tugas['judul_tugas']) ?> - 
                                <?= htmlspecialchars($tugas['nama_pelajaran']) ?> 
                                (Deadline: <?= date('d M Y H:i', strtotime($tugas['deadline'])) ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option disabled selected>Tidak ada tugas tersisa</option>
                    <?php endif; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="file_tugas" class="form-label">File Jawaban</label>
                <input type="file" name="file_tugas" class="form-control" required>
                <small class="text-muted">Format yang diperbolehkan: PDF, DOCX, XLSX, PPTX, TXT</small>
            </div>

            <button type="submit" class="btn btn-primary w-100">Upload Tugas</button>
        </form>

        <!-- Daftar Tugas Yang Sudah Diupload -->
        <h4 class="mt-5">📄 Tugas Yang Sudah Saya Upload</h4>

        <?php
        $stmt_lihat_tugas = $pdo->prepare("SELECT * FROM tugas_siswa WHERE id_siswa = ? ORDER BY submitted_at DESC");
        $stmt_lihat_tugas->execute([$siswa_id]);
        $lihat_tugas_list = $stmt_lihat_tugas->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <?php if (!empty($lihat_tugas_list)): ?>
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light text-center">
                    <tr>
                        <th>No</th>
                        <th>Tugas</th>
                        <th>Tanggal Submit</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lihat_tugas_list as $key => $ts): ?>
                        <tr>
                            <td><?= $key + 1 ?></td>
                            <td>
                                <?php
                                $stmt_judul = $pdo->prepare("SELECT judul FROM tugas WHERE id_tugas = ?");
                                $stmt_judul->execute([$ts['id_tugas']]);
                                echo $stmt_judul->fetchColumn() ?: 'Tugas tidak ditemukan';
                                ?>
                            </td>
                            <td><?= date('d M Y H:i', strtotime($ts['submitted_at'])) ?></td>
                            <td>
                                <span class="badge bg-success">Sudah dikumpulkan</span>
                            </td>
                            <td class="text-center">
                                <?php if (file_exists($ts['file_path'])): ?>
                                    <a href="<?= htmlspecialchars($ts['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-download me-1"></i>Download
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">File tidak tersedia</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">Belum ada tugas diupload.</div>
        <?php endif; ?>

        <a href="dashboard.php" class="btn btn-secondary mt-3">
            <i class="bi bi-arrow-left-circle me-1"></i> Kembali ke Dashboard
        </a>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>