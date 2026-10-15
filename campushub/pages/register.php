<?php
define('APP_ROOT', true);
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';

$errors = [];
$old = ['full_name'=>'','email'=>'','phone'=>'','institution'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $phone = sanitize_input($_POST['phone'] ?? '');
    $institution = sanitize_input($_POST['institution'] ?? '');

    $old = ['full_name'=>$full_name,'email'=>$email,'phone'=>$phone,'institution'=>$institution];

    if (!$full_name) $errors[] = 'Full name is required.';
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';
    if ($phone && !preg_match('/^[0-9\-\s\(\)\+]+$/', $phone)) $errors[] = 'Phone format is invalid.';

    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = db_prepare("INSERT INTO students (full_name,email,password,phone,institution) VALUES (?,?,?,?,?)");
        $stmt->bind_param('sssss',$full_name,$email,$hashed,$phone,$institution);
        if ($stmt->execute()) {
            redirect('/campushub/pages/login.php');
        } else {
            $errors[] = 'Registration failed: ' . $stmt->error;
        }
    }
}
?>

<div class="container">
  <h2>Student Registration</h2>
  <?php if ($errors): ?>
    <div class="alert-error">
      <ul>
        <?php foreach($errors as $e): ?><li><?php echo $e; ?></li><?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post" action="" novalidate>
    <div class="form-group">
      <label>Full Name</label>
      <input class="form-control" name="full_name" type="text" placeholder="Jane Doe" value="<?php echo $old['full_name']; ?>" required>
    </div>
    <div class="form-group">
      <label>Email</label>
      <input class="form-control" name="email" id="email" type="email" placeholder="you@example.com" value="<?php echo $old['email']; ?>" required>
      <div id="emailFeedback" style="color:#c0392b;font-size:.9rem"></div>
    </div>
    <div class="form-group">
      <label>Password</label>
      <input class="form-control" name="password" id="password" type="password" placeholder="Minimum 8 characters" required>
    </div>
    <div class="form-group">
      <label>Confirm Password</label>
      <input class="form-control" name="confirm_password" id="confirm_password" type="password" placeholder="Confirm password" required>
      <div id="pwMatch" style="color:#c0392b;font-size:.9rem"></div>
    </div>
    <div class="form-group">
      <label>Phone</label>
      <input class="form-control" name="phone" type="text" placeholder="(555) 123-4567" value="<?php echo $old['phone']; ?>">
    </div>
    <div class="form-group">
      <label>Institution</label>
      <input class="form-control" name="institution" type="text" placeholder="Your school" value="<?php echo $old['institution']; ?>">
    </div>
    <button class="btn btn-primary" type="submit">Register</button>
  </form>
</div>

<script>
document.getElementById('email').addEventListener('input', function(e){
  var ok = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e.target.value);
  document.getElementById('emailFeedback').textContent = ok ? '' : 'Invalid email format';
});
document.getElementById('confirm_password').addEventListener('input', function(){
  var p = document.getElementById('password').value;
  var c = this.value;
  document.getElementById('pwMatch').textContent = (p===c) ? '' : 'Passwords do not match';
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
