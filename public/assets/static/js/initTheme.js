function getThemeLink() {
  return document.getElementById('app-dark-css');
}

function setTheme() {
  document.documentElement.setAttribute('data-bs-theme', 'dark');
  document.documentElement.classList.add('theme-dark');
  document.documentElement.classList.remove('theme-light');

  const themeLink = getThemeLink();
  if (themeLink) {
    themeLink.disabled = false;
  }

  document.body.classList.add('theme-dark', 'dark');
  document.body.classList.remove('theme-light', 'light');

  const themeToggle = document.getElementById('toggle-dark');
  if (themeToggle) {
    themeToggle.checked = true;
    themeToggle.disabled = true;
  }

  localStorage.removeItem('theme');
}

function initThemeToggle() {
  setTheme();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initThemeToggle);
} else {
  initThemeToggle();
}
