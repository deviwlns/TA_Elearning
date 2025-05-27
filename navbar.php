<?php if ($_SESSION['user']['role'] === 'admin'): ?>
    <li><a href="admin/add_user.php">Kelola Pengguna</a></li>
<?php elseif ($_SESSION['user']['role'] === 'guru'): ?>
    <li><a href="guru/upload_materi.php">Upload Materi</a></li>
<?php elseif ($_SESSION['user']['role'] === 'siswa'): ?>
    <li><a href="siswa/lihat_materi.php">Lihat Materi</a></li>
<?php endif; ?>