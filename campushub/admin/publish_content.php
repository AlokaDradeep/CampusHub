<?php
define('APP_ROOT', true);
require_once __DIR__ . '/../includes/functions.php';
if (!is_logged_in()) redirect('/campushub/pages/login.php');
if (!is_admin()) redirect('/campushub/index.php');

$xml_file = __DIR__ . '/../xml/announcements.xml';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
  $title = sanitize_input($_POST['title'] ?? '');
  $content = sanitize_input($_POST['content'] ?? '');
  $date = sanitize_input($_POST['date'] ?? date('Y-m-d'));
  $priority = sanitize_input($_POST['priority'] ?? 'normal');

  if ($title && $content) {
    if (!file_exists($xml_file)) {
      $xml = new SimpleXMLElement('<announcements/>');
    } else {
      $xml = simplexml_load_file($xml_file);
    }
    $id = 1;
    foreach($xml->announcement as $a) $id = max($id, (int)$a['id']) + 1;
    $a = $xml->addChild('announcement');
    $a->addAttribute('id', $id);
    $a->addChild('title', $title);
    $a->addChild('content', $content);
    $a->addChild('date', $date);
    $a->addChild('priority', $priority);
    $xml->asXML($xml_file);
    $msg = 'Announcement added.';
  }
}

$ann = [];
if (file_exists($xml_file)) { $ann = simplexml_load_file($xml_file); }

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <h2>Publish Content</h2>
  <section>
    <h3>Add Announcement</h3>
    <form method="post">
      <div class="form-group"><label>Title</label><input class="form-control" name="title"></div>
      <div class="form-group"><label>Content</label><textarea class="form-control" name="content"></textarea></div>
      <div class="form-group"><label>Date</label><input class="form-control" name="date" type="date" value="<?php echo date('Y-m-d'); ?>"></div>
      <div class="form-group"><label>Priority</label><select class="form-control" name="priority"><option value="normal">Normal</option><option value="high">High</option></select></div>
      <button class="btn" name="add" type="submit">Publish</button>
    </form>
  </section>

  <section>
    <h3>Existing Announcements</h3>
    <div class="cards-grid">
      <?php if ($ann): foreach($ann->announcement as $a): ?>
        <article class="card">
          <h4><?php echo sanitize_input((string)$a->title); ?></h4>
          <p><?php echo sanitize_input((string)$a->content); ?></p>
          <small><?php echo sanitize_input((string)$a->date); ?></small>
        </article>
      <?php endforeach; endif; ?>
    </div>
  </section>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
