<?php
// CLI: Assign images from uploads/photos to events by filename matching
// Usage:
//   php scripts/assign_event_images.php --dry-run
//   php scripts/assign_event_images.php --apply
//   php scripts/assign_event_images.php --dir=uploads/photos --match=title|id|both --verbose

define('APP_ROOT', realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR);
require_once APP_ROOT . 'config/db.php';

$opts = getopt('', ['dir::', 'dry-run', 'apply', 'match::', 'help', 'verbose']);
if (isset($opts['help'])) {
    echo "Assign event images by filename to events in the database\n";
    echo "Options:\n";
    echo "  --dir=PATH       Directory relative to project root (default: uploads/photos)\n";
    echo "  --dry-run        Show matches but don't update DB (default)\n";
    echo "  --apply          Apply updates to DB (commits changes)\n";
    echo "  --match=STR      Matching mode: title (default), id, or both\n";
    echo "  --verbose        Verbose output\"\n";
    exit(0);
}

$dir = isset($opts['dir']) ? $opts['dir'] : 'uploads/photos';
$apply = isset($opts['apply']);
$dryRun = isset($opts['dry-run']) || !$apply;
$matchMode = isset($opts['match']) ? $opts['match'] : 'title';
$verbose = isset($opts['verbose']);

$searchPath = APP_ROOT . $dir;
if (!is_dir($searchPath)) {
    fwrite(STDERR, "Directory not found: $searchPath\n");
    exit(2);
}

// normalization function (keeps Unicode letters and numbers)
$normalize = function($s) {
    $s = trim((string)$s);
    $s = mb_strtolower($s, 'UTF-8');
    // remove punctuation and whitespace, keep letters/numbers
    $s = preg_replace('/[^\p{L}\p{N}]+/u', '', $s);
    return $s;
};

// load events
$events = [];
$res = $mysqli->query('SELECT id, title, image FROM events');
if (!$res) {
    fwrite(STDERR, "DB error: " . $mysqli->error . "\n");
    exit(3);
}
while ($row = $res->fetch_assoc()) {
    $events[(int)$row['id']] = $row;
}
$res->free();

// build normalized title map
$titleMap = [];
foreach ($events as $id => $ev) {
    $key = $normalize($ev['title']);
    if ($key === '') $key = 'id_' . $id;
    if (!isset($titleMap[$key])) $titleMap[$key] = [];
    $titleMap[$key][] = $id;
}

$files = array_values(array_filter(scandir($searchPath), function($f) use ($searchPath) {
    return is_file($searchPath . DIRECTORY_SEPARATOR . $f) && preg_match('/\.(jpe?g|png|gif|webp|bmp)$/i', $f);
}));

$matches = [];
$ambiguous = [];
$unmatched = [];

foreach ($files as $file) {
    $base = pathinfo($file, PATHINFO_FILENAME);
    $norm = $normalize($base);
    $found = false;

    // ID match
    if (($matchMode === 'id' || $matchMode === 'both') && preg_match('/^\d+$/', $norm)) {
        $id = (int)$norm;
        if (isset($events[$id])) {
            $matches[$file] = ['event_id' => $id, 'event_title' => $events[$id]['title'], 'old_image' => $events[$id]['image']];
            $found = true;
            if ($verbose) echo "Matched by id: $file -> event #$id ({$events[$id]['title']})\n";
            continue;
        }
    }

    // Title match
    if (($matchMode === 'title' || $matchMode === 'both')) {
        if (isset($titleMap[$norm])) {
            if (count($titleMap[$norm]) === 1) {
                $id = $titleMap[$norm][0];
                $matches[$file] = ['event_id' => $id, 'event_title' => $events[$id]['title'], 'old_image' => $events[$id]['image']];
                $found = true;
                if ($verbose) echo "Matched by title: $file -> event #$id ({$events[$id]['title']})\n";
                continue;
            } else {
                $ambiguous[$file] = $titleMap[$norm];
                if ($verbose) echo "Ambiguous match for $file -> event IDs: " . implode(',', $titleMap[$norm]) . "\n";
                continue;
            }
        }
    }

    if (!$found) {
        $unmatched[] = $file;
    }
}

// report
echo "Scanned directory: $searchPath\n";
echo "Total image files: " . count($files) . "\n";
echo "Potential matches: " . count($matches) . "\n";
if (count($ambiguous) > 0) echo "Ambiguous files: " . count($ambiguous) . " (skipped)\n";
if (count($unmatched) > 0) echo "Unmatched files: " . count($unmatched) . "\n";

foreach ($matches as $file => $m) {
    $newPath = rtrim($dir, '/') . '/' . $file;
    echo " - $file => [event: {$m['event_id']}] {$m['event_title']} (was: {$m['old_image']}) -> $newPath\n";
}

if ($dryRun) {
    echo "\nDry-run mode: no changes made. To apply changes, re-run with --apply\n";
    exit(0);
}

if (count($matches) === 0) {
    echo "No matches to apply. Exiting.\n";
    exit(0);
}

// apply changes in a transaction and write a CSV backup
$timestamp = date('Ymd_His');
$backupFile = APP_ROOT . 'scripts' . DIRECTORY_SEPARATOR . "assign_event_images_backup_$timestamp.csv";
$fp = fopen($backupFile, 'w');
fputcsv($fp, ['event_id', 'event_title', 'old_image', 'new_image']);

$mysqli->begin_transaction();
try {
    $stmt = db_prepare('UPDATE events SET image = ? WHERE id = ?');
    foreach ($matches as $file => $m) {
        $newPath = rtrim($dir, '/') . '/' . $file;
        $stmt->bind_param('si', $newPath, $m['event_id']);
        if (!$stmt->execute()) {
            throw new Exception('Failed to update event ' . $m['event_id'] . ': ' . $stmt->error);
        }
        fputcsv($fp, [$m['event_id'], $m['event_title'], $m['old_image'], $newPath]);
    }
    $mysqli->commit();
    fclose($fp);
    echo "\nApplied " . count($matches) . " updates. Backup written to: $backupFile\n";
} catch (Exception $e) {
    $mysqli->rollback();
    fclose($fp);
    unlink($backupFile);
    fwrite(STDERR, "Error applying updates: " . $e->getMessage() . "\n");
    exit(1);
}

exit(0);
