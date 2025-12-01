<?php
require_once 'db.php';
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'caretaker') { header('Location: index.php'); exit; }

$res = mysqli_query($conn,
    "SELECT u.name, u.email, r.room_label, r.block
     FROM users u
     LEFT JOIN allocations a ON a.student_id = u.id AND a.active=1
     LEFT JOIN rooms r ON r.id = a.room_id
     WHERE u.role='student'"
);
$students = mysqli_fetch_all($res, MYSQLI_ASSOC);
?>
<!doctype html>
<html>
<head>
<title>Students</title>
<link rel="stylesheet" href="assets/style.css?v=10">
</head>
<body>

<?php include "caretaker_sidebar.php"; ?>

<div class="main-content">
<h2>Students</h2>

<table class="table">
<thead>
<tr><th>Name</th><th>Email</th><th>Room</th><th>Block</th></tr>
</thead>
<tbody>
<?php foreach ($students as $s): ?>
<tr>
    <td><?= htmlspecialchars($s['name']) ?></td>
    <td><?= htmlspecialchars($s['email']) ?></td>
    <td><?= $s['room_label'] ?: 'Not Assigned' ?></td>
    <td><?= $s['block'] ?: '-' ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

</div>
</body>
</html>
