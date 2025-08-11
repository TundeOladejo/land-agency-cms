  </main>
</div> <!-- closes flex-1 -->
</div> <!-- closes main flex -->

<script>
// Global variables
let editorInstance;
let removeImages = [];
let storageAutoSaveKey = 'autosave_new_entry';

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

      // Re-bind after injecting new content
      setTimeout(() => {
        if (typeof initEditor === 'function') initEditor();
        if (typeof bindEditorEvents === 'function') bindEditorEvents();
      }, 100); // delay to ensure DOM is injected
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

  // Collect values
  const title = document.getElementById('title')?.value || '';
  const slug = document.getElementById('slug')?.value || '';
  const meta_title = document.getElementById('meta_title')?.value || '';
  const meta_description = document.getElementById('meta_description')?.value || '';
  const type = document.querySelector('input[name="type"]')?.value || 'news';
  const fileName = document.querySelector('input[name="file"]')?.value || '';


  // Append data to form
  formData.append('file', fileName); 
  formData.append('draft', draft ? '1' : '0');
  formData.append('title', title);
  formData.append('slug', slug);
  formData.append('meta_title', meta_title);
  formData.append('meta_description', meta_description);
  formData.append('body', editorInstance.getData());
  formData.append('remove_images', JSON.stringify(removeImages));
  formData.append('type', type);  // Ensure 'type' is sent

  try {
    const resp = await fetch('pages/save.php', {
      method: 'POST',
      body: formData
    });

    if (!resp.ok) throw new Error('Save failed');

    const result = await resp.json();

    if (result.success) {
      // Update last saved text
      document.getElementById('lastSaved').textContent = result.updated_at || 'Just now';

      // Set hidden input with returned file name
      if (result.file) {
        const fileInput = document.querySelector('input[name="file"]');
        if (fileInput) fileInput.value = result.file;
      }
      
      localStorage.removeItem(storageAutoSaveKey);

      // Redirect to list page (e.g., pages/news.php)
      if (window.parent?.loadPage) {
        const redirectType = result.type || type || 'news';
        window.parent.loadPage(`pages/${redirectType}.php`);
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

function deleteItem(type, file) {
  if (confirm("Are you sure?")) {
    fetch(`pages/delete.php?type=${encodeURIComponent(type)}&file=${encodeURIComponent(file)}`)
      .then(response => {
        if (!response.ok) throw new Error('Delete failed');
        return response.text();
      })
      .then(() => loadPage(`pages/${type}.php`))
      .catch(err => {
        console.error(err);
        alert('Error deleting item.');
      });
  }
}


function saveToLocalStorage() {
  if (!editorInstance) return;

  const fileInput = document.querySelector('input[name="file"]');
  if (fileInput?.value?.trim()) return; // don't autosave on existing item

  const title = document.getElementById('title')?.value;
  const slug = document.getElementById('slug')?.value;
  const body = editorInstance.getData();
  const meta_title = document.getElementById('meta_title')?.value;
  const meta_description = document.getElementById('meta_description')?.value;

  if (title || body) {
    const data = {
      title,
      slug,
      body,
      meta_title,
      meta_description,
      lastSaved: Date.now(),
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
  const fileInput = document.querySelector('input[name="file"]');
  const isEditingExisting = !!(fileInput && fileInput.value.trim());

  if (editorElement && !editorInstance) {
    ClassicEditor
      .create(editorElement)
      .then(editor => {
        editorInstance = editor;

        // Don't load autosaved data when editing an existing item
        if (isEditingExisting) {
          localStorage.removeItem(storageAutoSaveKey);
          return;
        }

        // Otherwise try to load autosave
        const savedData = localStorage.getItem(storageAutoSaveKey);
        if (savedData) {
          try {
            const { title, body } = JSON.parse(savedData);

            // Prevent restoring values with extra quotes
            const clean = str => (typeof str === 'string' ? str.replace(/^"+|"+$/g, '') : '');

            if (title) document.getElementById('title').value = clean(title);
            if (body) editorInstance.setData(clean(body));
          } catch (e) {
            console.warn("Failed to parse autosave data", e);
            localStorage.removeItem(storageAutoSaveKey);
          }
        }
      })
      .catch(error => console.error(error));
  }
}


function bindEditorEvents() {
  document.getElementById('saveDraftBtn')?.addEventListener('click', () => save(true));
  document.getElementById('publishBtn')?.addEventListener('click', () => save(false));
  document.getElementById('previewBtn')?.addEventListener('click', previewContent);
  document.getElementById('confirmPublish')?.addEventListener('click', confirmPublish);

  const imageInput = document.getElementById('images');
  if (imageInput) {
    imageInput.addEventListener('change', handleImagePreview);
  }
}

function handleImagePreview(event) {
  const previewContainer = document.getElementById('newImagePreviews');
  previewContainer.innerHTML = ''; // clear previous previews

  const files = Array.from(event.target.files);
  if (!files.length) return;

  files.forEach((file, index) => {
    if (!file.type.startsWith('image/')) return; // skip non-images

    const reader = new FileReader();
    reader.onload = function(e) {
      const wrapper = document.createElement('div');
      wrapper.className = 'relative';

      const img = document.createElement('img');
      img.src = e.target.result;
      img.className = 'h-24 w-36 object-cover rounded border';

      const removeBtn = document.createElement('button');
      removeBtn.type = 'button';
      removeBtn.innerHTML = '<i class="fa-solid fa-minus"></i>';
      removeBtn.className = 'absolute top-0 right-0 bg-red-500 text-white flex items-center justify-center rounded-full h-6 w-6 -translate-y-1/2 translate-x-1/2';
      removeBtn.addEventListener('click', (e) => {
  e.stopPropagation(); // prevent bubbling up to file input
  e.preventDefault();  // prevent triggering the file input

  // Remove preview
  wrapper.remove();

  // Remove file from input
  const input = document.getElementById('images');
  const newFiles = new DataTransfer();
  for (let i = 0; i < input.files.length; i++) {
    if (i !== index) newFiles.items.add(input.files[i]);
  }
  input.files = newFiles.files;
});

      wrapper.appendChild(img);
      wrapper.appendChild(removeBtn);
      previewContainer.appendChild(wrapper);
    };
    reader.readAsDataURL(file);
  });
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
  // Initialize editor if on editor page
  initEditor();
  bindEditorEvents();
  setInterval(saveToLocalStorage, 15000);
  
  // Button event listeners
  const imagesInput = document.getElementById('images');
  if (imagesInput) {
    imagesInput.addEventListener('change', function(event) {
        const previewContainer = document.getElementById('newImagePreviews');
  previewContainer.innerHTML = ''; // clear previous previews

  const files = event.target.files;
  console.log(files)
  if (!files.length) return;

  Array.from(files).forEach(file => {
    // if (!file.type.startsWith('image/')) return; // skip non-images

    const reader = new FileReader();
    reader.onload = function(e) {
      const img = document.createElement('img');
      img.src = e.target.result;
      img.className = 'h-24 w-36 object-cover rounded border';
      previewContainer.appendChild(img);
    };
    reader.readAsDataURL(file);
  });
    });
  }

  document.getElementById('saveDraftBtn')?.addEventListener('click', () => save(true));
  document.getElementById('publishBtn')?.addEventListener('click', () => save(false));
  document.getElementById('previewBtn')?.addEventListener('click', previewContent);
  document.getElementById('confirmPublish')?.addEventListener('click', confirmPublish);
});

function previewContent() {
  const title = document.getElementById('title')?.value || 'Untitled';
  const slug = document.getElementById('slug')?.value || '';
  const author = document.getElementById('author')?.value || 'Unknown';
  const metaTitle = document.getElementById('meta_title')?.value || '';
  const metaDescription = document.getElementById('meta_description')?.value || '';
  const bodyHtml = editorInstance?.getData() || '<p>No content yet</p>';

  const existingImages = Array.from(document.querySelectorAll('#existingImages img')).map(img => img.src);
  const newImages = Array.from(document.querySelectorAll('#newImagePreviews img')).map(img => img.src);
  const allImages = [...existingImages, ...newImages];

  const html = `
    <article class="prose prose-lg max-w-none">
      <h1 class="text-3xl font-bold mb-2">${escapeHtml(title)}</h1>
      ${slug ? `<p class="text-sm text-gray-500">Slug: <code>${escapeHtml(slug)}</code></p>` : ''}
      <p class="text-sm text-gray-600 mb-4">By <span class="font-semibold">${escapeHtml(author)}</span></p>

      ${allImages.length ? `
        <div class="flex flex-wrap gap-4 mb-6">
          ${allImages.map(src => `<img src="${src}" class="w-full md:w-1/2 rounded border shadow">`).join('')}
        </div>
      ` : ''}

      <div class="blog-body">${bodyHtml}</div>

      <div class="mt-8 border-t pt-4 text-sm text-gray-700">
        ${metaTitle ? `<p><strong>Meta Title:</strong> ${escapeHtml(metaTitle)}</p>` : ''}
        ${metaDescription ? `<p><strong>Meta Description:</strong> ${escapeHtml(metaDescription)}</p>` : ''}
      </div>
    </article>
  `;

  document.getElementById('previewContent').innerHTML = html;
  document.getElementById('previewModal').classList.remove('hidden');
  document.getElementById('previewModal').classList.add('flex');
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