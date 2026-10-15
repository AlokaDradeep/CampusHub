<?php
define('APP_ROOT', true);
require_once __DIR__ . '/../includes/functions.php';
if (!is_logged_in()) redirect('/campushub/pages/login.php');
if (!is_admin()) redirect('/campushub/index.php');
require_once __DIR__ . '/../config/db.php';

$stmt = db_prepare('SELECT id, name, email, subject, message, submitted_at FROM contact_messages ORDER BY submitted_at DESC');
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <h2>Contact Messages</h2>
  <div style="margin-bottom:1rem">
    <a class="btn" href="/campushub/admin/dashboard.php">Back to Dashboard</a>
  </div>

  <?php if (empty($messages)): ?>
    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded">No messages found.</div>
  <?php else: ?>
    <table class="table">
      <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Subject</th><th>Message</th><th>When</th></tr></thead>
      <tbody>
      <?php foreach($messages as $m): ?>
        <tr>
          <td><?php echo $m['id']; ?></td>
          <td><?php echo sanitize_input($m['name']); ?></td>
          <td><?php echo sanitize_input($m['email']); ?></td>
          <td><?php echo sanitize_input($m['subject']); ?></td>
          <td><?php echo nl2br(sanitize_input($m['message'])); ?></td>
          <td><?php echo $m['submitted_at']; ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
