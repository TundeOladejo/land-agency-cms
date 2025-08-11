  </main>
</div> <!-- closes flex-1 -->
</div> <!-- closes main flex -->

<script>
// Global variables
let editorInstance;
let removeImages = [];
let storageAutoSaveKey = 'autosave_<?= isset($type, $fileName) ? htmlspecialchars($type . '_' . $fileName) : 'new' ?>';

// Sidebar functions
function toggleSidebar() {
  document.getElementById('mobile-sidebar').classList.toggle('hidden');
}

function toggleDropdown(id) {
  document.getElementById(id).classList.toggle('hidden');
}

function togglePagesMenu() {
  document.getElementById('pages-submenu').classList.toggle('hidden');
}

// Page loading functions
function loadPage(url) {
  fetch(url)
    .then(response => {
      if (!response.ok) throw new Error('Network response was not ok');
      return response.text();
    })
    .then(html => {
      document.getElementById('main-content').innerHTML = html;
      // Update active nav item
      document.querySelectorAll('[data-link]').forEach(nav => nav.classList.remove('bg-blue-500', 'text-white'));
      document.querySelector(`[href="${url}"]`)?.classList.add('bg-blue-500', 'text-white');
    })
    .catch(err => {
      console.error('Error loading page:', err);
      document.getElementById('main-content').innerHTML = `<p class='text-red-500 p-4'>Error loading page: ${err.message}</p>`;
    });
}

// Editor functions
async function save(draft = true) {
  if (!editorInstance) {
    alert("Editor is still loading.");
    return;
  }

  const form = document.getElementById('editorForm');
  const formData = new FormData(form);
  formData.append('draft', draft ? '1' : '0');
  formData.append('body', editorInstance.getData());
  formData.append('remove_images', JSON.stringify(removeImages));

  try {
    const resp = await fetch('pages/save.php', {
      method: 'POST',
      body: formData
    });
    
    if (!resp.ok) throw new Error('Save failed');
    
    const result = await resp.json();
    
    if (result.success) {
      document.getElementById('lastSaved').textContent = result.updated_at || 'Just now';
      if (result.file) {
        document.querySelector('input[name="file"]').value = result.file;
      }
      return result;
    } else {
      throw new Error(result.message || 'Save failed');
    }
  } catch (err) {
    console.error('Save error:', err);
    alert(err.message);
    return { success: false, message: err.message };
  }
}

function removeImage(filename) {
  removeImages.push(filename);
  const nodes = document.querySelectorAll(`#existingImages img[data-filename="${filename}"]`);
  nodes.forEach(img => img.parentElement.style.display = 'none');
}

function saveToLocalStorage() {
  if (!editorInstance) return;
  
  const title = document.getElementById('title')?.value;
  const body = editorInstance.getData();

  if (title || body) {
    const data = {
      title,
      body,
      timestamp: Date.now(),
    };
    localStorage.setItem(storageAutoSaveKey, JSON.stringify(data));
  }
}

// Preview functions
function closePreview() {
  document.getElementById('previewModal').classList.add('hidden');
}

function escapeHtml(unsafe) {
  return unsafe
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

// Initialize editor when loaded
function initEditor() {
  const editorElement = document.querySelector('#body');
  if (editorElement && !editorInstance) {
    ClassicEditor
      .create(editorElement)
      .then(editor => {
        editorInstance = editor;
        // Load any autosaved content
        const savedData = localStorage.getItem(storageAutoSaveKey);
        if (savedData) {
          try {
            const { title, body } = JSON.parse(savedData);
            if (title) document.getElementById('title').value = title;
            if (body) editorInstance.setData(body);
          } catch (e) {
            console.warn("Failed to parse autosave data", e);
          }
        }
      })
      .catch(error => console.error(error));
  }
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
  // Initialize editor if on editor page
  initEditor();
  
  // Set up autosave
  setInterval(saveToLocalStorage, 15000);
  
  // Button event listeners
  document.getElementById('saveDraftBtn')?.addEventListener('click', () => save(true));
  document.getElementById('publishBtn')?.addEventListener('click', () => save(false));
  document.getElementById('previewBtn')?.addEventListener('click', previewContent);
  document.getElementById('confirmPublish')?.addEventListener('click', confirmPublish);
});

function previewContent() {
  const title = document.getElementById('title')?.value || 'Untitled';
  const bodyHtml = editorInstance?.getData() || '<p>No content yet</p>';
  document.getElementById('previewContent').innerHTML = `<h1>${escapeHtml(title)}</h1>` + bodyHtml;
  document.getElementById('previewModal').classList.remove('hidden');
}

async function confirmPublish() {
  const res = await save(false);
  if (res && res.success) {
    closePreview();
    alert('Published successfully!');
    // Optionally redirect or refresh content
    if (window.parent?.loadPage) {
      window.parent.loadPage(`pages/${res.type || 'news'}.php`);
    }
  }
}
</script>
</body>
</html>