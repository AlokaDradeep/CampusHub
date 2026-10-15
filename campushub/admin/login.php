<?php
define('APP_ROOT', true);
require_once __DIR__ . '/../includes/functions.php';
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
            if ($row['role'] !== 'admin') {
                $errors[] = 'Access denied. Not an admin account.';
            } elseif (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['full_name'] = $row['full_name'];
                redirect('/campushub/admin/dashboard.php');
            } else {
                $errors[] = 'Invalid credentials.';
            }
        } else {
            $errors[] = 'User not found.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100 py-12 px-4 sm:px-6 lg:px-8">
  <div class="w-full max-w-md">
    <div class="bg-white rounded-lg shadow-lg p-8">
      <div class="text-center mb-8">
        <h2 class="text-3xl font-bold text-slate-900 mb-2">Admin Login</h2>
        <p class="text-slate-600">Sign in to access the admin dashboard</p>
      </div>
      
      <?php if ($errors): ?>
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
          <?php foreach($errors as $e) echo '<div class="text-red-700 text-sm mb-2">✕ '. htmlspecialchars($e) .'</div>'; ?>
        </div>
      <?php endif; ?>

      <form method="post" action="" class="space-y-4">
        <div>
          <label class="block text-sm font-semibold text-slate-900 mb-2">Email</label>
          <input class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent" name="email" type="email" required placeholder="admin@example.com">
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-900 mb-2">Password</label>
          <input class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent" name="password" type="password" required placeholder="••••••••">
        </div>
        <button class="w-full px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition" type="submit">Sign In</button>
      </form>
      
      <div class="mt-6 pt-6 border-t border-slate-200">
        <p class="text-center text-slate-600 text-sm">Not an admin? <a href="/campushub/pages/login.php" class="text-blue-600 hover:text-blue-700 font-semibold">Student Login</a></p>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
