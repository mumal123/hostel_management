<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'warden') {
    header('Location: index.php');
    exit;
}

$user = $_SESSION['user'];

/* -------------------------
   Quick summary numbers
------------------------- */
$cnt = function($sql) use ($conn) {
    $r = mysqli_query($conn, $sql);
    if (!$r) return 0;
    $row = mysqli_fetch_row($r);
    return intval($row[0] ?? 0);
};

$open_complaints = $cnt("SELECT COUNT(*) FROM complaints WHERE status='open'");
$inprogress_complaints = $cnt("SELECT COUNT(*) FROM complaints WHERE status='in-progress'");
$closed_complaints = $cnt("SELECT COUNT(*) FROM complaints WHERE status='closed'");

$total_tasks = $cnt("SELECT COUNT(*) FROM tasks");
$total_shifts = $cnt("SELECT COUNT(*) FROM shifts");
$total_staff = $cnt("SELECT COUNT(*) FROM users WHERE role IN ('staff','security','caretaker')");
$total_students = $cnt("SELECT COUNT(*) FROM users WHERE role='student'");
$total_rooms = $cnt("SELECT COUNT(*) FROM rooms");
$occupied_beds = $cnt("SELECT COUNT(*) FROM allocations WHERE active=1");
$vacant_beds = max(0, $total_rooms * 1 - $occupied_beds); // simple, assuming 1 capacity default; adjust if needed

/* -------------------------
   Recent complaints (5)
------------------------- */
$recent_complaints_res = mysqli_query($conn,
    "SELECT c.id, c.title, c.description, c.status, c.created_at, u.name AS student_name
     FROM complaints c
     LEFT JOIN users u ON c.student_id = u.id
     ORDER BY c.created_at DESC
     LIMIT 5"
);
$recent_complaints = $recent_complaints_res ? mysqli_fetch_all($recent_complaints_res, MYSQLI_ASSOC) : [];

/* -------------------------
   Upcoming Duty Assignments (next 7 days)
------------------------- */
$upcoming_res = mysqli_query($conn,
    "SELECT ss.id, u.name AS staff_name, t.title AS task_name, sh.name AS shift_name,
            ss.day_of_week, ss.start_date, ss.end_date, sh.start_time, sh.end_time
     FROM staff_shifts ss
     LEFT JOIN users u ON ss.staff_user_id = u.id
     LEFT JOIN tasks t ON ss.task_id = t.id
     LEFT JOIN shifts sh ON ss.shift_id = sh.id
     WHERE ss.end_date >= CURDATE()
     ORDER BY ss.start_date ASC
     LIMIT 8"
);
$upcoming = $upcoming_res ? mysqli_fetch_all($upcoming_res, MYSQLI_ASSOC) : [];
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Warden - Dashboard</title>
<link rel="stylesheet" href="assets/style.css?v=7">
</head>
<body>

<?php include 'warden_sidebar.php'; ?>

<div class="main-content">

  <h1>Warden Dashboard</h1>

  <!-- Summary cards -->
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-bottom:18px">
    <div class="card">
      <h3>Complaints</h3>
      <p class="muted">Open / In-progress / Closed</p>
      <div style="display:flex;gap:12px;align-items:center;margin-top:12px">
        <div style="font-size:20px;font-weight:700;color:#b91c1c"><?= $open_complaints ?></div>
        <div style="font-size:20px;font-weight:700;color:#d97706"><?= $inprogress_complaints ?></div>
        <div style="font-size:20px;font-weight:700;color:#16a34a"><?= $closed_complaints ?></div>
      </div>
    </div>

    <div class="card">
      <h3>Staff & Tasks</h3>
      <p class="muted">Staff / Tasks / Shifts</p>
      <div style="margin-top:12px">
        <div><strong><?= $total_staff ?></strong> staff</div>
        <div><strong><?= $total_tasks ?></strong> tasks</div>
        <div><strong><?= $total_shifts ?></strong> shifts</div>
      </div>
    </div>

    <div class="card">
      <h3>Rooms & Occupancy</h3>
      <p class="muted">Rooms / Occupied / Students</p>
      <div style="margin-top:12px">
        <div><strong><?= $total_rooms ?></strong> rooms</div>
        <div><strong><?= $occupied_beds ?></strong> allocated</div>
        <div><strong><?= $total_students ?></strong> students</div>
      </div>
    </div>

    <div class="card">
      <h3>Quick Actions</h3>
      <p class="muted">Jump to management pages</p>
      <div style="margin-top:12px;display:flex;flex-direction:column;gap:8px">
        <a class="btn small" href="warden_complaints.php">Manage Complaints</a>
        <a class="btn small" href="warden_tasks.php">Manage Tasks</a>
        <a class="btn small" href="warden_assign_duty.php">Assign Duties</a>
        <a class="btn small" href="warden_room_allocation.php">Room Allocation</a>
      </div>
    </div>
  </div>

  <!-- Recent complaints -->
  <section class="card">
    <h3>Recent Complaints</h3>
    <?php if (empty($recent_complaints)): ?>
      <p class="muted">No recent complaints</p>
    <?php else: ?>
      <table class="table">
        <thead><tr><th>#</th><th>Student</th><th>Title</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($recent_complaints as $c): ?>
          <tr>
            <td><?= $c['id'] ?></td>
            <td><?= htmlspecialchars($c['student_name']) ?></td>
            <td><?= htmlspecialchars($c['title'] ?? substr($c['description'],0,60)) ?></td>
            <td><?= htmlspecialchars($c['status']) ?></td>
            <td><?= htmlspecialchars($c['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </section>

  <!-- Upcoming duty assignments -->
  <section class="card">
    <h3>Upcoming Duties</h3>
    <?php if (empty($upcoming)): ?>
      <p class="muted">No upcoming duties</p>
    <?php else: ?>
      <table class="table">
        <thead><tr><th>Staff</th><th>Task</th><th>Shift</th><th>Day</th><th>Date Range</th></tr></thead>
        <tbody>
        <?php foreach ($upcoming as $a): ?>
          <tr>
            <td><?= htmlspecialchars($a['staff_name']) ?></td>
            <td><?= htmlspecialchars($a['task_name']) ?></td>
            <td><?= htmlspecialchars($a['shift_name'] . ' (' . $a['start_time'] . '–' . $a['end_time'] . ')') ?></td>
            <td><?= htmlspecialchars($a['day_of_week']) ?></td>
            <td><?= htmlspecialchars($a['start_date'] . ' → ' . $a['end_date']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </section>

</div><!-- /.main-content -->

</body>
</html>
