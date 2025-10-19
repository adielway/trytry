<?php
require_once '../config.php';
require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $email = $_POST['email'];
  $class = $_POST['class'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  // Create student user
  $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'student')")->execute([$name, $email, $password]);
  $userId = $pdo->lastInsertId();

  // Create student record
  $pdo->prepare("INSERT INTO students (student_no, name, class, user_id) VALUES (?, ?, ?, ?)")->execute(['S-' . str_pad($userId, 4, '0', STR_PAD_LEFT), $name, $class, $userId]);
}

$students = $pdo->query("SELECT s.id, s.student_no, s.name, s.class, u.email FROM students s JOIN users u ON s.user_id=u.id ORDER BY s.id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Students</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container mt-4">
    <h2>Manage Students</h2>
    <form method="POST" class="row g-3 mb-4">
      <div class="col-md-3"><input type="text" name="name" class="form-control" placeholder="Student Name" required></div>
      <div class="col-md-3"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
      <div class="col-md-3"><input type="text" name="class" class="form-control" placeholder="Class" required></div>
      <div class="col-md-3"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
      <div class="col-12 text-end"><button class="btn btn-success">Add Student</button></div>
    </form>

    <table class="table table-bordered">
      <thead class="table-dark">
        <tr><th>ID</th><th>Student No</th><th>Name</th><th>Email</th><th>Class</th></tr>
      </thead>
      <tbody>
        <?php foreach ($students as $s): ?>
          <tr>
            <td><?= $s['id'] ?></td>
            <td><?= htmlspecialchars($s['student_no']) ?></td>
            <td><?= htmlspecialchars($s['name']) ?></td>
            <td><?= htmlspecialchars($s['email']) ?></td>
            <td><?= htmlspecialchars($s['class']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <a href="dashboard.php" class="btn btn-secondary mt-3">Back</a>
  </div>
</body>
</html>
