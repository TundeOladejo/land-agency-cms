<?php
// FILE: admin/pages/editor.php
// Usage:
//  - Create new:  editor.php?type=news
//  - Edit existing: editor.php?type=news&file=1596240000_ab12cd.json

session_start();
require __DIR__ . '/../../../includes/auth.php'; // ensures session + auth
// auth.php must ensure $_SESSION['user'] exists

$type = $_GET['type'] ?? null;
if (!$type || !in_array($type, ['news','projects','careers'])) {
  http_response_code(400);
  echo "<p class='text-red-500'>Invalid page type.</p>";
  exit;
}

$storageDir = realpath(__DIR__ . '/../../../storage') . DIRECTORY_SEPARATOR . $type;
if (!is_dir($storageDir)) {
  mkdir($storageDir, 0755, true);
}

$fileName = $_GET['file'] ?? null;
$record = [
  'id' => null,
  'title' => '',
  'slug' => '',
  'body' => '',
  'featured_images' => [],
  'meta_title' => '',
  'meta_description' => '',
  'author' => $_SESSION['user']['name'] ?? 'admin',
  'published' => false,
  'created_at' => null,
  'updated_at' => null,
];

if ($fileName) {
  $path = $storageDir . DIRECTORY_SEPARATOR . basename($fileName);
  if (file_exists($path)) {
    $data = json_decode(file_get_contents($path), true);
    if (is_array($data)) $record = array_merge($record, $data);
  } else {
    echo "<p class='text-red-500'>File not found.</p>";
    exit;
  }
}

// helper for image url
$uploadUrlBase = '/uploads/featured';
$uploadDir = realpath(__DIR__ . '/../../../uploads') . DIRECTORY_SEPARATOR . 'featured';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
?>
<div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
  <div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-semibold"><?= htmlspecialchars(ucfirst($type)) ?> — <?= $fileName ? "Edit" : "Create" ?></h2>
    <div class="space-x-2">
      <button id="previewBtn" class="bg-white border border-red-600 text-red-600 px-4 py-2 rounded"><i class="fa-solid fa-file-lines mr-1"></i>Preview</button>
      <button id="publishBtn" class="bg-red-600 text-white px-4 py-2 rounded 
      "><i class="fa-solid fa-paper-plane mr-1"></i>Publish</button>
    </div>
  </div>

  <form id="editorForm" class="space-y-4" enctype="multipart/form-data">
    <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
    <input type="hidden" name="file" value="<?= htmlspecialchars($fileName ?? '') ?>">

    <label class="block">
      Title <span class="text-red-500">*</span>
      <input type="text" name="title" id="title" required class="w-full border px-3 py-2 rounded" value="<?= htmlspecialchars($record['title']) ?>">
    </label>

    <label class="block">
      Slug (optional)
      <input type="text" name="slug" id="slug" class="w-full border px-3 py-2 rounded" value="<?= htmlspecialchars($record['slug']) ?>">
    </label>

    <label class="block">
      Featured Images <span class="text-sm text-gray-500">(max 3, 5MB each) </span>
      <input type="file" name="images[]" id="images" accept="image/*" multiple class="mt-2">
      <div id="existingImages" class="flex gap-2 mt-3 flex-wrap">
        <?php foreach ($record['featured_images'] as $img): ?>
          <div class="relative">
            <img src="<?= htmlspecialchars($uploadUrlBase . '/' . $img) ?>" 
     data-filename="<?= htmlspecialchars($img) ?>"
     class="h-24 w-36 object-cover rounded border">
            <button type="button" class="absolute top-0 right-0 bg-red-500 text-white items-center justify-center rounded-full h-6 w-6 -translate-y-1/2 translate-x-1/2" onclick="removeImage('<?= htmlspecialchars($img) ?>')">
              <i class="fa-solid fa-minus"></i>
            </button>
          </div>
        <?php endforeach; ?>
      </div>
      <div id="newImagePreviews" class="flex gap-2 mt-3 flex-wrap"></div>
    </label>

    <label class="block">
      Body
      <textarea id="body" name="body" class="w-full h-64 border px-3 py-2 rounded"><?= htmlspecialchars($record['body']) ?></textarea>
    </label>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <label class="block">
        Meta Title
        <input type="text" name="meta_title" id="meta_title" class="w-full border px-3 py-2 rounded" value="<?= htmlspecialchars($record['meta_title']) ?>">
      </label>

      <label class="block">
        Meta Description
        <input type="text" name="meta_description" id="meta_description" class="w-full border px-3 py-2 rounded" value="<?= htmlspecialchars($record['meta_description']) ?>">
      </label>
    </div>

    <label class="block">
      Author
      <input type="text" name="author" id="author" class="w-full border px-3 py-2 rounded" value="<?= htmlspecialchars($record['author']) ?>">
    </label>

    <div class="flex justify-between items-center">
      <div class="text-sm text-gray-600">Last saved: <span id="lastSaved"><?= htmlspecialchars($record['updated_at'] ?? $record['created_at'] ?? 'Never') ?></span></div>
      <div>
        <button type="button" id="saveDraftBtn" class="bg-red-600 text-white px-4 py-2 rounded"><i class="fa-solid fa-file-pen mr-1"></i>Save Draft</button>
      </div>
    </div>
  </form>
</div>

<!-- Preview modal -->
<div id="previewModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white max-w-3xl w-full h-3/4 p-6 rounded shadow-lg overflow-hidden flex flex-col">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold">Preview</h3>
      <button onclick="closePreview()" class="text-red-500 hover:text-black text-xl"><i class="fa-solid fa-circle-xmark"></i></button>
    </div>

    <!-- This scrollable content area -->
    <div id="previewContent" class="overflow-y-auto flex-1 pr-2"></div>

    <!-- Action buttons fixed at bottom -->
    <div class="mt-4 flex justify-between items-center border-t pt-4">
      <button onclick="closePreview()" class="text-gray-600 hover:text-black">Close</button>
      <button id="confirmPublish" class="bg-red-600 text-white px-4 py-2 rounded">
        <i class="fa-solid fa-paper-plane mr-1"></i> Confirm Publish
      </button>
    </div>
  </div>
</div>
