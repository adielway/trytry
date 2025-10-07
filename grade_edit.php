<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $grade = (int)$_POST['grade'];

    $stmt = $pdo->prepare("UPDATE grades SET grade = ? WHERE id = ?");
    $stmt->execute([$grade, $id]);

header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'dashboard.php'));
    exit;
}

