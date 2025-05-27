<?php
session_start();
include '../db.php';

if ($_SESSION['user']['role'] !== 'guru') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assignment_id = $_POST['assignment_id'];
    $nilai_input = $_POST['nilai'] ?? [];
    $ceklist_input = $_POST['ceklist'] ?? [];

    foreach ($nilai_input as $submit_id => $nilai) {
        $is_checked = isset($ceklist_input[$submit_id]) ? 1 : 0;

        $stmt = $pdo->prepare("
            UPDATE submitted_assignments 
            SET nilai = ?, is_checked = ? 
            WHERE id = ?
        ");
        $stmt->execute([$nilai, $is_checked, $submit_id]);
    }

    header("Location: dashboard_guru.php");
    exit;
}
?>