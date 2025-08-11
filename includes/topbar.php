<div class="flex-1 flex flex-col overflow-hidden">
  <!-- Header -->
  <header class="h-16 bg-white shadow flex items-center justify-between px-4 md:px-6 border-b">
    <button class="md:hidden text-gray-600" onclick="toggleSidebar()">☰</button>
    <h1 class="text-lg font-semibold text-gray-800">Welcome, <?= htmlspecialchars($user['name']) ?>!</h1>
    <a href="/login.php" class="text-red-500 text-lg"><i class="fa-solid fa-arrow-right-from-bracket mr-1"></i> Logout</a>
  </header>

  <!-- Main Panel -->
  <main id="main-content" class="flex-1 overflow-auto p-6 bg-gray-50">
