<div class="flex justify-between items-center mb-4">
  <h2 class="text-xl font-bold">Careers</h2>
  <button onclick="loadPage('pages/editor.php?type=careers')" class="px-4 py-2 bg-gray-800 text-white rounded">+ Create New</button>
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
    $files = glob(__DIR__ . '/../../storage/news/*.json');
    foreach ($files as $file) {
      $data = json_decode(file_get_contents($file), true);
      echo "<tr>
        <td class='p-2 border'>{$data['title']}</td>
        <td class='p-2 border'>{$data['updated_at']}</td>
        <td class='p-2 border capitalize'>" . ($data['published'] ? 'published' : 'draft') . "</td>
        <td class='p-2 border'>
          <button onclick=\"loadPage('pages/editor.php?type=careers&file=" . basename($file) . "')\" class='px-2 py-1 bg-yellow-500 text-white rounded'>
            <i class=\"fa-solid fa-pencil\"></i>
          </button>
          <button onclick=\"deleteItem('careers','" . addslashes($filename) . "')\" class='px-2 py-1 bg-red-500 text-white rounded'>
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
    fetch(`pages/delete.php?section=${section}&file=${file}`)
      .then(() => loadPage(section))
      .catch(err => console.error(err));
  }
}
</script>

