<?php
// FILE: admin/pages/save.php
session_start();
require __DIR__ . '/../../../includes/auth.php';

header('Content-Type: application/json');

$type = $_POST['type'] ?? null;
if (!$type || !in_array($type,['news','projects','careers'])) {
  echo json_encode(['success'=>false, 'message'=>'Invalid type']);
  exit;
}

$storageDir = realpath(__DIR__ . '/../../../storage') . DIRECTORY_SEPARATOR . $type;
if (!is_dir($storageDir)) mkdir($storageDir, 0755, true);

$uploadDir = realpath(__DIR__ . '/../../../uploads') . DIRECTORY_SEPARATOR . 'featured';
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

// respond
echo json_encode([
  'success' => true,
  'file' => $filename,
  'updated_at' => $record['updated_at'],
  'message' => $draft ? 'Saved draft' : 'Published'
]);
exit;
