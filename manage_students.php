<?php
define('APP_ROOT', true);
require_once __DIR__ . '/../includes/functions.php';
if (!is_logged_in()) redirect('/campushub/pages/login.php');
if (!is_admin()) redirect('/campushub/index.php');
require_once __DIR__ . '/../config/db.php';

$errors = [];
$success = '';

// Handle deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['student_id'])) {
  $sid = (int)$_GET['student_id'];
  if ($sid === (int)($_SESSION['user_id'] ?? 0)) {
    $errors[] = 'You cannot delete your own account.';
  } else {
    $stmt_chk = db_prepare('SELECT id, profile_pic, role FROM students WHERE id = ? LIMIT 1');
    $stmt_chk->bind_param('i', $sid);
    $stmt_chk->execute();
    $res_chk = $stmt_chk->get_result();
    if ($res_chk->num_rows === 0) {
      $errors[] = 'User not found.';
    } else {
      $row = $res_chk->fetch_assoc();
      if ($row['role'] === 'admin') {
        $stmt_cnt = db_prepare('SELECT COUNT(*) as cnt FROM students WHERE role = ?');
        $r = 'admin';
        $stmt_cnt->bind_param('s', $r);
        $stmt_cnt->execute();
        $cnt = (int)$stmt_cnt->get_result()->fetch_assoc()['cnt'];
        if ($cnt <= 1) {
          $errors[] = 'Cannot delete the only admin account.';
        }
      }
      if (empty($errors)) {
        $mysqli->begin_transaction();
        try {
          // remove event registrations (defensive)
          $stmt_del_regs = db_prepare('DELETE FROM event_registrations WHERE student_id = ?');
          $stmt_del_regs->bind_param('i', $sid);
          $stmt_del_regs->execute();

          // remove media files and delete files from disk
          $stmt_media = db_prepare('SELECT id,file_path FROM media_files WHERE uploaded_by = ?');
          $stmt_media->bind_param('i', $sid);
          $stmt_media->execute();
          $meds = $stmt_media->get_result()->fetch_all(MYSQLI_ASSOC);
          foreach ($meds as $m) {
            $fp = __DIR__ . '/../' . $m['file_path'];
            if (is_file($fp)) @unlink($fp);
          }
          $stmt_del_media = db_prepare('DELETE FROM media_files WHERE uploaded_by = ?');
          $stmt_del_media->bind_param('i', $sid);
          $stmt_del_media->execute();

          // delete profile picture file if present
          if (!empty($row['profile_pic'])) {
            $prof = __DIR__ . '/../' . $row['profile_pic'];
            if (is_file($prof)) @unlink($prof);
          }

          // delete student row
          $stmt_del = db_prepare('DELETE FROM students WHERE id = ?');
          $stmt_del->bind_param('i', $sid);
          if (!$stmt_del->execute()) {
            throw new Exception('Failed to delete student: ' . $stmt_del->error);
          }
          $mysqli->commit();
          $success = 'Student deleted successfully.';
        } catch (Exception $e) {
          $mysqli->rollback();
          $errors[] = $e->getMessage();
        }
      }
    }
  }
}

$search = sanitize_input($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$per = 10;
$offset = ($page-1)*$per;

$sql = 'SELECT SQL_CALC_FOUND_ROWS id,full_name,email,phone,institution,role,created_at FROM students';
if ($search) $sql .= ' WHERE full_name LIKE ? OR email LIKE ?';
$sql .= ' ORDER BY created_at DESC LIMIT ?,?';

$stmt = $search ? db_prepare($sql) : db_prepare($sql);
if ($search) {
  $term = "%{$search}%";
  $stmt->bind_param('ssii',$term,$term,$offset,$per);
} else {
  $stmt->bind_param('ii',$offset,$per);
}
$stmt->execute();
$res = $stmt->get_result();
$students = $res->fetch_all(MYSQLI_ASSOC);
$stmtTotal = db_prepare('SELECT FOUND_ROWS() as cnt');
$stmtTotal->execute();
$total = $stmtTotal->get_result()->fetch_assoc()['cnt'];

require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
  <h2 class="text-3xl font-bold text-slate-900 mb-4">Manage Students</h2>
  <?php if ($errors): ?>
    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
      <?php foreach($errors as $e) echo '<div class="text-red-700 text-sm mb-1">'.htmlspecialchars($e).'</div>'; ?>
    </div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
      <div class="text-green-700 text-sm"><?php echo htmlspecialchars($success); ?></div>
    </div>
  <?php endif; ?>
  <form method="get" class="mb-6">
    <input class="w-full md:w-1/2 px-4 py-2 border border-slate-300 rounded-lg" name="search" placeholder="Search by name or email" value="<?php echo $search; ?>">
  </form>

  <table class="table">
    <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach($students as $s): ?>
        <tr>
          <td><?php echo $s['id']; ?></td>
          <td><?php echo sanitize_input($s['full_name']); ?></td>
          <td><?php echo sanitize_input($s['email']); ?></td>
          <td><?php echo $s['role']; ?></td>
          <td><?php echo $s['created_at']; ?></td>
          <td>
            <a class="btn" href="/campushub/admin/manage_students.php?action=edit&student_id=<?php echo $s['id']; ?>">Edit</a>
            <a class="btn btn-danger" href="/campushub/admin/manage_students.php?action=delete&student_id=<?php echo $s['id']; ?>" onclick="return confirm('Delete student?')">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div>
    <?php $pages = ceil($total/$per); for($i=1;$i<=$pages;$i++): ?>
      <a class="btn" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
    <?php endfor; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
