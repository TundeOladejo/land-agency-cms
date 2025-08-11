<?php
// FILE: admin/pages/save.php
session_start();
require __DIR__ . '/../../../includes/auth.php';

function logEditorAction($data) {
    $logDir = __DIR__ . '/../../../storage/logs/editor';
    if (!is_dir($logDir)) mkdir($logDir, 0755, true);

    $logFile = $logDir . '/' . date('Y-m-d') . '.log.json';

    $entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'user' => $_SESSION['user']['email'] ?? 'unknown',
        'action' => isset($data['draft']) && $data['draft'] ? 'draft' : 'publish',
        'type' => $data['type'] ?? 'unknown',
        'file' => $data['file'] ?? null,
        'title' => $data['title'] ?? '',
        'slug' => $data['slug'] ?? '',
        'meta_title' => $data['meta_title'] ?? '',
        'meta_description' => $data['meta_description'] ?? '',
        'author' => $data['author'] ?? '',
        'featured_images' => $data['images'] ?? [],
        'body' => $data['body'] ?? '',
        'client_ip' => $_SERVER['REMOTE_ADDR'] ?? null,
    ];

    // Append as JSON per line
    file_put_contents($logFile, json_encode($entry, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND);
}


header('Content-Type: application/json');

$type = $_POST['type'] ?? null;
if (!$type || !in_array($type,['news','projects','careers'])) {
  echo json_encode(['success'=>false, 'message'=>'Invalid type']);
  exit;
}

$storageDir = realpath(__DIR__ . '/../../../storage') . DIRECTORY_SEPARATOR . $type;
if (!is_dir($storageDir)) mkdir($storageDir, 0755, true);

$uploadDir = realpath(__DIR__ . '/../../uploads') . DIRECTORY_SEPARATOR . 'featured';
$uploadUrlBase = '/uploads/featured';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$file = $_POST['file'] ?? '';
$title = trim($_POST['title'] ?? '');
$slug = trim($_POST['slug'] ?? '');
$body = $_POST['body'] ?? '';
$meta_title = $_POST['meta_title'] ?? '';
$meta_description = $_POST['meta_description'] ?? '';
$author = $_POST['author'] ?? $_SESSION['user']['name'] ?? 'admin';
$draft = isset($_POST['draft']) && $_POST['draft'] == '1';
$remove_images = json_decode($_POST['remove_images'] ?? '[]', true) ?: [];

if ($title === '') {
  echo json_encode(['success'=>false, 'message'=>'Title required']);
  exit;
}

// determine filename (existing or new)
if ($file) {
  $filename = basename($file);
} else {
  // generate a unique filename
  $filename = time() . '_' . bin2hex(random_bytes(4)) . '.json';
}

// load existing record if any
$path = $storageDir . DIRECTORY_SEPARATOR . $filename;
$record = [
  'id' => pathinfo($filename, PATHINFO_FILENAME),
  'title' => $title,
  'slug' => $slug ?: preg_replace('/[^a-z0-9-]+/i','-',strtolower($title)),
  'body' => $body,
  'featured_images' => [],
  'meta_title' => $meta_title,
  'meta_description' => $meta_description,
  'author' => $author,
  'published' => $draft ? false : true,
  'created_at' => date('c'),
  'updated_at' => date('c'),
];

// if existing file -> merge older featured_images
if (file_exists($path)) {
  $old = json_decode(file_get_contents($path), true);
  if (is_array($old) && !empty($old['featured_images'])) {
    $record['featured_images'] = $old['featured_images'];
    // keep created_at if existed
    if (!empty($old['created_at'])) $record['created_at'] = $old['created_at'];
  }
}

// handle image removals
if (!empty($remove_images) && is_array($remove_images)) {
  foreach ($remove_images as $rem) {
    $rem = basename($rem);
    $idx = array_search($rem, $record['featured_images']);
    if ($idx !== false) {
      unset($record['featured_images'][$idx]);
      $f = $uploadDir . DIRECTORY_SEPARATOR . $rem;
      if (file_exists($f)) @unlink($f);
    }
  }
  // reindex
  $record['featured_images'] = array_values($record['featured_images']);
}

// handle uploads
if (!empty($_FILES['images']) && is_array($_FILES['images']['name'])) {
  $names = $_FILES['images']['name'];
  $tmp = $_FILES['images']['tmp_name'];
  $size = $_FILES['images']['size'];
  for ($i=0;$i<count($names);$i++) {
    if (!$names[$i]) continue;
    if ($size[$i] > 5 * 1024 * 1024) continue; // skip >5MB
    $ext = pathinfo($names[$i], PATHINFO_EXTENSION);
    $newName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest = $uploadDir . DIRECTORY_SEPARATOR . $newName;
    if (move_uploaded_file($tmp[$i], $dest)) {
      // optional: you can implement image resizing / cropping here (GD or Imagick)
      $record['featured_images'][] = $newName;
    }
  }
  // limit to 3 images
  if (count($record['featured_images']) > 3) {
    $record['featured_images'] = array_slice($record['featured_images'], 0, 3);
  }
}

// write file
file_put_contents($path, json_encode($record, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));

logEditorAction($_POST);

// respond
echo json_encode([
  'success' => true,
  'file' => $filename,
  'updated_at' => $record['updated_at'],
  'message' => $draft ? 'Saved draft' : 'Published',
  'type' => $_POST['type'] ?? null,
]);
exit;
