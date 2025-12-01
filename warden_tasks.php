<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'warden') {
    header("Location: index.php");
    exit;
}

/* Fetch tasks */
$res = mysqli_query($conn, "SELECT * FROM tasks ORDER BY created_at DESC");
$tasks = mysqli_fetch_all($res, MYSQLI_ASSOC);

/* Create task */
if (isset($_POST['create_task'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $desc  = mysqli_real_escape_string($conn, $_POST['description']);

    mysqli_query($conn, "INSERT INTO tasks (title, description) VALUES ('$title', '$desc')");
    header("Location: warden_tasks.php");
    exit;
}
?>
<!doctype html>
<html>
<head>
<title>Tasks - Warden</title>
<link rel="stylesheet" href="assets/style.css?v=7">
</head>
<body>

<?php include "warden_sidebar.php"; ?>

<div class="main-content">

<h2>Create Task</h2>
<section class="card">
<form method="post">
    <label>Title</label>
    <input type="text" name="title" required>

    <label>Description</label>
    <textarea name="description"></textarea>

    <button name="create_task" class="btn">Create Task</button>
</form>
</section>

<h2>Task List</h2>
<section class="card">
<table class="table">
<thead><tr><th>ID</th><th>Title</th><th>Description</th></tr></thead>
<tbody>

<?php foreach ($tasks as $t): ?>
<tr>
    <td><?= $t['id'] ?></td>
    <td><?= htmlspecialchars($t['title']) ?></td>
    <td><?= htmlspecialchars($t['description']) ?></td>
</tr>
<?php endforeach; ?>

</tbody>
</table>
</section>

</div>
</body>
</html>
