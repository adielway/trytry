<?php
require_once '../config.php';
require_role(['adviser']);

$user_id = $_SESSION['user']['id'];

/* =======================
   FETCH ADVISER INFO
======================= */
$adviser_stmt = $pdo->prepare("SELECT * FROM advisers WHERE user_id = ?");
$adviser_stmt->execute([$user_id]);
$adviser = $adviser_stmt->fetch(PDO::FETCH_ASSOC);

if (!$adviser) {
    echo "<h3>You are not assigned as an adviser.</h3>";
    exit;
}

/* =======================
   FETCH STUDENTS UNDER ADVISER
======================= */
$students_stmt = $pdo->prepare("
    SELECT s.id, s.name
    FROM students s
    WHERE s.adviser_id = ?
    ORDER BY s.name
");
$students_stmt->execute([$adviser['id']]);
$students = $students_stmt->fetchAll(PDO::FETCH_ASSOC);

/* =======================
   GET STUDENT ID
======================= */
$student_id = $_GET['id'] ?? null;

if (!$student_id) {
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
  <div class="card shadow-sm">
    <div class="card-body">
      <h4>Select Student (Form 137)</h4>
      <form method="get">
        <select name="id" class="form-select mb-3" required>
          <option value="">-- Select Student --</option>
          <?php foreach ($students as $s): ?>
            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <button class="btn btn-primary">View Form 137</button>
      </form>
    </div>
  </div>
</div>

</body>
</html>
<?php
exit;
}

/* =======================
   FETCH STUDENT INFO
======================= */
$student_stmt = $pdo->prepare("
    SELECT s.*, u.email
    FROM students s
    JOIN users u ON s.user_id = u.id
    WHERE s.id = ? AND s.adviser_id = ?
");
$student_stmt->execute([$student_id, $adviser['id']]);
$student = $student_stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    echo "<h3>Student not found or not under your advisory.</h3>";
    exit;
}

/* =======================
   FETCH GRADES
======================= */
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
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Form 137 - <?= htmlspecialchars($student['name']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
  font-family: "Times New Roman", serif;
  background: #f2f2f2;
}

.form137 {
  background: #fff;
  max-width: 900px;
  margin: 30px auto;
  padding: 40px;
  border: 1px solid #000;
}

h3, h5 {
  text-align: center;
  margin: 0;
}

.info p {
  margin: 2px 0;
  font-size: 14px;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 15px;
  font-size: 14px;
}

th, td {
  border: 1px solid #000;
  padding: 5px;
  text-align: center;
}

.print-btn {
  text-align: center;
  margin-top: 25px;
}

@media print {
  .print-btn { display: none; }
  body { background: none; }
}
</style>
</head>

<body>

<div class="form137">
  <h3>REPUBLIC OF THE PHILIPPINES</h3>
  <h5>DEPARTMENT OF EDUCATION</h5>
  <h5>STUDENT PERMANENT RECORD (Form 137)</h5>
  <hr>

  <div class="info">
    <p><strong>School:</strong> AMLAN NATIONAL HIGH SCHOOL</p>
    <p><strong>Student Name:</strong> <?= htmlspecialchars($student['name']) ?></p>
    <p><strong>Student No:</strong> <?= htmlspecialchars($student['student_no']) ?></p>
    <p><strong>Class:</strong> <?= htmlspecialchars($student['class']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($student['email']) ?></p>
    <p><strong>Section:</strong> <?= htmlspecialchars($adviser['section']) ?></p>
    <p><strong>Adviser:</strong> <?= htmlspecialchars($_SESSION['user']['name']) ?></p>
  </div>

  <h5 class="mt-4">ACADEMIC RECORDS</h5>

  <table>
    <thead>
      <tr>
        <th>Subject</th>
        <th>Quarter</th>
        <th>Final Grade</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($grades): ?>
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
    <button onclick="window.print()" class="btn btn-dark">🖨 Print Form 137</button>
  </div>
</div>

</body>
</html>
