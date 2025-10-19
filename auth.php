<?php
require_once __DIR__ . '/config.php'; // Ensure $pdo and redirect_to_dashboard() are available

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        // Store user info in session
        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ];

        // Redirect based on role
        redirect_to_dashboard();
    } else {
        $_SESSION['error'] = "Invalid email or password.";
        header("Location: login.php");
        exit;
    }
}
?>
