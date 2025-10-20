<?php
require_once '../config.php';
require_role(['admin']);

// Fetch all advisers (for dropdown)
$advisers = $pdo->query("
  SELECT a.id AS adviser_id, u.name AS adviser_name, a.section
  FROM advisers a
  JOIN users u ON a.user_id = u.id
  ORDER BY u.name ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all students (for table and selection)
$students = $pdo->query("
  SELECT s.id, s.student_no, s.name, s.class, 
         COALESCE(u2.name, '—') AS adviser_name
  FROM students s
  LEFT JOIN advisers a ON s.adviser_id = a.id
  LEFT JOIN users u2 ON a.user_id = u2.id
  ORDER BY s.id ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Handle assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'], $_POST['adviser_id'])) {
    $student_id = $_POST['student_id'];
    $adviser_id = $_POST['adviser_id'];

    $stmt = $pdo->prepare("UPDATE students SET adviser_id = ? WHERE id = ?");
    $stmt->execute([$adviser_id, $student_id]);

    header("Location: assign_students.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Assign Students | Admin Panel</title>
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
    .card {
      border: none;
      border-radius: 12px;
      backdrop-filter: blur(8px);
      background: rgba(255, 255, 255, 0.12);
      color: #fff;
    }
    .table {
      color: #fff;
      background: rgba(255, 255, 255, 0.12);
      border-radius: 8px;
      overflow: hidden;
    }
    .table thead { background: rgba(255, 255, 255, 0.15); }
    .btn-primary {
      background-color: #1e90ff;
      border: none;
    }
    .btn-primary:hover { background-color: #0f78d1; }
    .toggle-btn {
      position: fixed;
      top: 15px;
      left: 15px;
      z-index: 1100;
      cursor: pointer;
      transition: transform 0.2s ease;
    }
    .toggle-btn img {
      width: 45px;
      height: 45px;
      border-radius: 50%;
      border: 2px solid #fff;
      object-fit: cover;
    }
    .toggle-btn:hover { transform: scale(1.1); }
  </style>
</head>
<body>
  <div class="sidebar" id="sidebar">
    <h3>Admin Panel</h3>
    <a href="dashboard.php" class="active"><i class="bi bi-house-door"></i> Dashboard</a>
    <a href="manage_advisers.php"><i class="bi bi-person-badge"></i> Manage Advisers</a>
    <a href="manage_teachers.php"><i class="bi bi-person-badge"></i> Manage Teachers</a>
    <a href="assign_students.php"><i class="bi bi-person-lines-fill"></i> Assign Students</a>
    <a href="manage_students.php"><i class="bi bi-mortarboard"></i> Manage Students</a>
    <a href="manage_subjects.php"><i class="bi bi-book"></i> Manage Subjects</a>
    <a href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
  </div>

  <div class="toggle-btn" onclick="toggleSidebar()">
    <img src="../logo.png" alt="Toggle Sidebar">
  </div>

  <div class="main" id="main">
    <h2 class="mb-4">Assign Students to Advisers</h2>

    <table class="table table-bordered table-hover align-middle">
      <thead>
        <tr>
          <th>ID</th>
          <th>Student No</th>
          <th>Name</th>
          <th>Class</th>
          <th>Current Adviser</th>
          <th>Assign Adviser</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($students as $s): ?>
        <tr>
          <td><?= $s['id'] ?></td>
          <td><?= htmlspecialchars($s['student_no']) ?></td>
          <td><?= htmlspecialchars($s['name']) ?></td>
          <td><?= htmlspecialchars($s['class']) ?></td>
          <td><?= htmlspecialchars($s['adviser_name']) ?></td>
          <td>
            <form method="POST" class="d-flex">
              <input type="hidden" name="student_id" value="<?= $s['id'] ?>">
              <select name="adviser_id" class="form-select form-select-sm me-2" required>
                <option value="">-- Select Adviser --</option>
                <?php foreach ($advisers as $a): ?>
                  <option value="<?= $a['adviser_id'] ?>"><?= htmlspecialchars($a['adviser_name']) ?> (<?= htmlspecialchars($a['section']) ?>)</option>
                <?php endforeach; ?>
              </select>
              <button class="btn btn-sm btn-primary">Assign</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <script>
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      const main = document.getElementById('main');
      sidebar.classList.toggle('collapsed');
      main.classList.toggle('full');
    }
  </script>
</body>
</html>
