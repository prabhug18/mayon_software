// sidebar




document.addEventListener('DOMContentLoaded', function () {
  const checkbox = document.getElementById('checkbox');
  const toggleBtn = document.getElementById('toggleBtn');
  const sidebar = document.getElementById('sidebar');
  const mainContent = document.getElementById('mainContent');

  // Handle toggle change
  checkbox.addEventListener('change', function () {
    const isMobile = window.innerWidth <= 768;

    if (isMobile) {
      if (checkbox.checked) {
        sidebar.classList.add('active');
      } else {
        sidebar.classList.remove('active');
      }
      // Prevent desktop collapse styles from conflicting
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
      // Prevent mobile styles from conflicting
      sidebar.classList.remove('active');
    }
  });

  // Auto-reset on window resize
  window.addEventListener('resize', function () {
    checkbox.checked = false;
    sidebar.classList.remove('active');
    sidebar.classList.remove('collapsed');
    mainContent.classList.remove('expanded');
  });

  // Optional: close sidebar on outside click (mobile only)
  document.addEventListener('click', function (e) {
    const isMobile = window.innerWidth <= 768;
    if (
      isMobile &&
      sidebar.classList.contains('active') &&
      !sidebar.contains(e.target) &&
      !toggleBtn.contains(e.target)
    ) {
      checkbox.checked = false;
      sidebar.classList.remove('active');
    }
  });

  // Submenu toggle logic
  document.querySelectorAll('.submenu-toggle').forEach(toggle => {
    toggle.addEventListener('click', function (e) {
      e.preventDefault();
      const submenu = this.nextElementSibling;

      // Optional: close other open submenus
      document.querySelectorAll('.submenu').forEach(menu => {
        if (menu !== submenu) {
          menu.classList.remove('open');
          const otherArrow = menu.previousElementSibling?.querySelector('.arrow');
          if (otherArrow) otherArrow.classList.remove('rotated');
        }
      });



      submenu.classList.toggle('open');

      // Arrow rotation
      const arrow = this.querySelector('.arrow');
      if (arrow) arrow.classList.toggle('rotated');
    });
  });
});






