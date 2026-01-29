<?php
require_once '../config.php';
require_role(['adviser']);

// Fetch adviser info
$user_id = $_SESSION['user']['id'];
$adviser_stmt = $pdo->prepare("SELECT * FROM advisers WHERE user_id = ?");
$adviser_stmt->execute([$user_id]);
$adviser_data = $adviser_stmt->fetch(PDO::FETCH_ASSOC);

if (!$adviser_data) {
    echo "<h3>You are not assigned as an adviser to any section yet.</h3>";
    exit;
}

// Get student ID
$student_id = $_GET['id'] ?? null;

/* ===============================
   IF NO STUDENT SELECTED → SHOW LIST
   =============================== */
if (!$student_id) {

    // Fetch students under adviser section
    $students_stmt = $pdo->prepare("
        SELECT s.id, s.name, s.student_no
        FROM students s
        WHERE s.section = ?
        ORDER BY s.name ASC
    ");
    $students_stmt->execute([$adviser_data['section']]);
    $students = $students_stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Select Student - Form 137</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">

    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Select Student for Form 137</h5>
            </div>
            <div class="card-body">

                <?php if (count($students) > 0): ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Student No</th>
                                <th>Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $s): ?>
                                <tr>
                                    <td><?= htmlspecialchars($s['student_no']) ?></td>
                                    <td><?= htmlspecialchars($s['name']) ?></td>
                                    <td>
                                        <a href="form137.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-success">
                                            View Form 137
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-center">No students found in your section.</p>
                <?php endif; ?>

            </div>
        </div>
    </div>

    </body>
    </html>
    <?php
    exit;
}

/* ===============================
   EXISTING LOGIC BELOW (UNCHANGED)
   =============================== */

// Fetch student info
$student_stmt = $pdo->prepare("
    SELECT s.*, u.email
    FROM students s
    JOIN users u ON s.user_id = u.id
    WHERE s.id = ?
");
$student_stmt->execute([$student_id]);
$student = $student_stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    echo "<h3>Student not found.</h3>";
    exit;
}

// Fetch grades
$grades_stmt = $pdo->prepare("
    SELECT sub.name AS subject, g.period, g.grade
    FROM grades g
    JOIN subjects sub ON g.subject_id = sub.id
    WHERE g.student_id = ?
    ORDER BY sub.name ASC, g.period ASC
");
$grades_stmt->execute([$student_id]);
$grades = $grades_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Form 137 - <?= htmlspecialchars($student['name']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Times New Roman', serif;
      background: #f0f0f0;
      padding: 20px;
    }
    .form137 {
      position: relative;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 0 8px rgba(0,0,0,0.15);
      max-width: 850px;
      margin: 30px auto;
      padding: 40px;
    }
    .form137::before {
      content: "";
      position: absolute;
      inset: 0;
      background: url('../form137_bg.png') no-repeat center top;
      background-size: cover;
      opacity: 0.15;
      z-index: 0;
    }
    .content {
      position: relative;
      z-index: 2;
    }
    table, th, td {
      border: 1px solid #000;
    }
    th, td {
      padding: 6px;
      text-align: center;
    }
    .print-btn {
      text-align: center;
      margin-top: 30px;
    }
    @media print {
      .print-btn { display: none; }
    }
  </style>
</head>
<body>

<div class="form137">
  <div class="content">
    <h3 class="text-center">AMLAN NATIONAL HIGH SCHOOL</h3>
    <h5 class="text-center">Form 137 - Student Permanent Record</h5>
    <hr>

    <p><strong>Student Name:</strong> <?= htmlspecialchars($student['name']) ?></p>
    <p><strong>Student No:</strong> <?= htmlspecialchars($student['student_no']) ?></p>
    <p><strong>Class:</strong> <?= htmlspecialchars($student['class']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($student['email']) ?></p>
    <p><strong>Adviser:</strong> <?= htmlspecialchars($_SESSION['user']['name']) ?></p>
    <p><strong>Section:</strong> <?= htmlspecialchars($adviser_data['section']) ?></p>

    <h5 class="text-center mt-4">Academic Records</h5>
    <table width="100%">
      <thead>
        <tr>
          <th>Subject</th>
          <th>Period</th>
          <th>Grade</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($grades) > 0): ?>
          <?php foreach ($grades as $g): ?>
            <tr>
              <td><?= htmlspecialchars($g['subject']) ?></td>
              <td><?= htmlspecialchars($g['period']) ?></td>
              <td><?= htmlspecialchars($g['grade']) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="3">No grades recorded.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <div class="print-btn">
      <button onclick="window.print()" class="btn btn-primary mt-3">🖨️ Print Form 137</button>
    </div>
  </div>
</div>

</body>
</html>
