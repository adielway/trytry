<?php
require_once '../config.php';
require_role(['admin']);

// --- ADD NEW TEACHER ---
if (isset($_POST['action']) && $_POST['action'] === 'add') {
  $name = $_POST['name'];
  $email = $_POST['email'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'teacher')");
  $stmt->execute([$name, $email, $password]);

  header("Location: manage_teachers.php");
  exit;
}

// --- EDIT TEACHER ---
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
  $id = $_POST['id'];
  $name = $_POST['name'];
  $email = $_POST['email'];

  $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ? AND role = 'teacher'");
  $stmt->execute([$name, $email, $id]);

  header("Location: manage_teachers.php");
  exit;
}

// --- DELETE TEACHER ---
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
  $id = $_POST['id'];
  $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'teacher'")->execute([$id]);
  $pdo->prepare("DELETE FROM assigned_subjects WHERE teacher_id = ?")->execute([$id]);
  header("Location: manage_teachers.php");
  exit;
}

// --- ASSIGN SUBJECT ---
if (isset($_POST['action']) && $_POST['action'] === 'assign_subject') {
  $teacher_id = $_POST['teacher_id'];
  $subject_id = $_POST['subject_id'];
  $section = trim($_POST['section'] ?? '');

  // Prevent duplicate assignments for same teacher + subject + section
  $check = $pdo->prepare("SELECT 1 FROM assigned_subjects WHERE teacher_id = ? AND subject_id = ? AND COALESCE(section,'') = COALESCE(?, '')");
  $check->execute([$teacher_id, $subject_id, $section]);
  if (!$check->fetch()) {
    $stmt = $pdo->prepare("INSERT INTO assigned_subjects (teacher_id, subject_id, section) VALUES (?, ?, ?)");
    $stmt->execute([$teacher_id, $subject_id, $section]);
  }

  header("Location: manage_teachers.php");
  exit;
}

// --- FETCH TEACHERS ---
$teachers = $pdo->query("
  SELECT 
    u.id, 
    u.name, 
    u.email, 
    COALESCE(STRING_AGG(s.name, ', '), '—') AS subjects
  FROM users u
  LEFT JOIN assigned_subjects asg ON u.id = asg.teacher_id
  LEFT JOIN subjects s ON asg.subject_id = s.id
  WHERE u.role = 'teacher'
  GROUP BY u.id, u.name, u.email
  ORDER BY u.id ASC
")->fetchAll(PDO::FETCH_ASSOC);

// --- FETCH SUBJECTS ---
$subjects = $pdo->query("SELECT id, name FROM subjects ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Teachers | Admin Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #0b1d39;
      color: #fff;
      min-height: 100vh;
      display: flex;
      overflow-x: hidden;
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
      transition: transform 0.3s ease;
      z-index: 1000;
    }
    .sidebar.collapsed { transform: translateX(-100%); }
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
      transition: margin-left 0.3s ease;
    }
    .main.full { margin-left: 0 !important; }
    .table {
      color: #fff;
      background: rgba(255, 255, 255, 0.12);
      border-radius: 8px;
      overflow: hidden;
    }
    .table thead { background: rgba(255, 255, 255, 0.15); }
    .btn-primary { background-color: #1e90ff; border: none; }
    .btn-primary:hover { background-color: #0f78d1; }
    .toggle-btn {
      position: fixed; top: 15px; left: 15px; z-index: 1100;
      cursor: pointer; transition: transform 0.2s ease;
    }
    .toggle-btn img {
      width: 45px; height: 45px; border-radius: 50%;
      border: 2px solid #fff; object-fit: cover;
    }
    .toggle-btn:hover { transform: scale(1.1); }
  </style>
</head>
<body>
  <div class="sidebar" id="sidebar">
    <h3>Admin Panel</h3>
    <a href="dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a>
    <a href="manage_advisers.php"><i class="bi bi-person-badge"></i> Manage Advisers</a>
    <a href="manage_teachers.php" class="active"><i class="bi bi-person-badge"></i> Manage Teachers</a>
    <a href="assign_students.php"><i class="bi bi-person-lines-fill"></i> Assign Students</a>
    <a href="manage_students.php"><i class="bi bi-mortarboard"></i> Manage Students</a>
    <a href="manage_subjects.php"><i class="bi bi-book"></i> Manage Subjects</a>
    <a href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
  </div>

  <div class="toggle-btn" onclick="toggleSidebar()">
    <img src="../logo.png" alt="Toggle Sidebar">
  </div>

  <div class="main" id="main">
    <h2 class="mb-4">Manage Teachers</h2>

    <!-- Add Teacher Form -->
    <form method="POST" class="row g-3 mb-4">
      <input type="hidden" name="action" value="add">
      <div class="col-md-4">
        <input type="text" name="name" class="form-control" placeholder="Teacher Name" required>
      </div>
      <div class="col-md-4">
        <input type="email" name="email" class="form-control" placeholder="Email" required>
      </div>
      <div class="col-md-4">
        <input type="password" name="password" class="form-control" placeholder="Password" required>
      </div>
      <div class="col-12 text-end">
        <button class="btn btn-primary">Add Teacher</button>
      </div>
    </form>

    <!-- Teachers Table -->
    <table class="table table-bordered table-hover align-middle">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Assigned Subjects</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($teachers as $t): ?>
        <tr>
          <td><?= $t['id'] ?></td>
          <td><?= htmlspecialchars($t['name']) ?></td>
          <td><?= htmlspecialchars($t['email']) ?></td>
          <td><?= htmlspecialchars($t['subjects']) ?></td>
          <td>
            <!-- Assign Subject -->
            <form method="POST" class="d-flex mb-2">
                <input type="hidden" name="action" value="assign_subject">
                <input type="hidden" name="teacher_id" value="<?= $t['id'] ?>">
                <select name="subject_id" class="form-select form-select-sm me-2" required>
                  <option value="">Assign Subject</option>
                  <?php foreach ($subjects as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                  <?php endforeach; ?>
                </select>

                <!-- NEW: pick section -->
                <select name="section" class="form-select form-select-sm me-2" required>
                  <option value="">Select Section</option>
                  <option value="Grade 7">Grade 7</option>
                  <option value="Grade 8">Grade 8</option>
                  <option value="Grade 9">Grade 9</option>
                  <option value="Grade 10">Grade 10</option>
                  <!-- You can generate options dynamically later -->
                </select>

                <button class="btn btn-sm btn-primary">Assign</button>
              </form>

            <!-- Edit -->
            <button type="button" class="btn btn-sm btn-warning"
              onclick="openEditModal('<?= $t['id'] ?>','<?= htmlspecialchars($t['name']) ?>','<?= htmlspecialchars($t['email']) ?>')">
              Edit
            </button>

            <!-- Delete -->
            <form method="POST" class="d-inline">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $t['id'] ?>">
              <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this teacher?')">Delete</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Edit Modal -->
  <div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content text-dark">
        <form method="POST">
          <input type="hidden" name="action" value="edit">
          <input type="hidden" id="editId" name="id">
          <div class="modal-header">
            <h5 class="modal-title">Edit Teacher</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <label>Name</label>
            <input type="text" id="editName" name="name" class="form-control mb-3" required>
            <label>Email</label>
            <input type="email" id="editEmail" name="email" class="form-control" required>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button class="btn btn-primary">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      const main = document.getElementById('main');
      sidebar.classList.toggle('collapsed');
      main.classList.toggle('full');
    }

    const editModal = new bootstrap.Modal(document.getElementById('editModal'));
    function openEditModal(id, name, email) {
      document.getElementById('editId').value = id;
      document.getElementById('editName').value = name;
      document.getElementById('editEmail').value = email;
      editModal.show();
    }
  </script>
</body>
</html>
