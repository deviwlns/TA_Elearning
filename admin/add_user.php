<?php
session_start();

// Proteksi halaman hanya untuk admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Koneksi database
include '../db.php';

// Inisialisasi variabel notifikasi
$success = '';
$error = '';

// Proses jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    // Validasi input
    if (empty($username) || empty($password) || empty($role)) {
        $error = "Semua field harus diisi!";
    } elseif (!in_array($role, ['admin', 'guru', 'siswa'])) {
        $error = "Role tidak valid!";
    } else {
        try {
            // Cek apakah username sudah terdaftar
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($stmt->rowCount() > 0) {
                $error = "Username sudah terdaftar!";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Simpan ke database
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                if ($stmt->execute([$username, $hashed_password, $role])) {
                    $success = "User berhasil ditambahkan!";
                } else {
                    $error = "Gagal menyimpan data ke database.";
                }
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
    <title>Tambah Pengguna Baru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Tambah Pengguna Baru</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username" class="form-control" required value="<?= htmlspecialchars($username ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <select name="role" class="form-select" required>
                <option value="">Pilih Role</option>
                <option value="admin" <?= isset($role) && $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="guru" <?= isset($role) && $role === 'guru' ? 'selected' : '' ?>>Guru</option>
                <option value="siswa" <?= isset($role) && $role === 'siswa' ? 'selected' : '' ?>>Siswa</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Tambah User</button>
        <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
    </form>
</div>
</body>
</html>