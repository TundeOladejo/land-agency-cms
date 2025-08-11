<!-- Mobile Sidebar -->
<div id="mobile-sidebar" class="fixed inset-0 z-40 flex md:hidden hidden">
  <div class="fixed inset-0 bg-black bg-opacity-50" onclick="toggleSidebar()"></div>
  <div class="relative flex flex-col w-64 bg-white border-r z-50">
    <div class="flex items-center justify-between h-16 px-4 border-b">
      <img src="/assets/images/oy-logo.png" class="w-24" />
      <button onclick="toggleSidebar()">✕</button>
    </div>
    <nav class="px-4 py-4 space-y-2">
      <div class="space-y-1">
  <button onclick="togglePagesMenu()" class="w-full text-left block px-3 py-2 rounded hover:bg-gray-100 focus:outline-none">
    Pages ▾
  </button>
  <div id="pages-submenu" class="pl-4 hidden space-y-1">
    <a href="pages/news.php" class="block px-3 py-2 rounded hover:bg-gray-100">News</a>
    <a href="pages/projects.php" class="block px-3 py-2 rounded hover:bg-gray-100">Projects</a>
    <a href="pages/careers.php" class="block px-3 py-2 rounded hover:bg-gray-100">Careers</a>
  </div>
</div>

      <a href="users.php" data-link class="block px-3 py-2 rounded hover:bg-gray-100">Users</a>
    </nav>
  </div>
</div>

<!-- Desktop Sidebar -->
<aside class="hidden md:flex md:flex-col w-64 bg-white border-r border-gray-200">
  <div class="flex items-center h-16 px-4 border-b">
    <img src="/assets/images/oy-logo.png" class="w-28" />
  </div>
  <nav class="flex-1 px-4 py-6 space-y-2">
    <!-- Pages Dropdown -->
    <div>
      <button 
        onclick="toggleDropdown('pagesDropdown')" 
        class="w-full flex justify-between px-3 py-2 rounded hover:bg-gray-100"
      >
        Pages <span>▼</span>
      </button>
      <div id="pagesDropdown" class="hidden pl-4 space-y-1">
        <a href="#" onclick="loadPage('pages/news.php')" class="block px-3 py-2 rounded hover:bg-gray-100">News</a>
        <a href="#" onclick="loadPage('pages/projects.php')" class="block px-3 py-2 rounded hover:bg-gray-100">Projects</a>
        <a href="#" onclick="loadPage('pages/careers.php')" class="block px-3 py-2 rounded hover:bg-gray-100">Careers</a>
      </div>
    </div>

    <a href="#" onclick="loadPage('users.php')" class="block px-3 py-2 rounded hover:bg-gray-100">Users</a>
  </nav>
</aside>
