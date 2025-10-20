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

/* ------------------------------
   ADD STUDENT
------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $class = trim($_POST['class']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'student')");
        $stmt->execute([$name, $email, $password]);
        $user_id = $pdo->lastInsertId();

        $student_no = 'S-' . str_pad($user_id, 4, '0', STR_PAD_LEFT);
        $stmt2 = $pdo->prepare("INSERT INTO students (student_no, name, class, user_id, adviser_id) VALUES (?, ?, ?, ?, ?)");
        $stmt2->execute([$student_no, $name, $class, $user_id, $adviser_data['id']]);

        $pdo->commit();
        $message = "✅ Student successfully added!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "❌ Error adding student: " . $e->getMessage();
    }
}

/* ------------------------------
   EDIT STUDENT
------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $class = trim($_POST['class']);
    $password = $_POST['password'];

    try {
        $pdo->beginTransaction();

        // Update student record
        $stmt = $pdo->prepare("UPDATE students SET name = ?, class = ? WHERE id = ?");
        $stmt->execute([$name, $class, $id]);

        // Update linked user record
        if (!empty($password)) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET name = ?, email = ?, password_hash = ? WHERE id = (SELECT user_id FROM students WHERE id = ?)")->execute([$name, $email, $password_hash, $id]);
        } else {
            $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = (SELECT user_id FROM students WHERE id = ?)")->execute([$name, $email, $id]);
        }

        $pdo->commit();
        $message = "✅ Student updated successfully!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "❌ Error updating student: " . $e->getMessage();
    }
}

/* ------------------------------
   DELETE STUDENT
------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = $_POST['id'];
    try {
        $pdo->beginTransaction();

        // Delete the linked user first (cascade manually)
        $pdo->prepare("DELETE FROM users WHERE id = (SELECT user_id FROM students WHERE id = ?)")->execute([$id]);
        // Delete student record
        $pdo->prepare("DELETE FROM students WHERE id = ?")->execute([$id]);

        $pdo->commit();
        $message = "✅ Student deleted successfully!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "❌ Error deleting student: " . $e->getMessage();
    }
}

/* ------------------------------
   FETCH STUDENTS
------------------------------ */
$stmt = $pdo->prepare("
  SELECT s.id, s.student_no, s.name, s.class, u.email
  FROM students s
  JOIN users u ON s.user_id = u.id
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
    .sidebar h3 { color: #fff; text-align: center; margin-bottom: 1rem; font-weight: 600; }
    .sidebar a { color: #dce3f3; text-decoration: none; padding: 12px 20px; display: block; border-left: 3px solid transparent; transition: all 0.2s ease; }
    .sidebar a:hover, .sidebar a.active { background-color: rgba(255,255,255,0.1); border-left: 3px solid #4ea1ff; color: #fff; }
    .main { margin-left: 250px; padding: 30px; flex-grow: 1; }
    .table { color: #fff; background: rgba(255,255,255,0.12); border-radius: 8px; }
    .table thead { background: rgba(255,255,255,0.15); }
    .btn-primary { background-color: #1e90ff; border: none; }
    .btn-primary:hover { background-color: #0f78d1; }
    .form-control { background-color: rgba(255,255,255,0.85); }
    .alert { margin-top: 10px; }
    .modal-content { color: #000; }
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

    <?php if (!empty($message)): ?>
      <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php elseif (!empty($error)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- ✅ Add Student Form -->
    <div class="card mb-4 p-4 bg-transparent border-light">
      <h5>Add / Enroll Student</h5>
      <form method="POST" class="row g-3">
        <input type="hidden" name="action" value="add">
        <div class="col-md-3"><input type="text" name="name" class="form-control" placeholder="Student Name" required></div>
        <div class="col-md-3"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
        <div class="col-md-3"><input type="text" name="class" class="form-control" placeholder="Class" required></div>
        <div class="col-md-3"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
        <div class="col-12 text-end"><button class="btn btn-primary">Add Student</button></div>
      </form>
    </div>

    <!-- ✅ Student List -->
    <table id="studentsTable" class="table table-hover">
      <thead>
        <tr><th>ID</th><th>Student No</th><th>Name</th><th>Email</th><th>Class</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($students as $s): ?>
        <tr>
          <td><?= $s['id'] ?></td>
          <td><?= htmlspecialchars($s['student_no']) ?></td>
          <td><?= htmlspecialchars($s['name']) ?></td>
          <td><?= htmlspecialchars($s['email']) ?></td>
          <td><?= htmlspecialchars($s['class']) ?></td>
          <td>
            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $s['id'] ?>"><i class="bi bi-pencil"></i></button>
            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this student?');">
              <input type="hidden" name="id" value="<?= $s['id'] ?>">
              <input type="hidden" name="action" value="delete">
              <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
            </form>
            <a href="form137.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-primary"><i class="bi bi-printer"></i></a>
          </td>
        </tr>

        <!-- ✅ Edit Modal -->
        <div class="modal fade" id="editModal<?= $s['id'] ?>" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header"><h5>Edit Student</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
              <form method="POST">
                <div class="modal-body">
                  <input type="hidden" name="id" value="<?= $s['id'] ?>">
                  <input type="hidden" name="action" value="edit">
                  <div class="mb-3"><label>Name</label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($s['name']) ?>" required></div>
                  <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($s['email']) ?>" required></div>
                  <div class="mb-3"><label>Class</label><input type="text" name="class" class="form-control" value="<?= htmlspecialchars($s['class']) ?>" required></div>
                  <div class="mb-3"><label>New Password (optional)</label><input type="password" name="password" class="form-control"></div>
                </div>
                <div class="modal-footer"><button class="btn btn-primary">Save Changes</button></div>
              </form>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>$(document).ready(()=>$('#studentsTable').DataTable());</script>
</body>
</html>