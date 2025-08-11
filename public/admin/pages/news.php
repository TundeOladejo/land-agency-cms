<div class="flex justify-between items-center mt-4 mb-10">
  <h2 class="text-xl font-bold">News</h2>
  <button onclick="loadPage('pages/editor.php?type=news')" class="px-4 py-2 bg-gray-800 text-white rounded">+ Create New</button>
</div>
<table class="w-full border">
  <thead>
    <tr class="bg-gray-100">
      <th class="p-2 border">Title</th>
      <th class="p-2 border">Date</th>
      <th class="p-2 border">Status</th>
      <th class="p-2 border">Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $files = glob(__DIR__ . '/../../../storage/news/*.json');
    foreach ($files as $file) {
      $data = json_decode(file_get_contents($file), true);
      $filename = basename($file);
      echo "<tr>
        <td class='p-2 border capitalize'>{$data['title']}</td>
        <td class='p-2 border'>{$data['updated_at']}</td>
        <td class='p-2 border capitalize'>" . ($data['published'] ? 'published' : 'draft') . "</td>
        <td class='p-2 border'>
          <button onclick=\"loadPage('pages/editor.php?type=news&file=" . basename($file) . "')\" class='px-2 py-1 bg-yellow-500 text-white rounded'>
            <i class=\"fa-solid fa-pencil\"></i>
          </button>
          <button onclick=\"deleteItem('news','" . addslashes($filename) . "')\" class='px-2 py-1 bg-red-500 text-white rounded'>
            <i class=\"fa-solid fa-trash-can\"></i>
          </button>
        </td>
      </tr>";
    }
    ?>
  </tbody>
</table>

<script>
function deleteItem(section, file) {
  if (confirm("Are you sure?")) {
    fetch(`delete.php?section=${section}&file=${file}`)
      .then(() => loadPage(section))
      .catch(err => console.error(err));
  }
}
</script>

