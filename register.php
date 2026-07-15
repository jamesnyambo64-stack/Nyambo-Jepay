<?php
require_once 'db.php';
require_once 'sms.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        exit('Invalid CSRF token');
    }

    $fullname = trim($_POST['fullname'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $phone = preg_replace('/[^0-9+]/', '', trim($_POST['phone'] ?? ''));
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (!$fullname || !$phone || !$password) {
        $error = 'Please fill required fields';
    } elseif ($password !== $password_confirm) {
        $error = 'Passwords do not match';
    } else {
        // check exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email OR phone = :phone");
        $stmt->execute(['email' => $email, 'phone' => $phone]);
        if ($stmt->fetch()) {
            $error = 'Email or phone already registered';
        } else {
            $pw_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (fullname, email, phone, password_hash) VALUES (:fullname, :email, :phone, :pw)");
            $stmt->execute(['fullname'=>$fullname, 'email'=>$email, 'phone'=>$phone, 'pw'=>$pw_hash]);
            $userId = $pdo->lastInsertId();

            // create OTP
            $code = random_int(100000, 999999);
            $expires = (new DateTime('+10 minutes'))->format('Y-m-d H:i:s');
            $stmt = $pdo->prepare("INSERT INTO otp_codes (user_id, code, expires_at) VALUES (:uid, :code, :exp)");
            $stmt->execute(['uid'=>$userId, 'code'=>$code, 'exp'=>$expires]);

            // Send SMS (wrap in try/catch in prod)
            $message = "Your verification code is: $code (valid 10 minutes)";
            send_sms_twilio($phone, $message);

            $_SESSION['pending_user_id'] = $userId;
            header('Location: verify_otp.php');
            exit;
        }
    }
}
$token = csrf_token();
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Register</title><link rel="stylesheet" href="style.css"></head>
<body>
  <h2>Register</h2>
  <?php if (!empty($error)): ?><div class="error"><?=e($error)?></div><?php endif; ?>
  <form method="post" action="">
    <input type="hidden" name="csrf_token" value="<?=e($token)?>">
    <label>Full name<input name="fullname" required></label>
    <label>Email (optional)<input name="email" type="email"></label>
    <label>Phone (required)<input name="phone" required></label>
    <label>Password<input name="password" type="password" required></label>
    <label>Confirm Password<input name="password_confirm" type="password" required></label>
    <button type="submit">Register</button>
  </form>
  <p><a href="login.php">Already have an account? Login</a></p>
</body>
</html>
