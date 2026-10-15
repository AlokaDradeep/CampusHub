<?php
define('APP_ROOT', true);
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/db.php';

// fetch counts
$total_students = get_student_count();
$total_events = get_event_count();

// featured events
$stmt = db_prepare("SELECT id,title,event_date,event_time,venue,image,capacity FROM events WHERE status='active' ORDER BY event_date ASC LIMIT 3");
$stmt->execute();
$res = $stmt->get_result();
$featured = $res->fetch_all(MYSQLI_ASSOC);
?>

<section class="bg-gradient-to-br from-blue-50 to-cyan-50 py-16 md:py-24">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
      <div>
        <h1 class="text-4xl md:text-5xl font-bold text-slate-900 mb-4">CampusHub</h1>
        <p class="text-lg text-slate-600 mb-6">Your central hub for student clubs, events, and campus activities.</p>
        <a href="/campushub/pages/events.php" class="inline-block px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition shadow-lg hover:shadow-xl">Explore Events</a>
      </div>
      <div>
        <div class="grid grid-cols-3 gap-4">
          <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-600">
            <div class="text-3xl font-bold text-blue-600"><?php echo $total_students; ?></div>
            <div class="text-slate-600 text-sm mt-2">Students</div>
          </div>
          <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-cyan-600">
            <div class="text-3xl font-bold text-cyan-600"><?php echo $total_events; ?></div>
            <div class="text-slate-600 text-sm mt-2">Events</div>
          </div>
          <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-amber-600">
            <div class="text-3xl font-bold text-amber-600">12</div>
            <div class="text-slate-600 text-sm mt-2">Clubs</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
  <section class="mb-12">
    <h2 class="text-3xl font-bold text-slate-900 mb-8">Featured Events</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach($featured as $ev): ?>
        <article class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-xl transition transform hover:-translate-y-1">
          <img src="/campushub/<?php echo sanitize_input($ev['image']); ?>" alt="<?php echo sanitize_input($ev['title']); ?>" class="w-full h-48 object-cover">
          <div class="p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-2"><?php echo sanitize_input($ev['title']); ?></h3>
            <p class="text-sm text-slate-600 mb-4"><i class="fas fa-calendar mr-2"></i><?php echo format_date($ev['event_date']); ?> at <?php echo substr($ev['event_time'],0,5); ?></p>
            <a href="/campushub/pages/event_detail.php?id=<?php echo $ev['id']; ?>" class="inline-block px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition">View Details</a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="mb-12">
    <h2 class="text-3xl font-bold text-slate-900 mb-8">Latest Announcements</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php
        $ann = get_active_announcements_xml(3);
        foreach($ann as $a): ?>
          <article class="bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-600">
            <h3 class="text-lg font-semibold text-slate-900 mb-2"><?php echo sanitize_input((string)$a->title); ?></h3>
            <p class="text-slate-600 mb-4"><?php echo sanitize_input((string)$a->content); ?></p>
            <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold <?php echo ((string)$a->priority==='high')? 'bg-red-100 text-red-700':'bg-blue-100 text-blue-700'; ?>"><?php echo sanitize_input((string)$a->priority); ?></span>
          </article>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="mb-12">
    <h2 class="text-3xl font-bold text-slate-900 mb-8">Quick Links</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <a href="/campushub/pages/register.php" class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition text-center hover:bg-blue-50">
        <div class="text-3xl mb-2">📝</div>
        <div class="font-semibold text-slate-900">Register as Student</div>
      </a>
      <a href="/campushub/pages/gallery.php" class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition text-center hover:bg-blue-50">
        <div class="text-3xl mb-2">🖼️</div>
        <div class="font-semibold text-slate-900">View Gallery</div>
      </a>
      <a href="/campushub/pages/contact.php" class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition text-center hover:bg-blue-50">
        <div class="text-3xl mb-2">✉️</div>
        <div class="font-semibold text-slate-900">Contact Us</div>
      </a>
      <?php if (is_admin()): ?>
        <a href="/campushub/admin/dashboard.php" class="bg-gradient-to-br from-slate-700 to-slate-900 p-6 rounded-lg shadow-md hover:shadow-lg transition text-center text-white">
          <div class="text-3xl mb-2">⚙️</div>
          <div class="font-semibold">Admin Dashboard</div>
        </a>
      <?php endif; ?>
    </div>
  </section>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
