<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../db.php';

// Ambil daftar kelas
$stmt_kelas = $pdo->query("SELECT id, nama_kelas FROM kelas ORDER BY nama_kelas ASC");
$kelas_list = $stmt_kelas->fetchAll(PDO::FETCH_ASSOC);

// Ambil daftar siswa yang belum masuk ke kelas mana pun
$query_siswa = "
    SELECT u.id, u.username 
    FROM users u
    WHERE u.role = 'siswa'
    AND u.id NOT IN (
        SELECT ks.id_siswa 
        FROM kelas_siswa ks
    )
    ORDER BY u.username ASC
";
$stmt_siswa = $pdo->query($query_siswa);
$siswa_list = $stmt_siswa->fetchAll(PDO::FETCH_ASSOC);

// Tambahkan siswa ke kelas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $id_siswa = $_POST['id_siswa'];
    $id_kelas = $_POST['id_kelas'];

    if ($id_siswa && $id_kelas) {
        try {
            // Cek apakah siswa ini sudah masuk kelas
            $stmt = $pdo->prepare("SELECT * FROM kelas_siswa WHERE id_siswa = ? AND id_kelas = ?");
            $stmt->execute([$id_siswa, $id_kelas]);

            if ($stmt->rowCount() > 0) {
                $_SESSION['error'] = "Siswa ini sudah tergabung di kelas tersebut.";
            } else {
                // Cek apakah siswa sudah tergabung di kelas lain
                $stmt_check = $pdo->prepare("SELECT * FROM kelas_siswa WHERE id_siswa = ?");
                $stmt_check->execute([$id_siswa]);

                if ($stmt_check->rowCount() > 0) {
                    $_SESSION['error'] = "Siswa ini sudah tergabung di kelas lain.";
                } else {
                    $stmt_insert = $pdo->prepare("INSERT INTO kelas_siswa (id_siswa, id_kelas) VALUES (?, ?)");
                    $stmt_insert->execute([$id_siswa, $id_kelas]);
                    $_SESSION['success_add'] = "Siswa berhasil ditambahkan ke kelas."; // Pesan sukses penambahan

                    // Redirect untuk refresh halaman dan memperbarui dropdown
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit;
                }
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Gagal menambah siswa ke kelas: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "Semua field harus dipilih.";
    }
}

// Hapus siswa dari kelas
if (isset($_GET['delete'])) {
    $id_kelas_siswa = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM kelas_siswa WHERE id_kelas_siswa = ?");
        $stmt->execute([$id_kelas_siswa]);
        $_SESSION['success_delete'] = "Siswa berhasil dihapus dari kelas."; // Pesan sukses penghapusan
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Gagal menghapus: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kelola Siswa di Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .sidebar {
            min-height: 100vh;
            background-color: #212529;
            color: white;
        }
        .nav-link:hover {
            background-color: #343a40;
        }
        .card-clickable:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.25rem 1rem rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<div class="d-flex">
    <!-- Sidebar Admin -->
    <div class="sidebar p-3" style="width: 250px;">
        <h4 class="text-center text-white mb-4">Admin Panel</h4>
        <ul class="nav flex-column">
            <li class="nav-item"><a href="dashboard.php" class="nav-link text-white">🏠 Dashboard</a></li>
            <li class="nav-item"><a href="users.php" class="nav-link text-white">🧑‍🎓 Kelola Pengguna</a></li>
            <li class="nav-item"><a href="subjects.php" class="nav-link text-white">📘 Mata Pelajaran</a></li>
            <li class="nav-item"><a href="kelas.php" class="nav-link text-white">🏫 Kelas</a></li>
            <li class="nav-item"><a href="#" class="nav-link active bg-primary">🧾 Kelola Siswa di Kelas</a></li>
            <li class="nav-item mt-auto"><a href="../logout.php" class="nav-link text-danger">🚪 Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-grow-1 p-4">
        <h2>Kelola Siswa → Kelas</h2>
        <p>Tambahkan atau hapus siswa dari kelas tertentu</p>

        <!-- Pesan Sukses/Error -->
        <?php if (isset($_SESSION['success_add'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success_add'] ?></div> <!-- Pesan sukses penambahan (hijau) -->
            <?php unset($_SESSION['success_add']); // Hapus pesan setelah ditampilkan ?>
        <?php elseif (isset($_SESSION['success_delete'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['success_delete'] ?></div> <!-- Pesan sukses penghapusan (merah) -->
            <?php unset($_SESSION['success_delete']); // Hapus pesan setelah ditampilkan ?>
        <?php elseif (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div> <!-- Pesan error (merah) -->
            <?php unset($_SESSION['error']); // Hapus pesan setelah ditampilkan ?>
        <?php endif; ?>

        <!-- Form Tambah Siswa ke Kelas -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white fw-bold">➕ Tambah Siswa ke Kelas</div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add">

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="id_siswa" class="form-label">Pilih Siswa</label>
                            <select name="id_siswa" class="form-select" required>
                                <option value="">-- Pilih Siswa --</option>
                                <?php foreach ($siswa_list as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['username']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="id_kelas" class="form-label">Pilih Kelas</label>
                            <select name="id_kelas" class="form-select" required>
                                <option value="">-- Pilih Kelas --</option>
                                <?php foreach ($kelas_list as $k): ?>
                                    <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100">Simpan Relasi</button>
                </form>
            </div>
        </div>

        <!-- Daftar Semua Siswa di Kelas -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold">📖 Daftar Siswa di Kelas</div>
            <div class="card-body table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("
                        SELECT ks.id_kelas_siswa, u.username, k.nama_kelas 
                        FROM kelas_siswa ks
                        JOIN users u ON ks.id_siswa = u.id
                        JOIN kelas k ON ks.id_kelas = k.id
                        ORDER BY k.nama_kelas, u.username
                    ");
                        $no = 1;
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                        ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['username']) ?></td>
                                <td><?= htmlspecialchars($row['nama_kelas']) ?></td>
                                <td>
                                    <a href="?<?= http_build_query(['delete' => $row['id_kelas_siswa']]) ?>" onclick="return confirm('Yakin ingin menghapus relasi ini?')" class="btn btn-sm btn-danger">Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>