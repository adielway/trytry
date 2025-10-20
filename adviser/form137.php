<?php
require_once '../config.php';
require_role(['adviser']);

// Get student ID
$student_id = $_GET['id'] ?? null;
if (!$student_id) {
    echo "<h3>No student selected.</h3>";
    exit;
}

// Fetch adviser info
$user_id = $_SESSION['user']['id'];
$adviser_stmt = $pdo->prepare("SELECT * FROM advisers WHERE user_id = ?");
$adviser_stmt->execute([$user_id]);
$adviser_data = $adviser_stmt->fetch(PDO::FETCH_ASSOC);

if (!$adviser_data) {
    echo "<h3>You are not assigned as an adviser to any section yet.</h3>";
    exit;
}

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

    /* Background Form Image */
    .form137::before {
      content: "";
      position: absolute;
      inset: 0;
      background: url('../form137_bg.png') no-repeat center top;
      background-size: cover;
      opacity: 0.15; /* Adjust transparency for readability */
      z-index: 0;
    }

    .content {
      position: relative;
      z-index: 2;
    }

    .header {
      text-align: center;
      margin-bottom: 40px;
    }

    .header h3 {
      margin: 0;
      font-weight: bold;
    }

    .header h5 {
      margin: 5px 0 15px;
      font-weight: normal;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
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

    .print-btn button {
      background-color: #1e40af;
      color: white;
      border: none;
      padding: 10px 25px;
      border-radius: 5px;
      cursor: pointer;
    }

    .print-btn button:hover {
      background-color: #16347d;
    }

    @media print {
      .print-btn {
        display: none;
      }

      body {
        background: none;
        -webkit-print-color-adjust: exact;
      }

      .form137 {
        box-shadow: none;
        border: none;
        margin: 0;
        padding: 0;
      }

      .form137::before {
        opacity: 0.3; /* Make background clearer when printing */
      }
    }
  </style>
</head>
<body>

<div class="form137">
  <div class="content">
    <div class="header">
      <h3>AMLAN NATIONAL HIGH SCHOOL</h3>
      <h5>Form 137 - Student Permanent Record</h5>
      <hr>
    </div>

    <div class="mb-4">
      <p><strong>Student Name:</strong> <?= htmlspecialchars($student['name']) ?></p>
      <p><strong>Student No:</strong> <?= htmlspecialchars($student['student_no']) ?></p>
      <p><strong>Class:</strong> <?= htmlspecialchars($student['class']) ?></p>
      <p><strong>Email:</strong> <?= htmlspecialchars($student['email']) ?></p>
      <p><strong>Adviser:</strong> <?= htmlspecialchars($_SESSION['user']['name']) ?></p>
      <p><strong>Section:</strong> <?= htmlspecialchars($adviser_data['section']) ?></p>
    </div>

    <h5 class="text-center">Academic Records</h5>
    <table>
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
      <button onclick="window.print()">🖨️ Print Form 137</button>
    </div>
  </div>
</div>

</body>
</html>
