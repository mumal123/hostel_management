<?php
require_once 'db.php';
session_start();
if (isset($_SESSION['user'])) {
    $role = $_SESSION['user']['role'];
    if ($role === 'student') header('Location: student_dashboard.php');
    if ($role === 'caretaker') header('Location: caretaker_dashboard.php');
    if ($role === 'warden') header('Location: warden_dashboard.php');
    if ($role === 'staff') header('Location: staff_dashboard.php');
    if ($role === 'security') header('Location: staff_dashboard.php');
}
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $stmt = mysqli_prepare($conn, 'SELECT id, name, email, password, role FROM users WHERE email = ?');
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($res);
    if ($user && $password === $user['password']) {
        $_SESSION['user'] = $user;
        if ($user['role'] === 'student') header('Location: student_dashboard.php');
        if ($user['role'] === 'caretaker') header('Location: caretaker_dashboard.php');
        if ($user['role'] === 'warden') header('Location: warden_dashboard.php');
        if ($user['role'] === 'staff' || $user['role']==='security') header('Location: staff_dashboard.php');
        exit;
    } else {
        $msg = 'Invalid credentials';
    }
}
?>
<!doctype html><html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Hostel Management - Login</title>
<!-- <link rel="stylesheet" href="assets/style.css"> -->
 <link rel="stylesheet" href="assets/style.css?v=5">

</head><body>
<div class="container">
  <div class="card login-card">
    <h1>Hostel Management</h1>
    <?php if ($msg): ?><div class="alert"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
    <form method="post" action="">
      <label>Email</label><input type="email" name="email" required>
      <label>Password</label><input type="password" name="password" required>
      <button type="submit" class="btn">Login</button>
    </form>
    <p class="muted">Use sample accounts: student@example.com / password</p>
  </div>
</div></body></html>
