<?php
require_once 'db.php';
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$stmt = $pdo->prepare("SELECT id, fullname, email, phone, created_at FROM users WHERE id = :id");
$stmt->execute(['id'=>$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Dashboard</title><link rel="stylesheet" href="style.css"></head>
<body>
  <h2>Welcome, <?=e($user['fullname'])?></h2>
  <p>Email: <?=e($user['email'])?></p>
  <p>Phone: <?=e($user['phone'])?></p>
  <p><a href="logout.php">Logout</a></p>
</body>
</html>
