<?php
require_once __DIR__ . '/../config.php';  // go up one folder
require_role(['teacher']);

$student_id = (int)($_POST['student_id'] ?? 0);
$subject_id = (int)($_POST['subject_id'] ?? 0);
$period = trim($_POST['period'] ?? '');
$grade = floatval($_POST['grade'] ?? 0);

if (!$student_id || !$subject_id || $period === '' || $grade < 0 || $grade > 100) {
    $_SESSION['error'] = "Invalid input.";
    header("Location: ../teacher/dashboard.php?student=" . $student_id);
    exit;
}

// fetch student's class
$s = $pdo->prepare("SELECT class FROM students WHERE id = ?");
$s->execute([$student_id]);
$student_row = $s->fetch();
$student_class = $student_row ? $student_row['class'] : '';

// verify teacher is assigned to this subject and section that matches student's class (or assigned with empty section)
$chk = $pdo->prepare("SELECT 1 FROM assigned_subjects WHERE teacher_id = ? AND subject_id = ? AND (section IS NULL OR ? ILIKE section || '%')");
$chk->execute([$_SESSION['user']['id'], $subject_id, $student_class]);
if (!$chk->fetch()) {
    $_SESSION['error'] = "You are not assigned to grade this student/subject.";
    header("Location: ../teacher/dashboard.php?student=" . $student_id);
    exit;
}

// Insert grade
$stmt = $pdo->prepare("
    INSERT INTO grades (student_id, subject_id, period, grade, teacher_id, created_at)
    VALUES (?, ?, ?, ?, ?, NOW())
");
$stmt->execute([$student_id, $subject_id, $period, $grade, $_SESSION['user']['id']]);

$_SESSION['message'] = "Grade saved successfully.";
header("Location: ../teacher/dashboard.php?student=" . $student_id);
exit;
