<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'warden') {
    header("Location: index.php");
    exit;
}

/* Fetch staff */
$staff_res = mysqli_query($conn,
    "SELECT id, name FROM users WHERE role IN ('staff','security','caretaker')"
);
$staff = mysqli_fetch_all($staff_res, MYSQLI_ASSOC);

/* Fetch tasks */
$task_res = mysqli_query($conn, "SELECT * FROM tasks ORDER BY title");
$tasks = mysqli_fetch_all($task_res, MYSQLI_ASSOC);

/* Fetch shifts */
$shift_res = mysqli_query($conn, "SELECT * FROM shifts ORDER BY start_time");
$shifts = mysqli_fetch_all($shift_res, MYSQLI_ASSOC);

/* Fetch duty assignments */
$duty_res = mysqli_query($conn,
    "SELECT ss.*, 
            u.name AS staff_name,
            t.title AS task_name,
            sh.name AS shift_name,
            sh.start_time, 
            sh.end_time
     FROM staff_shifts ss
     LEFT JOIN users u ON ss.staff_user_id = u.id
     LEFT JOIN tasks t ON ss.task_id = t.id
     LEFT JOIN shifts sh ON ss.shift_id = sh.id
     ORDER BY ss.day_of_week, sh.start_time"
);
$assignments = mysqli_fetch_all($duty_res, MYSQLI_ASSOC);

/* Assign staff duty */
if (isset($_POST['assign_duty'])) {

    $staff_id = intval($_POST['staff_id']);
    $task_id = intval($_POST['task_id']);
    $shift_id = intval($_POST['shift_id']);
    $day = $_POST['day_of_week'];
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];

    mysqli_query($conn,
        "INSERT INTO staff_shifts (staff_user_id, task_id, shift_id, day_of_week, start_date, end_date)
         VALUES ($staff_id, $task_id, $shift_id, '$day', '$start', '$end')"
    );

    header("Location: warden_assign_duty.php");
    exit;
}

?>
<!doctype html>
<html>
<head>
<title>Assign Duty - Warden</title>
<link rel="stylesheet" href="assets/style.css?v=7">
</head>
<body>

<?php include "warden_sidebar.php"; ?>

<div class="main-content">

<h2>Assign Duty</h2>

<section class="card">
<form method="post">

<label>Staff</label>
<select name="staff_id" required>
    <?php foreach ($staff as $s): ?>
        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
    <?php endforeach; ?>
</select>

<label>Task</label>
<select name="task_id" required>
    <?php foreach ($tasks as $t): ?>
        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['title']) ?></option>
    <?php endforeach; ?>
</select>

<label>Shift</label>
<select name="shift_id" required>
    <?php foreach ($shifts as $sh): ?>
        <option value="<?= $sh['id'] ?>">
            <?= htmlspecialchars($sh['name']) ?>
        </option>
    <?php endforeach; ?>
</select>

<label>Day of Week</label>
<select name="day_of_week" required>
    <option>Mon</option><option>Tue</option><option>Wed</option>
    <option>Thu</option><option>Fri</option><option>Sat</option><option>Sun</option>
</select>

<label>Start Date</label>
<input type="date" name="start_date" required>

<label>End Date</label>
<input type="date" name="end_date" required>

<button name="assign_duty" class="btn">Assign Duty</button>

</form>
</section>


<h2>Existing Duty Assignments</h2>

<section class="card">
<table class="table">
<thead>
<tr>
  <th>Staff</th>
  <th>Task</th>
  <th>Shift</th>
  <th>Day</th>
  <th>Time</th>
  <th>Date Range</th>
</tr>
</thead>
<tbody>

<?php foreach ($assignments as $a): ?>
<tr>
  <td><?= htmlspecialchars($a['staff_name']) ?></td>
  <td><?= htmlspecialchars($a['task_name']) ?></td>
  <td><?= htmlspecialchars($a['shift_name']) ?></td>
  <td><?= htmlspecialchars($a['day_of_week']) ?></td>
  <td><?= $a['start_time'] ?>–<?= $a['end_time'] ?></td>
  <td><?= $a['start_date'] ?> → <?= $a['end_date'] ?></td>
</tr>
<?php endforeach; ?>

</tbody>
</table>
</section>

</div>
</body>
</html>
