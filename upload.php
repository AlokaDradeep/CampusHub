<?php
define('APP_ROOT', true);
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';

if (!is_logged_in()) redirect('/campushub/pages/login.php');

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
  $file = $_FILES['file'];
  $allowed_image = ['jpg','jpeg','png','gif'];
  $allowed_doc = ['pdf','doc','docx'];
  $maxImage = 5 * 1024 * 1024; // 5MB
  $maxDoc = 10 * 1024 * 1024; // 10MB

  $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mime = finfo_file($finfo, $file['tmp_name']);
  finfo_close($finfo);

  if (in_array($ext, $allowed_image) && in_array($mime, ['image/jpeg','image/png','image/gif'])) {
    if ($file['size'] > $maxImage) $errors[] = 'Image too large.';
    else {
      $uploadDir = __DIR__ . '/../uploads/photos';
      if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
          $errors[] = 'Failed to create photos directory.';
        }
      }
      if (empty($errors)) {
        $filename = uniqid() . '.' . $ext;
        $destination = $uploadDir . DIRECTORY_SEPARATOR . $filename;
        if (!is_uploaded_file($file['tmp_name'])) {
          $errors[] = 'Invalid uploaded file.';
        } elseif (!move_uploaded_file($file['tmp_name'], $destination)) {
          $errors[] = 'Failed to move file.';
        } else {
          $newName = 'uploads/photos/' . $filename;
          $type = 'image';
          $stmt = db_prepare('INSERT INTO media_files (file_name,file_type,file_path,uploaded_by) VALUES (?,?,?,?)');
          $stmt->bind_param('sssi', $file['name'],$type,$newName,$_SESSION['user_id']);
          $stmt->execute();
          $success = 'Uploaded successfully.';
        }
      }
    }
  } elseif (in_array($ext, $allowed_doc) && in_array($mime, ['application/pdf','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document'])) {
    if ($file['size'] > $maxDoc) $errors[] = 'Document too large.';
    else {
      $uploadDir = __DIR__ . '/../uploads/documents';
      if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
          $errors[] = 'Failed to create documents directory.';
        }
      }
      if (empty($errors)) {
        $filename = uniqid() . '.' . $ext;
        $destination = $uploadDir . DIRECTORY_SEPARATOR . $filename;
        if (!is_uploaded_file($file['tmp_name'])) {
          $errors[] = 'Invalid uploaded file.';
        } elseif (!move_uploaded_file($file['tmp_name'], $destination)) {
          $errors[] = 'Failed to move file.';
        } else {
          $newName = 'uploads/documents/' . $filename;
          $type = 'document';
          $stmt = db_prepare('INSERT INTO media_files (file_name,file_type,file_path,uploaded_by) VALUES (?,?,?,?)');
          $stmt->bind_param('sssi', $file['name'],$type,$newName,$_SESSION['user_id']);
          $stmt->execute();
          $success = 'Uploaded successfully.';
        }
      }
    }
  } else {
    $errors[] = 'Invalid file type.';
  }
}

$stmt2 = db_prepare('SELECT * FROM media_files ORDER BY uploaded_at DESC LIMIT 20');
$stmt2->execute();
$files = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

?>

<div class="container">
  <h2>Upload File</h2>
  <?php if ($errors): ?><div class="alert-error"><?php foreach($errors as $e) echo '<div>'.$e.'</div>'; ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert-success"><?php echo $success; ?></div><?php endif; ?>

  <form method="post" enctype="multipart/form-data">
    <div class="form-group">
      <label>Choose file</label>
      <input type="file" name="file" class="form-control" required>
    </div>
    <button class="btn" type="submit">Upload</button>
  </form>

  <h3>Recent Uploads</h3>
  <table class="table">
    <thead><tr><th>File</th><th>Type</th><th>Uploaded At</th></tr></thead>
    <tbody>
      <?php foreach($files as $f): ?>
        <tr>
          <td><a href="/<?php echo sanitize_input($f['file_path']); ?>"><?php echo sanitize_input($f['file_name']); ?></a></td>
          <td><?php echo sanitize_input($f['file_type']); ?></td>
          <td><?php echo $f['uploaded_at']; ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
