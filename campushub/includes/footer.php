<?php
if (!defined('APP_ROOT')) { die('Direct access not allowed'); }
?>
  </main>
  <footer class="bg-slate-900 text-slate-100 mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div>
          <h3 class="text-xl font-bold text-white mb-4">CampusHub</h3>
          <p class="text-slate-400">Your central hub for student clubs, events, and activities.</p>
        </div>
        <div>
          <h4 class="text-lg font-semibold text-white mb-4">Quick Links</h4>
          <ul class="space-y-2 text-slate-400">
            <li><a href="/campushub/pages/events.php" class="hover:text-white transition">Events</a></li>
            <li><a href="/campushub/pages/gallery.php" class="hover:text-white transition">Gallery</a></li>
            <li><a href="/campushub/pages/contact.php" class="hover:text-white transition">Contact</a></li>
          </ul>
        </div>
        <div>
          <h4 class="text-lg font-semibold text-white mb-4">Legal</h4>
          <ul class="space-y-2 text-slate-400">
            <li><a href="#" class="hover:text-white transition">Privacy Policy</a></li>
            <li><a href="#" class="hover:text-white transition">Terms of Service</a></li>
          </ul>
        </div>
      </div>
      <div class="border-t border-slate-700 mt-8 pt-8">
        <p class="text-slate-400 text-center">&copy; <?php echo date('Y'); ?> CampusHub. All rights reserved.</p>
      </div>
    </div>
  </footer>
  <script>
  // Mobile menu toggle
  document.addEventListener('DOMContentLoaded', function(){
    const menuBtn = document.querySelector('.nav-toggle');
    const navLinks = document.querySelector('.nav-links');
    if (menuBtn && navLinks) {
      menuBtn.addEventListener('click', function(){
        navLinks.classList.toggle('hidden');
        navLinks.classList.toggle('flex');
        navLinks.classList.toggle('absolute');
        navLinks.classList.toggle('top-16');
        navLinks.classList.toggle('right-0');
        navLinks.classList.toggle('bg-white');
        navLinks.classList.toggle('flex-col');
        navLinks.classList.toggle('rounded-lg');
        navLinks.classList.toggle('shadow-lg');
        navLinks.classList.toggle('p-4');
        navLinks.classList.toggle('m-4');
      });
    }
  });
  </script>
</body>
</html>
