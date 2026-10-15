<?php
define('APP_ROOT', true);
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';

if (!is_logged_in()) redirect('/campushub/pages/login.php');

$uid = $_SESSION['user_id'];
$stmt = db_prepare('SELECT id,full_name,email,phone,institution,profile_pic,created_at FROM students WHERE id = ?');
$stmt->bind_param('i',$uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $full_name = sanitize_input($_POST['full_name'] ?? '');
  $phone = sanitize_input($_POST['phone'] ?? '');
  $institution = sanitize_input($_POST['institution'] ?? '');

  if (!$full_name) $errors[] = 'Name required.';
  if ($phone && !preg_match('/^[0-9\-\s\(\)\+]+$/', $phone)) $errors[] = 'Phone invalid.';

  // profile pic handling
  if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['image/jpeg','image/png','image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES['profile_pic']['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, $allowed)) $errors[] = 'Invalid profile image type.';
    if ($_FILES['profile_pic']['size'] > 5 * 1024 * 1024) $errors[] = 'Profile image too large.';
    if (empty($errors)) {
      $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
      $uploadDir = __DIR__ . '/../uploads/photos';
      if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
          $errors[] = 'Failed to create upload directory.';
        }
      }
      if (empty($errors)) {
        $filename = uniqid('prof_') . '.' . $ext;
        $destination = $uploadDir . DIRECTORY_SEPARATOR . $filename;
        if (!is_uploaded_file($_FILES['profile_pic']['tmp_name'])) {
          $errors[] = 'Invalid uploaded file.';
        } elseif (!move_uploaded_file($_FILES['profile_pic']['tmp_name'], $destination)) {
          $errors[] = 'Failed to move uploaded file. Check permissions and path.';
        } else {
          $profile_pic = 'uploads/photos/' . $filename;
        }
      }
    }
  }

  if (empty($errors)) {
    if (isset($profile_pic)) {
      $stmt2 = db_prepare('UPDATE students SET full_name=?, phone=?, institution=?, profile_pic=? WHERE id=?');
      $stmt2->bind_param('ssssi', $full_name, $phone, $institution, $profile_pic, $uid);
    } else {
      $stmt2 = db_prepare('UPDATE students SET full_name=?, phone=?, institution=? WHERE id=?');
      $stmt2->bind_param('sssi', $full_name, $phone, $institution, $uid);
    }
    if ($stmt2->execute()) { $success = 'Profile updated.'; }
    else $errors[] = 'Update failed.';
  }
}

// registered events
$stmt3 = db_prepare('SELECT e.* FROM events e JOIN event_registrations r ON e.id = r.event_id WHERE r.student_id = ?');
$stmt3->bind_param('i',$uid);
$stmt3->execute();
$regs = $stmt3->get_result()->fetch_all(MYSQLI_ASSOC);

?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
  <div class="mb-8">
    <h1 class="text-4xl font-bold text-slate-900">My Profile</h1>
    <p class="text-slate-600 mt-2">Update your profile information and manage your account</p>
  </div>

  <?php if ($errors): ?>
    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
      <?php foreach($errors as $e) echo '<div class="text-red-700 text-sm mb-2">✕ '.htmlspecialchars($e).'</div>'; ?>
    </div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
      <div class="text-green-700 text-sm">✓ '.htmlspecialchars($success).'</div>
    </div>
  <?php endif; ?>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Profile Photo Section -->
    <div class="lg:col-span-1">
      <div class="bg-white rounded-lg shadow-lg overflow-hidden sticky top-20">
        <div class="bg-gradient-to-br from-blue-500 to-cyan-500 h-32"></div>
        <div class="px-6 pb-6">
          <div class="flex justify-center -mt-16 mb-4">
            <?php if ($user['profile_pic']): ?>
              <img src="/campushub/<?php echo sanitize_input($user['profile_pic']); ?>" alt="<?php echo sanitize_input($user['full_name']); ?>" class="w-32 h-32 rounded-full border-4 border-white shadow-lg object-cover">
            <?php else: ?>
              <div class="w-32 h-32 rounded-full border-4 border-white shadow-lg bg-slate-300 flex items-center justify-center text-slate-500 text-4xl">👤</div>
            <?php endif; ?>
          </div>
          <h2 class="text-2xl font-bold text-slate-900 text-center"><?php echo sanitize_input($user['full_name']); ?></h2>
          <p class="text-slate-600 text-center text-sm mt-1"><?php echo sanitize_input($user['email']); ?></p>
          <div class="mt-4 p-4 bg-slate-50 rounded-lg">
            <p class="text-xs text-slate-600 mb-1">Member since</p>
            <p class="text-sm font-semibold text-slate-900"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></p>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Profile Section -->
    <div class="lg:col-span-2">
      <div class="bg-white rounded-lg shadow-lg p-8">
        <h3 class="text-2xl font-bold text-slate-900 mb-6">Edit Profile</h3>
        <form method="post" enctype="multipart/form-data" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-semibold text-slate-900 mb-2">Full Name</label>
              <input class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent" name="full_name" value="<?php echo sanitize_input($user['full_name']); ?>" required>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-900 mb-2">Phone</label>
              <input class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent" name="phone" value="<?php echo sanitize_input($user['phone'] ?? ''); ?>" placeholder="(123) 456-7890">
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-slate-900 mb-2">Institution</label>
            <input class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent" name="institution" value="<?php echo sanitize_input($user['institution'] ?? ''); ?>" placeholder="Your school/university">
          </div>

          <div>
            <label class="block text-sm font-semibold text-slate-900 mb-2">Profile Picture</label>
            <div class="border-2 border-dashed border-slate-300 rounded-lg p-6 hover:border-blue-500 transition cursor-pointer">
              <input class="hidden" id="profile-pic-input" type="file" name="profile_pic" accept="image/*" onchange="previewImage(event)">
              <label for="profile-pic-input" class="flex flex-col items-center justify-center cursor-pointer">
                <svg class="w-10 h-10 text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <p class="text-slate-600 font-medium">Click to upload or drag and drop</p>
                <p class="text-slate-400 text-sm">PNG, JPG, GIF up to 5MB</p>
              </label>
            </div>
            <div id="preview-container" class="mt-4"></div>
          </div>

          <div class="flex gap-4">
            <button class="flex-1 px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition" type="submit">Save Changes</button>
            <a href="/campushub/index.php" class="flex-1 px-6 py-3 bg-slate-200 text-slate-900 font-semibold rounded-lg hover:bg-slate-300 transition text-center">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Registered Events Section -->
  <div class="mt-12">
    <h3 class="text-3xl font-bold text-slate-900 mb-8">My Registrations</h3>
    <?php if (count($regs) > 0): ?>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach($regs as $r): ?>
          <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
            <?php if (!empty($r['image'])):
              $imgPath = __DIR__ . '/../' . $r['image'];
              $imgUrl = '/campushub/' . sanitize_input($r['image']);
              if (is_file($imgPath)) $imgUrl .= '?v=' . filemtime($imgPath);
            ?>
              <img src="<?php echo $imgUrl; ?>" alt="<?php echo sanitize_input($r['title']); ?>" class="w-full h-40 object-cover">
            <?php else: ?>
              <div class="h-40 bg-gradient-to-br from-blue-400 to-cyan-400 flex items-center justify-center">
                <svg class="w-16 h-16 text-white opacity-50" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zm-5.04-6.71l-2.75-3.54-4.96 6.71h12l-3.75-4.63z"/></svg>
              </div>
            <?php endif; ?>
            <div class="p-6">
              <h4 class="text-lg font-semibold text-slate-900 mb-2"><?php echo sanitize_input($r['title']); ?></h4>
              <div class="space-y-2 text-sm text-slate-600">
                <div class="flex items-center gap-2">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z"/></svg>
                  <span><?php echo format_date($r['event_date']); ?></span>
                </div>
                <div class="flex items-center gap-2">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/></svg>
                  <span><?php echo sanitize_input($r['venue']); ?></span>
                </div>
              </div>
              <a href="/campushub/pages/event_detail.php?id=<?php echo $r['id']; ?>" class="mt-4 inline-block px-4 py-2 bg-blue-100 text-blue-600 font-semibold rounded-lg hover:bg-blue-200 transition">View Details</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="text-center py-12 bg-slate-50 rounded-lg">
        <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <p class="text-slate-600 font-semibold mb-2">No registrations yet</p>
        <p class="text-slate-500 mb-4">You haven't registered for any events yet.</p>
        <a href="/campushub/pages/events.php" class="inline-block px-6 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">Browse Events</a>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
function previewImage(event) {
  const file = event.target.files[0];
  const container = document.getElementById('preview-container');
  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      container.innerHTML = '<img src="' + e.target.result + '" alt="Preview" class="w-full rounded-lg shadow-md">';
    };
    reader.readAsDataURL(file);
  }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
