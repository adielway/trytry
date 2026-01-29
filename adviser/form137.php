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

<style>
body {
    font-family: "Times New Roman", serif;
    background: #fff;
    margin: 0;
    padding: 20px;
}

.form-container {
    width: 100%;
    max-width: 900px;
    margin: auto;
    border: 1px solid #000;
    padding: 20px;
}

.header {
    text-align: center;
    margin-bottom: 10px;
}

.header h4, .header h5 {
    margin: 0;
    font-weight: bold;
}

.header p {
    margin: 2px 0;
    font-size: 13px;
}

.section-title {
    text-align: center;
    font-weight: bold;
    margin: 15px 0 8px;
    text-transform: uppercase;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

table, th, td {
    border: 1px solid #000;
}

th, td {
    padding: 5px;
    text-align: center;
}

.info-table td {
    text-align: left;
    padding: 6px;
}

.print-btn {
    text-align: center;
    margin-top: 20px;
}

@media print {
    .print-btn {
        display: none;
    }
}
</style>
</head>

<body>

<div class="form-container">

    <!-- HEADER -->
    <div class="header">
        <h4>DEPARTMENT OF EDUCATION</h4>
        <p>Region VII – Central Visayas</p>
        <p>Division of Negros Oriental</p>
        <h5>AMLAN NATIONAL HIGH SCHOOL</h5>
        <p>Amlan, Negros Oriental</p>
        <br>
        <strong>STUDENT PERMANENT RECORD (FORM 137)</strong>
    </div>

    <!-- STUDENT INFO -->
    <div class="section-title">Student Information</div>
    <table class="info-table">
        <tr>
            <td width="25%"><strong>Student Name</strong></td>
            <td width="75%"><?= htmlspecialchars($student['name']) ?></td>
        </tr>
        <tr>
            <td><strong>Student Number</strong></td>
            <td><?= htmlspecialchars($student['student_no']) ?></td>
        </tr>
        <tr>
            <td><strong>Class</strong></td>
            <td><?= htmlspecialchars($student['class']) ?></td>
        </tr>
        <tr>
            <td><strong>Email</strong></td>
            <td><?= htmlspecialchars($student['email']) ?></td>
        </tr>
        <tr>
            <td><strong>Adviser</strong></td>
            <td><?= htmlspecialchars($_SESSION['user']['name']) ?></td>
        </tr>
        <tr>
            <td><strong>Section</strong></td>
            <td><?= htmlspecialchars($adviser_data['section']) ?></td>
        </tr>
    </table>

    <!-- ACADEMIC RECORDS -->
    <div class="section-title">Academic Records</div>
    <table>
        <thead>
            <tr>
                <th width="50%">Subject</th>
                <th width="25%">Period</th>
                <th width="25%">Final Grade</th>
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
            <tr>
                <td colspan="3">No grades recorded.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

    <!-- FOOTER -->
    <br><br>
    <table class="info-table">
        <tr>
            <td width="50%">
                <strong>Certified Correct:</strong><br><br>
                _______________________________<br>
                <em>Class Adviser</em>
            </td>
            <td width="50%">
                <strong>Date:</strong><br><br>
                _______________________________
            </td>
        </tr>
    </table>

    <div class="print-btn">
        <button onclick="window.print()">🖨️ Print Form 137</button>
    </div>

</div>

</body>
</html>
