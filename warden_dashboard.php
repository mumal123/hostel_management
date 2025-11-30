<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'warden') {
    header('Location: index.php');
    exit;
}

$user = $_SESSION['user'];

/* ---------------------------------------------------------
   FETCH DATA
--------------------------------------------------------- */

// Complaints (view + warden can update status)
$complaints_res = mysqli_query($conn,
    "SELECT c.*, u.name AS student_name 
     FROM complaints c
     LEFT JOIN users u ON u.id = c.student_id 
     ORDER BY c.created_at DESC"
);
$complaints = mysqli_fetch_all($complaints_res, MYSQLI_ASSOC);

// Staff list for duty assignment
$staff_res = mysqli_query($conn,
    "SELECT id, name FROM users WHERE role IN ('staff','security','caretaker')"
);
$staff = mysqli_fetch_all($staff_res, MYSQLI_ASSOC);

// Rooms
$rooms_res = mysqli_query($conn, "SELECT * FROM rooms");
$rooms = mysqli_fetch_all($rooms_res, MYSQLI_ASSOC);

// Students
$students_res = mysqli_query($conn, "SELECT id, name FROM users WHERE role='student'");
$students = mysqli_fetch_all($students_res, MYSQLI_ASSOC);

// Fetch Tasks
$tasks_res = mysqli_query($conn, "SELECT * FROM tasks ORDER BY created_at DESC");
$tasks = mysqli_fetch_all($tasks_res, MYSQLI_ASSOC);

// Fetch Shifts
$shifts_res = mysqli_query($conn, "SELECT * FROM shifts ORDER BY start_time");
$shifts = mysqli_fetch_all($shifts_res, MYSQLI_ASSOC);

// Fetch Duty Assignments
$assign_res = mysqli_query($conn,
    "SELECT ss.*, 
            u.name AS staff_name,
            t.title AS task_name,
            sh.name AS shift_name,
            sh.start_time, sh.end_time
     FROM staff_shifts ss
     LEFT JOIN users u ON ss.staff_user_id = u.id
     LEFT JOIN tasks t ON ss.task_id = t.id
     LEFT JOIN shifts sh ON ss.shift_id = sh.id
     ORDER BY ss.day_of_week, sh.start_time"
);
$assignments = mysqli_fetch_all($assign_res, MYSQLI_ASSOC);


/* ---------------------------------------------------------
   FORM HANDLING
--------------------------------------------------------- */

// Update complaint status
if (isset($_POST['update_complaint'])) {
    $cid = intval($_POST['complaint_id']);
    $status = $_POST['status'];
    mysqli_query($conn, "UPDATE complaints SET status='$status' WHERE id=$cid");
    header("Location: warden_dashboard.php");
    exit;
}

// Allocate room
if (isset($_POST['allocate_room'])) {
    $student_id = intval($_POST['student_id']);
    $room_id = intval($_POST['room_id']);

    mysqli_query($conn, "UPDATE allocations SET active=0 WHERE student_id=$student_id");
    mysqli_query($conn,
        "INSERT INTO allocations (student_id, room_id, start_date, active)
         VALUES ($student_id, $room_id, CURDATE(), 1)"
    );

    header("Location: warden_dashboard.php");
    exit;
}

// Create Task
if (isset($_POST['create_task'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $desc  = mysqli_real_escape_string($conn, $_POST['description']);

    mysqli_query($conn,
        "INSERT INTO tasks (title, description) VALUES ('$title', '$desc')"
    );

    header("Location: warden_dashboard.php");
    exit;
}

// Create Shift
if (isset($_POST['create_shift'])) {
    $name = $_POST['name'];
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];

    mysqli_query($conn,
        "INSERT INTO shifts (name, start_time, end_time)
         VALUES ('$name', '$start', '$end')"
    );

    header("Location: warden_dashboard.php");
    exit;
}

// Assign Staff Duty
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

    header("Location: warden_dashboard.php");
    exit;
}
?>
<!doctype html>
<html>
<head>
    <title>Warden Dashboard</title>
    <!-- <link rel="stylesheet" href="assets/style.css"> -->
     <link rel="stylesheet" href="assets/style.css?v=5">

</head>

<body>
<nav class="topbar">
  <div class="brand">Hostel - Warden</div>
  <div class="nav-actions">
    <?php echo htmlspecialchars($user['name']); ?>
    <a href="logout.php" class="link">Logout</a>
  </div>
</nav>

<main class="container">

<!-- COMPLAINTS -->
<section class="card">
<h3>Complaints</h3>

<table class="table">
<thead>
<tr><th>#</th><th>Student</th><th>Description</th><th>Status</th><th>Action</th></tr>
</thead>
<tbody>
<?php foreach ($complaints as $c): ?>
<tr>
  <td><?= $c['id'] ?></td>
  <td><?= htmlspecialchars($c['student_name']) ?></td>
  <td><?= htmlspecialchars($c['description']) ?></td>
  <td><?= htmlspecialchars($c['status']) ?></td>
  <td>
    <form method="post">
        <input type="hidden" name="complaint_id" value="<?= $c['id'] ?>">
        <select name="status">
            <option value="open">Open</option>
            <option value="in-progress">In-progress</option>
            <option value="closed">Closed</option>
        </select>
        <button name="update_complaint" class="btn small">Update</button>
    </form>
  </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</section>


<!-- CREATE TASK -->
<section class="card">
<h3>Create Task</h3>
<form method="post">
    <label>Task Title</label>
    <input type="text" name="title" required>

    <label>Description</label>
    <textarea name="description"></textarea>

    <button name="create_task" class="btn">Create Task</button>
</form>
</section>


<!-- CREATE SHIFT -->
<section class="card">
<h3>Create Shift</h3>
<form method="post">
    <label>Name</label>
    <input type="text" name="name" required>

    <label>Start Time</label>
    <input type="time" name="start_time" required>

    <label>End Time</label>
    <input type="time" name="end_time" required>

    <button name="create_shift" class="btn">Create Shift</button>
</form>
</section>


<!-- ASSIGN DUTY -->
<section class="card">
<h3>Assign Duty</h3>
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
        <option value="<?= $sh['id'] ?>"><?= htmlspecialchars($sh['name']) ?></option>
    <?php endforeach; ?>
</select>

<label>Day of Week</label>
<select name="day_of_week" required>
    <option>Mon</option>
    <option>Tue</option>
    <option>Wed</option>
    <option>Thu</option>
    <option>Fri</option>
    <option>Sat</option>
    <option>Sun</option>
</select>

<label>Start Date</label>
<input type="date" name="start_date" required>

<label>End Date</label>
<input type="date" name="end_date" required>

<button name="assign_duty" class="btn">Assign Duty</button>

</form>
</section>


<!-- DUTY LIST -->
<section class="card">
<h3>All Duty Assignments</h3>

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
  <td><?= $a['day_of_week'] ?></td>
  <td><?= $a['start_time'] ?>–<?= $a['end_time'] ?></td>
  <td><?= $a['start_date'] ?> → <?= $a['end_date'] ?></td>
</tr>
<?php endforeach; ?>

</tbody>
</table>
</section>


<!-- ROOM ALLOCATION -->
<section class="card">
<h3>Allocate Room</h3>
<form method="post">
    <label>Student</label>
    <select name="student_id">
        <?php foreach ($students as $s): ?>
            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
        <?php endforeach; ?>
    </select>

    <label>Room</label>
    <select name="room_id">
        <?php foreach ($rooms as $r): ?>
            <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['room_label']) ?></option>
        <?php endforeach; ?>
    </select>

    <button name="allocate_room" class="btn">Allocate</button>
</form>
</section>


</main>
</body>
</html>
