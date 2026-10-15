<?php
define('APP_ROOT', true);
require_once __DIR__ . '/../includes/functions.php';
if (!is_logged_in()) redirect('/campushub/pages/login.php');
if (!is_admin()) redirect('/campushub/index.php');
require_once __DIR__ . '/../config/db.php';

$filter_event = (int)($_GET['event_id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
  $rid = (int)$_POST['registration_id'];
  $status = sanitize_input($_POST['status']);
  $stmt = db_prepare('UPDATE event_registrations SET attendance_status = ? WHERE id = ?');
  $stmt->bind_param('si', $status, $rid);
  $stmt->execute();
}

$queryBase = 'SELECT r.id, s.full_name, e.title, r.registered_at, r.attendance_status FROM event_registrations r JOIN students s ON r.student_id=s.id JOIN events e ON r.event_id=e.id';
if ($filter_event) {
  $stmt = db_prepare($queryBase . ' WHERE e.id = ? ORDER BY r.registered_at DESC');
  $stmt->bind_param('i', $filter_event);
} else {
  $stmt = db_prepare($queryBase . ' ORDER BY r.registered_at DESC');
}
$stmt->execute();
$regs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmtEv = db_prepare('SELECT id,title FROM events');
$stmtEv->execute();
$events = $stmtEv->get_result()->fetch_all(MYSQLI_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <h2>Manage Registrations</h2>
  <form method="get">
    <label>Filter by event</label>
    <select name="event_id" class="form-control" onchange="this.form.submit()">
      <option value="0">All</option>
      <?php foreach($events as $ev): ?>
        <option value="<?php echo $ev['id']; ?>" <?php if ($filter_event==$ev['id']) echo 'selected'; ?>><?php echo sanitize_input($ev['title']); ?></option>
      <?php endforeach; ?>
    </select>
  </form>

  <table class="table">
    <thead><tr><th>ID</th><th>Student</th><th>Event</th><th>Registered At</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach($regs as $r): ?>
        <tr>
          <td><?php echo $r['id']; ?></td>
          <td><?php echo sanitize_input($r['full_name']); ?></td>
          <td><?php echo sanitize_input($r['title']); ?></td>
          <td><?php echo $r['registered_at']; ?></td>
          <td><?php echo sanitize_input($r['attendance_status']); ?></td>
          <td>
            <form method="post" style="display:inline-block">
              <input type="hidden" name="registration_id" value="<?php echo $r['id']; ?>">
              <select name="status">
                <option value="registered" <?php if ($r['attendance_status']=='registered') echo 'selected'; ?>>Registered</option>
                <option value="attended" <?php if ($r['attendance_status']=='attended') echo 'selected'; ?>>Attended</option>
                <option value="absent" <?php if ($r['attendance_status']=='absent') echo 'selected'; ?>>Absent</option>
              </select>
              <button class="btn" name="update_status">Update</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
