<?php
require_once '../config.php';
require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $pdo->prepare("INSERT INTO subjects (name) VALUES (?)")->execute([$name]);
}

$subjects = $pdo->query("SELECT * FROM subjects ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Subjects | Admin Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    <?php include 'style.css'; ?>
  </style>
</head>
<body>
  <div class="sidebar">
    <h3><i class="bi bi-speedometer2"></i> Admin Panel</h3>
    <a href="dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a>
    <a href="manage_teachers.php"><i class="bi bi-person-badge"></i> Manage Teachers</a>
    <a href="manage_students.php"><i class="bi bi-mortarboard"></i> Manage Students</a>
    <a href="manage_subjects.php" class="active"><i class="bi bi-book"></i> Manage Subjects</a>
    <a href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
  </div>

  <div class="main">
    <h2 class="mb-4">Manage Subjects</h2>
    <form method="POST" class="row g-3 mb-4">
      <div class="col-md-8"><input type="text" name="name" class="form-control" placeholder="Subject Name" required></div>
      <div class="col-12 text-end"><button class="btn btn-primary">Add Subject</button></div>
    </form>

    <table class="table table-bordered table-hover">
      <thead>
        <tr><th>ID</th><th>Name</th></tr>
      </thead>
      <tbody>
        <?php foreach ($subjects as $sub): ?>
          <tr>
            <td><?= $sub['id'] ?></td>
            <td><?= htmlspecialchars($sub['name']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
