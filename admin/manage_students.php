<?php
require_once '../config.php';
require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $email = $_POST['email'];
  $class = $_POST['class'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'student')")->execute([$name, $email, $password]);
  $userId = $pdo->lastInsertId();
  $pdo->prepare("INSERT INTO students (student_no, name, class, user_id) VALUES (?, ?, ?, ?)")->execute(['S-' . str_pad($userId, 4, '0', STR_PAD_LEFT), $name, $class, $userId]);
}

$students = $pdo->query("SELECT s.id, s.student_no, s.name, s.class, u.email FROM students s JOIN users u ON s.user_id=u.id ORDER BY s.id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Students | Admin Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    <?php include 'style.css'; // We'll use the same style as manage_teachers ?>
  </style>
</head>
<body>
  <div class="sidebar">
    <h3><i class="bi bi-speedometer2"></i> Admin Panel</h3>
    <a href="dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a>
    <a href="manage_teachers.php"><i class="bi bi-person-badge"></i> Manage Teachers</a>
    <a href="manage_students.php" class="active"><i class="bi bi-mortarboard"></i> Manage Students</a>
    <a href="manage_subjects.php"><i class="bi bi-book"></i> Manage Subjects</a>
    <a href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
  </div>

  <div class="toggle-btn" onclick="toggleSidebar()">
  <img src="logo.png" alt="Toggle Sidebar">
</div>


  <div class="main">
    <h2 class="mb-4">Manage Students</h2>
    <form method="POST" class="row g-3 mb-4">
      <div class="col-md-3"><input type="text" name="name" class="form-control" placeholder="Student Name" required></div>
      <div class="col-md-3"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
      <div class="col-md-3"><input type="text" name="class" class="form-control" placeholder="Class" required></div>
      <div class="col-md-3"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
      <div class="col-12 text-end"><button class="btn btn-primary">Add Student</button></div>
    </form>

    <table class="table table-bordered table-hover">
      <thead>
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
  </div>
</body>
 <script>
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      const main = document.getElementById('main');
      sidebar.classList.toggle('collapsed');
      main.classList.toggle('full');
    }
  </script>
</html>
