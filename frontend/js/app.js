import '../scss/app.scss';

import $ from 'jquery';
window.$ = window.jQuery = $;

import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

import DataTable from 'datatables.net-dt';
DataTable(window, $);

import 'jquery-mask-plugin';

const THEME_STORAGE_KEY = 'audiovisual-theme';
const root = document.documentElement;

function preferredTheme() {
  const stored = localStorage.getItem(THEME_STORAGE_KEY);
  if (stored === 'light' || stored === 'dark') {
    return stored;
  }

  return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

function applyTheme(theme) {
  root.dataset.theme = theme;
  root.dataset.bsTheme = theme;
  root.classList.toggle('dark', theme === 'dark');
  root.style.colorScheme = theme;
  document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
    const isDark = theme === 'dark';
    button.setAttribute('aria-label', isDark ? 'Ativar tema claro' : 'Ativar tema escuro');
    button.setAttribute('title', isDark ? 'Tema claro' : 'Tema escuro');
    button.innerHTML = `<i class="bi ${isDark ? 'bi-sun' : 'bi-moon-stars'}"></i>`;
  });
  window.dispatchEvent(new CustomEvent('app:themechange', { detail: { theme } }));
}

function toggleTheme() {
  const next = root.dataset.theme === 'dark' ? 'light' : 'dark';
  localStorage.setItem(THEME_STORAGE_KEY, next);
  applyTheme(next);
}

window.appTheme = {
  apply: applyTheme,
  toggle: toggleTheme,
};

applyTheme(preferredTheme());

window.addEventListener('DOMContentLoaded', () => {
  applyTheme(preferredTheme());
});

document.addEventListener('click', (event) => {
  if (event.defaultPrevented) {
    return;
  }

  const button = event.target.closest('[data-theme-toggle]');
  if (!button) {
    return;
  }

  event.preventDefault();
  toggleTheme();
});

import './legacy/main';
