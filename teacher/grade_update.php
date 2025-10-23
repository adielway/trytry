<?php
require_once __DIR__ . '/../config.php';
require_role(['teacher']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int)($_POST['id'] ?? 0);
    $grade  = $_POST['grade'] ?? null;
    $period = $_POST['period'] ?? null;

    if ($id && $grade !== null && $period !== null) {
        // Sanitize
        $grade = max(0, min(100, floatval($grade)));
        $period = (int)$period;

        $stmt = $pdo->prepare("UPDATE grades SET grade = ?, period = ? WHERE id = ?");
        $stmt->execute([$grade, $period, $id]);
    }
}

header("Location: dashboard.php");
exit;
