<?php
define('APP_ROOT', true);
require_once __DIR__ . '/../includes/functions.php';
if (!is_logged_in()) redirect('/campushub/pages/login.php');
if (!is_admin()) redirect('/campushub/index.php');
require_once __DIR__ . '/../config/db.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['files'])) {
  foreach($_FILES['files']['tmp_name'] as $i => $tmp) {
    if ($_FILES['files']['error'][$i] !== UPLOAD_ERR_OK) continue;
    $name = $_FILES['files']['name'][$i];
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $tmp);
    finfo_close($finfo);
    $type = 'document';
    if (in_array($ext, ['jpg','jpeg','png','gif'])) $type='image';
    elseif (in_array($ext, ['mp4','webm','ogg'])) $type='video';
    elseif (in_array($ext, ['mp3','wav'])) $type='audio';
    $uploadDir = __DIR__ . '/../uploads/media';
    if (!is_dir($uploadDir)) {
      if (!mkdir($uploadDir, 0755, true)) {
        $errors[] = 'Failed to create media directory.';
        continue;
      }
    }
    $filename = uniqid() . '.' . $ext;
    $destination = $uploadDir . DIRECTORY_SEPARATOR . $filename;
    if (!is_uploaded_file($tmp)) continue;
    if (move_uploaded_file($tmp, $destination)) {
      $new = 'uploads/media/' . $filename;
      $stmt = db_prepare('INSERT INTO media_files (file_name,file_type,file_path,uploaded_by) VALUES (?,?,?,?)');
      $stmt->bind_param('sssi',$name,$type,$new,$_SESSION['user_id']);
      $stmt->execute();
      $success = 'Files uploaded.';
    }
  }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <h2>Bulk Media Upload</h2>
  <?php if ($errors): ?><div class="alert-error"><?php foreach($errors as $e) echo '<div>'.$e.'</div>'; ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert-success"><?php echo $success; ?></div><?php endif; ?>
  <form method="post" enctype="multipart/form-data">
    <div class="form-group">
      <label>Select files</label>
      <input class="form-control" type="file" name="files[]" multiple>
    </div>
    <button class="btn" type="submit">Upload</button>
  </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
