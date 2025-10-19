<?php
$host = "dpg-d3q4mjripnbc73aa95f0-a.singapore-postgres.render.com";
$port = "5432";
$dbname = "grading_portal";
$username = "grading_portal_user";
$password = "qlgWv6WZFRTWTO4zRObNiw7oVN6Kzdn5";

try {
    // Use PostgreSQL PDO connection instead of MySQL
    $pdo = new PDO("pgsql:host=dpg-d3q4mjripnbc73aa95f0-a.singapore-postgres.render.com;port=5432;dbname=grading_portal", "grading_portal_user", "qlgWv6WZFRTWTO4zRObNiw7oVN6Kzdn5");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
}

session_start();

function is_logged_in() {
    return isset($_SESSION['user']);
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: login.php");
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

function h($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }
?>
