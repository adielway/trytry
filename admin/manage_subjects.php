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
  <title>Manage Subjects</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container mt-4">
    <h2>Manage Subjects</h2>
    <form method="POST" class="row g-3 mb-4">
      <div class="col-md-8"><input type="text" name="name" class="form-control" placeholder="Subject Name" required></div>
      <div class="col-12 text-end"><button class="btn btn-warning">Add Subject</button></div>
    </form>

    <table class="table table-bordered">
      <thead class="table-dark"><tr><th>ID</th><th>Name</th></tr></thead>
      <tbody>
        <?php foreach ($subjects as $sub): ?>
          <tr>
            <td><?= $sub['id'] ?></td>
            <td><?= htmlspecialchars($sub['name']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <a href="dashboard.php" class="btn btn-secondary mt-3">Back</a>
  </div>
</body>
</html>
