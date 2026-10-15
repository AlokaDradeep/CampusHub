<?php
define('APP_ROOT', true);
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = sanitize_input($_POST['name'] ?? '');
  $email = sanitize_input($_POST['email'] ?? '');
  $subject = sanitize_input($_POST['subject'] ?? '');
  $message = sanitize_input($_POST['message'] ?? '');

  if (!$name) $errors[] = 'Name required.';
  if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';
  if (!$message) $errors[] = 'Message required.';

  if (empty($errors)) {
    $stmt = db_prepare('INSERT INTO contact_messages (name,email,subject,message) VALUES (?,?,?,?)');
    $stmt->bind_param('ssss',$name,$email,$subject,$message);
    if ($stmt->execute()) { $success = 'Message submitted.'; }
    else $errors[] = 'Submission failed.';
  }
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
    <!-- Contact Info -->
    <div>
      <h1 class="text-4xl font-bold text-slate-900 mb-8">Get in Touch</h1>
      
      <div class="space-y-6">
        <div class="flex gap-4">
          <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path></svg>
          </div>
          <div>
            <h3 class="font-semibold text-slate-900">Email</h3>
            <p class="text-slate-600">info@campushub.edu</p>
          </div>
        </div>

        <div class="flex gap-4">
          <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773c.457 1.883 2.013 3.995 3.768 5.667 1.755 1.672 3.867 3.228 5.75 3.686l.773-1.548a1 1 0 011.06-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"></path></svg>
          </div>
          <div>
            <h3 class="font-semibold text-slate-900">Phone</h3>
            <p class="text-slate-600">+1 (555) 123-4567</p>
          </div>
        </div>

        <div class="flex gap-4">
          <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg>
          </div>
          <div>
            <h3 class="font-semibold text-slate-900">Location</h3>
            <p class="text-slate-600">Campus Hub, University Area</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Contact Form -->
    <div class="lg:col-span-2">
      <div class="bg-white rounded-lg shadow-lg p-8">
        <h2 class="text-2xl font-bold text-slate-900 mb-6">Send us a Message</h2>
        
        <?php if ($errors): ?>
          <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <?php foreach($errors as $e) echo '<div class="text-red-700 text-sm mb-2">'.htmlspecialchars($e).'</div>'; ?>
          </div>
        <?php endif; ?>
        <?php if ($success): ?>
          <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <div class="text-green-700 text-sm">✓ '.htmlspecialchars($success).'</div>
          </div>
        <?php endif; ?>

        <form method="post" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-slate-900 mb-2">Name</label>
              <input class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" name="name" value="<?php echo sanitize_input($_POST['name'] ?? ''); ?>" required>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-900 mb-2">Email</label>
              <input class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" name="email" type="email" value="<?php echo sanitize_input($_POST['email'] ?? ''); ?>" required>
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-slate-900 mb-2">Subject</label>
            <input class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" name="subject" value="<?php echo sanitize_input($_POST['subject'] ?? ''); ?>">
          </div>

          <div>
            <label class="block text-sm font-semibold text-slate-900 mb-2">Message</label>
            <textarea class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" name="message" id="message" rows="6" required><?php echo sanitize_input($_POST['message'] ?? ''); ?></textarea>
            <div class="text-right text-xs text-slate-500 mt-2" id="counter">0 characters</div>
          </div>

          <button class="w-full px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition" type="submit">Send Message</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
var ta = document.getElementById('message');
var counter = document.getElementById('counter');
if (ta) ta.addEventListener('input', function(){ counter.textContent = this.value.length + ' characters'; });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
