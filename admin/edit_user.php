<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../db.php';

// Cek apakah ID user diberikan
if (!isset($_GET['id'])) {
    header("Location: users.php");
    exit;
}

$id = $_GET['id'];

// Ambil data user dari database
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: users.php");
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $role = $_POST['role'];
    $password = $_POST['password'];

    // Validasi input
    if (empty($username) || empty($role)) {
        $error = "Username dan role harus diisi!";
    } elseif (!in_array($role, ['admin', 'guru', 'siswa'])) {
        $error = "Role tidak valid!";
    } else {
        try {
            // Cek username apakah sudah dipakai oleh user lain
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$username, $id]);
            if ($stmt->rowCount() > 0) {
                $error = "Username sudah digunakan oleh pengguna lain.";
            } else {
                // Siapkan query update
                $sql = "UPDATE users SET username = ?, role = ?";
                $params = [$username, $role];

                // Jika password diisi, tambahkan ke update
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $sql .= ", password = ?";
                    $params[] = $hashed_password;
                }

                $sql .= " WHERE id = ?";
                $params[] = $id;

                // Jalankan query
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

                $success = "Data user berhasil diperbarui.";
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
    <title>Edit Pengguna - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Edit Pengguna</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username" class="form-control" required value="<?= htmlspecialchars($user['username']) ?>">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password (kosongkan jika tidak ingin ubah)</label>
            <input type="password" name="password" class="form-control">
        </div>
        <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <select name="role" class="form-select" required>
                <option value="">Pilih Role</option>
                <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="guru" <?= $user['role'] == 'guru' ? 'selected' : '' ?>>Guru</option>
                <option value="siswa" <?= $user['role'] == 'siswa' ? 'selected' : '' ?>>Siswa</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="users.php" class="btn btn-secondary">Batal</a>
    </form>
</div>
</body>
</html>