<?php
require_once __DIR__ . '/../config.php';
require_role(['teacher']);

$student_id = (int)($_POST['student_id'] ?? 0);
$subject_id = (int)($_POST['subject_id'] ?? 0);
$teacher_id = $_SESSION['user']['id'];

// Delete only grades belonging to this teacher and student
if ($student_id) {
    if ($subject_id) {
        // Delete only the grade for that specific subject
        $stmt = $pdo->prepare("DELETE FROM grades WHERE student_id = ? AND subject_id = ? AND teacher_id = ?");
        $stmt->execute([$student_id, $subject_id, $teacher_id]);
    } else {
        // Delete all grades by this teacher for the student
        $stmt = $pdo->prepare("DELETE FROM grades WHERE student_id = ? AND teacher_id = ?");
        $stmt->execute([$student_id, $teacher_id]);
    }
}

// Redirect back to teacher dashboard
header("Location: dashboard.php");
exit;
