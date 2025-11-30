<?php
require_once 'db.php';
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') { header('Location: index.php'); exit; }
$user = $_SESSION['user'];
// fetch allocation
$alloc_q = mysqli_prepare($conn, 'SELECT a.*, r.room_label, r.block FROM allocations a LEFT JOIN rooms r ON r.id = a.room_id WHERE a.student_id = ? AND a.active = 1');
mysqli_stmt_bind_param($alloc_q, 'i', $user['id']);
mysqli_stmt_execute($alloc_q);
$res_alloc = mysqli_stmt_get_result($alloc_q);
$allocation = $res_alloc ? mysqli_fetch_assoc($res_alloc) : null;
// fetch complaints
$compl = mysqli_prepare($conn, 'SELECT * FROM complaints WHERE student_id = ? ORDER BY created_at DESC');
mysqli_stmt_bind_param($compl, 'i', $user['id']);
mysqli_stmt_execute($compl);
$resc = mysqli_stmt_get_result($compl);
$complaints = $resc ? mysqli_fetch_all($resc, MYSQLI_ASSOC) : [];
// submit complaint
$msg='';
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['complaint'])) {
    $desc = $_POST['complaint'];
    $title = $_POST['title'] ?? 'General';
    $stmt = mysqli_prepare($conn, 'INSERT INTO complaints (student_id,title,description,status,priority,created_at) VALUES (?,?,?,?,?,NOW())');
    $status='open'; $priority='medium';
    mysqli_stmt_bind_param($stmt, 'issss', $user['id'],$title,$desc,$status,$priority);
    mysqli_stmt_execute($stmt);
    $msg='Complaint submitted';
    header('Location: student_dashboard.php'); exit;
}
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Student</title>
<!-- <link rel="stylesheet" href="assets/style.css"> -->
 <link rel="stylesheet" href="assets/style.css?v=5">

</head><body>
<nav class="topbar"><div class="brand">Hostel - Student</div><div class="nav-actions"><?php echo htmlspecialchars($user['name']); ?> <a href="logout.php" class="link">Logout</a></div></nav>
<main class="container">
<section class="card"><h3>Your Room</h3>
<?php if ($allocation): ?>
<p><strong>Room:</strong> <?php echo htmlspecialchars($allocation['room_label']); ?> <strong>Block:</strong> <?php echo htmlspecialchars($allocation['block']); ?></p>
<?php else: ?><p class="muted">No active allocation</p><?php endif; ?></section>

<section class="card"><h3>Submit Complaint</h3><?php if ($msg) echo '<div class="success">'.htmlspecialchars($msg).'</div>'; ?>
<form method="post"><label>Title</label><input name="title"><label>Description</label><textarea name="complaint" rows="4" required></textarea><button class="btn">Submit</button></form></section>

<section class="card"><h3>Your Complaints</h3>
<?php if (count($complaints)==0): ?><p class="muted">No complaints</p><?php else: ?>
<table class="table"><thead><tr><th>#</th><th>Title</th><th>Status</th><th>Date</th></tr></thead><tbody>
<?php foreach($complaints as $c): ?><tr><td><?php echo $c['id'];?></td><td><?php echo htmlspecialchars($c['title']);?></td><td><?php echo htmlspecialchars($c['status']);?></td><td><?php echo $c['created_at'];?></td></tr><?php endforeach; ?></tbody></table>
<?php endif; ?></section>
</main></body></html>
