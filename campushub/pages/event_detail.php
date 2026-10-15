<?php
define('APP_ROOT', true);
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    redirect('/campushub/pages/events.php');
}

$stmt = db_prepare('SELECT * FROM events WHERE id = ? LIMIT 1');
$stmt->bind_param('i',$id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
if (!$event) redirect('/campushub/pages/events.php');

$errors = [];
$success = '';

// check registration
$already = false;
if (is_logged_in()) {
  $stmt2 = db_prepare('SELECT id FROM event_registrations WHERE event_id = ? AND student_id = ? LIMIT 1');
  $stmt2->bind_param('ii',$id,$_SESSION['user_id']);
  $stmt2->execute();
  $already = $stmt2->get_result()->num_rows > 0;
}

// registration handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!is_logged_in()) { $errors[] = 'You must be logged in to register.'; }
  else {
    // check capacity
    $stmt3 = db_prepare('SELECT COUNT(*) as cnt FROM event_registrations WHERE event_id = ?');
    $stmt3->bind_param('i',$id);
    $stmt3->execute();
    $cnt = $stmt3->get_result()->fetch_assoc()['cnt'];
    if ($cnt >= $event['capacity']) { $errors[] = 'Event Full.'; }
    elseif ($already) { $errors[] = 'You are already registered.'; }
    else {
      $stmt4 = db_prepare('INSERT INTO event_registrations (student_id,event_id) VALUES (?,?)');
      $stmt4->bind_param('ii', $_SESSION['user_id'], $id);
      if ($stmt4->execute()) { $success = 'Registered successfully.'; $already = true; }
      else $errors[] = 'Registration failed.';
    }
  }
}

// compute spots remaining
$stmt5 = db_prepare('SELECT COUNT(*) as cnt FROM event_registrations WHERE event_id = ?');
$stmt5->bind_param('i',$id);
$stmt5->execute();
$registered = (int)$stmt5->get_result()->fetch_assoc()['cnt'];
$spots = max(0, $event['capacity'] - $registered);

?>

<div class="container">
  <article class="card">
    <img src="/campushub/<?php echo sanitize_input($event['image']); ?>" alt="<?php echo sanitize_input($event['title']); ?>">
    <h2><?php echo sanitize_input($event['title']); ?></h2>
    <p><?php echo nl2br(sanitize_input($event['description'])); ?></p>
    <p><strong>Date:</strong> <?php echo format_date($event['event_date']); ?> <strong>Time:</strong> <?php echo substr($event['event_time'],0,5); ?></p>
    <p><strong>Venue:</strong> <?php echo sanitize_input($event['venue']); ?></p>
    <p><strong>Spots remaining:</strong> <?php echo $spots; ?></p>

    <?php if ($errors): ?>
      <div class="alert-error"><?php foreach($errors as $e) echo '<div>'.$e.'</div>'; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if ($already): ?>
      <div class="badge">Already Registered</div>
    <?php elseif ($spots <= 0): ?>
      <div class="badge priority-high">Event Full</div>
    <?php else: ?>
      <form method="post">
        <button class="btn btn-primary" type="submit">Register for this Event</button>
      </form>
    <?php endif; ?>
  </article>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
