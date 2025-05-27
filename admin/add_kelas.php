<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

include '../db.php';

// Ambil daftar guru untuk dropdown
$stmt = $pdo->query("SELECT id, username FROM users WHERE role = 'guru'");
$guru_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kelas = trim($_POST['nama_kelas']);
    $wali_kelas_id = $_POST['wali_kelas_id'] ?: null;

    if (empty($nama_kelas)) {
        $error = "Nama kelas harus diisi.";
    } else {
        try {
            // Cek apakah nama kelas sudah ada
            $stmt = $pdo->prepare("SELECT * FROM kelas WHERE nama_kelas = ?");
            $stmt->execute([$nama_kelas]);
            if ($stmt->rowCount() > 0) {
                $error = "Nama kelas sudah terdaftar.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO kelas (nama_kelas, wali_kelas_id) VALUES (?, ?)");
                $stmt->execute([$nama_kelas, $wali_kelas_id]);
                header("Location: kelas.php?added=1");
                exit;
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tambah Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Tambah Kelas Baru</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="nama_kelas" class="form-label">Nama Kelas</label>
            <input type="text" name="nama_kelas" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="wali_kelas_id" class="form-label">Wali Kelas (Opsional)</label>
            <select name="wali_kelas_id" class="form-select">
                <option value="">Tidak Ada</option>
                <?php foreach ($guru_list as $guru): ?>
                    <option value="<?= $guru['id'] ?>"><?= htmlspecialchars($guru['username']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="kelas.php" class="btn btn-secondary">Batal</a>
    </form>
</div>
</body>
</html>