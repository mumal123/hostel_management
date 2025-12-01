<?php
require_once 'db.php';
session_start();

if ($_SESSION['user']['role'] !== 'warden') { 
    header("Location: index.php"); 
    exit; 
}

/* Fetch students */
$students_res = mysqli_query($conn, 
    "SELECT id, name FROM users WHERE role='student' ORDER BY name"
);
$students = mysqli_fetch_all($students_res, MYSQLI_ASSOC);

/* Fetch rooms */
$rooms_res = mysqli_query($conn, 
    "SELECT * FROM rooms ORDER BY block, room_label"
);
$rooms = mysqli_fetch_all($rooms_res, MYSQLI_ASSOC);

/* Allocation handler */
if (isset($_POST['allocate_room'])) {

    $student = intval($_POST['student_id']);
    $room    = intval($_POST['room_id']);

    mysqli_query($conn, "UPDATE allocations SET active=0 WHERE student_id=$student");

    mysqli_query($conn,
        "INSERT INTO allocations (student_id, room_id, start_date, active)
         VALUES ($student, $room, CURDATE(), 1)"
    );

    header("Location: warden_room_allocation.php");
    exit;
}

/* Fetch current allocations list */
$list_res = mysqli_query($conn,
    "SELECT u.name AS student_name, r.room_label, r.block
     FROM users u
     LEFT JOIN allocations a ON a.student_id = u.id AND a.active=1
     LEFT JOIN rooms r ON r.id = a.room_id
     WHERE u.role='student'
     ORDER BY r.block, r.room_label"
);
$allocations = mysqli_fetch_all($list_res, MYSQLI_ASSOC);

?>
<!doctype html>
<html>
<head>
<title>Room Allocation - Warden</title>
<link rel="stylesheet" href="assets/style.css?v=7">
</head>
<body>

<?php include "warden_sidebar.php"; ?>

<div class="main-content">

<h2>Allocate Room</h2>

<section class="card">
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
    <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['room_label']) ?> (<?= $r['block'] ?>)</option>
<?php endforeach; ?>
</select>

<button name="allocate_room" class="btn">Allocate</button>
</form>
</section>


<h2>Current Allocations</h2>

<section class="card">

<table class="table">
<thead>
<tr><th>Student</th><th>Room</th><th>Block</th></tr>
</thead>
<tbody>

<?php foreach ($allocations as $a): ?>
<tr>
  <td><?= htmlspecialchars($a['student_name']) ?></td>
  <td><?= $a['room_label'] ?: "Not Assigned" ?></td>
  <td><?= $a['block'] ?: "-" ?></td>
</tr>
<?php endforeach; ?>

</tbody>
</table>

</section>

</div>
</body>
</html>
