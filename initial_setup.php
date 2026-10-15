<?php
define('APP_ROOT', true);
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

// only allow initial setup if no admin exists
$stmt = db_prepare("SELECT COUNT(*) as cnt FROM students WHERE role = ?");
$r = 'admin';
$stmt->bind_param('s', $r);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
if ($row && (int)$row['cnt'] > 0) {
  // admins already exist — don't allow reuse
  redirect('/campushub/admin/login.php');
}

$errors = [];
$success = '';
$generated_password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $full_name = sanitize_input($_POST['full_name'] ?? '');
  $email = sanitize_input($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if (!$full_name) $errors[] = 'Full name required.';
  if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';

  if (empty($errors)) {
    // create admin
    if (!$password) {
      $generated_password = bin2hex(random_bytes(6));
      $passwordToStore = $generated_password;
    } else {
      $passwordToStore = $password;
    }
    $hash = password_hash($passwordToStore, PASSWORD_DEFAULT);
    $role = 'admin';
    $stmt_ins = db_prepare('INSERT INTO students (full_name, email, password, role) VALUES (?,?,?,?)');
    $stmt_ins->bind_param('ssss', $full_name, $email, $hash, $role);
    if ($stmt_ins->execute()) {
      $success = 'Initial admin account created.';
    } else {
      $errors[] = 'Insert failed: ' . $stmt_ins->error;
    }
  }
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container">
  <h2>Initial Admin Setup</h2>
  <p>This page can be used once to create the first administrator account. It will redirect after an admin exists — remove or restrict this file after use.</p>
  <?php if ($errors): ?><div class="alert-error"><?php foreach($errors as $e) echo '<div>'.$e.'</div>'; ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert-success"><?php echo $success; ?></div><?php endif; ?>

  <form method="post">
    <div class="form-group"><label>Full Name</label><input class="form-control" name="full_name" value="<?php echo sanitize_input($full_name ?? ''); ?>"></div>
    <div class="form-group"><label>Email</label><input class="form-control" name="email" type="email" value="<?php echo sanitize_input($email ?? ''); ?>"></div>
    <div class="form-group"><label>Password (optional — leave blank to generate)</label><input class="form-control" name="password" type="text"></div>
    <button class="btn btn-primary" type="submit">Create Admin</button>
  </form>

  <?php if ($generated_password && $success): ?>
    <div class="card" style="margin-top:1rem;">
      <h4>Generated password (copy now)</h4>
      <div class="alert-success"><?php echo sanitize_input($generated_password); ?></div>
      <p>Use this password to log in at <a href="/campushub/admin/login.php">Admin Login</a>. Delete or restrict this file after use.</p>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
