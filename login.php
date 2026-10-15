<?php
define('APP_ROOT', true);
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$email || !$password) {
        $errors[] = 'Email and password are required.';
    } else {
        $stmt = db_prepare('SELECT id, password, role, full_name FROM students WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 1) {
            $row = $res->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['full_name'] = $row['full_name'];
                if ($row['role'] === 'admin') {
                    redirect('/campushub/admin/dashboard.php');
                } else {
                    redirect('/campushub/pages/profile.php');
                }
            } else {
                $errors[] = 'Invalid credentials.';
            }
        } else {
            $errors[] = 'User not found.';
        }
    }
}
?>

<div class="container">
  <h2>Login</h2>
  <?php if ($errors): ?>
    <div class="alert-error">
      <?php foreach($errors as $e) echo '<div>'. $e .'</div>'; ?>
    </div>
  <?php endif; ?>

  <form method="post" action="">
    <div class="form-group">
      <label>Email</label>
      <input class="form-control" name="email" type="email" required>
    </div>
    <div class="form-group">
      <label>Password</label>
      <input class="form-control" name="password" type="password" required>
    </div>
    <button class="btn btn-primary" type="submit">Login</button>
  </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
