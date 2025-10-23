<?php
require_once __DIR__ . '/../config.php';  // go up one folder
require_role(['teacher']);

$student_id = (int)($_POST['student_id'] ?? 0);
$subject_id = (int)($_POST['subject_id'] ?? 0);
$period = trim($_POST['period'] ?? '');
$grade = floatval($_POST['grade'] ?? 0);

if (!$student_id || !$subject_id || !$period || $grade < 0 || $grade > 100) {
    $_SESSION['error'] = "Invalid input.";
    header("Location: dashboard.php" . $student_id);
    exit;
}

// Save the grade
$stmt = $pdo->prepare("
    INSERT INTO grades (student_id, subject_id, period, grade, teacher_id, created_at)
    VALUES (?, ?, ?, ?, ?, NOW())
");
$stmt->execute([$student_id, $subject_id, $period, $grade, $_SESSION['user']['id']]);

$_SESSION['message'] = "Grade saved successfully.";
header("Location: dashboard.php" . $student_id);
exit;
