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

  if (!confirm(t('settings.update_confirm'))) return;

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
    alert(t('login.alert_fields'));
    return;
  }

  const result = await apiCall('login', {username, password});

  if (result.success) {
    currentUser = result.data;
    await loadProjectsFromServer();
    showApp();
  } else {
    alert(result.message || t('login.alert_failed'));
  }
}

async function register() {
  const name = document.getElementById('regName').value.trim();
  const username = document.getElementById('regUsername').value.trim();
  const password = document.getElementById('regPassword').value;

  if (!name || !username || !password) {
    alert(t('register.alert_fields'));
    return;
  }

  const result = await apiCall('register', {name, username, password});

  if (result.success) {
    alert(t('register.alert_success'));
    showLogin();
  } else {
    alert(result.message || t('register.alert_failed'));
  }
}

async function logout() {
  if (confirm(t('nav.logout_confirm'))) {
    await apiCall('logout');
    currentUser = null;
    projects = [];
    document.getElementById('appContainer').classList.remove('active');
    document.getElementById('loginUsername').value = '';
    document.getElementById('loginPassword').value = '';
    showLogin();
  }
}

function showApp() {
  document.getElementById('loginScreen').style.display = 'none';
  document.getElementById('registerScreen').style.display = 'none';
  document.getElementById('appContainer').classList.add('active');

  document.getElementById('userName').textContent = currentUser.name;
  document.getElementById('userAvatar').textContent = currentUser.name.charAt(0).toUpperCase();

  renderDashboard();
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
  document.getElementById('newProjectModal').classList.add('active');
  document.getElementById('newProjectName').focus();
}

function closeModal(id) {
  document.getElementById(id).classList.remove('active');
}

async function createProject() {
  const name = document.getElementById('newProjectName').value.trim();
  const desc = document.getElementById('newProjectDesc').value.trim();

  if (!name) {
    alert(t('projects.name_required'));
    return;
  }

  const result = await apiCall('createProject', {name, desc});

  if (result.success) {
    await loadProjectsFromServer();
    document.getElementById('newProjectName').value = '';
    document.getElementById('newProjectDesc').value = '';
    closeModal('newProjectModal');
    renderDashboard();
    renderProjects();
  } else {
    alert(result.message || t('projects.create_error'));
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
}

async function showUsers() {
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

    return `
      <div class="project-card" onclick="openProjectDetail(${p.id})">
        <div class="project-icon">📁</div>
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

    return `
      <div class="project-card" onclick="openProjectDetail(${p.id})">
        <div class="project-icon">📁</div>
        <button class="project-menu" onclick="event.stopPropagation();deleteProject(${p.id})">⋮</button>
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
  container.innerHTML = users.map(u => `
    <div style="display:flex;align-items:center;gap:16px;padding:16px;border-bottom:1px solid var(--border)">
      <div class="user-avatar">${u.name.charAt(0).toUpperCase()}</div>
      <div style="flex:1">
        <div style="font-weight:600;margin-bottom:4px">${escapeHtml(u.name)}</div>
        <div style="font-size:14px;color:var(--text-muted)">@${escapeHtml(u.username)}</div>
      </div>
      <div style="font-size:12px;color:var(--text-muted)">
        ${escapeHtml(t('users.created'))} ${new Date(u.createdAt).toLocaleDateString(currentLang === 'de' ? 'de-DE' : 'en-US')}
      </div>
      ${u.id !== currentUser.id ? `<button class="btn btn-danger btn-sm" onclick="deleteUser(${u.id})" title="${escapeHtml(t('users.delete_btn'))}">🗑️</button>` : ''}
    </div>
  `).join('');
}

async function deleteProject(id) {
  if (confirm(t('projects.delete_confirm'))) {
    const result = await apiCall('deleteProject', {id});
    if (result.success) {
      await loadProjectsFromServer();
      renderDashboard();
      renderProjects();
    } else {
      alert(result.message || t('projects.delete_error'));
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
  currentProjectView = 'list';
  switchTodoView('active');

  // Reset view toggle buttons
  const kanbanContainer = document.getElementById('kanbanContainer');
  if (kanbanContainer) kanbanContainer.style.display = 'none';
  const listBtn = document.getElementById('listViewBtn');
  const kanbanBtn = document.getElementById('kanbanViewBtn');
  if (listBtn) { listBtn.style.background = 'var(--card)'; listBtn.style.boxShadow = 'var(--shadow-sm)'; listBtn.classList.remove('btn-ghost'); }
  if (kanbanBtn) { kanbanBtn.style.background = 'transparent'; kanbanBtn.style.boxShadow = 'none'; kanbanBtn.classList.add('btn-ghost'); }

  renderProjectStats();
  renderProjectTodos();
}

function backToProjects() {
  currentProjectId = null;
  showProjects();
}

async function editProject() {
  const project = projects.find(p => p.id === currentProjectId);
  if (!project) return;

  const newName = prompt(t('projects.prompt_name'), project.name);
  if (!newName || !newName.trim()) return;

  const newDesc = prompt(t('projects.prompt_desc'), project.desc);

  const result = await apiCall('updateProject', {
    id: currentProjectId,
    name: newName.trim(),
    desc: newDesc !== null ? newDesc.trim() : project.desc
  });

  if (result.success) {
    await loadProjectsFromServer();
    const updated = projects.find(p => p.id === currentProjectId);
    document.getElementById('projectDetailTitle').textContent = updated.name;
    document.getElementById('projectDetailDesc').textContent = updated.desc || t('projects.no_desc');
    renderDashboard();
    renderProjects();
  } else {
    alert(result.message || t('projects.edit_error'));
  }
}

async function deleteCurrentProject() {
  if (!confirm(t('projects.delete_confirm'))) return;

  const result = await apiCall('deleteProject', {id: currentProjectId});
  if (result.success) {
    await loadProjectsFromServer();
    backToProjects();
    renderDashboard();
  } else {
    alert(result.message || t('projects.delete_error'));
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
    alert(t('todos.alert_required'));
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
    alert(result.message || t('todos.alert_add_error'));
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
        <div class="todo-item ${todo.done ? 'done' : ''}">
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
  if (!text) { alert(t('todos.alert_required')); return; }

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
  if (!confirm(t('todos.delete_confirm'))) return;

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

    alert(t('data.export_success'));
  } else {
    alert(result.message || t('data.export_failed'));
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
        alert(t('data.import_invalid'));
        return;
      }

      if (confirm(t('data.import_confirm'))) {
        const result = await apiCall('importData', data);
        if (result.success) {
          await loadProjectsFromServer();
          renderDashboard();
          alert(t('data.import_success'));
        } else {
          alert(result.message || t('data.import_failed'));
        }
      }
    } catch (error) {
      alert(t('data.import_read_error') + error.message);
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
    alert(t('settings.password_fields_required'));
    return;
  }

  if (newPw !== confirmPw) {
    alert(t('settings.password_mismatch'));
    return;
  }

  const result = await apiCall('changePassword', {currentPassword: currentPw, newPassword: newPw});
  if (result.success) {
    alert(t('settings.password_success'));
    document.getElementById('currentPassword').value = '';
    document.getElementById('newPassword').value = '';
    document.getElementById('confirmPassword').value = '';
  } else {
    alert(result.message || t('settings.password_error'));
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
}

async function createUser() {
  const name = document.getElementById('newUserName').value;
  const username = document.getElementById('newUserUsername').value;
  const password = document.getElementById('newUserPassword').value;

  if (!name || !username || !password) {
    alert(t('register.alert_fields'));
    return;
  }

  const result = await apiCall('createUser', {name, username, password});
  if (result.success) {
    closeCreateUserForm();
    const usersResult = await apiCall('getUsers');
    if (usersResult.success) {
      users = usersResult.data;
      renderUsers();
    }
  } else {
    alert(result.message || t('users.create_error'));
  }
}

// User Deletion
async function deleteUser(id) {
  if (!confirm(t('users.delete_confirm'))) return;

  const result = await apiCall('deleteUser', {id});
  if (result.success) {
    const usersResult = await apiCall('getUsers');
    if (usersResult.success) {
      users = usersResult.data;
      renderUsers();
    }
  } else {
    alert(result.message || t('users.delete_error'));
  }
}

// Logo Management
function uploadLogo(event) {
  const file = event.target.files[0];
  if (!file) return;

  if (!file.type.match(/^image\/(png|jpeg|svg\+xml)$/)) {
    alert(t('settings.logo_invalid'));
    event.target.value = '';
    return;
  }

  if (file.size > 2 * 1024 * 1024) {
    alert(t('settings.logo_too_big'));
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
function changeTheme(theme) {
  document.body.setAttribute('data-theme', theme);
  localStorage.setItem('taskflow_theme', theme);

  document.querySelectorAll('.theme-option').forEach(opt => {
    opt.classList.remove('active');
  });
  const opt = document.querySelector(`.theme-option[data-theme="${theme}"]`);
  if (opt) opt.classList.add('active');
}

function loadTheme() {
  const savedTheme = localStorage.getItem('taskflow_theme') || 'purple';
  changeTheme(savedTheme);
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

// Dark Mode
function toggleDarkMode() {
  const isDark = document.body.getAttribute('data-dark') === 'true';
  const newVal = !isDark;
  document.body.setAttribute('data-dark', newVal);
  localStorage.setItem('taskflow_dark', newVal);
  updateDarkModeUI(newVal);
}

function loadDarkMode() {
  const savedDark = localStorage.getItem('taskflow_dark') === 'true';
  document.body.setAttribute('data-dark', savedDark);
  updateDarkModeUI(savedDark);
}

function updateDarkModeUI(isDark) {
  const toggle = document.getElementById('darkModeToggle');
  if (toggle) toggle.textContent = isDark ? '☀️' : '🌙';
  const checkbox = document.getElementById('darkModeCheckbox');
  if (checkbox) checkbox.checked = isDark;
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

// Start app
init();
