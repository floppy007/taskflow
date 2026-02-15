/**
 * TaskFlow v1.2 - App
 * Copyright (c) 2026 Florian Hesse
 * Fischer Str. 11, 16515 Oranienburg
 * https://comnic-it.de
 * Alle Rechte vorbehalten.
 */
// Data Structure
let users = [];
let projects = [];
let currentUser = null;
let currentProjectId = null;
let currentTodoView = 'active';
let currentProjectView = 'list';

const projectColors = [
  '#667eea','#3b82f6','#06b6d4','#0d9488','#10b981',
  '#84cc16','#f59e0b','#f97316','#ef4444','#ec4899',
  '#8b5cf6','#6366f1'
];

// i18n
let translations = {};
let currentLang = 'de';

// Animated counter
function animateValue(el, target, suffix = '') {
  const start = parseInt(el.textContent) || 0;
  if (start === target) { el.textContent = target + suffix; return; }
  const duration = 500;
  const startTime = performance.now();
  function step(now) {
    const progress = Math.min((now - startTime) / duration, 1);
    const ease = 1 - Math.pow(1 - progress, 3); // easeOutCubic
    el.textContent = Math.round(start + (target - start) * ease) + suffix;
    if (progress < 1) requestAnimationFrame(step);
  }
  requestAnimationFrame(step);
}

function t(key) {
  return translations[key] || key;
}

async function loadLanguage(lang) {
  try {
    const response = await fetch(`lang/${lang}.json`);
    translations = await response.json();
    currentLang = lang;
    localStorage.setItem('taskflow_lang', lang);
    document.documentElement.lang = lang;
    translatePage();
    updateLangButtons();
    // Re-render dynamic content
    renderDashboard();
    renderProjects();
    if (currentProjectId) {
      renderProjectStats();
      renderProjectTodos();
    }
  } catch (error) {
    console.error('Language load error:', error);
  }
}

function translatePage() {
  document.querySelectorAll('[data-i18n]').forEach(el => {
    const key = el.getAttribute('data-i18n');
    if (translations[key]) el.textContent = translations[key];
  });
  document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
    const key = el.getAttribute('data-i18n-placeholder');
    if (translations[key]) el.placeholder = translations[key];
  });
  document.querySelectorAll('[data-i18n-title]').forEach(el => {
    const key = el.getAttribute('data-i18n-title');
    if (translations[key]) el.title = translations[key];
  });
}

function updateLangButtons() {
  document.getElementById('langBtnDe').classList.toggle('active', currentLang === 'de');
  document.getElementById('langBtnEn').classList.toggle('active', currentLang === 'en');
  const settingsDe = document.getElementById('settingsLangDe');
  const settingsEn = document.getElementById('settingsLangEn');
  if (settingsDe) settingsDe.classList.toggle('active', currentLang === 'de');
  if (settingsEn) settingsEn.classList.toggle('active', currentLang === 'en');
}

function changeLanguage(lang) {
  loadLanguage(lang);
  if (currentUser) {
    apiCall('savePreferences', { preferences: { lang } });
  }
}

// API Helper
async function apiCall(action, data = {}) {
  try {
    const response = await fetch(`api.php?action=${action}&lang=${currentLang}`, {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify(data)
    });
    const result = await response.json();
    return result;
  } catch (error) {
    console.error('API Error:', error);
    return {success: false, message: t('data.connection_error')};
  }
}

// Version & Updates
let appVersion = '';

async function loadVersion() {
  const result = await apiCall('getVersion');
  if (result.success && result.data) {
    appVersion = result.data.version || '';
  }
  document.querySelectorAll('.copyright').forEach(el => {
    const link = el.querySelector('a');
    const linkHtml = link ? ' \u00b7 ' + link.outerHTML : '';
    el.innerHTML = 'TaskFlow v' + appVersion + ' \u00a9 2026 Florian Hesse' + linkHtml;
  });
  const versionEl = document.getElementById('currentVersion');
  if (versionEl) versionEl.textContent = 'v' + appVersion;
}

async function checkForUpdate() {
  const btn = document.getElementById('checkUpdateBtn');
  const status = document.getElementById('updateStatus');
  const installBtn = document.getElementById('installUpdateBtn');

  btn.disabled = true;
  btn.textContent = t('settings.update_checking');
  status.style.display = 'none';
  installBtn.style.display = 'none';

  const result = await apiCall('checkUpdate');
  btn.disabled = false;
  btn.textContent = t('settings.update_check');

  if (result.success) {
    status.style.display = 'block';
    if (result.data.update_available) {
      status.innerHTML = '<div style="padding:12px;background:var(--primary);color:#fff;border-radius:8px">' +
        t('settings.update_available') + ': <strong>v' + result.data.remote + '</strong>' +
        '</div>';
      installBtn.style.display = 'inline-flex';
    } else {
      status.innerHTML = '<div style="padding:12px;background:var(--bg-secondary);border-radius:8px;color:var(--text)">' +
        t('settings.update_up_to_date') +
        '</div>';
    }
  } else {
    status.style.display = 'block';
    status.innerHTML = '<div style="padding:12px;background:var(--danger);color:#fff;border-radius:8px">' +
      (result.message || t('settings.update_error')) +
      '</div>';
  }
}

async function installUpdate() {
  const installBtn = document.getElementById('installUpdateBtn');
  const status = document.getElementById('updateStatus');

  if (!await showConfirm(t('settings.update_confirm'), { icon: '🔄', title: t('settings.update_title'), danger: false })) return;

  installBtn.disabled = true;
  installBtn.textContent = t('settings.update_installing');

  const result = await apiCall('doUpdate');

  if (result.success) {
    status.innerHTML = '<div style="padding:12px;background:var(--primary);color:#fff;border-radius:8px">' +
      result.data.message + ' (v' + result.data.version + ')' +
      '</div>';
    installBtn.style.display = 'none';
    loadVersion();
    setTimeout(() => location.reload(), 2000);
  } else {
    status.innerHTML = '<div style="padding:12px;background:var(--danger);color:#fff;border-radius:8px">' +
      (result.message || t('settings.update_error')) +
      '</div>';
    installBtn.disabled = false;
    installBtn.textContent = t('settings.update_install');
  }
}

// Copyright Protection
(function() {
  const _cf = () => 'TaskFlow' + (appVersion ? ' v' + appVersion : '') + ' \u00a9 2026 Florian Hesse \u00b7 <a href="https://comnic-it.de" target="_blank" style="color:inherit;text-decoration:underline">comnic-it.de</a>';
  function _pc() {
    document.querySelectorAll('.content-footer .copyright').forEach(el => {
      if (!el.innerHTML.includes('Florian Hesse')) el.innerHTML = _cf();
    });
    document.querySelectorAll('.login-box .copyright').forEach(el => {
      if (!el.innerHTML.includes('Florian Hesse')) el.innerHTML = _cf();
    });
    document.querySelectorAll('.content-footer').forEach(el => {
      el.style.removeProperty('display');
      el.style.removeProperty('visibility');
      el.style.removeProperty('opacity');
      el.style.removeProperty('height');
      el.style.removeProperty('overflow');
    });
    if (document.getElementById('appContainer') && !document.querySelector('.content-footer')) {
      const f = document.createElement('footer');
      f.className = 'content-footer';
      f.innerHTML = '<div class="copyright">' + _cf() + '</div>';
      document.querySelector('.main-content').appendChild(f);
    }
  }
  const _ob = new MutationObserver(_pc);
  document.addEventListener('DOMContentLoaded', function() {
    _pc();
    _ob.observe(document.body, {childList: true, subtree: true, attributes: true, characterData: true});
  });
  setInterval(_pc, 3000);
})();

// Initialize
async function init() {
  let savedLang = localStorage.getItem('taskflow_lang');
  if (!savedLang) {
    const browserLang = (navigator.language || navigator.userLanguage || 'de').substring(0, 2);
    savedLang = browserLang === 'en' ? 'en' : 'de';
  }
  await loadLanguage(savedLang);
  loadTheme();
  loadDarkMode();
  applyLogo();
  loadVersion();

  // Check if user is logged in
  const sessionResult = await apiCall('getSession');
  if (sessionResult.success) {
    currentUser = sessionResult.data;
    await loadProjectsFromServer();
    showApp();
  }
}

// User Management
function showLogin() {
  document.getElementById('loginScreen').style.display = 'flex';
  document.getElementById('registerScreen').style.display = 'none';
}

function showRegister() {
  document.getElementById('loginScreen').style.display = 'none';
  document.getElementById('registerScreen').style.display = 'flex';
}

async function login() {
  const username = document.getElementById('loginUsername').value.trim();
  const password = document.getElementById('loginPassword').value;

  if (!username || !password) {
    showToast('Login', t('login.alert_fields'), 'warning');
    return;
  }

  const result = await apiCall('login', {username, password});

  if (result.success) {
    currentUser = result.data;
    await loadProjectsFromServer();
    showApp();
  } else {
    showToast('Login', result.message || t('login.alert_failed'), 'error');
  }
}

async function register() {
  const name = document.getElementById('regName').value.trim();
  const username = document.getElementById('regUsername').value.trim();
  const password = document.getElementById('regPassword').value;

  if (!name || !username || !password) {
    showToast('Register', t('register.alert_fields'), 'warning');
    return;
  }

  const result = await apiCall('register', {name, username, password});

  if (result.success) {
    showToast('Register', t('register.alert_success'), 'success');
    showLogin();
  } else {
    showToast('Register', result.message || t('register.alert_failed'), 'error');
  }
}

async function logout() {
  if (await showConfirm(t('nav.logout_confirm'), { icon: '🚪', danger: false })) {
    await apiCall('logout');
    currentUser = null;
    projects = [];
    document.getElementById('appContainer').classList.remove('active');
    document.getElementById('loginUsername').value = '';
    document.getElementById('loginPassword').value = '';
    showLogin();
  }
}

async function showApp() {
  document.getElementById('loginScreen').style.display = 'none';
  document.getElementById('registerScreen').style.display = 'none';
  document.getElementById('appContainer').classList.add('active');

  document.getElementById('userName').textContent = currentUser.name;
  document.getElementById('userAvatar').textContent = currentUser.name.charAt(0).toUpperCase();

  const isAdmin = currentUser.role === 'admin';
  const navUsersItem = document.getElementById('navUsersItem');
  if (navUsersItem) navUsersItem.style.display = isAdmin ? '' : 'none';

  const userRoleEl = document.getElementById('userRole');
  if (userRoleEl) userRoleEl.textContent = isAdmin ? t('users.role_admin') : t('users.role_user');

  // Load per-user preferences from server
  await loadUserPreferences();

  renderDashboard();
  loadNotifications();
}

async function loadUserPreferences() {
  const result = await apiCall('getPreferences');
  if (result.success && result.data) {
    const prefs = result.data;
    if (prefs.theme) {
      localStorage.setItem('taskflow_theme', prefs.theme);
      changeTheme(prefs.theme, false);
    }
    if (prefs.darkMode !== undefined) {
      const isDark = !!prefs.darkMode;
      localStorage.setItem('taskflow_dark', isDark ? 'true' : 'false');
      document.body.setAttribute('data-dark', isDark);
      updateDarkModeUI(isDark);
    }
    if (prefs.lang && prefs.lang !== currentLang) {
      await loadLanguage(prefs.lang);
    }
  }
}

// Project Management
async function loadProjectsFromServer() {
  const result = await apiCall('getProjects');
  if (result.success) {
    projects = result.data || [];
  }
}

function openFeedback() {
  const repoUrl = 'https://github.com/floppy007/taskflow/issues/new/choose';
  window.open(repoUrl, '_blank');
}

function openNewProjectModal() {
  document.getElementById('newProjectColor').value = '';
  renderColorPicker('newProjectColorPicker', 'newProjectColor', '');
  document.getElementById('newProjectModal').classList.add('active');
  document.getElementById('newProjectName').focus();
}

function renderColorPicker(containerId, inputId, activeColor) {
  const container = document.getElementById(containerId);
  container.innerHTML = projectColors.map(c =>
    `<div class="color-dot ${c === activeColor ? 'active' : ''}" style="background:${c}" onclick="selectProjectColor('${inputId}','${containerId}','${c}')"></div>`
  ).join('');
}

function selectProjectColor(inputId, containerId, color) {
  document.getElementById(inputId).value = color;
  document.querySelectorAll(`#${containerId} .color-dot`).forEach(d => d.classList.remove('active'));
  event.target.classList.add('active');
}

function closeModal(id) {
  document.getElementById(id).classList.remove('active');
}

async function createProject() {
  const name = document.getElementById('newProjectName').value.trim();
  const desc = document.getElementById('newProjectDesc').value.trim();

  if (!name) {
    showToast(t('topbar.new_project'), t('projects.name_required'), 'warning');
    return;
  }

  const color = document.getElementById('newProjectColor').value;
  const result = await apiCall('createProject', {name, desc, color});

  if (result.success) {
    await loadProjectsFromServer();
    document.getElementById('newProjectName').value = '';
    document.getElementById('newProjectDesc').value = '';
    closeModal('newProjectModal');
    renderDashboard();
    renderProjects();
  } else {
    showToast(t('topbar.new_project'), result.message || t('projects.create_error'), 'error');
  }
}

// Views
function showDashboard() {
  setActiveNav(0);
  hideAllViews();
  showViewAnimated('dashboardView');
  renderDashboard();
}

function showProjects() {
  setActiveNav(1);
  hideAllViews();
  showViewAnimated('projectsView');
  renderProjects();
  loadDeletedProjects();
}

async function showUsers() {
  if (currentUser.role !== 'admin') {
    showDashboard();
    return;
  }
  setActiveNav(2);
  hideAllViews();
  showViewAnimated('usersView');

  const result = await apiCall('getUsers');
  if (result.success) {
    users = result.data;
    renderUsers();
  }
}

function showSettings() {
  setActiveNav(3);
  hideAllViews();
  showViewAnimated('settingsView');
}

function hideAllViews() {
  ['dashboardView','projectsView','usersView','settingsView','projectDetailView'].forEach(id => {
    const el = document.getElementById(id);
    el.style.display = 'none';
    el.style.opacity = '0';
    el.style.transform = 'translateY(12px)';
  });
}

function showViewAnimated(id) {
  const el = document.getElementById(id);
  el.style.display = 'block';
  el.style.opacity = '0';
  el.style.transform = 'translateY(12px)';
  requestAnimationFrame(() => {
    el.style.transition = 'opacity .3s ease, transform .3s ease';
    el.style.opacity = '1';
    el.style.transform = 'translateY(0)';
  });
}

function setActiveNav(index) {
  document.querySelectorAll('.nav-item').forEach((item, i) => {
    item.classList.toggle('active', i === index);
  });
}

// Render Functions
function renderDashboard() {
  const totalProjects = projects.length;
  const allTodos = projects.flatMap(p => p.todos || []);
  const activeTodos = allTodos.filter(t => !t.archived);
  const openTodos = activeTodos.filter(t => !t.done).length;
  const doneTodos = activeTodos.filter(t => t.done).length;
  const progress = activeTodos.length ? Math.round((doneTodos / activeTodos.length) * 100) : 0;

  animateValue(document.getElementById('statProjects'), totalProjects);
  animateValue(document.getElementById('statOpen'), openTodos);
  animateValue(document.getElementById('statDone'), doneTodos);
  animateValue(document.getElementById('statProgress'), progress, '%');
  document.getElementById('projectCount').textContent = totalProjects;
  renderActivityFeed();

  const container = document.getElementById('dashboardProjects');

  if (projects.length === 0) {
    container.innerHTML = `
      <div class="empty-state" style="grid-column:1/-1">
        <div class="empty-icon">📁</div>
        <div class="empty-title">${escapeHtml(t('projects.empty_title'))}</div>
        <div class="empty-text">${escapeHtml(t('projects.empty_text'))}</div>
        <button class="btn btn-primary" onclick="openNewProjectModal()">${escapeHtml(t('topbar.new_project'))}</button>
      </div>
    `;
    return;
  }

  container.innerHTML = projects.map(p => {
    const todos = p.todos || [];
    const activeTodos = todos.filter(t => !t.archived);
    const total = activeTodos.length;
    const done = activeTodos.filter(t => t.done).length;
    const open = total - done;
    const pct = total ? Math.round((done / total) * 100) : 0;
    const canDelete = canDeleteProject(p);
    const canManage = canDeleteProject(p);

    return `
      <div class="project-card" onclick="openProjectDetail(${p.id})" ${p.color ? `style="border-top:3px solid ${p.color}"` : ''}>
        <div class="project-icon" ${p.color ? `style="background:${p.color}20;color:${p.color}"` : ''}>📁</div>
        ${canManage || canDelete ? `<div class="project-card-actions">
          ${canManage ? `<button class="project-action-btn btn-members" onclick="event.stopPropagation();openManageMembersModal(${p.id})" title="${escapeHtml(t('members.title'))}">👥</button>` : ''}
          ${canDelete ? `<button class="project-action-btn" onclick="event.stopPropagation();deleteProject(${p.id})" title="${escapeHtml(t('project_detail.delete'))}" style="border-color:var(--border);" onmouseover="this.style.background='var(--danger)';this.style.color='#fff';this.style.borderColor='var(--danger)'" onmouseout="this.style.background='transparent';this.style.color='var(--text-muted)';this.style.borderColor='var(--border)'">🗑️</button>` : ''}
        </div>` : ''}
        <div class="project-header">
          <div>
            <div class="project-title">${escapeHtml(p.name)}</div>
            <div class="project-desc">${escapeHtml(p.desc || t('projects.no_desc'))}</div>
          </div>
        </div>
        <div class="project-progress">
          <div class="progress-label">
            <span>${escapeHtml(t('projects.progress'))}</span>
            <span>${pct}%</span>
          </div>
          <div class="progress-bar-bg">
            <div class="progress-bar" style="width:${pct}%"></div>
          </div>
        </div>
        <div class="project-stats">
          <div class="project-stat">
            <span class="project-stat-value">${open}</span>
            <span class="project-stat-label">${escapeHtml(t('projects.open'))}</span>
          </div>
          <div class="project-stat">
            <span class="project-stat-value">${done}</span>
            <span class="project-stat-label">${escapeHtml(t('projects.done'))}</span>
          </div>
          <div class="project-stat">
            <span class="project-stat-value">${total}</span>
            <span class="project-stat-label">${escapeHtml(t('projects.total'))}</span>
          </div>
        </div>
      </div>
    `;
  }).join('');
}

function canDeleteProject(p) {
  if (currentUser.role === 'admin') return true;
  if (p.members) {
    return p.members.some(m => m.userId === currentUser.id && m.role === 'owner');
  }
  return p.createdBy === currentUser.id;
}

function renderProjects() {
  const container = document.getElementById('projectsList');

  if (projects.length === 0) {
    container.innerHTML = `
      <div class="empty-state" style="grid-column:1/-1">
        <div class="empty-icon">📁</div>
        <div class="empty-title">${escapeHtml(t('projects.empty_title'))}</div>
        <div class="empty-text">${escapeHtml(t('projects.empty_text'))}</div>
        <button class="btn btn-primary" onclick="openNewProjectModal()">${escapeHtml(t('topbar.new_project'))}</button>
      </div>
    `;
    return;
  }

  container.innerHTML = projects.map(p => {
    const todos = p.todos || [];
    const activeTodos = todos.filter(t => !t.archived);
    const total = activeTodos.length;
    const done = activeTodos.filter(t => t.done).length;
    const open = total - done;
    const pct = total ? Math.round((done / total) * 100) : 0;
    const canDelete = canDeleteProject(p);
    const canManage = canDeleteProject(p);

    return `
      <div class="project-card" onclick="openProjectDetail(${p.id})" ${p.color ? `style="border-top:3px solid ${p.color}"` : ''}>
        <div class="project-icon">📁</div>
        ${canManage || canDelete ? `<div class="project-card-actions">
          ${canManage ? `<button class="project-action-btn btn-members" onclick="event.stopPropagation();openManageMembersModal(${p.id})" title="${escapeHtml(t('members.title'))}">👥</button>` : ''}
          ${canDelete ? `<button class="project-action-btn" onclick="event.stopPropagation();deleteProject(${p.id})" title="${escapeHtml(t('project_detail.delete'))}" style="border-color:var(--border);" onmouseover="this.style.background='var(--danger)';this.style.color='#fff';this.style.borderColor='var(--danger)'" onmouseout="this.style.background='transparent';this.style.color='var(--text-muted)';this.style.borderColor='var(--border)'">🗑️</button>` : ''}
        </div>` : ''}
        <div class="project-title">${escapeHtml(p.name)}</div>
        <div class="project-desc">${escapeHtml(p.desc || t('projects.no_desc'))}</div>
        <div class="project-progress">
          <div class="progress-label">
            <span>${escapeHtml(t('projects.progress'))}</span>
            <span>${pct}%</span>
          </div>
          <div class="progress-bar-bg">
            <div class="progress-bar" style="width:${pct}%"></div>
          </div>
        </div>
        <div class="project-stats">
          <div class="project-stat">
            <span class="project-stat-value">${open}</span>
            <span class="project-stat-label">${escapeHtml(t('projects.open'))}</span>
          </div>
          <div class="project-stat">
            <span class="project-stat-value">${done}</span>
            <span class="project-stat-label">${escapeHtml(t('projects.done'))}</span>
          </div>
          <div class="project-stat">
            <span class="project-stat-value">${total}</span>
            <span class="project-stat-label">${escapeHtml(t('projects.total'))}</span>
          </div>
        </div>
      </div>
    `;
  }).join('');
}

function renderUsers() {
  const container = document.getElementById('usersList');
  container.innerHTML = users.map(u => {
    const role = u.role || 'admin';
    const isAdmin = role === 'admin';
    const badgeClass = isAdmin ? 'badge-admin' : 'badge-user';
    const badgeLabel = isAdmin ? t('users.role_admin') : t('users.role_user');
    const badgeIcon = isAdmin ? '🛡️' : '👤';
    const newRole = isAdmin ? 'user' : 'admin';

    return `
    <div style="display:flex;align-items:center;gap:16px;padding:16px;border-bottom:1px solid var(--border)">
      <div class="user-avatar">${u.name.charAt(0).toUpperCase()}</div>
      <div style="flex:1">
        <div style="font-weight:600;margin-bottom:4px">${escapeHtml(u.name)}</div>
        <div style="font-size:14px;color:var(--text-muted)">@${escapeHtml(u.username)}</div>
      </div>
      <span class="${badgeClass}">${badgeIcon} ${escapeHtml(badgeLabel)}</span>
      <div style="font-size:12px;color:var(--text-muted)">
        ${escapeHtml(t('users.created'))} ${new Date(u.createdAt).toLocaleDateString(currentLang === 'de' ? 'de-DE' : 'en-US')}
      </div>
      ${u.id !== currentUser.id ? `<button class="btn btn-secondary btn-sm" onclick="toggleUserRole(${u.id},'${newRole}')" title="${escapeHtml(t('users.change_role'))}">${escapeHtml(t('users.change_role'))}</button>` : ''}
      ${u.id !== currentUser.id ? `<button class="btn btn-danger btn-sm" onclick="deleteUser(${u.id})" title="${escapeHtml(t('users.delete_btn'))}">🗑️</button>` : ''}
    </div>
  `;
  }).join('');
}

async function deleteProject(id) {
  if (await showConfirm(t('projects.delete_confirm'), { icon: '🗑️', title: t('project_detail.delete') })) {
    const result = await apiCall('deleteProject', {id});
    if (result.success) {
      await loadProjectsFromServer();
      renderDashboard();
      renderProjects();
    } else {
      showToast(t('project_detail.delete'), result.message || t('projects.delete_error'), 'error');
    }
  }
}

// Project Detail View Functions
function openProjectDetail(projectId) {
  currentProjectId = projectId;
  const project = projects.find(p => p.id === projectId);
  if (!project) return;

  hideAllViews();
  showViewAnimated('projectDetailView');

  document.getElementById('projectDetailTitle').textContent = project.name;
  document.getElementById('projectDetailDesc').textContent = project.desc || t('projects.no_desc');

  currentTodoView = 'active';
  currentProjectView = 'kanban';
  switchTodoView('active');

  renderProjectStats();
  switchProjectView('kanban');
  renderMembers(project);
}

function backToProjects() {
  currentProjectId = null;
  showProjects();
}

function editProject() {
  const project = projects.find(p => p.id === currentProjectId);
  if (!project) return;

  document.getElementById('editProjectName').value = project.name;
  document.getElementById('editProjectDesc').value = project.desc || '';
  document.getElementById('editProjectColor').value = project.color || '';
  renderColorPicker('editProjectColorPicker', 'editProjectColor', project.color || '');

  document.getElementById('editProjectModal').classList.add('active');
  document.getElementById('editProjectName').focus();
}

async function saveEditProject() {
  const name = document.getElementById('editProjectName').value.trim();
  if (!name) { showToast(t('modal.edit_project'), t('projects.name_required'), 'warning'); return; }

  const result = await apiCall('updateProject', {
    id: currentProjectId,
    name,
    desc: document.getElementById('editProjectDesc').value.trim(),
    color: document.getElementById('editProjectColor').value
  });

  if (result.success) {
    closeModal('editProjectModal');
    await loadProjectsFromServer();
    const updated = projects.find(p => p.id === currentProjectId);
    document.getElementById('projectDetailTitle').textContent = updated.name;
    document.getElementById('projectDetailDesc').textContent = updated.desc || t('projects.no_desc');
    renderDashboard();
    renderProjects();
  } else {
    showToast(t('modal.edit_project'), result.message || t('projects.edit_error'), 'error');
  }
}

async function deleteCurrentProject() {
  if (!await showConfirm(t('projects.delete_confirm'), { icon: '🗑️', title: t('project_detail.delete') })) return;

  const result = await apiCall('deleteProject', {id: currentProjectId});
  if (result.success) {
    await loadProjectsFromServer();
    backToProjects();
    renderDashboard();
  } else {
    showToast(t('project_detail.delete'), result.message || t('projects.delete_error'), 'error');
  }
}

function renderProjectStats() {
  const project = projects.find(p => p.id === currentProjectId);
  if (!project) return;

  const activeTodos = (project.todos || []).filter(t => !t.archived);
  const total = activeTodos.length;
  const done = activeTodos.filter(t => t.done).length;
  const open = total - done;
  const progress = total ? Math.round((done / total) * 100) : 0;

  animateValue(document.getElementById('projectStatTotal'), total);
  animateValue(document.getElementById('projectStatOpen'), open);
  animateValue(document.getElementById('projectStatDone'), done);
  animateValue(document.getElementById('projectStatProgress'), progress, '%');
}

function toggleNewTodoForm() {
  const form = document.getElementById('newTodoFormCard');
  const btn = document.getElementById('newTodoToggleBtn');
  const isVisible = form.style.display !== 'none';
  if (isVisible) {
    form.style.opacity = '0';
    form.style.transform = 'translateY(-10px)';
    setTimeout(() => { form.style.display = 'none'; btn.style.display = 'inline-flex'; }, 200);
  } else {
    form.style.display = 'block';
    form.style.opacity = '0';
    form.style.transform = 'translateY(-10px)';
    requestAnimationFrame(() => {
      form.style.transition = 'opacity .2s, transform .2s';
      form.style.opacity = '1';
      form.style.transform = 'translateY(0)';
    });
    btn.style.display = 'none';
    document.getElementById('newTodoText').focus();
  }
}

async function addTodoToProject() {
  const project = projects.find(p => p.id === currentProjectId);
  if (!project) return;

  const text = document.getElementById('newTodoText').value.trim();
  if (!text) {
    showToast(t('todos.new_title'), t('todos.alert_required'), 'warning');
    return;
  }

  const category = document.getElementById('newTodoCategory').value;
  const priority = document.getElementById('newTodoPriority').value;
  const note = document.getElementById('newTodoNote').value.trim();
  const dueDate = document.getElementById('newTodoDueDate').value || null;

  const result = await apiCall('addTodo', {
    projectId: currentProjectId,
    text,
    category,
    priority,
    note,
    dueDate
  });

  if (result.success) {
    await loadProjectsFromServer();
    document.getElementById('newTodoText').value = '';
    document.getElementById('newTodoNote').value = '';
    document.getElementById('newTodoDueDate').value = '';
    toggleNewTodoForm();
    renderProjectStats();
    renderProjectTodos();
    renderDashboard();
  } else {
    showToast(t('todos.new_title'), result.message || t('todos.alert_add_error'), 'error');
  }
}

function switchTodoView(view) {
  currentTodoView = view;

  const activeBtn = document.getElementById('viewActiveBtn');
  const archiveBtn = document.getElementById('viewArchiveBtn');

  if (view === 'active') {
    activeBtn.style.background = 'var(--card)';
    activeBtn.style.boxShadow = 'var(--shadow-sm)';
    activeBtn.classList.remove('btn-ghost');
    archiveBtn.style.background = 'transparent';
    archiveBtn.style.boxShadow = 'none';
    archiveBtn.classList.add('btn-ghost');
  } else {
    archiveBtn.style.background = 'var(--card)';
    archiveBtn.style.boxShadow = 'var(--shadow-sm)';
    archiveBtn.classList.remove('btn-ghost');
    activeBtn.style.background = 'transparent';
    activeBtn.style.boxShadow = 'none';
    activeBtn.classList.add('btn-ghost');
  }

  renderProjectTodos();
}

function renderProjectTodos() {
  const project = projects.find(p => p.id === currentProjectId);
  if (!project) return;

  const container = document.getElementById('todoListContainer');
  const filterCat = document.getElementById('filterCategory').value;
  const filterStat = document.getElementById('filterStatus').value;

  let todos = (project.todos || []).filter(td => {
    if (currentTodoView === 'active' && td.archived) return false;
    if (currentTodoView === 'archive' && !td.archived) return false;
    if (filterCat !== 'all' && td.category !== filterCat) return false;
    if (filterStat === 'open' && td.done) return false;
    if (filterStat === 'done' && !td.done) return false;
    return true;
  });

  if (todos.length === 0) {
    container.innerHTML = `
      <div class="empty-todos">
        <div class="empty-todos-icon">${currentTodoView === 'archive' ? '📦' : '✓'}</div>
        <p>${escapeHtml(currentTodoView === 'archive' ? t('todos.empty_archive') : t('todos.empty_active'))}</p>
      </div>
    `;
    return;
  }

  const grouped = {};
  todos.forEach(todo => {
    const cat = todo.category || 'Other';
    if (!grouped[cat]) grouped[cat] = [];
    grouped[cat].push(todo);
  });

  const categoryIcons = {
    Development: '💻',
    Design: '🎨',
    Content: '📝',
    Testing: '🧪',
    Meeting: '👥',
    Other: '📌'
  };

  const priorityLabels = {
    low: t('todos.priority_low'),
    medium: t('todos.priority_medium'),
    high: t('todos.priority_high')
  };

  let html = '';

  Object.keys(grouped).forEach(category => {
    html += `
      <div class="category-section">
        <div class="category-section-title">
          <span class="category-icon">${categoryIcons[category] || '📌'}</span>
          ${category}
        </div>
    `;

    grouped[category].forEach(todo => {
      html += `
        <div class="todo-item ${todo.done ? 'done' : ''}" draggable="true" data-todo-id="${todo.id}" ondragstart="todoDragStart(event)" ondragover="todoDragOver(event)" ondrop="todoDrop(event)" ondragend="todoDragEnd(event)">
          <div class="todo-drag-handle" title="Drag to reorder">⋮⋮</div>
          <div class="todo-checkbox ${todo.done ? 'checked' : ''}" onclick="toggleTodo(${todo.id})">
            ${todo.done ? '✓' : ''}
          </div>
          <div class="todo-content">
            <div class="todo-header">
              <div class="todo-text">${escapeHtml(todo.text)}</div>
            </div>
            <div class="todo-badges">
              <span class="todo-badge badge-category">${categoryIcons[todo.category] || '📌'} ${todo.category}</span>
              <span class="todo-badge badge-priority ${todo.priority}">${priorityLabels[todo.priority]}</span>
              ${todo.dueDate ? (() => { const di = getDueDateInfo(todo.dueDate); return `<span class="todo-badge badge-due ${di.class}">📅 ${di.label}</span>`; })() : ''}
            </div>
            <div class="todo-meta" style="font-size:12px;color:var(--text-muted);margin-top:4px">
              ${todo.createdBy ? `<span>${escapeHtml(t('todos.created_by'))} @${escapeHtml(todo.createdBy)}</span>` : ''}
              ${todo.done && todo.closedBy ? `<span style="margin-left:8px">${escapeHtml(t('todos.closed_by'))} @${escapeHtml(todo.closedBy)}${todo.closedAt ? ' (' + new Date(todo.closedAt).toLocaleDateString(currentLang === 'de' ? 'de-DE' : 'en-US') + ')' : ''}</span>` : ''}
            </div>
            ${todo.note ? `<div class="todo-note">${escapeHtml(todo.note)}</div>` : ''}
            <div class="todo-actions">
              <button class="todo-action-btn" onclick="editTodo(${todo.id})">✏️ ${escapeHtml(t('todos.edit_btn'))}</button>
              <button class="todo-action-btn" onclick="archiveTodo(${todo.id})">${todo.archived ? '📂 ' + escapeHtml(t('todos.restore_btn')) : '📦 ' + escapeHtml(t('todos.archive_btn'))}</button>
              <button class="todo-action-btn danger" onclick="deleteTodo(${todo.id})">🗑️ ${escapeHtml(t('todos.delete_btn'))}</button>
            </div>
          </div>
        </div>
      `;
    });

    html += '</div>';
  });

  container.innerHTML = html;
}

async function toggleTodo(todoId) {
  const project = projects.find(p => p.id === currentProjectId);
  if (!project) return;

  const todo = (project.todos || []).find(t => t.id === todoId);
  if (!todo) return;

  const result = await apiCall('updateTodo', {
    projectId: currentProjectId,
    todoId,
    updates: {done: !todo.done}
  });

  if (result.success) {
    await loadProjectsFromServer();
    renderProjectStats();
    renderProjectTodos();
    renderDashboard();
  }
}

function editTodo(todoId) {
  const project = projects.find(p => p.id === currentProjectId);
  if (!project) return;

  const todo = (project.todos || []).find(t => t.id === todoId);
  if (!todo) return;

  document.getElementById('editTodoId').value = todoId;
  document.getElementById('editTodoText').value = todo.text;
  document.getElementById('editTodoCategory').value = todo.category || 'Other';
  document.getElementById('editTodoPriority').value = todo.priority || 'medium';
  document.getElementById('editTodoDueDate').value = todo.dueDate || '';
  document.getElementById('editTodoNote').value = todo.note || '';

  document.getElementById('editTodoModal').classList.add('active');
  document.getElementById('editTodoText').focus();
}

async function saveEditTodo() {
  const todoId = parseInt(document.getElementById('editTodoId').value);
  const text = document.getElementById('editTodoText').value.trim();
  if (!text) { showToast(t('todos.edit_title'), t('todos.alert_required'), 'warning'); return; }

  const result = await apiCall('updateTodo', {
    projectId: currentProjectId,
    todoId,
    updates: {
      text,
      category: document.getElementById('editTodoCategory').value,
      priority: document.getElementById('editTodoPriority').value,
      dueDate: document.getElementById('editTodoDueDate').value || null,
      note: document.getElementById('editTodoNote').value.trim()
    }
  });

  if (result.success) {
    closeModal('editTodoModal');
    await loadProjectsFromServer();
    renderProjectTodos();
    if (currentProjectView === 'kanban') renderKanbanBoard();
    renderDashboard();
  }
}

async function archiveTodo(todoId) {
  const project = projects.find(p => p.id === currentProjectId);
  if (!project) return;

  const todo = (project.todos || []).find(t => t.id === todoId);
  if (!todo) return;

  const result = await apiCall('updateTodo', {
    projectId: currentProjectId,
    todoId,
    updates: {archived: !todo.archived}
  });

  if (result.success) {
    await loadProjectsFromServer();
    renderProjectStats();
    renderProjectTodos();
    renderDashboard();
  }
}

async function deleteTodo(todoId) {
  if (!await showConfirm(t('todos.delete_confirm'), { icon: '🗑️', title: t('todos.delete_btn') })) return;

  const result = await apiCall('deleteTodo', {
    projectId: currentProjectId,
    todoId
  });

  if (result.success) {
    await loadProjectsFromServer();
    renderProjectStats();
    renderProjectTodos();
    renderDashboard();
  }
}

// Data Management
async function exportData() {
  const result = await apiCall('exportData');

  if (result.success) {
    const data = JSON.stringify(result.data, null, 2);
    const blob = new Blob([data], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `taskflow_backup_${new Date().toISOString().split('T')[0]}.json`;
    a.click();
    URL.revokeObjectURL(url);

    showToast(t('settings.backup_title'), t('data.export_success'), 'success');
  } else {
    showToast(t('settings.backup_title'), result.message || t('data.export_failed'), 'error');
  }
}

async function importData(event) {
  const file = event.target.files[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = async (e) => {
    try {
      const data = JSON.parse(e.target.result);

      if (!data.users || !data.projects) {
        showToast(t('settings.backup_title'), t('data.import_invalid'), 'error');
        return;
      }

      if (await showConfirm(t('data.import_confirm'), { icon: '⚠️', title: t('settings.backup_title') })) {
        const result = await apiCall('importData', data);
        if (result.success) {
          await loadProjectsFromServer();
          renderDashboard();
          showToast(t('settings.backup_title'), t('data.import_success'), 'success');
        } else {
          showToast(t('settings.backup_title'), result.message || t('data.import_failed'), 'error');
        }
      }
    } catch (error) {
      showToast(t('settings.backup_title'), t('data.import_read_error') + error.message, 'error');
    }
  };
  reader.readAsText(file);
}

// Password Change
async function changePassword() {
  const currentPw = document.getElementById('currentPassword').value;
  const newPw = document.getElementById('newPassword').value;
  const confirmPw = document.getElementById('confirmPassword').value;

  if (!currentPw || !newPw || !confirmPw) {
    showToast(t('settings.password_title'), t('settings.password_fields_required'), 'warning');
    return;
  }

  if (newPw !== confirmPw) {
    showToast(t('settings.password_title'), t('settings.password_mismatch'), 'warning');
    return;
  }

  const result = await apiCall('changePassword', {currentPassword: currentPw, newPassword: newPw});
  if (result.success) {
    showToast(t('settings.password_title'), t('settings.password_success'), 'success');
    document.getElementById('currentPassword').value = '';
    document.getElementById('newPassword').value = '';
    document.getElementById('confirmPassword').value = '';
  } else {
    showToast(t('settings.password_title'), result.message || t('settings.password_error'), 'error');
  }
}

// User Creation
function openCreateUserForm() {
  document.getElementById('createUserCard').style.display = 'block';
}

function closeCreateUserForm() {
  document.getElementById('createUserCard').style.display = 'none';
  document.getElementById('newUserName').value = '';
  document.getElementById('newUserUsername').value = '';
  document.getElementById('newUserPassword').value = '';
  document.getElementById('newUserRole').value = 'user';
}

async function createUser() {
  const name = document.getElementById('newUserName').value;
  const username = document.getElementById('newUserUsername').value;
  const password = document.getElementById('newUserPassword').value;
  const role = document.getElementById('newUserRole').value;

  if (!name || !username || !password) {
    showToast(t('users.create_title'), t('register.alert_fields'), 'warning');
    return;
  }

  const result = await apiCall('createUser', {name, username, password, role});
  if (result.success) {
    closeCreateUserForm();
    showToast(t('users.create_title'), result.message, 'success');
    const usersResult = await apiCall('getUsers');
    if (usersResult.success) {
      users = usersResult.data;
      renderUsers();
    }
  } else {
    showToast(t('users.create_title'), result.message || t('users.create_error'), 'error');
  }
}

// User Deletion
async function deleteUser(id) {
  if (!await showConfirm(t('users.delete_confirm'), { icon: '🗑️', title: t('users.delete_btn') })) return;

  const result = await apiCall('deleteUser', {id});
  if (result.success) {
    showToast(t('users.title'), result.message, 'success');
    const usersResult = await apiCall('getUsers');
    if (usersResult.success) {
      users = usersResult.data;
      renderUsers();
    }
  } else {
    showToast(t('users.title'), result.message || t('users.delete_error'), 'error');
  }
}

// Toggle User Role
async function toggleUserRole(id, newRole) {
  const result = await apiCall('updateUserRole', {id, role: newRole});
  if (result.success) {
    const usersResult = await apiCall('getUsers');
    if (usersResult.success) {
      users = usersResult.data;
      renderUsers();
    }
  } else {
    showToast(t('users.title'), result.message || t('users.no_permission'), 'error');
  }
}

// Logo Management
function uploadLogo(event) {
  const file = event.target.files[0];
  if (!file) return;

  if (!file.type.match(/^image\/(png|jpeg|svg\+xml)$/)) {
    showToast(t('settings.logo_title'), t('settings.logo_invalid'), 'warning');
    event.target.value = '';
    return;
  }

  if (file.size > 2 * 1024 * 1024) {
    showToast(t('settings.logo_title'), t('settings.logo_too_big'), 'warning');
    event.target.value = '';
    return;
  }

  const reader = new FileReader();
  reader.onload = (e) => {
    localStorage.setItem('taskflow_logo', e.target.result);
    applyLogo();
  };
  reader.readAsDataURL(file);
  event.target.value = '';
}

function removeLogo() {
  localStorage.removeItem('taskflow_logo');
  applyLogo();
}

function applyLogo() {
  const logoDataUrl = localStorage.getItem('taskflow_logo');
  const loginLogo = document.getElementById('loginLogo');
  const registerLogo = document.getElementById('registerLogo');
  const sidebarLogo = document.getElementById('sidebarLogo');
  const logoPreview = document.getElementById('logoPreview');
  const logoPlaceholder = document.getElementById('logoPlaceholder');

  const logoSrc = logoDataUrl || 'logo.png';

  if (loginLogo) loginLogo.innerHTML = `<img src="${logoSrc}" alt="TaskFlow" class="login-logo-img">`;
  if (registerLogo) registerLogo.innerHTML = `<img src="${logoSrc}" alt="TaskFlow" class="login-logo-img">`;
  if (sidebarLogo) sidebarLogo.innerHTML = `<img src="${logoSrc}" alt="TaskFlow" class="sidebar-logo-img">`;

  if (logoPreview) {
    logoPreview.src = logoDataUrl || '';
    logoPreview.style.display = logoDataUrl ? 'block' : 'none';
  }
  if (logoPlaceholder) logoPlaceholder.style.display = logoDataUrl ? 'none' : 'block';
}

// Theme Management
function changeTheme(theme, save = true) {
  document.body.setAttribute('data-theme', theme);
  localStorage.setItem('taskflow_theme', theme);

  document.querySelectorAll('.theme-option').forEach(opt => {
    opt.classList.remove('active');
  });
  const opt = document.querySelector(`.theme-option[data-theme="${theme}"]`);
  if (opt) opt.classList.add('active');

  if (save && currentUser) {
    apiCall('savePreferences', { preferences: { theme } });
  }
}

function loadTheme() {
  const savedTheme = localStorage.getItem('taskflow_theme') || 'purple';
  changeTheme(savedTheme, false);
}

// Due Date Helper
function getDueDateInfo(dueDate) {
  if (!dueDate) return null;
  const now = new Date(); now.setHours(0,0,0,0);
  const due = new Date(dueDate); due.setHours(0,0,0,0);
  const diff = Math.ceil((due - now) / (1000*60*60*24));
  if (diff < 0) return { class: 'overdue', label: t('todos.due_overdue') };
  if (diff === 0) return { class: 'today', label: t('todos.due_today') };
  if (diff <= 3) return { class: 'upcoming', label: due.toLocaleDateString(currentLang === 'de' ? 'de-DE' : 'en-US') };
  return { class: 'later', label: due.toLocaleDateString(currentLang === 'de' ? 'de-DE' : 'en-US') };
}

// Dark Mode (auto-detect system, user can override via topbar)
function toggleDarkMode() {
  const isDark = document.body.getAttribute('data-dark') === 'true';
  const newVal = !isDark;
  document.body.setAttribute('data-dark', newVal);
  localStorage.setItem('taskflow_dark', newVal ? 'true' : 'false');
  updateDarkModeUI(newVal);

  if (currentUser) {
    apiCall('savePreferences', { preferences: { darkMode: newVal } });
  }
}

function loadDarkMode() {
  const saved = localStorage.getItem('taskflow_dark');
  let isDark;
  if (saved !== null) {
    isDark = saved === 'true';
  } else {
    isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  }
  document.body.setAttribute('data-dark', isDark);
  updateDarkModeUI(isDark);

  // Listen for system theme changes (only if user hasn't overridden)
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
    if (localStorage.getItem('taskflow_dark') === null) {
      document.body.setAttribute('data-dark', e.matches);
      updateDarkModeUI(e.matches);
    }
  });
}

function updateDarkModeUI(isDark) {
  const toggle = document.getElementById('darkModeToggle');
  if (toggle) toggle.textContent = isDark ? '☀️' : '🌙';
}

// Kanban Board
function switchProjectView(view) {
  currentProjectView = view;
  const listBtn = document.getElementById('listViewBtn');
  const kanbanBtn = document.getElementById('kanbanViewBtn');
  const kanbanContainer = document.getElementById('kanbanContainer');
  const newTodoBtn = document.getElementById('newTodoToggleBtn');
  const newTodoForm = document.getElementById('newTodoFormCard');
  const todoCard = document.getElementById('todoListContainer')?.closest('.card');

  if (view === 'list') {
    listBtn.style.background = 'var(--card)'; listBtn.style.boxShadow = 'var(--shadow-sm)'; listBtn.classList.remove('btn-ghost');
    kanbanBtn.style.background = 'transparent'; kanbanBtn.style.boxShadow = 'none'; kanbanBtn.classList.add('btn-ghost');
    if (todoCard) todoCard.style.display = '';
    if (newTodoBtn) newTodoBtn.style.display = '';
    kanbanContainer.style.display = 'none';
    renderProjectTodos();
  } else {
    kanbanBtn.style.background = 'var(--card)'; kanbanBtn.style.boxShadow = 'var(--shadow-sm)'; kanbanBtn.classList.remove('btn-ghost');
    listBtn.style.background = 'transparent'; listBtn.style.boxShadow = 'none'; listBtn.classList.add('btn-ghost');
    if (newTodoForm) newTodoForm.style.display = 'none';
    if (newTodoBtn) newTodoBtn.style.display = 'none';
    if (todoCard) todoCard.style.display = 'none';
    kanbanContainer.style.display = 'block';
    renderKanbanBoard();
  }
}

function renderKanbanBoard() {
  const project = projects.find(p => p.id === currentProjectId);
  if (!project) return;

  const container = document.getElementById('kanbanContainer');
  const todos = (project.todos || []).filter(td => !td.archived);

  const columns = {
    todo: { title: t('kanban.col_todo'), icon: '📋', items: [] },
    inprogress: { title: t('kanban.col_inprogress'), icon: '🔄', items: [] },
    done: { title: t('kanban.col_done'), icon: '✅', items: [] }
  };

  todos.forEach(td => {
    const status = td.status || (td.done ? 'done' : 'todo');
    if (columns[status]) columns[status].items.push(td);
    else columns.todo.items.push(td);
  });

  const categoryIcons = { Development:'💻', Design:'🎨', Content:'📝', Testing:'🧪', Meeting:'👥', Other:'📌' };
  const priorityLabels = { low: t('todos.priority_low'), medium: t('todos.priority_medium'), high: t('todos.priority_high') };

  container.innerHTML = '<div class="kanban-board">' +
    Object.entries(columns).map(([status, col]) => `
      <div class="kanban-column" data-status="${status}"
           ondragover="event.preventDefault();this.classList.add('drag-over')"
           ondragleave="this.classList.remove('drag-over')"
           ondrop="dropKanbanCard(event,'${status}');this.classList.remove('drag-over')">
        <div class="kanban-column-header">
          <span>${col.icon} ${col.title}</span>
          <span class="kanban-column-count">${col.items.length}</span>
        </div>
        ${col.items.map(td => `
          <div class="kanban-card" draggable="true" data-todo-id="${td.id}"
               ondragstart="event.dataTransfer.setData('text/plain','${td.id}');this.classList.add('dragging')"
               ondragend="this.classList.remove('dragging')">
            <div class="kanban-card-title">${escapeHtml(td.text)}</div>
            <div class="kanban-card-badges">
              <span class="todo-badge badge-category">${categoryIcons[td.category]||'📌'} ${td.category}</span>
              <span class="todo-badge badge-priority ${td.priority}">${priorityLabels[td.priority]}</span>
              ${td.dueDate ? (() => { const di = getDueDateInfo(td.dueDate); return `<span class="todo-badge badge-due ${di.class}">📅 ${di.label}</span>`; })() : ''}
            </div>
          </div>
        `).join('')}
      </div>
    `).join('') + '</div>';
}

async function dropKanbanCard(event, newStatus) {
  event.preventDefault();
  const todoId = parseInt(event.dataTransfer.getData('text/plain'));
  const updates = { status: newStatus };
  if (newStatus === 'done') updates.done = true;
  else updates.done = false;

  const result = await apiCall('updateTodo', {
    projectId: currentProjectId,
    todoId,
    updates
  });

  if (result.success) {
    await loadProjectsFromServer();
    renderKanbanBoard();
    renderProjectStats();
    renderDashboard();
  }
}

// Global Search
let searchTimeout = null;

function onSearchInput() {
  clearTimeout(searchTimeout);
  const query = document.getElementById('globalSearchInput').value.trim().toLowerCase();
  if (query.length < 2) {
    document.getElementById('searchResults').classList.remove('active');
    return;
  }
  searchTimeout = setTimeout(() => performSearch(query), 250);
}

function performSearch(query) {
  const results = [];

  projects.forEach(p => {
    if (p.name.toLowerCase().includes(query)) {
      results.push({ type: 'project', projectId: p.id, title: p.name, context: p.desc || '' });
    }
    (p.todos || []).forEach(td => {
      if (td.archived) return;
      const matchText = td.text.toLowerCase().includes(query);
      const matchNote = (td.note || '').toLowerCase().includes(query);
      if (matchText || matchNote) {
        results.push({
          type: 'todo', projectId: p.id, todoId: td.id, title: td.text,
          context: p.name + (matchNote ? ' - ' + td.note.substring(0, 80) : '')
        });
      }
    });
  });

  const container = document.getElementById('searchResults');

  if (results.length === 0) {
    container.innerHTML = `<div class="search-no-results">${escapeHtml(t('search.no_results'))}</div>`;
    container.classList.add('active');
    return;
  }

  container.innerHTML = results.slice(0, 15).map(r => `
    <div class="search-result-item" onclick="navigateToSearchResult(${r.projectId})">
      <div class="search-result-type">${r.type === 'project' ? '📁 ' + t('search.type_project') : '✓ ' + t('search.type_task')}</div>
      <div class="search-result-title">${escapeHtml(r.title)}</div>
      <div class="search-result-context">${escapeHtml(r.context)}</div>
    </div>
  `).join('');
  container.classList.add('active');
}

function navigateToSearchResult(projectId) {
  document.getElementById('searchResults').classList.remove('active');
  document.getElementById('globalSearchInput').value = '';
  openProjectDetail(projectId);
}

document.addEventListener('click', (e) => {
  if (!e.target.closest('.search-bar')) {
    const sr = document.getElementById('searchResults');
    if (sr) sr.classList.remove('active');
  }
});

// Activity Feed
async function renderActivityFeed() {
  const container = document.getElementById('activityFeed');
  if (!container) return;

  const result = await apiCall('getActivity', { count: 20 });
  if (!result.success || !result.data || result.data.length === 0) {
    container.innerHTML = `<div class="empty-todos"><p>${escapeHtml(t('activity.empty'))}</p></div>`;
    return;
  }

  const actionIcons = {
    user_login: '🔑', project_created: '📁', project_deleted: '🗑️',
    todo_created: '➕', todo_completed: '✅', todo_deleted: '❌'
  };

  const actionLabels = {
    user_login: t('activity.user_login'), project_created: t('activity.project_created'),
    project_deleted: t('activity.project_deleted'), todo_created: t('activity.todo_created'),
    todo_completed: t('activity.todo_completed'), todo_deleted: t('activity.todo_deleted')
  };

  container.innerHTML = result.data.map(a => `
    <div class="activity-item">
      <div class="activity-icon">${actionIcons[a.action] || '📌'}</div>
      <div>
        <div class="activity-text">
          <strong>${escapeHtml(a.userName)}</strong> ${escapeHtml(actionLabels[a.action] || a.action)}
          ${a.projectName ? ' - <em>' + escapeHtml(a.projectName) + '</em>' : ''}
          ${a.todoText ? ': ' + escapeHtml(a.todoText) : ''}
        </div>
        <div class="activity-time">${timeAgo(a.timestamp)}</div>
      </div>
    </div>
  `).join('');
}

function timeAgo(dateStr) {
  const now = new Date();
  const date = new Date(dateStr);
  const seconds = Math.floor((now - date) / 1000);

  if (seconds < 60) return t('activity.just_now');
  const minutes = Math.floor(seconds / 60);
  if (minutes < 60) return minutes + ' ' + t('activity.minutes_ago');
  const hours = Math.floor(minutes / 60);
  if (hours < 24) return hours + ' ' + t('activity.hours_ago');
  const days = Math.floor(hours / 24);
  if (days < 7) return days + ' ' + t('activity.days_ago');
  return date.toLocaleDateString(currentLang === 'de' ? 'de-DE' : 'en-US');
}

function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}

// Todo Drag & Drop Reorder
let draggedTodoId = null;

function todoDragStart(e) {
  draggedTodoId = parseInt(e.currentTarget.dataset.todoId);
  e.currentTarget.classList.add('dragging');
  e.dataTransfer.effectAllowed = 'move';
}

function todoDragOver(e) {
  e.preventDefault();
  e.dataTransfer.dropEffect = 'move';
  const item = e.currentTarget.closest('.todo-item');
  if (item) {
    document.querySelectorAll('.todo-item.drag-over').forEach(el => el.classList.remove('drag-over'));
    item.classList.add('drag-over');
  }
}

function todoDragEnd(e) {
  e.currentTarget.classList.remove('dragging');
  document.querySelectorAll('.todo-item.drag-over').forEach(el => el.classList.remove('drag-over'));
  draggedTodoId = null;
}

async function todoDrop(e) {
  e.preventDefault();
  const targetItem = e.currentTarget.closest('.todo-item');
  if (!targetItem) return;
  const targetId = parseInt(targetItem.dataset.todoId);
  if (draggedTodoId === null || draggedTodoId === targetId) return;

  const project = projects.find(p => p.id === currentProjectId);
  if (!project) return;

  const todos = project.todos || [];
  const todoIds = todos.map(t => t.id);
  const fromIdx = todoIds.indexOf(draggedTodoId);
  const toIdx = todoIds.indexOf(targetId);
  if (fromIdx === -1 || toIdx === -1) return;

  todoIds.splice(fromIdx, 1);
  todoIds.splice(toIdx, 0, draggedTodoId);

  const result = await apiCall('reorderTodos', { projectId: currentProjectId, todoIds });
  if (result.success) {
    await loadProjectsFromServer();
    renderProjectTodos();
  }
}

// Toast Notifications
function showToast(title, msg, type = 'info') {
  const container = document.getElementById('toastContainer');
  if (!container) return;

  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.style.display = 'flex';
  toast.style.alignItems = 'flex-start';
  toast.style.gap = '12px';
  toast.style.padding = '14px 16px';

  const icons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };

  toast.innerHTML = `
    <div class="toast-icon">${icons[type] || icons.info}</div>
    <div class="toast-body">
      <div class="toast-title">${escapeHtml(title)}</div>
      <div class="toast-msg">${escapeHtml(msg)}</div>
    </div>
  `;

  container.appendChild(toast);

  setTimeout(() => {
    toast.classList.add('toast-out');
    setTimeout(() => toast.remove(), 300);
  }, 5000);
}

// Custom Confirm Dialog (replaces browser confirm())
function showConfirm(message, { title = '', icon = '⚠️', yesText = 'OK', noText = '', danger = true } = {}) {
  return new Promise(resolve => {
    const modal = document.getElementById('confirmModal');
    document.getElementById('confirmIcon').textContent = icon;
    document.getElementById('confirmTitle').textContent = title || t('modal.confirm_title') || 'Bestätigung';
    document.getElementById('confirmMessage').textContent = message;

    const yesBtn = document.getElementById('confirmYesBtn');
    const noBtn = document.getElementById('confirmNoBtn');
    yesBtn.textContent = yesText || 'OK';
    noBtn.textContent = noText || t('modal.cancel') || 'Abbrechen';
    yesBtn.className = danger ? 'btn btn-danger' : 'btn btn-primary';

    function cleanup(result) {
      modal.classList.remove('active');
      yesBtn.removeEventListener('click', onYes);
      noBtn.removeEventListener('click', onNo);
      resolve(result);
    }
    function onYes() { cleanup(true); }
    function onNo() { cleanup(false); }

    yesBtn.addEventListener('click', onYes);
    noBtn.addEventListener('click', onNo);
    modal.classList.add('active');
  });
}

// Member Management
async function openAddMemberModal() {
  const project = projects.find(p => p.id === currentProjectId);
  if (!project) return;

  const result = await apiCall('getAllUsers');
  if (!result.success) return;

  const allUsers = result.data || [];
  const memberIds = (project.members || []).map(m => m.userId);
  const available = allUsers.filter(u => !memberIds.includes(u.id));

  const select = document.getElementById('addMemberUserId');
  if (available.length === 0) {
    select.innerHTML = `<option value="">${escapeHtml(t('members.no_users'))}</option>`;
  } else {
    select.innerHTML = available.map(u =>
      `<option value="${u.id}">${escapeHtml(u.name)} (@${escapeHtml(u.username)})</option>`
    ).join('');
  }

  document.getElementById('addMemberRole').value = 'editor';
  document.getElementById('addMemberModal').classList.add('active');
}

async function addMember() {
  const userId = document.getElementById('addMemberUserId').value;
  if (!userId) return;

  const role = document.getElementById('addMemberRole').value;
  const result = await apiCall('addMember', {
    projectId: currentProjectId,
    userId: parseInt(userId),
    role
  });

  if (result.success) {
    closeModal('addMemberModal');
    await loadProjectsFromServer();
    const project = projects.find(p => p.id === currentProjectId);
    if (project) renderMembers(project);
    showToast(t('members.title'), result.message, 'success');
  } else {
    showToast(t('members.add_error'), result.message || t('members.add_error'), 'error');
  }
}

async function removeMember(projectId, userId) {
  if (!await showConfirm(t('members.remove_confirm'), { icon: '🗑️', title: t('members.remove_btn') })) return;

  const result = await apiCall('removeMember', { projectId, userId });
  if (result.success) {
    await loadProjectsFromServer();
    const project = projects.find(p => p.id === projectId);
    if (project) renderMembers(project);
    showToast(t('members.title'), result.message, 'success');
  } else {
    showToast(t('members.remove_error'), result.message || t('members.remove_error'), 'error');
  }
}

async function updateMemberRole(projectId, userId, newRole) {
  const result = await apiCall('updateMemberRole', { projectId, userId, role: newRole });
  if (result.success) {
    await loadProjectsFromServer();
    const project = projects.find(p => p.id === projectId);
    if (project) renderMembers(project);
  } else {
    showToast(t('members.role_error'), result.message || t('members.role_error'), 'error');
  }
}

function renderMembers(project) {
  const container = document.getElementById('membersList');
  const addBtn = document.getElementById('addMemberBtn');
  if (!container) return;

  const members = project.members || [];
  const isAdmin = currentUser.role === 'admin';
  const isOwner = members.some(m => m.userId === currentUser.id && m.role === 'owner');
  const canManage = isAdmin || isOwner;

  if (addBtn) addBtn.style.display = canManage ? '' : 'none';

  const roleLabels = {
    owner: t('members.role_owner'),
    editor: t('members.role_editor'),
    viewer: t('members.role_viewer')
  };
  const roleIcons = { owner: '👑', editor: '✏️', viewer: '👁️' };

  container.innerHTML = members.map(m => {
    const isMe = m.userId === currentUser.id;
    const memberName = m.userName || m.username || `User #${m.userId}`;
    const nextRole = m.role === 'editor' ? 'viewer' : 'editor';
    const roleColor = m.role === 'owner' ? 'var(--warning)' : m.role === 'editor' ? 'var(--primary)' : 'var(--text-muted)';

    return `
      <div style="display:flex;align-items:center;gap:8px;padding:8px 0;border-bottom:1px solid var(--border)">
        <div class="user-avatar" style="width:32px;height:32px;font-size:13px">${escapeHtml(memberName.charAt(0).toUpperCase())}</div>
        <div style="flex:1;min-width:0">
          <div style="font-weight:600;font-size:13px">${escapeHtml(memberName)}${isMe ? ' <span style="color:var(--text-muted);font-weight:400;font-size:11px">' + escapeHtml(t('members.you')) + '</span>' : ''}</div>
        </div>
        ${canManage && m.role !== 'owner' ? `
          <button onclick="updateMemberRole(${project.id},${m.userId},'${nextRole}')" style="height:28px;font-size:12px;padding:0 10px;border-radius:14px;background:${roleColor}18;color:${roleColor};border:1px solid ${roleColor}40;cursor:pointer;transition:all .15s;font-weight:500;line-height:26px" title="${escapeHtml(t('users.change_role'))}">${roleIcons[m.role]} ${escapeHtml(roleLabels[m.role])}</button>
          <button onclick="removeMember(${project.id},${m.userId})" style="height:28px;width:28px;padding:0;border-radius:14px;border:1px solid var(--danger);background:transparent;color:var(--danger);cursor:pointer;font-size:12px;transition:all .15s;display:flex;align-items:center;justify-content:center" onmouseover="this.style.background='var(--danger)';this.style.color='#fff'" onmouseout="this.style.background='transparent';this.style.color='var(--danger)'">🗑️</button>
        ` : `
          <span style="height:28px;font-size:12px;padding:0 10px;border-radius:14px;background:${roleColor}18;color:${roleColor};font-weight:500;line-height:28px;display:inline-block">${roleIcons[m.role]} ${escapeHtml(roleLabels[m.role])}</span>
        `}
      </div>
    `;
  }).join('');
}

// Manage Members Modal (standalone, works from project cards)
let manageMembersProjectId = null;

async function openManageMembersModal(projectId) {
  manageMembersProjectId = projectId;
  const project = projects.find(p => p.id === projectId);
  if (!project) return;

  renderManageMembersModal(project);

  // Load available users for the add dropdown
  const result = await apiCall('getAllUsers');
  if (result.success) {
    const allUsers = result.data || [];
    const memberIds = (project.members || []).map(m => m.userId);
    const available = allUsers.filter(u => !memberIds.includes(u.id));

    const select = document.getElementById('manageMemberUserId');
    const addSection = document.getElementById('manageMembersAdd');
    if (available.length === 0) {
      select.innerHTML = `<option value="">${escapeHtml(t('members.no_users'))}</option>`;
    } else {
      select.innerHTML = available.map(u =>
        `<option value="${u.id}">${escapeHtml(u.name)} (@${escapeHtml(u.username)})</option>`
      ).join('');
    }
  }

  document.getElementById('manageMembersModal').classList.add('active');
}

function renderManageMembersModal(project) {
  const container = document.getElementById('manageMembersList');
  const addSection = document.getElementById('manageMembersAdd');
  if (!container) return;

  const members = project.members || [];
  const isAdmin = currentUser.role === 'admin';
  const isOwner = members.some(m => m.userId === currentUser.id && m.role === 'owner');
  const canManage = isAdmin || isOwner;

  if (addSection) addSection.style.display = canManage ? '' : 'none';

  const roleLabels = {
    owner: t('members.role_owner'),
    editor: t('members.role_editor'),
    viewer: t('members.role_viewer')
  };
  const roleIcons = { owner: '👑', editor: '✏️', viewer: '👁️' };

  container.innerHTML = members.map(m => {
    const isMe = m.userId === currentUser.id;
    const memberName = m.userName || `User #${m.userId}`;
    const nextRole = m.role === 'editor' ? 'viewer' : 'editor';
    const roleColor = m.role === 'owner' ? 'var(--warning)' : m.role === 'editor' ? 'var(--primary)' : 'var(--text-muted)';

    return `
      <div style="display:flex;align-items:center;gap:8px;padding:8px 0;border-bottom:1px solid var(--border)">
        <div class="user-avatar" style="width:32px;height:32px;font-size:13px">${escapeHtml(memberName.charAt(0).toUpperCase())}</div>
        <div style="flex:1;min-width:0">
          <div style="font-weight:600;font-size:13px">${escapeHtml(memberName)}${isMe ? ' <span style="color:var(--text-muted);font-weight:400;font-size:11px">' + escapeHtml(t('members.you')) + '</span>' : ''}</div>
        </div>
        ${canManage && m.role !== 'owner' ? `
          <button onclick="updateMemberFromModal(${project.id},${m.userId},'${nextRole}')" style="height:28px;font-size:12px;padding:0 10px;border-radius:14px;background:${roleColor}18;color:${roleColor};border:1px solid ${roleColor}40;cursor:pointer;transition:all .15s;font-weight:500;line-height:26px" title="${escapeHtml(t('users.change_role'))}">${roleIcons[m.role]} ${escapeHtml(roleLabels[m.role])}</button>
          <button onclick="removeMemberFromModal(${project.id},${m.userId})" style="height:28px;width:28px;padding:0;border-radius:14px;border:1px solid var(--danger);background:transparent;color:var(--danger);cursor:pointer;font-size:12px;transition:all .15s;display:flex;align-items:center;justify-content:center" onmouseover="this.style.background='var(--danger)';this.style.color='#fff'" onmouseout="this.style.background='transparent';this.style.color='var(--danger)'">🗑️</button>
        ` : `
          <span style="height:28px;font-size:12px;padding:0 10px;border-radius:14px;background:${roleColor}18;color:${roleColor};font-weight:500;line-height:28px;display:inline-block">${roleIcons[m.role]} ${escapeHtml(roleLabels[m.role])}</span>
        `}
      </div>
    `;
  }).join('');
}

function selectMemberRole(role) {
  document.getElementById('manageMemberRole').value = role;
  document.querySelectorAll('#manageMemberRoleToggle .member-role-btn').forEach(btn => {
    btn.classList.toggle('active', btn.dataset.role === role);
  });
}

async function addMemberFromModal() {
  const userId = document.getElementById('manageMemberUserId').value;
  if (!userId || !manageMembersProjectId) return;

  const role = document.getElementById('manageMemberRole').value;
  const result = await apiCall('addMember', {
    projectId: manageMembersProjectId,
    userId: parseInt(userId),
    role
  });

  if (result.success) {
    await loadProjectsFromServer();
    const project = projects.find(p => p.id === manageMembersProjectId);
    if (project) {
      renderManageMembersModal(project);
      renderMembers(project);
      // Refresh the user dropdown
      openManageMembersModal(manageMembersProjectId);
    }
    showToast(t('members.title'), result.message, 'success');
  } else {
    showToast(t('members.add_error'), result.message || t('members.add_error'), 'error');
  }
}

async function removeMemberFromModal(projectId, userId) {
  if (!await showConfirm(t('members.remove_confirm'), { icon: '🗑️', title: t('members.remove_btn') })) return;

  const result = await apiCall('removeMember', { projectId, userId });
  if (result.success) {
    await loadProjectsFromServer();
    const project = projects.find(p => p.id === projectId);
    if (project) {
      renderManageMembersModal(project);
      renderMembers(project);
      // Refresh dropdown
      openManageMembersModal(projectId);
    }
    showToast(t('members.title'), result.message, 'success');
  } else {
    showToast(t('members.remove_error'), result.message || t('members.remove_error'), 'error');
  }
}

async function updateMemberFromModal(projectId, userId, newRole) {
  const result = await apiCall('updateMemberRole', { projectId, userId, role: newRole });
  if (result.success) {
    await loadProjectsFromServer();
    const project = projects.find(p => p.id === projectId);
    if (project) {
      renderManageMembersModal(project);
      renderMembers(project);
    }
  } else {
    showToast(t('members.role_error'), result.message || t('members.role_error'), 'error');
  }
}

// Notifications
async function loadNotifications() {
  const result = await apiCall('getNotifications');
  if (result.success && result.data && result.data.length > 0) {
    showPendingNotifications(result.data);
  }
}

async function showPendingNotifications(notifications) {
  const ids = [];
  notifications.forEach(n => {
    ids.push(n.id);
    if (n.type === 'project_added') {
      const title = t('notifications.project_added_title');
      const msg = t('notifications.project_added').replace('{name}', n.projectName || '');
      showToast(title, msg, 'info');
    }
  });

  if (ids.length > 0) {
    await apiCall('dismissNotifications', { ids });
  }
}

// Trash / Deleted Projects
async function loadDeletedProjects() {
  const container = document.getElementById('deletedProjectsList');
  const card = document.getElementById('trashCard');
  if (!container) return;

  const result = await apiCall('getDeletedProjects');
  if (!result.success || !result.data || result.data.length === 0) {
    if (card) card.style.display = 'none';
    return;
  }

  if (card) card.style.display = 'block';
  const isAdmin = currentUser.role === 'admin';

  container.innerHTML = result.data.map(p => {
    const deletedDate = new Date(p.deletedAt).toLocaleDateString(currentLang === 'de' ? 'de-DE' : 'en-US');
    return `
      <div style="display:flex;align-items:center;gap:16px;padding:14px 0;border-bottom:1px solid var(--border)">
        <div style="width:40px;height:40px;border-radius:10px;background:var(--bg-secondary);display:flex;align-items:center;justify-content:center;font-size:18px">📁</div>
        <div style="flex:1;min-width:0">
          <div style="font-weight:600;font-size:15px;color:var(--text)">${escapeHtml(p.name)}</div>
          <div style="font-size:12px;color:var(--text-muted);margin-top:2px">
            ${escapeHtml(t('trash.deleted_by'))} ${escapeHtml(p.deletedByName)} &middot; ${deletedDate} &middot;
            <span style="color:var(--warning);font-weight:500">${p.daysLeft} ${escapeHtml(t('trash.days_left'))}</span>
          </div>
        </div>
        <button class="btn btn-primary btn-sm" onclick="restoreProject(${p.id})" style="font-size:13px">↩️ ${escapeHtml(t('trash.restore'))}</button>
        ${isAdmin ? `<button class="btn btn-danger btn-sm" onclick="permanentDeleteProject(${p.id})" style="font-size:13px">🗑️ ${escapeHtml(t('trash.delete_permanent'))}</button>` : ''}
      </div>
    `;
  }).join('');
}

async function restoreProject(id) {
  const result = await apiCall('restoreProject', { id });
  if (result.success) {
    showToast(t('trash.title'), t('trash.restored'), 'success');
    await loadProjectsFromServer();
    renderDashboard();
    renderProjects();
    loadDeletedProjects();
  } else {
    showToast(t('trash.title'), result.message, 'error');
  }
}

async function permanentDeleteProject(id) {
  if (!await showConfirm(t('trash.confirm_permanent'), { icon: '⚠️', title: t('trash.delete_permanent') })) return;

  const result = await apiCall('permanentDeleteProject', { id });
  if (result.success) {
    showToast(t('trash.title'), result.message, 'success');
    loadDeletedProjects();
  } else {
    showToast(t('trash.title'), result.message, 'error');
  }
}

// Start app
init();
