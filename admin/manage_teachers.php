<?php
require_once '../config.php';
require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $email = $_POST['email'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'teacher')");
  $stmt->execute([$name, $email, $password]);
}

$teachers = $pdo->query("SELECT * FROM users WHERE role='teacher' ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Teachers</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container mt-4">
    <h2>Manage Teachers</h2>
    <form method="POST" class="row g-3 mb-4">
      <div class="col-md-4"><input type="text" name="name" class="form-control" placeholder="Teacher Name" required></div>
      <div class="col-md-4"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
      <div class="col-md-4"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
      <div class="col-12 text-end"><button class="btn btn-primary">Add Teacher</button></div>
    </form>

    <table class="table table-bordered">
      <thead class="table-dark">
        <tr><th>ID</th><th>Name</th><th>Email</th><th>Action</th></tr>
      </thead>
      <tbody>
        <?php foreach ($teachers as $t): ?>
          <tr>
            <td><?= $t['id'] ?></td>
            <td><?= htmlspecialchars($t['name']) ?></td>
            <td><?= htmlspecialchars($t['email']) ?></td>
            <td><a href="delete_teacher.php?id=<?= $t['id'] ?>" class="btn btn-danger btn-sm">Delete</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <a href="dashboard.php" class="btn btn-secondary mt-3">Back</a>
  </div>
</body>
</html>
