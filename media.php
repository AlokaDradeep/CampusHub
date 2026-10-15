<?php
define('APP_ROOT', true);
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';

$video = '/campushub/uploads/media/promo-video.mp4';
$audio = '/campushub/uploads/media/intro-audio.mp3';

$stmt = db_prepare("SELECT * FROM media_files WHERE file_type IN ('image') ORDER BY uploaded_at DESC LIMIT 12");
$stmt->execute();
$images = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
  <div class="mb-12">
    <h1 class="text-4xl font-bold text-slate-900 mb-2">Media</h1>
    <p class="text-slate-600">Watch and listen to content from CampusHub</p>
  </div>

  <!-- Video Section -->
  <section class="mb-12">
    <h2 class="text-3xl font-bold text-slate-900 mb-6">Featured Video</h2>
    <div class="bg-slate-900 rounded-lg overflow-hidden shadow-lg">
      <video controls width="100%" poster="/campushub/uploads/photos/event2.jpg" class="w-full">
        <source src="<?php echo $video; ?>" type="video/mp4">
        Your browser does not support the video tag.
      </video>
    </div>
  </section>

  <!-- Audio Section -->
  <section class="mb-12">
    <h2 class="text-3xl font-bold text-slate-900 mb-6">Audio</h2>
    <div class="bg-gradient-to-br from-blue-50 to-cyan-50 p-8 rounded-lg border border-blue-200">
      <div class="flex items-center gap-4 mb-4">
        <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3v9.28c-.47-.98-1.45-1.78-2.64-1.78C6.55 9.5 5 11.12 5 13s1.55 3.5 4.36 3.5c1.19 0 2.17-.8 2.64-1.78V21h7V3h-7zm0 16h-2v-2h2v2zm4-9h-2V5h2v5z"/></svg>
        <div>
          <p class="font-semibold text-slate-900">Intro Audio Track</p>
          <p class="text-sm text-slate-600">Duration: ~2 minutes</p>
        </div>
      </div>
      <audio controls class="w-full">
        <source src="<?php echo $audio; ?>" type="audio/mpeg">
        Your browser does not support audio.
      </audio>
    </div>
  </section>

  <!-- Gallery Section -->
  <section>
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-3xl font-bold text-slate-900">Gallery Preview</h2>
      <a href="/campushub/pages/gallery.php" class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">View Full Gallery</a>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
      <?php foreach($images as $img): ?>
        <div class="rounded-lg overflow-hidden shadow-md hover:shadow-lg transition">
          <img src="/campushub/<?php echo sanitize_input($img['file_path']); ?>" alt="<?php echo sanitize_input($img['file_name']); ?>" class="w-full h-40 object-cover hover:scale-110 transition duration-300">
        </div>
      <?php endforeach; ?>
    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
