<?php
require_once '../config.php';
require_role(['adviser']);

$user_id = $_SESSION['user']['id'];

$adviser_stmt = $pdo->prepare("SELECT * FROM advisers WHERE user_id = ?");
$adviser_stmt->execute([$user_id]);
$adviser = $adviser_stmt->fetch(PDO::FETCH_ASSOC);

if (!$adviser) {
    echo "You are not assigned as an adviser.";
    exit;
}

$student_id = $_GET['id'] ?? null;

if (!$student_id) {
    echo "No student selected.";
    exit;
}

$student_stmt = $pdo->prepare("
    SELECT s.*, u.email
    FROM students s
    JOIN users u ON s.user_id = u.id
    WHERE s.id = ? AND s.adviser_id = ?
");
$student_stmt->execute([$student_id, $adviser['id']]);
$student = $student_stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    echo "Student not found.";
    exit;
}

$grades_stmt = $pdo->prepare("
    SELECT sub.name AS subject, g.period, g.grade
    FROM grades g
    JOIN subjects sub ON g.subject_id = sub.id
    WHERE g.student_id = ?
    ORDER BY sub.name, g.period
");
$grades_stmt->execute([$student_id]);
$grades = $grades_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Form 137</title>

<style>
body {
  font-family: "Times New Roman", serif;
  background: #fff;
}

.form137 {
  width: 900px;
  margin: auto;
  border: 2px solid #000;
  padding: 25px;
}

.header {
  text-align: center;
  font-size: 14px;
}

.header h3 {
  margin: 3px 0;
  font-size: 16px;
}

.section-title {
  text-align: center;
  font-weight: bold;
  margin: 15px 0 5px;
  font-size: 14px;
}

.info-table,
.grades-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}

.info-table td {
  padding: 4px;
}

.grades-table th,
.grades-table td {
  border: 1px solid #000;
  padding: 5px;
  text-align: center;
}

.grades-table th {
  font-weight: bold;
}

.print-btn {
  text-align: center;
  margin-top: 20px;
}

@media print {
  .print-btn { display: none; }
}
</style>
</head>

<body>

<div class="form137">

  <div class="header">
    <h3>REPUBLIC OF THE PHILIPPINES</h3>
    <h3>DEPARTMENT OF EDUCATION</h3>
    <h3>STUDENT PERMANENT RECORD (FORM 137)</h3>
  </div>

  <table class="info-table">
    <tr>
      <td><strong>Name:</strong> <?= htmlspecialchars($student['name']) ?></td>
      <td><strong>Student No:</strong> <?= htmlspecialchars($student['student_no']) ?></td>
    </tr>
    <tr>
      <td><strong>School:</strong> AMLAN NATIONAL HIGH SCHOOL</td>
      <td><strong>Class:</strong> <?= htmlspecialchars($student['class']) ?></td>
    </tr>
    <tr>
      <td><strong>Section:</strong> <?= htmlspecialchars($adviser['section']) ?></td>
      <td><strong>Adviser:</strong> <?= htmlspecialchars($_SESSION['user']['name']) ?></td>
    </tr>
  </table>

  <div class="section-title">ACADEMIC RECORD</div>

  <table class="grades-table">
    <thead>
      <tr>
        <th rowspan="2">Learning Area</th>
        <th colspan="4">Periodic Rating</th>
        <th rowspan="2">Final Rating</th>
      </tr>
      <tr>
        <th>1</th>
        <th>2</th>
        <th>3</th>
        <th>4</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $subjects = [];
      foreach ($grades as $g) {
          $subjects[$g['subject']][$g['period']] = $g['grade'];
      }

      foreach ($subjects as $subject => $periods):
        $final = array_sum($periods) / count($periods);
      ?>
      <tr>
        <td style="text-align:left"><?= htmlspecialchars($subject) ?></td>
        <td><?= $periods[1] ?? '' ?></td>
        <td><?= $periods[2] ?? '' ?></td>
        <td><?= $periods[3] ?? '' ?></td>
        <td><?= $periods[4] ?? '' ?></td>
        <td><?= number_format($final, 2) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="print-btn">
    <button onclick="window.print()">🖨 Print Form 137</button>
  </div>

</div>

</body>
</html>
