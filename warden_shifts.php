<?php
require_once 'db.php';
session_start();

if ($_SESSION['user']['role'] !== 'warden') { header("Location: index.php"); exit; }

/* Fetch shifts */
$res = mysqli_query($conn, "SELECT * FROM shifts ORDER BY start_time");
$shifts = mysqli_fetch_all($res, MYSQLI_ASSOC);

/* Create shift */
if (isset($_POST['create_shift'])) {
    $name = $_POST['name'];
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];

    mysqli_query($conn, "INSERT INTO shifts (name, start_time, end_time) VALUES ('$name','$start','$end')");
    header("Location: warden_shifts.php");
    exit;
}
?>
<!doctype html>
<html>
<head>
<title>Shifts - Warden</title>
<link rel="stylesheet" href="assets/style.css?v=7">
</head>
<body>

<?php include "warden_sidebar.php"; ?>

<div class="main-content">

<h2>Create Shift</h2>
<section class="card">
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

<h2>Shift List</h2>
<section class="card">
<table class="table">
<thead><tr><th>Name</th><th>Start</th><th>End</th></tr></thead>
<tbody>
<?php foreach ($shifts as $sh): ?>
<tr>
    <td><?= htmlspecialchars($sh['name']) ?></td>
    <td><?= $sh['start_time'] ?></td>
    <td><?= $sh['end_time'] ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</section>

</div>
</body>
</html>
