<?php
require_once 'db.php';
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'caretaker') { header('Location: index.php'); exit; }
$user = $_SESSION['user'];
// My tasks
$stmt = mysqli_prepare($conn, 'SELECT * FROM tasks WHERE assigned_to = ? ORDER BY created_at DESC');
mysqli_stmt_bind_param($stmt, 'i', $user['id']);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$my_tasks = $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
// All staff tasks
$res2 = mysqli_query($conn, "SELECT t.*, u.name as staff_name FROM tasks t LEFT JOIN users u ON u.id = t.assigned_to ORDER BY t.created_at DESC");
$all_tasks = $res2 ? mysqli_fetch_all($res2, MYSQLI_ASSOC) : [];
// Students with rooms
$res3 = mysqli_query($conn, "SELECT u.id,u.name,u.email,r.room_label,r.block FROM users u LEFT JOIN allocations a ON a.student_id = u.id LEFT JOIN rooms r ON r.id = a.room_id WHERE u.role='student'");
$students = $res3 ? mysqli_fetch_all($res3, MYSQLI_ASSOC) : [];
// Rooms with occupants
$res4 = mysqli_query($conn, "SELECT r.*, (SELECT COUNT(*) FROM allocations WHERE room_id = r.id AND active=1) AS occupants FROM rooms r ORDER BY r.block, r.room_label");
$rooms = $res4 ? mysqli_fetch_all($res4, MYSQLI_ASSOC) : [];
// // Update task status
// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'], $_POST['status'])) {
//     $tid = intval($_POST['task_id']);
//     $status = $_POST['status'];
//     $stmtu = mysqli_prepare($conn, 'UPDATE tasks SET status = ? WHERE id = ? AND assigned_to = ?');
//     mysqli_stmt_bind_param($stmtu, 'sii', $status, $tid, $user['id']);
//     mysqli_stmt_execute($stmtu);
//     header('Location: caretaker_dashboard.php'); exit;
// }
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Caretaker</title>
<!-- <link rel="stylesheet" href="assets/style.css"></head><body> -->
    <link rel="stylesheet" href="assets/style.css?v=5">

<nav class="topbar"><div class="brand">Hostel - Caretaker</div><div class="nav-actions"><?php echo htmlspecialchars($user['name']); ?> <a href="logout.php" class="link">Logout</a></div></nav>
<main class="container">
<section class="card"><h3>Your Tasks</h3>
<?php if (count($my_tasks)==0): ?><p class="muted">No tasks assigned</p><?php else: ?>
<table class="table"><thead><tr><th>ID</th><th>Title</th><th>Description</th><th>Status</th>
<!-- <th>Action</th> -->
</tr></thead><tbody>
<?php foreach($my_tasks as $t): ?><tr><td><?php echo $t['id'];?></td><td><?php echo htmlspecialchars($t['title']);?></td><td><?php echo htmlspecialchars($t['description']);?></td><td><?php echo htmlspecialchars($t['status']);?></td>
    <!-- <td><form method="post"><input type="hidden" name="task_id" value="<?php echo $t['id'];?>"><select name="status"><option value="pending" <?php if($t['status']=='pending') echo 'selected'; ?>>Pending</option><option value="in-progress" <?php if($t['status']=='in-progress') echo 'selected'; ?>>In-progress</option><option value="completed" <?php if($t['status']=='completed') echo 'selected'; ?>>Completed</option></select><button class="btn small">Update</button></form></td> -->
     <!-- <td><?php echo htmlspecialchars($t['status']); ?></td> -->

</tr><?php endforeach; ?></tbody></table><?php endif; ?></section>

<section class="card"><h3>All Staff Tasks (View)</h3>
<table class="table"><thead><tr><th>ID</th><th>Staff</th><th>Title</th><th>Description</th><th>Status</th></tr></thead><tbody>
<?php foreach($all_tasks as $t): ?><tr><td><?php echo $t['id'];?></td><td><?php echo htmlspecialchars($t['staff_name']);?></td><td><?php echo htmlspecialchars($t['title']);?></td><td><?php echo htmlspecialchars($t['description']);?></td><td><?php echo htmlspecialchars($t['status']);?></td></tr><?php endforeach; ?></tbody></table></section>

<section class="card"><h3>Students</h3><table class="table"><thead><tr><th>Name</th><th>Email</th><th>Room</th><th>Block</th></tr></thead><tbody>
<?php foreach($students as $s): ?><tr><td><?php echo htmlspecialchars($s['name']);?></td><td><?php echo htmlspecialchars($s['email']);?></td><td><?php echo $s['room_label']?:'Not Assigned';?></td><td><?php echo $s['block']?:'-';?></td></tr><?php endforeach; ?></tbody></table></section>

<section class="card"><h3>Rooms</h3><table class="table"><thead><tr><th>Room</th><th>Block</th><th>Capacity</th><th>Occupants</th></tr></thead><tbody>
<?php foreach($rooms as $r): ?><tr><td><?php echo htmlspecialchars($r['room_label']);?></td><td><?php echo htmlspecialchars($r['block']);?></td><td><?php echo $r['capacity'];?></td><td><?php echo $r['occupants'];?></td></tr><?php endforeach; ?></tbody></table></section>

</main></body></html>
