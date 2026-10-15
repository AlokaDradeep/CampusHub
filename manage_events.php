<?php
define('APP_ROOT', true);
require_once __DIR__ . '/../includes/functions.php';
if (!is_logged_in()) redirect('/campushub/pages/login.php');
if (!is_admin()) redirect('/campushub/index.php');
require_once __DIR__ . '/../config/db.php';

$errors = [];
$success = '';

// Handle deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['event_id'])) {
  $eid = (int)$_GET['event_id'];
  $stmt_chk = db_prepare('SELECT id,image FROM events WHERE id = ? LIMIT 1');
  $stmt_chk->bind_param('i', $eid);
  $stmt_chk->execute();
  $res_chk = $stmt_chk->get_result();
  if ($res_chk->num_rows === 0) {
    $errors[] = 'Event not found.';
  } else {
    $row = $res_chk->fetch_assoc();
    if (empty($errors)) {
      $mysqli->begin_transaction();
      try {
        // delete event row (event_registrations will cascade)
        $stmt_del = db_prepare('DELETE FROM events WHERE id = ?');
        $stmt_del->bind_param('i', $eid);
        if (!$stmt_del->execute()) {
          throw new Exception('Failed to delete event: ' . $stmt_del->error);
        }

        // remove image file if it points to uploads/photos
        if (!empty($row['image']) && (strpos($row['image'], 'uploads/photos/') === 0 || strpos($row['image'], 'uploads\\photos\\') === 0)) {
          $fp = __DIR__ . '/../' . $row['image'];
          if (is_file($fp)) @unlink($fp);
        }

        $mysqli->commit();
        $success = 'Event deleted successfully.';
      } catch (Exception $e) {
        $mysqli->rollback();
        $errors[] = $e->getMessage();
      }
    }
  }
}

// Add event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
  $title = sanitize_input($_POST['title'] ?? '');
  $description = sanitize_input($_POST['description'] ?? '');
  $category = sanitize_input($_POST['category'] ?? '');
  $date = sanitize_input($_POST['date'] ?? '');
  $time = sanitize_input($_POST['time'] ?? '');
  $venue = sanitize_input($_POST['venue'] ?? '');
  $capacity = (int)($_POST['capacity'] ?? 0);

  if (!$title) $errors[] = 'Title required.';
  if (!$date) $errors[] = 'Date required.';

  $imagePath = '';
  if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, ['image/jpeg','image/png','image/gif'])) $errors[] = 'Invalid image type.';
    if ($_FILES['image']['size'] > 5*1024*1024) $errors[] = 'Image too large.';
    if (empty($errors)) {
      $uploadDir = __DIR__ . '/../uploads/photos';
      if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
          $errors[] = 'Failed to create photos directory.';
        }
      }
      if (empty($errors)) {
        $filename = uniqid() . '.' . $ext;
        $destination = $uploadDir . DIRECTORY_SEPARATOR . $filename;
        if (!is_uploaded_file($_FILES['image']['tmp_name'])) {
          $errors[] = 'Invalid uploaded file.';
        } elseif (!move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
          $errors[] = 'Failed to move uploaded image.';
        } else {
          $imagePath = 'uploads/photos/' . $filename;
        }
      }
    }
  }

  if (empty($errors)) {
    $stmt = db_prepare('INSERT INTO events (title,description,category,event_date,event_time,venue,capacity,image,status) VALUES (?,?,?,?,?,?,?,?,?)');
    $status = 'active';
    $stmt->bind_param('ssssssiss',$title,$description,$category,$date,$time,$venue,$capacity,$imagePath,$status);
    if ($stmt->execute()) { $success = 'Event added.'; }
    else $errors[] = 'Insert failed.';
  }
}

// Edit event - handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
  $eid = (int)($_POST['event_id'] ?? 0);
  $title = sanitize_input($_POST['title'] ?? '');
  $description = sanitize_input($_POST['description'] ?? '');
  $category = sanitize_input($_POST['category'] ?? '');
  $date = sanitize_input($_POST['date'] ?? '');
  $time = sanitize_input($_POST['time'] ?? '');
  $venue = sanitize_input($_POST['venue'] ?? '');
  $capacity = (int)($_POST['capacity'] ?? 0);

  if (!$title) $errors[] = 'Title required.';
  if (!$date) $errors[] = 'Date required.';

  $newImagePath = '';
  if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, ['image/jpeg','image/png','image/gif'])) $errors[] = 'Invalid image type.';
    if ($_FILES['image']['size'] > 5*1024*1024) $errors[] = 'Image too large.';
    if (empty($errors)) {
      $uploadDir = __DIR__ . '/../uploads/photos';
      if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
          $errors[] = 'Failed to create photos directory.';
        }
      }
      if (empty($errors)) {
        $filename = uniqid() . '.' . $ext;
        $destination = $uploadDir . DIRECTORY_SEPARATOR . $filename;
        if (!is_uploaded_file($_FILES['image']['tmp_name'])) {
          $errors[] = 'Invalid uploaded file.';
        } elseif (!move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
          $errors[] = 'Failed to move uploaded image.';
        } else {
          $newImagePath = 'uploads/photos/' . $filename;
        }
      }
    }
  }

  if (empty($errors)) {
    // fetch old image for deletion if replaced
    $oldImage = '';
    if ($newImagePath) {
      $stmtOld = db_prepare('SELECT image FROM events WHERE id = ? LIMIT 1');
      $stmtOld->bind_param('i', $eid);
      $stmtOld->execute();
      $resOld = $stmtOld->get_result();
      if ($resOld && $resOld->num_rows) {
        $oldImage = $resOld->fetch_assoc()['image'];
      }
    }

    if ($newImagePath) {
      $stmtUp = db_prepare('UPDATE events SET title=?, description=?, category=?, event_date=?, event_time=?, venue=?, capacity=?, image=? WHERE id=?');
      $stmtUp->bind_param('ssssssisi', $title, $description, $category, $date, $time, $venue, $capacity, $newImagePath, $eid);
    } else {
      $stmtUp = db_prepare('UPDATE events SET title=?, description=?, category=?, event_date=?, event_time=?, venue=?, capacity=? WHERE id=?');
      $stmtUp->bind_param('ssssssii', $title, $description, $category, $date, $time, $venue, $capacity, $eid);
    }

    if ($stmtUp->execute()) {
      // remove old image file if replaced and stored under uploads/photos
      if ($newImagePath && !empty($oldImage) && (strpos($oldImage, 'uploads/photos/') === 0 || strpos($oldImage, 'uploads\\photos\\') === 0)) {
        $fp = __DIR__ . '/../' . $oldImage;
        if (is_file($fp)) @unlink($fp);
      }
      $success = 'Event updated.';
    } else {
      $errors[] = 'Update failed.';
    }
  }
}

// fetch events
$stmt2 = db_prepare('SELECT * FROM events ORDER BY event_date DESC');
$stmt2->execute();
$events = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

// If editing, load event data for form
$edit_event = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && !empty($_GET['event_id'])) {
  $eid = (int)$_GET['event_id'];
  $stmtE = db_prepare('SELECT * FROM events WHERE id = ? LIMIT 1');
  $stmtE->bind_param('i', $eid);
  $stmtE->execute();
  $resE = $stmtE->get_result();
  if ($resE && $resE->num_rows) $edit_event = $resE->fetch_assoc();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <h2>Manage Events</h2>
  <?php if ($errors): ?><div class="alert-error"><?php foreach($errors as $e) echo '<div>'.$e.'</div>'; ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert-success"><?php echo $success; ?></div><?php endif; ?>

  <section>
  <?php if ($edit_event): ?>
    <h3>Edit Event</h3>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="event_id" value="<?php echo $edit_event['id']; ?>">
      <div class="form-group"><label>Title</label><input class="form-control" name="title" value="<?php echo sanitize_input($edit_event['title']); ?>"></div>
      <div class="form-group"><label>Description</label><textarea class="form-control" name="description"><?php echo sanitize_input($edit_event['description']); ?></textarea></div>
      <div class="form-group"><label>Category</label>
        <select class="form-control" name="category">
          <option value="club" <?php if ($edit_event['category']=='club') echo 'selected'; ?>>Club</option>
          <option value="workshop" <?php if ($edit_event['category']=='workshop') echo 'selected'; ?>>Workshop</option>
          <option value="sports" <?php if ($edit_event['category']=='sports') echo 'selected'; ?>>Sports</option>
          <option value="competition" <?php if ($edit_event['category']=='competition') echo 'selected'; ?>>Competition</option>
        </select>
      </div>
      <div class="form-group"><label>Date</label><input class="form-control" name="date" type="date" value="<?php echo $edit_event['event_date']; ?>"></div>
      <div class="form-group"><label>Time</label><input class="form-control" name="time" type="time" value="<?php echo $edit_event['event_time']; ?>"></div>
      <div class="form-group"><label>Venue</label><input class="form-control" name="venue" value="<?php echo sanitize_input($edit_event['venue']); ?>"></div>
      <div class="form-group"><label>Capacity</label><input class="form-control" name="capacity" type="number" value="<?php echo (int)$edit_event['capacity']; ?>"></div>
      <div class="form-group"><label>Current Image</label>
        <?php if (!empty($edit_event['image'])):
          $imgPath = __DIR__ . '/../' . $edit_event['image'];
          $imgUrl = '/campushub/' . sanitize_input($edit_event['image']);
          if (is_file($imgPath)) $imgUrl .= '?v=' . filemtime($imgPath);
        ?>
          <div style="margin-bottom:.5rem"><img src="<?php echo $imgUrl; ?>" alt="" style="max-width:200px;max-height:120px;object-fit:cover;border:1px solid #ddd;padding:4px;background:#fff"></div>
        <?php else: ?>
          <div class="text-sm text-slate-500">No image set</div>
        <?php endif; ?>
        <div style="margin-top:.5rem"><label>Replace Image</label><input class="form-control" name="image" type="file" accept="image/*"></div>
      </div>
      <div style="margin-top:.5rem">
        <button class="btn" type="submit">Save Changes</button>
        <a class="btn" href="/campushub/admin/manage_events.php">Cancel</a>
      </div>
    </form>
  <?php else: ?>
    <h3>Add Event</h3>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="action" value="add">
      <div class="form-group"><label>Title</label><input class="form-control" name="title"></div>
      <div class="form-group"><label>Description</label><textarea class="form-control" name="description"></textarea></div>
      <div class="form-group"><label>Category</label><select class="form-control" name="category"><option value="club">Club</option><option value="workshop">Workshop</option><option value="sports">Sports</option><option value="competition">Competition</option></select></div>
      <div class="form-group"><label>Date</label><input class="form-control" name="date" type="date"></div>
      <div class="form-group"><label>Time</label><input class="form-control" name="time" type="time"></div>
      <div class="form-group"><label>Venue</label><input class="form-control" name="venue"></div>
      <div class="form-group"><label>Capacity</label><input class="form-control" name="capacity" type="number" value="50"></div>
      <div class="form-group"><label>Image</label><input class="form-control" name="image" type="file" accept="image/*"></div>
      <button class="btn" type="submit">Add Event</button>
    </form>
  <?php endif; ?>
  </section>

  <section>
    <h3>All Events</h3>
    <table class="table">
      <thead><tr><th>ID</th><th>Title</th><th>Date</th><th>Category</th><th>Capacity</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach($events as $e): ?>
          <tr>
            <td><?php echo $e['id']; ?></td>
            <td><?php echo sanitize_input($e['title']); ?></td>
            <td><?php echo $e['event_date']; ?></td>
            <td><?php echo sanitize_input($e['category']); ?></td>
            <td><?php echo $e['capacity']; ?></td>
            <td>
              <a class="btn" href="/campushub/admin/manage_events.php?action=edit&event_id=<?php echo $e['id']; ?>">Edit</a>
              <a class="btn btn-danger" href="/campushub/admin/manage_events.php?action=delete&event_id=<?php echo $e['id']; ?>" onclick="return confirm('Delete event?')">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </section>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
