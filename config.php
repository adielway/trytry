<?php
$host = "dpg-d3q4mjripnbc73aa95f0-a.singapore-postgres.render.com";
$port = "5432";
$dbname = "grading_portal";
$username = "grading_portal_user";
$password = "qlgWv6WZFRTWTO4zRObNiw7oVN6Kzdn5";

try {
    // PostgreSQL PDO connection
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
    exit;
}

session_start();

function is_logged_in() {
    return isset($_SESSION['user']);
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: /login.php");
        exit;
    }
}

function require_role($roles) {
    if (!is_logged_in() || !in_array($_SESSION['user']['role'], (array)$roles)) {
        http_response_code(403);
        echo "<h2>Forbidden</h2><p>You do not have access to this page.</p>";
        exit;
    }
}

function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect user to their role-specific dashboard
 */
function redirect_to_dashboard() {
    if (!isset($_SESSION['user']['role'])) {
        header("Location: /login.php");
        exit;
    }

    $role = $_SESSION['user']['role'];

    switch ($role) {
        case 'admin':
            header("Location: /admin/dashboard.php");
            break;
        case 'teacher':
            header("Location: /dashboard.php");
            break;
        case 'student':
            header("Location: /dashboard.php");
            break;
        case 'parent':
            header("Location: /dashboard.php");
            break;
        default:
            header("Location: /login.php");
            break;
    }

    exit;
}
?>
