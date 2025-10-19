<?php
require_once '../config.php';
require_role(['admin']);

$teacherCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role='teacher'")->fetchColumn();
$studentCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$subjectCount = $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <h2 class="mb-4">Welcome, Admin</h2>

    <div class="row text-center">
      <div class="col-md-4">
        <div class="card p-3 shadow-sm">
          <h3><?= $teacherCount ?></h3>
          <p>Teachers</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card p-3 shadow-sm">
          <h3><?= $studentCount ?></h3>
          <p>Students</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card p-3 shadow-sm">
          <h3><?= $subjectCount ?></h3>
          <p>Subjects</p>
        </div>
      </div>
    </div>

    <div class="mt-4">
      <a href="manage_teachers.php" class="btn btn-primary me-2">Manage Teachers</a>
      <a href="manage_students.php" class="btn btn-success me-2">Manage Students</a>
      <a href="manage_subjects.php" class="btn btn-warning me-2">Manage Subjects</a>
      <a href="../logout.php" class="btn btn-danger">Logout</a>
    </div>
  </div>
</body>
</html>
