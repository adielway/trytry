<?php
require_once '../config.php';
require_role(['teacher']);

$user = $_SESSION['user'];

// 1️⃣ Fetch assigned subjects for this teacher
$stmt = $pdo->prepare("
  SELECT s.id, s.name 
  FROM assigned_subjects a
  JOIN subjects s ON a.subject_id = s.id
  WHERE a.teacher_id = ?
  ORDER BY s.name
");
$stmt->execute([$user['id']]);
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2️⃣ Get section(s) where this teacher is an adviser (optional)
$stmt = $pdo->prepare("
  SELECT id, section, school_year 
  FROM advisers 
  WHERE user_id = ?
");
$stmt->execute([$user['id']]);
$adviser = $stmt->fetch(PDO::FETCH_ASSOC);

// 3️⃣ Fetch students only in their section (if adviser)
$students = [];
if ($adviser) {
  $stmt = $pdo->prepare("
    SELECT id, student_no, name, class 
    FROM students 
    WHERE adviser_id = ?
    ORDER BY name ASC
  ");
  $stmt->execute([$adviser['id']]);
  $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Teacher Dashboard | ANHS Portal</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <style>
    body {
      background-color: #0b1d39;
      color: #fff;
      font-family: 'Segoe UI', sans-serif;
      min-height: 100vh;
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
    .navbar {
      background-color: rgba(0,51,102,0.95) !important;
    }
    .card {
      background: rgba(255,255,255,0.12);
      border: none;
      border-radius: 12px;
    }
    .table {
      color: #fff;
    }
    .btn-primary {
      background-color: #1e90ff;
      border: none;
    }
    .btn-primary:hover {
      background-color: #0f78d1;
    }
    .btn-danger {
      border: none;
    }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand text-white fw-bold" href="#">Teacher Dashboard</a>
    <div class="d-flex">
      <span class="navbar-text me-3"><?= h($user['name']) ?> (Teacher)</span>
      <a href="../logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <div class="card p-4 shadow-sm mb-4">
    <h4>Assigned Subjects</h4>
    <?php if (empty($subjects)): ?>
      <p class="text-light">No assigned subjects yet.</p>
    <?php else: ?>
      <ul>
        <?php foreach ($subjects as $s): ?>
          <li><?= h($s['name']) ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <?php if ($adviser): ?>
      <p class="mt-3"><strong>Adviser Section:</strong> <?= h($adviser['section']) ?> (<?= h($adviser['school_year']) ?>)</p>
    <?php else: ?>
      <p class="text-warning">You are not an adviser of any section.</p>
    <?php endif; ?>
  </div>

  <?php if ($adviser && !empty($subjects)): ?>
  <div class="card p-4 shadow-sm">
    <h4>Manage Grades — <?= h($adviser['section']) ?></h4>

    <div class="mb-3">
      <label class="form-label">Select Subject</label>
      <select id="subjectSelect" class="form-select w-50" required>
        <option value="">-- Select Subject --</option>
        <?php foreach ($subjects as $s): ?>
          <option value="<?= $s['id'] ?>"><?= h($s['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="table-responsive">
      <table id="studentsTable" class="table table-striped">
        <thead>
          <tr>
            <th>ID</th>
            <th>Student No</th>
            <th>Name</th>
            <th>Class</th>
            <th>Quarter</th>
            <th>Grade</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($students as $st): ?>
          <tr>
            <td><?= $st['id'] ?></td>
            <td><?= h($st['student_no']) ?></td>
            <td><?= h($st['name']) ?></td>
            <td><?= h($st['class']) ?></td>
            <td>
              <select name="period" class="form-select form-select-sm quarterInput">
                <option value="1">Q1</option>
                <option value="2">Q2</option>
                <option value="3">Q3</option>
                <option value="4">Q4</option>
              </select>
            </td>
            <td>
              <form method="post" action="../grade_save.php" class="d-flex gap-2">
                <input type="hidden" name="student_id" value="<?= $st['id'] ?>">
                <input type="hidden" name="subject_id" class="subjectInput">
                <input type="hidden" name="period" class="quarterHidden">
                <input type="number" step="0.01" min="0" max="100" name="grade" class="form-control form-control-sm" required>
                <button class="btn btn-primary btn-sm">Save</button>
              </form>
            </td>
            <td>
              <form method="post" action="../grade_delete.php" onsubmit="return confirm('Delete all grades for this student?');">
                <input type="hidden" name="student_id" value="<?= $st['id'] ?>">
                <button class="btn btn-danger btn-sm">Delete</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php else: ?>
  <div class="alert alert-warning mt-4">
    You currently have no assigned section or subjects.
  </div>
  <?php endif; ?>
</div>

<script>
$(document).ready(function() {
  $('#studentsTable').DataTable();

  // When teacher selects a subject or quarter, update hidden inputs
  $('#subjectSelect').on('change', function() {
    const subjectId = $(this).val();
    $('.subjectInput').val(subjectId);
  });

  $('.quarterInput').on('change', function() {
    const quarterVal = $(this).val();
    $(this).closest('tr').find('.quarterHidden').val(quarterVal);
  });
});
</script>
</body>
</html>
