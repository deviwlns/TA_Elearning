<?php
include 'db.php';

echo "<pre>";
echo "🔍 Memulai migrasi tugas dari materi ke tabel tugas...\n";

try {
    // Ambil semua record dari tabel materi yang berupa tugas
    // Misal: filter berdasarkan kolom deadline atau jenis lain
    $stmt = $pdo->query("SELECT * FROM materi WHERE deadline IS NOT NULL OR deskripsi LIKE '%tugas%'");

    $count = 0;

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Cek apakah sudah ada di tabel tugas
        $check = $pdo->prepare("SELECT COUNT(*) FROM tugas WHERE judul = ? AND id_kelas = ?");
        $check->execute([$row['judul'], $row['id_kelas']]);
        if ($check->fetchColumn() > 0) continue;

        // Masukkan ke tabel tugas
        $insert = $pdo->prepare("
            INSERT INTO tugas (
                judul, deskripsi, deadline, id_kelas, id_pelajaran, file_path, id_guru, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insert->execute([
            $row['judul'],
            $row['deskripsi'],
            $row['deadline'],
            $row['id_kelas'],
            $row['id_pelajaran'],
            $row['file_path'],
            $row['id_guru'],
            $row['created_at']
        ]);

        $count++;
    }

    echo "✅ Berhasil memigrasi $count tugas dari tabel materi ke tabel tugas.\n";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
echo "</pre>";