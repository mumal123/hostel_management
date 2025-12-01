<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'warden') {
    header("Location: index.php");
    exit;
}

/* Fetch complaints */
$res = mysqli_query($conn,
    "SELECT c.*, u.name AS student_name
     FROM complaints c
     LEFT JOIN users u ON u.id = c.student_id
     ORDER BY c.created_at DESC"
);
$complaints = mysqli_fetch_all($res, MYSQLI_ASSOC);

/* Update status */
if (isset($_POST['update_status'])) {
    $cid = intval($_POST['complaint_id']);
    $status = $_POST['status'];
    mysqli_query($conn, "UPDATE complaints SET status='$status' WHERE id=$cid");
    header("Location: warden_complaints.php");
    exit;
}
?>
<!doctype html>
<html>
<head>
<title>Complaints - Warden</title>
<link rel="stylesheet" href="assets/style.css?v=7">
</head>
<body>

<?php include "warden_sidebar.php"; ?>

<div class="main-content">
<h2>Complaints</h2>

<table class="table">
<thead>
<tr><th>ID</th><th>Student</th><th>Description</th><th>Status</th><th>Action</th></tr>
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
                <option value="in-progress">In Progress</option>
                <option value="closed">Closed</option>
            </select>
            <button name="update_status" class="btn small">Update</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>

</tbody>
</table>

</div>
</body>
</html>
