<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'caretaker') {
    header('Location: index.php'); exit;
}

$user = $_SESSION['user'];

/* Fetch caretaker personal tasks */
$stmt = mysqli_prepare($conn, 'SELECT * FROM tasks WHERE assigned_to = ? ORDER BY created_at DESC');
mysqli_stmt_bind_param($stmt, 'i', $user['id']);
mysqli_stmt_execute($stmt);
$my_tasks = mysqli_stmt_get_result($stmt)->fetch_all(MYSQLI_ASSOC);

/* Fetch all staff duties */
$duty_res = mysqli_query($conn,
    "SELECT ss.id,
            u.name AS staff_name,
            t.title AS task_title,
            t.description AS task_description,
            sh.name AS shift_name,
            sh.start_time,
            sh.end_time,
            ss.day_of_week,
            ss.start_date,
            ss.end_date
     FROM staff_shifts ss
     LEFT JOIN users u ON ss.staff_user_id = u.id
     LEFT JOIN tasks t ON ss.task_id = t.id
     LEFT JOIN shifts sh ON ss.shift_id = sh.id
     ORDER BY ss.day_of_week, sh.start_time"
);
$all_staff_tasks = mysqli_fetch_all($duty_res, MYSQLI_ASSOC);
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Caretaker Dashboard</title>
<link rel="stylesheet" href="assets/style.css?v=10">
</head>

<body>

<?php include "caretaker_sidebar.php"; ?>

<div class="main-content">

<h2>Dashboard</h2>

<!-- PERSONAL TASKS -->
<section class="card"><h3>Your Tasks</h3>
<?php if (!$my_tasks): ?>
    <p class="muted">No tasks assigned.</p>
<?php else: ?>
<table class="table">
<thead><tr><th>ID</th><th>Title</th><th>Description</th><th>Status</th></tr></thead>
<tbody>
<?php foreach ($my_tasks as $t): ?>
<tr>
    <td><?= $t['id'] ?></td>
    <td><?= htmlspecialchars($t['title']) ?></td>
    <td><?= htmlspecialchars($t['description']) ?></td>
    <td><?= htmlspecialchars($t['status']) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</section>

<!-- STAFF DUTIES -->
<section class="card"><h3>All Staff Duties</h3>
<table class="table">
<thead>
<tr>
    <th>ID</th>
    <th>Staff</th>
    <th>Task</th>
    <th>Shift</th>
    <th>Day</th>
    <th>Date Range</th>
</tr>
</thead>
<tbody>
<?php foreach ($all_staff_tasks as $d): ?>
<tr>
    <td><?= $d['id'] ?></td>
    <td><?= htmlspecialchars($d['staff_name']) ?></td>
    <td><?= htmlspecialchars($d['task_title']) ?></td>
    <td><?= htmlspecialchars($d['shift_name'] . " (" . $d['start_time'] . "–" . $d['end_time'] . ")") ?></td>
    <td><?= htmlspecialchars($d['day_of_week']) ?></td>
    <td><?= htmlspecialchars($d['start_date'] . " → " . $d['end_date']) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</section>

</div>
</body>
</html>
