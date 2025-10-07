<?php
require_once __DIR__ . '/config.php';
require_login();

$user = $_SESSION['user'];

// Determine view context for students/parents
$student_id = null;
if ($user['role'] === 'student') {
    $stmt = $pdo->prepare("SELECT s.id FROM students s JOIN users u ON s.user_id = u.id WHERE u.id = ? LIMIT 1");
    $stmt->execute([$user['id']]);
    $row = $stmt->fetch();
    $student_id = $row ? (int)$row['id'] : null;
} elseif ($user['role'] === 'parent') {
    $stmt = $pdo->prepare("SELECT child_student_id AS id FROM parents WHERE user_id = ? LIMIT 1");
    $stmt->execute([$user['id']]);
    $row = $stmt->fetch();
    $student_id = $row ? (int)$row['id'] : null;
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
  body {
    background-color: #4169E1; /* Royal Blue */
    min-height: 100vh;
  }

  .card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
  }

  .navbar {
    background-color: #27408B !important; /* Darker shade of blue for navbar */
  }

  .navbar .navbar-brand, 
  .navbar .navbar-text, 
  .navbar .btn {
    color: white !important;
  }

  .navbar .btn:hover {
  background-color: #1E90FF !important; /* Dodger Blue */
  color: #fff !important;               /* White text on hover */
  border-color: #1E90FF !important;     /* Match border */
}

  .table {
    background-color: white;
  }

  .watermark-tiles {
  position: fixed;
  inset: 0;                 /* top:0; right:0; bottom:0; left:0; */
  pointer-events: none;     /* don't block clicks */
  z-index: 9999;
  background-repeat: repeat;
  background-position: 0 0;
  background-size: 320px 200px; /* tile size — adjust if needed */
  transform: rotate(0deg);
  opacity: 0.50;            /* watermark transparency */
  mix-blend-mode: normal;
  }
</style>
</head>
<body>

<div id="watermark" class="watermark-tiles"></div>

<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php">GRADING PORTAL</a>
    <div class="d-flex">
      <span class="navbar-text me-3"><?= h($user['name']) ?> (<?= h($user['role']) ?>)</span>
      <a class="btn btn-outline-secondary btn-sm" href="logout.php">LOGOUT</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <?php if ($user['role'] === 'teacher'): ?>
    <div class="row">
      <div class="col-lg-4">
        <div class="card mb-4 shadow-sm">
          <div class="card-header">Students</div>
          <div class="card-body">
            <form class="mb-3" method="get" action="">
              <input type="hidden" name="filter" value="1">
              <input type="text" name="q" class="form-control" placeholder="Search by name or class" value="<?= h($_GET['q'] ?? '') ?>">
            </form>
            <?php
            $q = '%' . ($_GET['q'] ?? '') . '%';
            $stmt = $pdo->prepare("SELECT id, student_no, name, class FROM students WHERE name LIKE ? OR class LIKE ? ORDER BY class, name");
            $stmt->execute([$q, $q]);
            $students = $stmt->fetchAll();
            ?>
            <div class="list-group small">
              <?php foreach ($students as $s): ?>
                <a class="list-group-item list-group-item-action" href="?student=<?= (int)$s['id'] ?>">
                  <strong><?= h($s['name']) ?></strong><br>
                  <span class="text-muted"><?= h($s['class']) ?> · <?= h($s['student_no']) ?></span>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-8">
        <div class="card shadow-sm">
          <div class="card-header">Manage Grades</div>
          <div class="card-body">
            <?php
            $selected_student = isset($_GET['student']) ? (int)$_GET['student'] : null;
            if ($selected_student):
              $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
              $stmt->execute([$selected_student]);
              $s = $stmt->fetch();
              if ($s):
            ?>
              <h5 class="mb-3"><?= h($s['name']) ?> <span class="text-muted small">/ <?= h($s['class']) ?></span></h5>
              <form class="row g-2 mb-4" method="post" action="grade_save.php">
                <input type="hidden" name="student_id" value="<?= (int)$s['id'] ?>">
                <div class="col-md-4">
                  <label class="form-label">Subject</label>
                  <select name="subject_id" class="form-select" required>
                    <?php
                    $subs = $pdo->query("SELECT id, name FROM subjects ORDER BY name")->fetchAll();
                    foreach ($subs as $sub) {
                        echo '<option value="'.(int)$sub['id'].'">'.h($sub['name']).'</option>';
                    }
                    ?>
                  </select>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Quarter</label>
                  <select name="period" class="form-select" required>
                    <option value="1">Q1</option>
                    <option value="2">Q2</option>
                    <option value="3">Q3</option>
                    <option value="4">Q4</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Grade</label>
                  <input type="number" step="0.01" min="0" max="100" name="grade" class="form-control" required>
                </div>
                <div class="col-md-2 d-grid">
                  <label class="form-label">&nbsp;</label>
                  <button class="btn btn-primary">Save</button>
                </div>
              </form>
              <?php
              $quarters = [1, 2, 3, 4];
              $grades_by_q = [];
              foreach ($quarters as $q) {
                  $stmt = $pdo->prepare("
                      SELECT g.id, sub.name AS subject, g.grade, g.created_at
                      FROM grades g
                      JOIN subjects sub ON sub.id = g.subject_id
                      WHERE g.student_id = ? AND g.period = ?
                      ORDER BY sub.name
                  ");
                  $stmt->execute([$s['id'], $q]);
                  $grades_by_q[$q] = $stmt->fetchAll();
              }
              ?>

              <?php foreach ($grades_by_q as $quarter => $grades): ?>
                <h5 class="mt-3">Quarter <?= $quarter ?></h5>
                <div class="table-responsive mb-4">
                  <table class="table table-striped">
                    <thead>
                        <tr>
                          <th>Subject</th>
                          <th>Grade</th>
                          <th>Remarks</th>
                          <th>Created</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                    <tbody>
                      <?php if (empty($grades)): ?>
                        <tr>
                          <td colspan="4" class="text-muted">No grades for Quarter <?= $quarter ?>.</td>
                        </tr>
                      <?php else: ?>
                        <?php
                        $total = 0;
                        foreach ($grades as $g) { $total += $g['grade']; }
                        $avg = $total / count($grades);
                        ?>
                        <?php foreach ($grades as $g): ?>
                                  <tr>
                                    <td><?= h($g['subject']) ?></td>
                                    <td><?= h($g['grade']) ?></td>
                                        <td>
                                          <?php if ($g['grade'] >= 75): ?>
                                            <span class="text-success">PASSED</span>
                                          <?php else: ?>
                                            <span class="text-danger">FAILED</span>
                                          <?php endif; ?>
                                        </td>
                                        <td><?= h($g['created_at']) ?></td>
                                   <td class="text-end">
                                  <!-- Edit button -->
                                  <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleEditForm(<?= (int)$g['id'] ?>)">
                                    Edit
                                  </button>

                                  <!-- Delete form -->
                                  <form method="post" action="grade_delete.php" onsubmit="return confirm('Delete this grade?');" style="display:inline-block;">
                                    <input type="hidden" name="id" value="<?= (int)$g['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                  </form>
                                </td>
                          </tr>
                          <!-- Inline edit row -->
<tr id="edit-form-<?= (int)$g['id'] ?>" style="display:none;">
  <td colspan="100%">
    <form method="post" action="grade_edit.php" class="d-flex align-items-center gap-2">
      <input type="hidden" name="id" value="<?= (int)$g['id'] ?>">

      <!-- Only grade field -->
      <input type="number" name="grade" class="form-control" value="<?= (int)$g['grade'] ?>" required min="0" max="100">

      <button type="submit" class="btn btn-success btn-sm">Save</button>
      <button type="button" class="btn btn-secondary btn-sm" onclick="toggleEditForm(<?= (int)$g['id'] ?>)">Cancel</button>
    </form>
  </td>
</tr>
</tr>

                        <?php endforeach; ?>
                        <tr class="fw-bold">
                          <td>Average</td>
                          <td><?= number_format($avg, 2) ?></td>
                          <td>
                            <?php if ($avg >= 95): ?>
                              <span class="text-primary">WITH HIGH HONORS</span>
                               <?php elseif ($avg >=90): ?>
                              <span class="text-primary">WITH HONORS</span>
                            <?php elseif ($avg < 75): ?>
                              <span class="text-danger">FAILED</span>
                            <?php else: ?>
                              <span class="text-success">PASSED</span>
                            <?php endif; ?>
                          </td>
                          <td colspan="2"></td>
                        </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p class="text-muted">Student not found.</p>
            <?php endif; else: ?>
              <p class="text-muted">Select a student to manage grades.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  <?php else: ?>
    <div class="card shadow-sm">
      <div class="card-header">My Grades</div>
      <div class="card-body">
        <?php if (!$student_id): ?>
          <p class="text-muted">No student record linked to this account.</p>
        <?php else: 
          $stmt = $pdo->prepare("SELECT name, class FROM students WHERE id = ?");
          $stmt->execute([$student_id]);
          $s = $stmt->fetch();
          ?>
          <h5 class="mb-3"><?= h($s['name']) ?> <span class="text-muted small">/ <?= h($s['class']) ?></span></h5>
          <?php
          $quarters = [1, 2, 3, 4];
          $grades_by_q = [];
          foreach ($quarters as $q) {
              $stmt = $pdo->prepare("
                  SELECT sub.name AS subject, g.grade
                  FROM grades g
                  JOIN subjects sub ON sub.id = g.subject_id
                  WHERE g.student_id = ? AND g.period = ?
                  ORDER BY sub.name
              ");
              $stmt->execute([$student_id, $q]);
              $grades_by_q[$q] = $stmt->fetchAll();
          }
          ?>

          <?php foreach ($grades_by_q as $quarter => $grades): ?>
            <h5 class="mt-3">Quarter <?= $quarter ?></h5>
            <div class="table-responsive mb-4">
              <table class="table table-striped">
                <thead>
                    <tr>
                      <th>Subject</th>
                      <th>Grade</th>
                      <th>Remarks</th>
                    </tr>
                  </thead>
                <tbody>
                  <?php if (empty($grades)): ?>
                    <tr>
                      <td colspan="2" class="text-muted">No grades for Quarter <?= $quarter ?>.</td>
                    </tr>
                  <?php else: ?>
                    <?php
                    $total = 0;
                    foreach ($grades as $g) { $total += $g['grade']; }
                    $avg = $total / count($grades);
                    ?>
                    <?php foreach ($grades as $g): ?>
                      <tr>
                          <td><?= h($g['subject']) ?></td>
                          <td><?= h($g['grade']) ?></td>
                    <td>
                      <?php if ($g['grade'] >= 75): ?>
                        <span class="text-success">PASSED</span>
                      <?php else: ?>
                        <span class="text-danger">FAILED</span>
                      <?php endif; ?>
                    </td>

                        </tr>
                      <?php endforeach; ?>
                    <tr class="fw-bold">
                          <td>Average</td>
                          <td><?= number_format($avg, 2) ?></td>
                          <td>
                          <?php if ($avg >= 95): ?>
                              <span class="text-primary">WITH HIGH HONORS</span>
                               <?php elseif ($avg >=90): ?>
                              <span class="text-primary">WITH HONORS</span>
                            <?php elseif ($avg < 75): ?>
                              <span class="text-danger">FAILED</span>
                            <?php else: ?>
                              <span class="text-success">PASSED</span>
                            <?php endif; ?>
                          </td>
                          <td colspan="2"></td>
                        </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</div>
<script>
(function(){
  // server-side value for user identification
  const wmOwner = <?= json_encode($_SESSION['user']['email'] ?? ($_SESSION['user']['name'] ?? 'user')) ?>;

  // returns an SVG string with the watermark text
  function makeSVG(text) {
    const ts = new Date().toLocaleString();
    // width/height match background-size in CSS (320x200)
    return `
      <svg xmlns='http://www.w3.org/2000/svg' width='320' height='200'>
        <style>
          text { font-family: Arial, sans-serif; font-weight: 700; font-size:18px; fill: #000000; fill-opacity:0.12; }
        </style>
        <text x='50%' y='50%' dominant-baseline='middle' text-anchor='middle'>
          ${escapeXml(text + ' • ' + ts)}
        </text>
      </svg>
    `;
  }

  // simple XML escape for safety
  function escapeXml(unsafe) {
    return unsafe.replace(/[&<>"']/g, function (c) {
      return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&apos;'}[c];
    });
  }

  // create the tiled watermark overlay
  const overlay = document.createElement('div');
  overlay.className = 'watermark-tiles';
  document.body.appendChild(overlay);

  // set background to the encoded SVG
  function updateWatermark() {
    const svg = makeSVG(wmOwner);
    const url = 'data:image/svg+xml;utf8,' + encodeURIComponent(svg);
    overlay.style.backgroundImage = `url("${url}")`;
  }

  // initial set + periodic update so timestamp changes
  updateWatermark();
  setInterval(updateWatermark, 10000); // update every 10s
})();
</script>
<script> 
  function toggleEditForm(id) {
  let row = document.getElementById("edit-form-" + id);
  row.style.display = (row.style.display === "none" ? "table-row" : "none");
}
</script>
</body>
</html>
