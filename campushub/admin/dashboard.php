<?php
define('APP_ROOT', true);
require_once __DIR__ . '/../includes/functions.php';
if (!is_logged_in()) redirect('/campushub/pages/login.php');
if (!is_admin()) redirect('/campushub/index.php');
require_once __DIR__ . '/../config/db.php';

$total_students = get_student_count();
$total_events = get_event_count();
$stmt = db_prepare('SELECT COUNT(*) as cnt FROM event_registrations'); $stmt->execute(); $total_reg = $stmt->get_result()->fetch_assoc()['cnt'];
$stmt2 = db_prepare('SELECT COUNT(*) as cnt FROM contact_messages WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)'); $stmt2->execute(); $recent_msgs = $stmt2->get_result()->fetch_assoc()['cnt'];

$stmt3 = db_prepare('SELECT r.id, s.full_name, e.title, r.registered_at FROM event_registrations r JOIN students s ON r.student_id=s.id JOIN events e ON r.event_id=e.id ORDER BY r.registered_at DESC LIMIT 10');
$stmt3->execute(); $recent_regs = $stmt3->get_result()->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <h2>Admin Dashboard</h2>
  <div class="stats-bar">
    <div class="card"><strong><?php echo $total_students; ?></strong><div>Students</div></div>
    <div class="card"><strong><?php echo $total_events; ?></strong><div>Events</div></div>
    <div class="card"><strong><?php echo $total_reg; ?></strong><div>Registrations</div></div>
    <div class="card"><strong><?php echo $recent_msgs; ?></strong><div>Messages (30d)</div></div>
  </div>

  <section>
    <h3>Recent Registrations</h3>
    <table class="table">
      <thead><tr><th>ID</th><th>Student</th><th>Event</th><th>When</th></tr></thead>
      <tbody>
        <?php foreach($recent_regs as $r): ?>
        <tr>
          <td><?php echo $r['id']; ?></td>
          <td><?php echo sanitize_input($r['full_name']); ?></td>
          <td><?php echo sanitize_input($r['title']); ?></td>
          <td><?php echo $r['registered_at']; ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </section>

  <div style="margin-top:1rem">
    <a class="btn" href="/campushub/admin/manage_students.php">Manage Students</a>
    <a class="btn" href="/campushub/admin/manage_events.php">Manage Events</a>
    <a class="btn" href="/campushub/admin/manage_registrations.php">Manage Registrations</a>
    <a class="btn" href="/campushub/admin/publish_content.php">Manage Announcements</a>
    <a class="btn" href="/campushub/admin/messages.php">View Messages</a>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
