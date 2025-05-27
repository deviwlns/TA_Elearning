<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'guru') {
    header("Location: ../login.php");
    exit;
}

include '../db.php';

$guru_id = $_SESSION['user']['id'];
$error = '';
$success = '';

// Ambil semua kelas yang diampu oleh guru
$stmt_kelas = $pdo->prepare("
    SELECT DISTINCT k.id, k.nama_kelas 
    FROM kelas k
    JOIN guru_kelas_pelajaran gkp ON k.id = gkp.kelas_id
    WHERE gkp.guru_id = ?
");
$stmt_kelas->execute([$guru_id]);
$kelas_list = $stmt_kelas->fetchAll(PDO::FETCH_ASSOC);

// Ambil semua pelajaran yang diajarkan di kelas yang diampu
$stmt_pelajaran = $pdo->prepare("
    SELECT DISTINCT s.id, s.nama_pelajaran AS name 
    FROM subjects s
    JOIN guru_kelas_pelajaran gkp ON s.id = gkp.pelajaran_id
    WHERE gkp.guru_id = ?
");
$stmt_pelajaran->execute([$guru_id]);
$pelajaran_list = $stmt_pelajaran->fetchAll(PDO::FETCH_ASSOC);

// 🔁 Proses Submit Form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil dari form
$judul = $_POST['judul'];
$deskripsi = $_POST['deskripsi'];
$deadline = $_POST['deadline'];
$kelas_id = $_POST['id'];
$pelajaran_id = $_POST['id_pelajaran'];
$guru_id = $_SESSION['user']['id']; // ← pastikan ini ada dan valid

// Upload file
$upload_dir = "../uploads/tugas/";
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

$file_name = $_FILES['file_tugas']['name'];
$file_tmp = $_FILES['file_tugas']['tmp_name'];
$file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
$allowed_ext = ['pdf', 'doc', 'docx', 'pptx', 'ppt', 'xlsx', 'xls', 'txt'];

if (in_array($file_ext, $allowed_ext)) {
    $new_file_name = uniqid('tugas_', true) . '.' . $file_ext;
    $file_path = $upload_dir . $new_file_name;

    if (move_uploaded_file($file_tmp, $file_path)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO tugas (
                    judul, deskripsi, deadline, id_kelas, id_pelajaran, file_path, id_guru
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $judul, $deskripsi, $deadline, $kelas_id, 
                $pelajaran_id, $file_path, $guru_id
            ]);

            $success = "Tugas berhasil dikirim ke kelas!";
        } catch (PDOException $e) {
            $error = "Gagal menyimpan tugas: " . $e->getMessage();
        }
    } else {
        $error = "Gagal mengupload file.";
    }
} else {
    $error = "Format file tidak diperbolehkan.";
}
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tambah Tugas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h3>➕ Tambah Tugas Baru</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="judul">Judul Tugas</label>
            <input type="text" name="judul" class="form-control" required value="<?= isset($judul) ? htmlspecialchars($judul) : '' ?>">
        </div>

        <div class="mb-3">
            <label for="deskripsi">Deskripsi Tugas</label>
            <textarea name="deskripsi" class="form-control" rows="4"><?= isset($deskripsi) ? htmlspecialchars($deskripsi) : '' ?></textarea>
        </div>

        <div class="mb-3">
            <label for="deadline">Tanggal Batas Pengumpulan</label>
            <input type="datetime-local" name="deadline" class="form-control" required value="<?= isset($deadline) ? htmlspecialchars($deadline) : '' ?>">
        </div>

        <!-- Kelas Tujuan -->
        <div class="mb-3">
            <label>Pilih Kelas</label>
            <select name="id" class="form-select" required>
                <option value="">-- Pilih Salah Satu --</option>
                <?php foreach ($kelas_list as $k): ?>
                    <option value="<?= $k['id'] ?>" <?= (isset($kelas_id) && $kelas_id == $k['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($k['nama_kelas']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Mata Pelajaran -->
        <div class="mb-3">
            <label>Pilih Mata Pelajaran</label>
            <select name="id_pelajaran" class="form-select" required>
                <option value="">-- Pilih Salah Satu --</option>
                <?php foreach ($pelajaran_list as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= (isset($pelajaran_id) && $pelajaran_id == $p['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Upload File -->
        <div class="mb-3">
            <label>Upload File Tugas</label>
            <input type="file" name="file_tugas" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Kirim Tugas</button>
        <a href="dashboard.php" class="btn btn-outline-secondary ms-2">⬅️ Kembali</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>