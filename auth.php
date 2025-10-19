<?php
require_once __DIR__ . '/config.php'; // Ensure $pdo and redirect_to_dashboard() are available

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

   function redirect_to_dashboard() {
    // Ensure session is active
    if (!isset($_SESSION['user']) || !isset($_SESSION['user']['role'])) {
        header("Location: login.php");
        exit;
    }

    $role = $_SESSION['user']['role'];

    // ✅ Only admin goes to admin/dashboard.php
    if ($role === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: dashboard.php"); // teacher, student, parent
    }
    exit;
}

}
?>
