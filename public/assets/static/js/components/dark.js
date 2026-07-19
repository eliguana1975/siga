
const THEME_KEY = "theme"

function toggleDarkTheme() {
  setTheme("dark")
}

/**
 * Set theme for mazer
 * @param {"dark"|"light"} theme
 * @param {boolean} persist 
 */
function setTheme(theme, persist = false) {
  document.body.classList.add("dark", "theme-dark")
  document.body.classList.remove("light", "theme-light")
  document.documentElement.setAttribute('data-bs-theme', "dark")
  document.documentElement.classList.add("theme-dark")
  document.documentElement.classList.remove("theme-light")
  localStorage.removeItem(THEME_KEY)
}

/**
 * Init theme from setTheme()
 */
function initTheme() {
  return setTheme("dark")
}

window.addEventListener('DOMContentLoaded', () => {
  const toggler = document.getElementById("toggle-dark")

  if(toggler) {
    toggler.checked = true
    toggler.disabled = true
    
    toggler.addEventListener("input", (e) => {
      setTheme("dark", true)
    })
  }

});

initTheme()
