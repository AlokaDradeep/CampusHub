<?php
define('APP_ROOT', true);
require_once __DIR__ . '/../includes/header.php';
// load XML announcements
$xml_file = __DIR__ . '/../xml/announcements.xml';
$announcements = [];
if (file_exists($xml_file)) {
  $xml = simplexml_load_file($xml_file);
  foreach ($xml->announcement as $a) $announcements[] = $a;
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
  <div class="mb-8">
    <h1 class="text-4xl font-bold text-slate-900 mb-2">Announcements</h1>
    <p class="text-slate-600">Stay updated with the latest campus news and events</p>
  </div>

  <?php if (count($announcements) > 0): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach($announcements as $a): ?>
      <article class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-lg transition border-l-4 <?php echo ((string)$a->priority==='high')? 'border-red-500':'border-blue-500'; ?>">
        <div class="p-6">
          <div class="flex items-center justify-between mb-2">
            <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo ((string)$a->priority==='high')? 'bg-red-100 text-red-700':'bg-blue-100 text-blue-700'; ?>"><?php echo ucfirst(sanitize_input((string)$a->priority)); ?></span>
            <span class="text-xs text-slate-500"><?php echo sanitize_input((string)$a->date); ?></span>
          </div>
          <h3 class="text-xl font-semibold text-slate-900 mb-2"><?php echo sanitize_input((string)$a->title); ?></h3>
          <p class="text-slate-600 line-clamp-3"><?php echo sanitize_input((string)$a->content); ?></p>
        </div>
      </article>
    <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="text-center py-12 bg-slate-50 rounded-lg">
      <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
      <p class="text-slate-600 font-semibold">No announcements yet</p>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
