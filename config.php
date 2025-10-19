<?php
$servername = "anhs-portal-scoutmillares-01eb.j.aivencloud.com";  // your MySQL Host Name
$username = "avnadmin";               // your MySQL Username
$password = "AVNS_322bJX9DQixmhc9yXzJ";                // your MySQL Password
$dbname = "defaultdb";  // your full Database Name

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connected successfully";
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
