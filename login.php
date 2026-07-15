<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf_token'] ?? '')) exit('Invalid CSRF');
    $identifier = trim($_POST['identifier'] ?? ''); // email or phone
    $password = $_POST['password'] ?? '';

    if (!$identifier || !$password) $error = 'Fill fields';
    else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :ident OR phone = :ident LIMIT 1");
        $stmt->execute(['ident'=>$identifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $error = 'Invalid credentials';
        } elseif (!$user['is_verified']) {
            // start pending flow
            $_SESSION['pending_user_id'] = $user['id'];
            header('Location: verify_otp.php');
            exit;
        } else {
            // login
            $_SESSION['user_id'] = $user['id'];
            header('Location: dashboard.php');
            exit;
        }
    }
}
$token = csrf_token();
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Login</title><link rel="stylesheet" href="style.css"></head>
<body>
  <h2>Login</h2>
  <?php if (!empty($error)): ?><div class="error"><?=e($error)?></div><?php endif; ?>
  <form method="post" action="">
    <input type="hidden" name="csrf_token" value="<?=e($token)?>">
    <label>Email or Phone<input name="identifier" required></label>
    <label>Password<input name="password" type="password" required></label>
    <button type="submit">Login</button>
  </form>
  <p><a href="register.php">Create account</a></p>
</body>
</html>
