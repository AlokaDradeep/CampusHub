<?php
define('APP_ROOT', true);
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';

$stmt = db_prepare("SELECT * FROM media_files WHERE file_type = 'image' ORDER BY uploaded_at DESC");
$stmt->execute();
$images = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
  <div class="mb-8">
    <h1 class="text-4xl font-bold text-slate-900 mb-2">Gallery</h1>
    <p class="text-slate-600">Explore photos and moments from our events</p>
  </div>

  <?php if (count($images) > 0): ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
      <?php foreach($images as $img): ?>
        <div class="group cursor-pointer overflow-hidden rounded-lg shadow-md hover:shadow-xl transition">
          <img src="/campushub/<?php echo sanitize_input($img['file_path']); ?>" alt="<?php echo sanitize_input($img['file_name']); ?>" data-src="/campushub/<?php echo sanitize_input($img['file_path']); ?>" class="gallery-item w-full h-40 object-cover group-hover:scale-110 transition duration-300">
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="text-center py-12 bg-slate-50 rounded-lg">
      <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
      <p class="text-slate-600 font-semibold">No images yet</p>
    </div>
  <?php endif; ?>

  <div class="fixed inset-0 bg-black bg-opacity-90 hidden items-center justify-center z-50 lightbox" id="lightbox">
    <div class="relative w-full h-full flex items-center justify-center">
      <button onclick="document.getElementById('lightbox').classList.add('hidden')" class="absolute top-4 right-4 text-white hover:bg-slate-700 p-2 rounded-lg">✕</button>
      <img src="" alt="lightbox" class="max-w-4xl max-h-screen object-contain">
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.gallery-item').forEach(function(img){
  img.addEventListener('click', function(){
    var lb = document.getElementById('lightbox');
    lb.querySelector('img').src = this.dataset.src;
    lb.classList.remove('hidden');
    lb.classList.add('flex');
  });
});
document.getElementById('lightbox').addEventListener('click', function(e){
  if (e.target === this) this.classList.add('hidden');
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
