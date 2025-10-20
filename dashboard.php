<?php
require_once __DIR__ . '/config.php';
require_login();

$user = $_SESSION['user'];

// Determine view context for students/parents
$student_id = null;
if ($user['role'] === 'student') {
    $stmt = $pdo->prepare("SELECT s.id FROM students s JOIN users u ON s.user_id = u.id WHERE u.id = ? LIMIT 1");
    $stmt->execute([$user['id']]);
    $row = $stmt->fetch();
    $student_id = $row ? (int)$row['id'] : null;
} elseif ($user['role'] === 'parent') {
    $stmt = $pdo->prepare("SELECT child_student_id AS id FROM parents WHERE user_id = ? LIMIT 1");
    $stmt->execute([$user['id']]);
    $row = $stmt->fetch();
    $student_id = $row ? (int)$row['id'] : null;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
  body {
    background-color: #4169E1;
    min-height: 100vh;
  }
  .card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
  }
  .navbar {
    background-color: #27408B !important;
  }
  .navbar .navbar-brand, .navbar .navbar-text, .navbar .btn {
    color: white !important;
  }
  .navbar .btn:hover {
    background-color: #1E90FF !important;
    color: #fff !important;
    border-color: #1E90FF !important;
  }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php">GRADING PORTAL</a>
    <div class="d-flex">
      <span class="navbar-text me-3"><?= h($user['name']) ?> (<?= h($user['role']) ?>)</span>
      <a class="btn btn-outline-secondary btn-sm" href="logout.php">LOGOUT</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <div class="card shadow-sm">
    <div class="card-header">My Grades</div>
    <div class="card-body">
      <?php if (!$student_id): ?>
        <p class="text-muted">No student record linked to this account.</p>
      <?php else:
        $stmt = $pdo->prepare("SELECT name, class FROM students WHERE id = ?");
        $stmt->execute([$student_id]);
        $s = $stmt->fetch();
      ?>
        <h5 class="mb-3"><?= h($s['name']) ?> <span class="text-muted small">/ <?= h($s['class']) ?></span></h5>
        <?php
        $quarters = [1, 2, 3, 4];
        foreach ($quarters as $q) {
          $stmt = $pdo->prepare("
            SELECT sub.name AS subject, g.grade
            FROM grades g
            JOIN subjects sub ON sub.id = g.subject_id
            WHERE g.student_id = ? AND g.period = ?
            ORDER BY sub.name
          ");
          $stmt->execute([$student_id, $q]);
          $grades = $stmt->fetchAll();

          echo "<h5 class='mt-3'>Quarter $q</h5>";
          echo '<div class="table-responsive mb-4"><table class="table table-striped"><thead><tr><th>Subject</th><th>Grade</th><th>Remarks</th></tr></thead><tbody>';

          if (empty($grades)) {
            echo "<tr><td colspan='3' class='text-muted'>No grades for Quarter $q.</td></tr>";
          } else {
            $total = 0;
            foreach ($grades as $g) { $total += $g['grade']; }
            $avg = $total / count($grades);

            foreach ($grades as $g) {
              echo "<tr><td>".h($g['subject'])."</td><td>".h($g['grade'])."</td><td>";
              echo $g['grade'] >= 75 ? "<span class='text-success'>PASSED</span>" : "<span class='text-danger'>FAILED</span>";
              echo "</td></tr>";
            }

            echo "<tr class='fw-bold'><td>Average</td><td>".number_format($avg,2)."</td><td>";
            if ($avg >= 95) echo "<span class='text-primary'>WITH HIGH HONORS</span>";
            elseif ($avg >= 90) echo "<span class='text-primary'>WITH HONORS</span>";
            elseif ($avg < 75) echo "<span class='text-danger'>FAILED</span>";
            else echo "<span class='text-success'>PASSED</span>";
            echo "</td></tr>";
          }
          echo "</tbody></table></div>";
        }
        ?>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>
