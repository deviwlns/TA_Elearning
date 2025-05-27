<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'guru') {
    header("Location: ../login.php");
    exit;
}

include '../db.php';

// Ambil ID guru dari session
$id_guru = $_SESSION['user']['id'];
$error = '';
$success = '';

// Ambil pelajaran yang diajar oleh guru ini
$stmt_pelajaran = $pdo->prepare("
    SELECT DISTINCT s.id, s.nama_pelajaran 
    FROM subjects s
    JOIN guru_kelas_pelajaran gkp ON s.id = gkp.pelajaran_id
    WHERE gkp.guru_id = ?
");
$stmt_pelajaran->execute([$id_guru]);
$pelajaran_list = $stmt_pelajaran->fetchAll(PDO::FETCH_ASSOC);

// Ambil kelas yang diampu oleh guru ini
$stmt_kelas = $pdo->prepare("
    SELECT DISTINCT k.id, k.nama_kelas 
    FROM kelas k
    JOIN guru_kelas_pelajaran gkp ON k.id = gkp.kelas_id
    WHERE gkp.guru_id = ?
");
$stmt_kelas->execute([$id_guru]);
$kelas_list = $stmt_kelas->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']); // ✅ Ditambahkan
    $id_pelajaran = $_POST['id_pelajaran'] ?? '';
    $id_kelas = $_POST['id_kelas'] ?? ''; // <-- NAMA INPUT SUDAH SESUAI
    $id_guru = $_SESSION['user']['id']; // Simpan id guru dari session

    if (empty($judul) || empty($id_pelajaran) || empty($id_kelas)) {
        $error = "Judul, Pelajaran, dan Kelas harus diisi.";
    } elseif (empty($_FILES['file_materi']['name'])) {
        $error = "File materi harus dipilih.";
    } elseif ($_FILES['file_materi']['error'] !== UPLOAD_ERR_OK) {
        $error = "Ada kesalahan saat upload file.";
    } elseif (!is_uploaded_file($_FILES['file_materi']['tmp_name'])) {
        $error = "File tidak valid.";
    } else {
        // Direktori uploads
        if (!is_dir("../uploads")) {
            mkdir("../uploads", 0777, true);
        }

        $file_tmp = $_FILES['file_materi']['tmp_name'];
        $file_name = $_FILES['file_materi']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt'];

        if (!in_array($file_ext, $allowed)) {
            $error = "Format file tidak diperbolehkan.";
        } else {
            $new_name = uniqid('materi_', true) . '.' . $file_ext;
            $upload_path = "../uploads/" . $new_name;

            if (!move_uploaded_file($file_tmp, $upload_path)) {
                $error = "Gagal mengupload file.";
            } else {
                try {
                    $stmt = $pdo->prepare("INSERT INTO materi (judul, deskripsi, file_path, id_pelajaran, id_kelas, id_guru) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$judul, $deskripsi, $upload_path, $id_pelajaran, $id_kelas, $id_guru]); // Sekarang semua variabel sudah didefinisikan
                    $success = "Materi berhasil diupload!";
                } catch (PDOException $e) {
                    $error = "Gagal menyimpan data: " . $e->getMessage();
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
    <title>Upload Materi - Guru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            margin-top: 2rem;
            box-shadow: 0 0.15rem 1.75rem rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm rounded my-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">E-Learning Guru</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="upload_materi.php">Upload Materi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="list_materi.php">Daftar Materi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Card Form Upload -->
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0">Upload Materi Baru</h4>
                </div>
                <div class="card-body">

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="judul" class="form-label">Judul Materi</label>
                            <input type="text" name="judul" class="form-control" placeholder="Contoh: Pengenalan Fisika Dasar" required>
                        </div>
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi (Opsional)</label>
                            <textarea name="deskripsi" class="form-control" rows="3" placeholder="Isi dengan penjelasan singkat tentang materi ini..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="id_pelajaran" class="form-label">Mata Pelajaran</label>
                            <select name="id_pelajaran" class="form-select" required>
                                <option value="">Pilih Mata Pelajaran</option>
                                <?php foreach ($pelajaran_list as $pelajaran): ?>
                                    <option value="<?= $pelajaran['id'] ?>"><?= htmlspecialchars($pelajaran['nama_pelajaran']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="id_kelas" class="form-label">Kelas</label>
                            <select name="id_kelas" class="form-select" required>
                                <option value="">Pilih Kelas</option>
                                <?php foreach ($kelas_list as $kelas): ?>
                                    <option value="<?= $kelas['id'] ?>"><?= htmlspecialchars($kelas['nama_kelas']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="file_materi" class="form-label">Upload File Materi</label>
                            <input type="file" name="file_materi" class="form-control" required>
                            <small class="text-muted">Format: PDF, Word, Excel, PowerPoint (.pdf, .docx, .pptx, .xlsx)</small>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">📤 Upload Materi</button>
                            <a href="dashboard.php" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>