<?php
define('APP_ROOT', true);
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';

$category = sanitize_input($_GET['category'] ?? '');
$params = [];
$sql = "SELECT * FROM events WHERE status='active'";
if ($category) {
    $sql .= " AND category = ?";
}
$sql .= " ORDER BY event_date ASC";

$stmt = $category ? db_prepare($sql) : db_prepare($sql);
if ($category) $stmt->bind_param('s', $category);
$stmt->execute();
$res = $stmt->get_result();
$events = $res->fetch_all(MYSQLI_ASSOC);

// categories for filter
$cats = ['club','workshop','sports','competition'];
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
  <div class="mb-8">
    <h1 class="text-4xl font-bold text-slate-900 mb-2">Discover Events</h1>
    <p class="text-slate-600">Join clubs, workshops, sports, and competitions on campus</p>
  </div>

  <div class="mb-8 flex items-center gap-4">
    <label class="text-sm font-semibold text-slate-900">Filter by category:</label>
    <select name="category" class="px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" onchange="this.form.submit()">
      <option value="">All Events</option>
      <?php foreach($cats as $c): ?>
        <option value="<?php echo $c; ?>" <?php if ($category===$c) echo 'selected'; ?>><?php echo ucfirst($c); ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach($events as $e):
      $registered_count = 0;
      $stmt2 = db_prepare('SELECT COUNT(*) as cnt FROM event_registrations WHERE event_id = ?');
      $stmt2->bind_param('i', $e['id']);
      $stmt2->execute();
      $r2 = $stmt2->get_result()->fetch_assoc();
      $registered_count = (int)$r2['cnt'];
      $spots = max(0, $e['capacity'] - $registered_count);
    ?>
    <article class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-xl transition transform hover:-translate-y-1">
      <img src="/campushub/<?php echo sanitize_input($e['image']); ?>" alt="<?php echo sanitize_input($e['title']); ?>" class="w-full h-48 object-cover">
      <div class="p-6">
        <div class="flex items-center justify-between mb-2">
          <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full"><?php echo ucfirst(sanitize_input($e['category'])); ?></span>
          <span class="text-xs font-semibold text-green-600 bg-green-50 px-2 py-1 rounded"><?php echo $spots; ?> spots</span>
        </div>
        <h3 class="text-xl font-semibold text-slate-900 mb-2"><?php echo sanitize_input($e['title']); ?></h3>
        <p class="text-sm text-slate-600 mb-4"><i class="fas fa-calendar"></i> <?php echo format_date($e['event_date']); ?> • <?php echo sanitize_input($e['venue']); ?></p>
        <a href="/campushub/pages/event_detail.php?id=<?php echo $e['id']; ?>" class="inline-block w-full text-center px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">View Details</a>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
