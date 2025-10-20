<?php
require_once '../config.php';
require_role(['admin']);

$teacherCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role='teacher'")->fetchColumn();
$adviserrCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role='adviser'")->fetchColumn();
$studentCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$subjectCount = $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard | ANHS Portal</title>
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

    /* Background Image */
    body::before {
      content: "";
      position: fixed;
      inset: 0;
      background: url('../anhs.jpg') no-repeat center center fixed;
      background-size: cover;
      opacity: 0.25;
      z-index: -1;
    }

    /* Sidebar */
    .sidebar {
      width: 250px;
      background: rgba(0, 51, 102, 0.95);
      padding-top: 20px;
      flex-shrink: 0;
      display: flex;
      flex-direction: column;
      height: 100vh;
      position: fixed;
      left: 0;
      top: 0;
      transition: transform 0.3s ease;
      z-index: 1000;
    }

    .sidebar.collapsed {
      transform: translateX(-100%);
    }

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

    .sidebar a:hover,
    .sidebar a.active {
      background-color: rgba(255, 255, 255, 0.1);
      border-left: 3px solid #4ea1ff;
      color: #fff;
    }

    /* Toggle Button (Image) */
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

    .toggle-btn:hover {
      transform: scale(1.1);
    }

    /* Main Content */
    .main {
      margin-left: 250px;
      padding: 30px;
      flex-grow: 1;
      transition: margin-left 0.3s ease;
    }

    .main.full {
      margin-left: 0;
    }

    .dashboard-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 30px;
    }

    .card {
      border: none;
      border-radius: 12px;
      backdrop-filter: blur(8px);
      background: rgba(255, 255, 255, 0.12);
      color: #fff;
      transition: transform 0.2s ease;
    }

    .card:hover {
      transform: translateY(-4px);
      background: rgba(255, 255, 255, 0.18);
    }

    .footer {
      text-align: center;
      color: #9cb4dd;
      font-size: 0.85rem;
      margin-top: 50px;
    }
  </style>
</head>
<body>

  <!-- Sidebar -->
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

  <!-- Toggle Button (Image) -->
  <div class="toggle-btn" onclick="toggleSidebar()">
    <img src="../logo.png" alt="Toggle Sidebar">
  </div>

  <!-- Main Content -->
  <div class="main" id="main">
    <div class="dashboard-header">
      <h2>Welcome, <?= htmlspecialchars($_SESSION['user']['name']) ?></h2>
      <span class="text-light-50 small">Administrator Dashboard</span>
    </div>

    <div class="row g-4">
      <div class="col-md-4">
        <div class="card p-4 text-center shadow-sm">
          <h3><?= $teacherCount ?></h3>
          <p><i class="bi bi-person-badge-fill"></i> Teachers</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card p-4 text-center shadow-sm">
          <h3><?= $adviserCount ?></h3>
          <p><i class="bi bi-person-badge-fill"></i> Advisers</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card p-4 text-center shadow-sm">
          <h3><?= $studentCount ?></h3>
          <p><i class="bi bi-mortarboard-fill"></i> Students</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card p-4 text-center shadow-sm">
          <h3><?= $subjectCount ?></h3>
          <p><i class="bi bi-book-fill"></i> Subjects</p>
        </div>
      </div>
    </div>

    <div class="footer mt-5">
      <p>© <?= date('Y') ?> Amlan National High School | Grading Portal Admin</p>
    </div>
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
