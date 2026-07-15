<?php
require_once 'db.php';

if (empty($_SESSION['pending_user_id'])) {
    header('Location: login.php');
    exit;
}
$userId = (int)$_SESSION['pending_user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf_token'] ?? '')) exit('Invalid CSRF');
    $code = trim($_POST['code'] ?? '');
    if (!$code) { $error = 'Enter the code'; }
    else {
        $stmt = $pdo->prepare("SELECT id, expires_at, used FROM otp_codes WHERE user_id = :uid AND code = :code ORDER BY id DESC LIMIT 1");
        $stmt->execute(['uid'=>$userId,'code'=>$code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            $error = 'Invalid code';
        } elseif ($row['used']) {
            $error = 'Code already used';
        } elseif (new DateTime() > new DateTime($row['expires_at'])) {
            $error = 'Code expired';
        } else {
            // mark used and verify user
            $pdo->prepare("UPDATE otp_codes SET used = 1 WHERE id = :id")->execute(['id'=>$row['id']]);
            $pdo->prepare("UPDATE users SET is_verified = 1 WHERE id = :uid")->execute(['uid'=>$userId]);

            // login user
            $_SESSION['user_id'] = $userId;
            unset($_SESSION['pending_user_id']);
            header('Location: dashboard.php');
            exit;
        }
    }
}
$token = csrf_token();
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Verify</title><link rel="stylesheet" href="style.css"></head>
<body>
  <h2>Enter SMS Code</h2>
  <?php if (!empty($error)): ?><div class="error"><?=e($error)?></div><?php endif; ?>
  <form method="post" action="">
    <input type="hidden" name="csrf_token" value="<?=e($token)?>">
    <label>Code<input name="code" required></label>
    <button type="submit">Verify</button>
  </form>
</body>
</html>
