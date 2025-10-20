<?php
require_once '../config.php';
require_role(['adviser']);

// Fetch adviser info
$user_id = $_SESSION['user']['id'];
$adviser = $pdo->prepare("SELECT * FROM advisers WHERE user_id = ?");
$adviser->execute([$user_id]);
$adviser_data = $adviser->fetch(PDO::FETCH_ASSOC);

if (!$adviser_data) {
    echo "<h3>You are not assigned as an adviser to any section yet.</h3>";
    exit;
}

$section = $adviser_data['section'];

// Fetch students of this adviser
$stmt = $pdo->prepare("
  SELECT s.id, s.student_no, s.name, s.class
  FROM students s
  WHERE s.adviser_id = ?
  ORDER BY s.name ASC
");
$stmt->execute([$adviser_data['id']]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Adviser Dashboard | ANHS Grading Portal</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #0b1d39;
      color: #fff;
      min-height: 100vh;
      display: flex;
    }
    body::before {
      content: "";
      position: fixed;
      inset: 0;
      background: url('../anhs.jpg') no-repeat center center fixed;
      background-size: cover;
      opacity: 0.25;
      z-index: -1;
    }
    .sidebar {
      width: 250px;
      background: rgba(0, 51, 102, 0.95);
      padding-top: 20px;
      position: fixed;
      height: 100vh;
    }
    .sidebar h3 {
      color: #fff;
      text-align: center;
      margin-bottom: 1rem;
      font-weight: 600;
    }
    .sidebar a {
      color: #dce3f3;
      text-decoration: none;
      padding: 12px 20px;
      display: block;
      border-left: 3px solid transparent;
      transition: all 0.2s ease;
    }
    .sidebar a:hover, .sidebar a.active {
      background-color: rgba(255, 255, 255, 0.1);
      border-left: 3px solid #4ea1ff;
      color: #fff;
    }
    .main {
      margin-left: 250px;
      padding: 30px;
      flex-grow: 1;
    }
    .table {
      color: #fff;
      background: rgba(255, 255, 255, 0.12);
      border-radius: 8px;
    }
    .table thead {
      background: rgba(255, 255, 255, 0.15);
    }
    .btn-primary {
      background-color: #1e90ff;
      border: none;
    }
    .btn-primary:hover {
      background-color: #0f78d1;
    }
  </style>
</head>
<body>
  <div class="sidebar">
    <h3><i class="bi bi-person-badge"></i> Adviser Panel</h3>
    <a href="dashboard.php" class="active"><i class="bi bi-house-door"></i> Dashboard</a>
    <a href="form137.php"><i class="bi bi-file-earmark-text"></i> Form 137</a>
    <a href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
  </div>

  <div class="main">
    <h2 class="mb-4">Adviser Dashboard</h2>
    <h5 class="mb-3">Section: <?= htmlspecialchars($section) ?></h5>

    <table id="studentsTable" class="table table-hover">
      <thead>
        <tr>
          <th>ID</th>
          <th>Student No</th>
          <th>Name</th>
          <th>Class</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($students as $s): ?>
        <tr>
          <td><?= $s['id'] ?></td>
          <td><?= htmlspecialchars($s['student_no']) ?></td>
          <td><?= htmlspecialchars($s['name']) ?></td>
          <td><?= htmlspecialchars($s['class']) ?></td>
          <td>
            <a href="form137.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-primary"><i class="bi bi-printer"></i> Print Form 137</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <script>
    $(document).ready(function(){
      $('#studentsTable').DataTable();
    });
  </script>
</body>
</html>
