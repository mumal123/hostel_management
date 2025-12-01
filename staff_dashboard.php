<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['staff','security','caretaker'])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['id'];

/* ================================================
   FETCH DUTY ASSIGNMENTS for this staff member
================================================ */
$duty_res = mysqli_query($conn,
    "SELECT ss.*, 
            t.title AS task_name,
            t.description AS task_description,
            sh.name AS shift_name,
            sh.start_time,
            sh.end_time
     FROM staff_shifts ss
     LEFT JOIN tasks t ON ss.task_id = t.id
     LEFT JOIN shifts sh ON ss.shift_id = sh.id
     WHERE ss.staff_user_id = $user_id
     ORDER BY ss.day_of_week, sh.start_time"
);

$duties = $duty_res ? mysqli_fetch_all($duty_res, MYSQLI_ASSOC) : [];
?>
<!doctype html>
<html>
<head>
<title>Staff Dashboard</title>
<link rel="stylesheet" href="assets/style.css?v=7">
</head>
<body>

<!-- Top Bar -->
<nav class="topbar">
    <div class="brand">Hostel - Staff</div>
    <div class="nav-actions">
        <?= htmlspecialchars($user['name']) ?>
        <a href="logout.php" class="link">Logout</a>
    </div>
</nav>

<div class="main-content">

<h2>Your Duty Assignments</h2>

<section class="card">
<?php if (empty($duties)): ?>
    <p class="muted">No duties assigned.</p>
<?php else: ?>
<table class="table">
<thead>
<tr>
    <th>Task</th>
    <th>Shift</th>
    <th>Day</th>
    <th>Time</th>
    <th>Date Range</th>
</tr>
</thead>
<tbody>
<?php foreach ($duties as $d): ?>
<tr>
    <td><?= htmlspecialchars($d['task_name']) ?></td>
    <td><?= htmlspecialchars($d['shift_name']) ?></td>
    <td><?= htmlspecialchars($d['day_of_week']) ?></td>
    <td><?= $d['start_time'] ?> – <?= $d['end_time'] ?></td>
    <td><?= $d['start_date'] ?> → <?= $d['end_date'] ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</section>

</div>
</body>
</html>
