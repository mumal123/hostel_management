<?php
require_once 'db.php';
session_start();
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['staff','security'])) { header('Location: index.php'); exit; }
$user = $_SESSION['user'];
$res = mysqli_query($conn, sprintf("SELECT * FROM tasks WHERE assigned_to=%d ORDER BY created_at DESC", $user['id']));
$tasks = $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Staff</title>
<!-- <link rel="stylesheet" href="assets/style.css"> -->
 <link rel="stylesheet" href="assets/style.css?v=5">

</head><body>
<nav class="topbar"><div class="brand">Hostel - Staff</div><div class="nav-actions"><?php echo htmlspecialchars($user['name']); ?> <a href="logout.php" class="link">Logout</a></div></nav>
<main class="container">
<section class="card"><h3>Your Tasks</h3><?php if(count($tasks)==0): ?><p class="muted">No tasks assigned</p><?php else: ?><table class="table"><thead><tr><th>ID</th><th>Title</th><th>Status</th></tr></thead><tbody><?php foreach($tasks as $t): ?><tr><td><?php echo $t['id'];?></td><td><?php echo htmlspecialchars($t['title']);?></td><td><?php echo htmlspecialchars($t['status']);?></td></tr><?php endforeach;?></tbody></table><?php endif;?></section>
</main></body></html>
