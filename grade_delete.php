<?php
require_once __DIR__ . '/config.php';
require_role(['teacher']);

$id = (int)($_POST['id'] ?? 0);
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM grades WHERE id = ?");
    $stmt->execute([$id]);
}
header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'dashboard.php'));
exit;
