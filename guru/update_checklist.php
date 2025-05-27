<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'guru') {
    header("Location: ../login.php");
    exit;
}

include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assignment_id = $_POST['assignment_id'];
    $ceklist = $_POST['ceklist'] ?? [];

    // Reset semua checklist terlebih dahulu
    $stmt_reset = $pdo->prepare("UPDATE submitted_assignments SET is_checked = 0 WHERE assignment_id = ?");
    $stmt_reset->execute([$assignment_id]);

    // Update yang dicentang
    if (!empty($ceklist)) {
        foreach ($ceklist as $submit_id => $value) {
            $stmt_update = $pdo->prepare("UPDATE submitted_assignments SET is_checked = 1 WHERE id = ?");
            $stmt_update->execute([$submit_id]);
        }
    }

    header("Location: dashboard.php");
    exit;
}