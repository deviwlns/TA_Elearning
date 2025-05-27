<?php
session_start();

// Cek login dan role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'guru') {
    header("Location: ../login.php");
    exit;
}

include '../db.php'; // Koneksi database

// Ambil ID dari URL
if (!isset($_GET['id'])) {
    die("ID Materi tidak ditemukan.");
}
$id_materi = $_GET['id'];

// Ambil data materi dari DB
$stmt = $pdo->prepare("SELECT * FROM materi WHERE id_materi = ? AND id_guru = ?");
$stmt->execute([$id_materi, $_SESSION['user']['id']]);
$materi = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$materi) {
    die("Materi tidak ditemukan atau Anda tidak memiliki akses.");
}

// Ambil data pelajaran dan kelas untuk dropdown
$pelajaran_list = $pdo->query("SELECT id, nama_pelajaran FROM subjects ORDER BY nama_pelajaran ASC")->fetchAll(PDO::FETCH_ASSOC);
$kelas_list = $pdo->query("SELECT id, nama_kelas FROM kelas ORDER BY nama_kelas ASC")->fetchAll(PDO::FETCH_ASSOC);

$error = '';
$success = '';

// Handle submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);
    $id_pelajaran = $_POST['id_pelajaran'] ?? '';
    $id_kelas = $_POST['id_kelas'] ?? '';

    if (empty($judul) || empty($id_pelajaran) || empty($id_kelas)) {
        $error = "Judul, Pelajaran, dan Kelas wajib diisi.";
    } else {
        // Jika upload file baru
        $file_path = $materi['file_path'];
        if (!empty($_FILES['file_materi']['name'])) {
            $allowed_types = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt'];
            $file_tmp = $_FILES['file_materi']['tmp_name'];
            $file_name = $_FILES['file_materi']['name'];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed_types)) {
                $error = "Format file tidak diperbolehkan.";
            } elseif (!is_uploaded_file($file_tmp)) {
                $error = "File tidak valid.";
            } else {
                // Hapus file lama jika ada
                if (file_exists("../" . $file_path)) {
                    unlink("../" . $file_path);
                }

                // Simpan file baru
                $new_name = uniqid('materi_', true) . '.' . $ext;
                $upload_path = "../uploads/" . $new_name;

                if (!move_uploaded_file($file_tmp, $upload_path)) {
                    $error = "Upload file gagal.";
                } else {
                    $file_path = $upload_path;
                }
            }
        }

        // Update ke database jika tidak ada error
        if (!$error) {
            try {
                $stmt = $pdo->prepare("UPDATE materi SET judul = ?, deskripsi = ?, file_path = ?, id_pelajaran = ?, id_kelas = ? WHERE id_materi = ?");
                $stmt->execute([$judul, $deskripsi, $file_path, $id_pelajaran, $id_kelas, $id_materi]);
                $success = "Materi berhasil diperbarui!";
            } catch (PDOException $e) {
                $error = "Gagal menyimpan perubahan: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Materi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">
    <h2><i class="bi bi-pencil-square me-2"></i>Edit Materi</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="judul" class="form-label">Judul Materi</label>
            <input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($materi['judul']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="deskripsi" class="form-label">Deskripsi (Opsional)</label>
            <textarea name="deskripsi" class="form-control" rows="4"><?= htmlspecialchars($materi['deskripsi']) ?></textarea>
        </div>

        <div class="mb-3">
            <label for="id_pelajaran" class="form-label">Mata Pelajaran</label>
            <select name="id_pelajaran" class="form-select" required>
                <option value="">Pilih Mata Pelajaran</option>
                <?php foreach ($pelajaran_list as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $p['id'] == $materi['id_pelajaran'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['nama_pelajaran']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="id_kelas" class="form-label">Kelas</label>
            <select name="id_kelas" class="form-select" required>
                <option value="">Pilih Kelas</option>
                <?php foreach ($kelas_list as $k): ?>
                    <option value="<?= $k['id'] ?>" <?= $k['id'] == $materi['id_kelas'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($k['nama_kelas']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">File Saat Ini:</label><br>
            <?php if (file_exists($materi['file_path'])): ?>
                <a href="<?= htmlspecialchars($materi['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-file-earmark-text me-1"></i><?= basename($materi['file_path']) ?>
                </a>
            <?php else: ?>
                <span class="badge bg-danger">File Tidak Ditemukan</span>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="file_materi" class="form-label">Ganti File (Opsional)</label>
            <input type="file" name="file_materi" class="form-control">
            <small class="text-muted">Format didukung: PDF, Word, Excel, PowerPoint</small>
        </div>

        <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i>Simpan Perubahan</button>
        <a href="list_materi.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i>Batal</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>