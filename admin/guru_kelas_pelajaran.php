<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

include '../db.php';

$error = $success = "";

// --- Tambah Data ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'tambah') {
    $guru_id = $_POST['guru_id'];
    $kelas_id = $_POST['kelas_id'];
    $pelajaran_id = $_POST['pelajaran_id'];

    if ($guru_id && $kelas_id && $pelajaran_id) {
        try {
            // Cek apakah sudah ada relasi yang sama
            $stmt = $pdo->prepare("SELECT * FROM guru_kelas_pelajaran WHERE guru_id = ? AND kelas_id = ? AND pelajaran_id = ?");
            $stmt->execute([$guru_id, $kelas_id, $pelajaran_id]);
            if ($stmt->rowCount() > 0) {
                $error = "Relasi guru-kelas-pelajaran sudah ada.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO guru_kelas_pelajaran (guru_id, kelas_id, pelajaran_id) VALUES (?, ?, ?)");
                $stmt->execute([$guru_id, $kelas_id, $pelajaran_id]);
                $success = "Relasi berhasil ditambahkan.";
            }
        } catch (PDOException $e) {
            $error = "Gagal menambah data: " . $e->getMessage();
        }
    } else {
        $error = "Semua field harus dipilih.";
    }
}

// --- Hapus Data ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM guru_kelas_pelajaran WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Data berhasil dihapus.";
    } catch (PDOException $e) {
        $error = "Gagal menghapus data: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kelola Relasi Guru - Kelas - Mata Pelajaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .card-clickable:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.25rem 1rem rgba(0,0,0,0.1);
        }
        .icon-bg {
            font-size: 1.5rem;
            padding: 10px;
            border-radius: 50%;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="d-flex">
    <div class="sidebar p-3 bg-dark text-white" style="width: 250px; min-height:100vh;">
        <h4 class="text-center mb-4">Admin Panel</h4>
        <ul class="nav flex-column">
            <li class="nav-item mb-2"><a href="dashboard.php" class="nav-link text-white">🏠 Dashboard</a></li>
            <li class="nav-item mb-2"><a href="users.php" class="nav-link text-white">🧑‍🎓 Kelola Pengguna</a></li>
            <li class="nav-item mb-2"><a href="subjects.php" class="nav-link text-white">📘 Mata Pelajaran</a></li>
            <li class="nav-item mb-2"><a href="kelas.php" class="nav-link text-white">🏫 Kelas</a></li>
            <li class="nav-item mb-2"><a href="kelas_pelajaran_guru.php" class="nav-link active bg-primary">👩‍🏫 Kelola Kelas & Pelajaran Guru</a></li>
            <li class="nav-item mt-auto"><a href="../logout.php" class="nav-link text-danger">🚪 Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="container-fluid py-4 px-4" style="flex-grow:1;">
        <h2 class="mb-4">Kelola Relasi Guru - Kelas - Mata Pelajaran</h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Form Tambah Relasi -->
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">Tambah Relasi Baru</div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="tambah">

                            <div class="mb-3">
                                <label for="guru_id" class="form-label">Pilih Guru</label>
                                <select name="guru_id" class="form-select" required>
                                    <option value="">-- Pilih Guru --</option>
                                    <?php
                                    $stmt = $pdo->query("SELECT id, username FROM users WHERE role = 'guru'");
                                    foreach ($stmt as $guru):
                                        echo "<option value='{$guru['id']}'>{$guru['username']}</option>";
                                    endforeach;
                                    ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="kelas_id" class="form-label">Pilih Kelas</label>
                                <select name="kelas_id" class="form-select" required>
                                    <option value="">-- Pilih Kelas --</option>
                                    <?php
                                    $stmt = $pdo->query("SELECT id, nama_kelas FROM kelas");
                                    foreach ($stmt as $kelas):
                                        echo "<option value='{$kelas['id']}'>{$kelas['nama_kelas']}</option>";
                                    endforeach;
                                    ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="pelajaran_id" class="form-label">Pilih Mata Pelajaran</label>
                                <select name="pelajaran_id" class="form-select" required>
                                    <option value="">-- Pilih Mata Pelajaran --</option>
                                    <?php
                                    $stmt = $pdo->query("SELECT id, nama_pelajaran FROM subjects ORDER BY nama_pelajaran ASC");
                                    foreach ($stmt as $pelajaran):
                                        echo "<option value='{$pelajaran['id']}'>{$pelajaran['nama_pelajaran']}</option>";
                                    endforeach;
                                    ?>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-success w-100">Simpan Relasi</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Daftar Relasi -->
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <span>Daftar Relasi Guru - Kelas - Mata Pelajaran</span>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Guru</th>
                                    <th>Kelas</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $pdo->query("
                                    SELECT gkp.id, u.username AS guru, k.nama_kelas, s.nama_pelajaran 
                                    FROM guru_kelas_pelajaran gkp
                                    JOIN users u ON gkp.guru_id = u.id
                                    JOIN kelas k ON gkp.kelas_id = k.id
                                    JOIN subjects s ON gkp.pelajaran_id = s.id
                                    ORDER BY u.username ASC
                                ");
                                $no = 1;
                                foreach ($stmt as $row):
                                ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['guru']) ?></td>
                                        <td><?= htmlspecialchars($row['nama_kelas']) ?></td>
                                        <td><?= htmlspecialchars($row['nama_pelajaran']) ?></td>
                                        <td>
                                            <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus?')" class="btn btn-sm btn-danger">Hapus</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>