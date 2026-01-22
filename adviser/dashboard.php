<?php
require_once '../config.php';
require_role(['adviser']);

/* ------------------------------
   FETCH ADVISER
------------------------------ */
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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $class = trim($_POST['class']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,'student')");
        $stmt->execute([$name, $email, $password]);
        $uid = $pdo->lastInsertId();

        $student_no = 'S-' . str_pad($uid, 4, '0', STR_PAD_LEFT);
        $pdo->prepare("
            INSERT INTO students (student_no,name,class,user_id,adviser_id)
            VALUES (?,?,?,?,?)
        ")->execute([$student_no, $name, $class, $uid, $adviser_data['id']]);

        $pdo->commit();
        $message = "✅ Student successfully added!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}

/* ------------------------------
   EDIT STUDENT
------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit') {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $class = trim($_POST['class']);
    $password = $_POST['password'];

    try {
        $pdo->beginTransaction();

        $pdo->prepare("UPDATE students SET name=?, class=? WHERE id=?")
            ->execute([$name, $class, $id]);

        if ($password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare("
              UPDATE users SET name=?, email=?, password_hash=?
              WHERE id=(SELECT user_id FROM students WHERE id=?)
            ")->execute([$name, $email, $hash, $id]);
        } else {
            $pdo->prepare("
              UPDATE users SET name=?, email=?
              WHERE id=(SELECT user_id FROM students WHERE id=?)
            ")->execute([$name, $email, $id]);
        }

        $pdo->commit();
        $message = "✅ Student updated successfully!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}

/* ------------------------------
   DELETE STUDENT
------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = $_POST['id'];
    try {
        $pdo->beginTransaction();
        $pdo->prepare("DELETE FROM users WHERE id=(SELECT user_id FROM students WHERE id=?)")->execute([$id]);
        $pdo->prepare("DELETE FROM students WHERE id=?")->execute([$id]);
        $pdo->commit();
        $message = "✅ Student deleted successfully!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}

/* ------------------------------
   SAVE BEHAVIORAL REMARK
------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_remark') {
    $student_id = $_POST['student_id'];
    $quarter = $_POST['quarter'];
    $remark = trim($_POST['remark']);

    $pdo->prepare("
      INSERT INTO behavioral_remarks (student_id,quarter,remark)
      VALUES (?,?,?)
      ON CONFLICT (student_id,quarter)
      DO UPDATE SET remark=EXCLUDED.remark
    ")->execute([$student_id, $quarter, $remark]);

    $message = "✅ Behavioral remark saved.";
}

/* ------------------------------
   FETCH STUDENTS
------------------------------ */
$stmt = $pdo->prepare("
  SELECT s.id, s.student_no, s.name, s.class, u.email
  FROM students s
  JOIN users u ON s.user_id = u.id
  WHERE s.adviser_id = ?
  ORDER BY s.name
");
$stmt->execute([$adviser_data['id']]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Adviser Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="bg-dark text-light">
<div class="container-fluid p-4">

<h3>Adviser Dashboard — Section <?= htmlspecialchars($section) ?></h3>

<?php if (!empty($message)): ?>
<div class="alert alert-success"><?= $message ?></div>
<?php elseif (!empty($error)): ?>
<div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<table class="table table-dark table-hover mt-4">
<thead>
<tr>
<th>ID</th>
<th>Student No</th>
<th>Name</th>
<th>Email</th>
<th>Class</th>
<th>Actions</th>
</tr>
</thead>
<tbody>

<?php foreach ($students as $s): ?>
<tr>
<td><?= $s['id'] ?></td>
<td><?= $s['student_no'] ?></td>
<td><?= htmlspecialchars($s['name']) ?></td>
<td><?= htmlspecialchars($s['email']) ?></td>
<td><?= htmlspecialchars($s['class']) ?></td>
<td class="text-nowrap">

<!-- EDIT -->
<button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#edit<?= $s['id'] ?>">
<i class="bi bi-pencil"></i>
</button>

<!-- DELETE -->
<form method="POST" class="d-inline">
<input type="hidden" name="action" value="delete">
<input type="hidden" name="id" value="<?= $s['id'] ?>">
<button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
</form>

<!-- PRINT -->
<a href="form137.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-primary">
<i class="bi bi-printer"></i>
</a>

<!-- BEHAVIOR -->
<button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#remark<?= $s['id'] ?>">
<i class="bi bi-journal-text"></i>
</button>

</td>
</tr>

<!-- REMARK MODAL -->
<div class="modal fade" id="remark<?= $s['id'] ?>">
<div class="modal-dialog">
<div class="modal-content text-dark">
<form method="POST">
<div class="modal-header"><h5>Behavioral Remark</h5></div>
<div class="modal-body">
<input type="hidden" name="action" value="save_remark">
<input type="hidden" name="student_id" value="<?= $s['id'] ?>">
<select name="quarter" class="form-select mb-2" required>
<option value="">Quarter</option>
<option value="1">1</option>
<option value="2">2</option>
<option value="3">3</option>
<option value="4">4</option>
</select>
<textarea name="remark" class="form-control" rows="4" required></textarea>
</div>
<div class="modal-footer">
<button class="btn btn-primary">Save</button>
</div>
</form>
</div>
</div>
</div>

<?php endforeach; ?>

</tbody>
</table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
