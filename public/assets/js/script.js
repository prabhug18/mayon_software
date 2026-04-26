// sidebar




document.addEventListener('DOMContentLoaded', function () {
  const checkbox = document.getElementById('checkbox');
  const toggleBtn = document.getElementById('toggleBtn');
  const sidebar = document.getElementById('sidebar');
  const mainContent = document.getElementById('mainContent');

  // Handle toggle change
  if (checkbox && sidebar && mainContent) {
    checkbox.addEventListener('change', function () {
      const isMobile = window.innerWidth <= 768;

      if (isMobile) {
        if (checkbox.checked) {
          sidebar.classList.add('active');
        } else {
          sidebar.classList.remove('active');
        }
        sidebar.classList.remove('collapsed');
        mainContent.classList.remove('expanded');
      } else {
        if (checkbox.checked) {
          sidebar.classList.add('collapsed');
          mainContent.classList.add('expanded');
        } else {
          sidebar.classList.remove('collapsed');
          mainContent.classList.remove('expanded');
        }
        sidebar.classList.remove('active');
      }
    });
  }

  // Auto-reset on window resize
  window.addEventListener('resize', function () {
    if (checkbox) checkbox.checked = false;
    if (sidebar) {
      sidebar.classList.remove('active');
      sidebar.classList.remove('collapsed');
    }
    if (mainContent) mainContent.classList.remove('expanded');
  });

  // Optional: close sidebar on outside click (mobile only)
  document.addEventListener('click', function (e) {
    const isMobile = window.innerWidth <= 768;
    if (
      isMobile &&
      sidebar && sidebar.classList.contains('active') &&
      !sidebar.contains(e.target) &&
      toggleBtn && !toggleBtn.contains(e.target)
    ) {
      if (checkbox) checkbox.checked = false;
      sidebar.classList.remove('active');
    }
  });

  // Submenu toggle logic
  document.querySelectorAll('.submenu-toggle').forEach(toggle => {
    toggle.addEventListener('click', function (e) {
      e.preventDefault();
      const submenu = this.nextElementSibling;
      if (!submenu) return;

      document.querySelectorAll('.submenu').forEach(menu => {
        if (menu !== submenu) {
          menu.classList.remove('open');
          const otherArrow = menu.previousElementSibling?.querySelector('.arrow');
          if (otherArrow) otherArrow.classList.remove('rotated');
        }
      });

      submenu.classList.toggle('open');
      const arrow = this.querySelector('.arrow');
      if (arrow) arrow.classList.toggle('rotated');
    });
  });

  // Password visibility toggle
  const passwordToggles = document.querySelectorAll('.toggle-password');
  passwordToggles.forEach(toggle => {
    toggle.addEventListener('click', function () {
      const group = this.closest('.input-group');
      if (!group) return;
      const input = group.querySelector('input');
      const icon = this.querySelector('i');
      if (!input || !icon) return;

      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
      } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
      }
    });
  });
});






