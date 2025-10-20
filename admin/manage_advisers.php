<?php
require_once '../config.php';
require_role(['admin']);

// Handle new adviser creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $email = $_POST['email'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $section = $_POST['section'];
  $school_year = $_POST['school_year'];

  // 1️⃣ Create adviser user account
  $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'adviser')");
  $stmt->execute([$name, $email, $password]);
  $userId = $pdo->lastInsertId();

  // 2️⃣ Create adviser record
  $stmt = $pdo->prepare("INSERT INTO advisers (user_id, section, school_year) VALUES (?, ?, ?)");
  $stmt->execute([$userId, $section, $school_year]);

  header("Location: manage_advisers.php");
  exit;
}

// Fetch all advisers with their linked user info
$advisers = $pdo->query("
  SELECT a.id, u.name, u.email, a.section, a.school_year
  FROM advisers a
  JOIN users u ON a.user_id = u.id
  ORDER BY a.id ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Advisers | Admin Panel</title>
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
    <h2 class="mb-4">Manage Advisers</h2>

    <form method="POST" class="row g-3 mb-4">
      <div class="col-md-3">
        <input type="text" name="name" class="form-control" placeholder="Adviser Name" required>
      </div>
      <div class="col-md-3">
        <input type="email" name="email" class="form-control" placeholder="Email" required>
      </div>
      <div class="col-md-2">
        <input type="text" name="section" class="form-control" placeholder="Section" required>
      </div>
      <div class="col-md-2">
        <input type="text" name="school_year" class="form-control" placeholder="School Year" required>
      </div>
      <div class="col-md-2">
        <input type="password" name="password" class="form-control" placeholder="Password" required>
      </div>
      <div class="col-12 text-end">
        <button class="btn btn-primary">Add Adviser</button>
      </div>
    </form>

    <table class="table table-bordered table-hover">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Section</th>
          <th>School Year</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($advisers as $a): ?>
          <tr>
            <td><?= $a['id'] ?></td>
            <td><?= htmlspecialchars($a['name']) ?></td>
            <td><?= htmlspecialchars($a['email']) ?></td>
            <td><?= htmlspecialchars($a['section']) ?></td>
            <td><?= htmlspecialchars($a['school_year']) ?></td>
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
