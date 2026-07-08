document.addEventListener('DOMContentLoaded', () => {
  const menuToggle = document.querySelector('.menu-toggle');
  const sidebar = document.getElementById('sidebar');

  if (!menuToggle || !sidebar) return;

  const closeSidebar = () => {
    sidebar.classList.remove('is-open');
    menuToggle.setAttribute('aria-expanded', 'false');
  };

  menuToggle.addEventListener('click', (event) => {
    event.stopPropagation();
    const isOpen = sidebar.classList.toggle('is-open');
    menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  });

  // モバイルでサイドバーの外側をクリックしたら閉じる
  document.addEventListener('click', (event) => {
    if (!sidebar.classList.contains('is-open')) return;
    if (sidebar.contains(event.target) || event.target === menuToggle) return;
    closeSidebar();
  });
});
