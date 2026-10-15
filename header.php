<?php
if (!defined('APP_ROOT')) { die('Direct access not allowed'); }
require_once __DIR__ . '/functions.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>CampusHub</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#0b5cff',
            secondary: '#06b6d4',
            accent: '#ffb020'
          }
        }
      }
    }
  </script>
  <link rel="stylesheet" href="/campushub/css/style.css">
</head>
<body class="bg-slate-50 text-slate-900 font-sans">
  <header class="sticky top-0 z-50 bg-white shadow-sm border-b border-slate-200">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
      <div class="flex items-center gap-2">
        <a href="/campushub/index.php" class="text-2xl font-bold text-blue-600 hover:text-blue-700 transition">CampusHub</a>
      </div>
      <ul class="hidden md:flex items-center gap-1 nav-links">
        <li><a href="/campushub/index.php" class="px-3 py-2 text-slate-600 hover:text-blue-600 hover:bg-slate-100 rounded-lg transition font-medium text-sm">Home</a></li>
        <li><a href="/campushub/pages/events.php" class="px-3 py-2 text-slate-600 hover:text-blue-600 hover:bg-slate-100 rounded-lg transition font-medium text-sm">Events</a></li>
        <li><a href="/campushub/pages/gallery.php" class="px-3 py-2 text-slate-600 hover:text-blue-600 hover:bg-slate-100 rounded-lg transition font-medium text-sm">Gallery</a></li>
        <li><a href="/campushub/pages/media.php" class="px-3 py-2 text-slate-600 hover:text-blue-600 hover:bg-slate-100 rounded-lg transition font-medium text-sm">Media</a></li>
        <li><a href="/campushub/pages/announcements.php" class="px-3 py-2 text-slate-600 hover:text-blue-600 hover:bg-slate-100 rounded-lg transition font-medium text-sm">Announcements</a></li>
        <li><a href="/campushub/pages/contact.php" class="px-3 py-2 text-slate-600 hover:text-blue-600 hover:bg-slate-100 rounded-lg transition font-medium text-sm">Contact</a></li>
      </ul>
      <div class="flex items-center gap-2 nav-actions">
        <?php if (is_logged_in()): ?>
          <a class="px-3 py-2 text-slate-600 hover:text-blue-600 font-medium text-sm" href="/campushub/pages/profile.php">Profile</a>
          <?php if (is_admin()): ?>
            <a class="px-3 py-2 bg-slate-700 text-white hover:bg-slate-800 rounded-lg font-medium text-sm" href="/campushub/admin/dashboard.php">Admin</a>
          <?php endif; ?>
          <a class="px-3 py-2 bg-red-500 text-white hover:bg-red-600 rounded-lg font-medium text-sm transition" href="/campushub/pages/logout.php">Logout</a>
        <?php else: ?>
          <a class="px-3 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-lg font-medium text-sm transition" href="/campushub/pages/login.php">Login</a>
          <a class="px-3 py-2 bg-slate-200 text-slate-900 hover:bg-slate-300 rounded-lg font-medium text-sm transition hidden md:inline-block" href="/campushub/pages/register.php">Register</a>
          <a class="px-3 py-2 bg-amber-500 text-white hover:bg-amber-600 rounded-lg font-medium text-sm transition hidden md:inline-block" href="/campushub/admin/login.php">Admin</a>
        <?php endif; ?>
      </div>
      <button class="md:hidden text-slate-600 hover:text-slate-900 nav-toggle" aria-label="Toggle navigation">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
      </button>
    </nav>
  </header>
  <main>
