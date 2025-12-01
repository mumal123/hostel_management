<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'caretaker') { 
    header('Location: index.php'); exit; 
}

$res = mysqli_query($conn,
    "SELECT r.*, 
            (SELECT COUNT(*) FROM allocations WHERE room_id = r.id AND active=1) AS occupants
     FROM rooms r ORDER BY r.block, r.room_label"
);
$rooms = mysqli_fetch_all($res, MYSQLI_ASSOC);
?>
<!doctype html>
<html>
<head>
<title>Rooms</title>
<link rel="stylesheet" href="assets/style.css?v=10">
</head>
<body>

<?php include "caretaker_sidebar.php"; ?>

<div class="main-content">
<h2>Rooms</h2>

<table class="table">
<thead>
<tr>
    <th>Room</th>
    <th>Block</th>
    <th>Capacity</th>
    <th>Occupants</th>
</tr>
</thead>
<tbody>
<?php foreach ($rooms as $r): ?>
<tr>
    <td><?= htmlspecialchars($r['room_label']) ?></td>
    <td><?= htmlspecialchars($r['block']) ?></td>
    <td><?= $r['capacity'] ?></td>
    <td><?= $r['occupants'] ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

</div>
</body>
</html>
