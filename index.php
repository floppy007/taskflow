<?php
// Redirect to installer if not yet installed
if (!file_exists(__DIR__ . '/data/users.json') && file_exists(__DIR__ . '/install.php')) {
    header('Location: install.php');
    exit;
}
?>
<!--
  TaskFlow v1.2
  Copyright (c) 2026 Florian Hesse
  Fischer Str. 11, 16515 Oranienburg
  https://comnic-it.de
  Alle Rechte vorbehalten.
-->
<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="icon" type="image/png" href="logo.png">
<link rel="apple-touch-icon" href="logo.png">
<title>TaskFlow - Projekt Management</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root {
    --primary:#667eea; --primary-dark:#5568d3; --primary-light:#7c8cf0;
    --accent:#764ba2; --accent-light:#9d5fc4;
    --success:#10b981; --warning:#f59e0b; --danger:#ef4444;
    --bg:#f8fafc; --bg-secondary:#f1f5f9; --card:#ffffff;
    --text:#0f172a; --text-muted:#64748b; --text-light:#94a3b8;
    --border:#e2e8f0; --border-light:#f1f5f9;
    --shadow-sm:0 1px 2px 0 rgba(0,0,0,.05);
    --shadow:0 4px 6px -1px rgba(0,0,0,.1),0 2px 4px -2px rgba(0,0,0,.1);
    --shadow-lg:0 10px 15px -3px rgba(0,0,0,.1),0 4px 6px -4px rgba(0,0,0,.1);
    --radius:12px; --radius-lg:16px;
    --gradient:linear-gradient(135deg,var(--primary) 0%,var(--accent) 100%);
    --gradient-light:linear-gradient(135deg,var(--primary-light) 0%,var(--accent-light) 100%);
  }

  /* Theme: Purple Dream (Default) */
  body[data-theme="purple"] {
    --primary:#667eea; --primary-dark:#5568d3; --primary-light:#7c8cf0;
    --accent:#764ba2; --accent-light:#9d5fc4;
    --gradient:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
    --gradient-light:linear-gradient(135deg,#7c8cf0 0%,#9d5fc4 100%);
  }

  /* Theme: Ocean Blue */
  body[data-theme="ocean"] {
    --primary:#3b82f6; --primary-dark:#2563eb; --primary-light:#60a5fa;
    --accent:#06b6d4; --accent-light:#22d3ee;
    --gradient:linear-gradient(135deg,#3b82f6 0%,#06b6d4 100%);
    --gradient-light:linear-gradient(135deg,#60a5fa 0%,#22d3ee 100%);
  }

  /* Theme: Sunset Orange */
  body[data-theme="sunset"] {
    --primary:#f59e0b; --primary-dark:#d97706; --primary-light:#fbbf24;
    --accent:#ef4444; --accent-light:#f87171;
    --gradient:linear-gradient(135deg,#f59e0b 0%,#ef4444 100%);
    --gradient-light:linear-gradient(135deg,#fbbf24 0%,#f87171 100%);
  }

  /* Theme: Petrol */
  body[data-theme="petrol"] {
    --primary:#0d9488; --primary-dark:#0f766e; --primary-light:#14b8a6;
    --accent:#0f2b3c; --accent-light:#1a4a63;
    --gradient:linear-gradient(135deg,#0d9488 0%,#0f2b3c 100%);
    --gradient-light:linear-gradient(135deg,#14b8a6 0%,#1a4a63 100%);
  }

  /* Theme: Rose Pink */
  body[data-theme="rose"] {
    --primary:#ec4899; --primary-dark:#db2777; --primary-light:#f472b6;
    --accent:#8b5cf6; --accent-light:#a78bfa;
    --gradient:linear-gradient(135deg,#ec4899 0%,#8b5cf6 100%);
    --gradient-light:linear-gradient(135deg,#f472b6 0%,#a78bfa 100%);
  }

  /* Theme: Midnight Dark */
  body[data-theme="midnight"] {
    --primary:#6366f1; --primary-dark:#4f46e5; --primary-light:#818cf8;
    --accent:#1e293b; --accent-light:#334155;
    --gradient:linear-gradient(135deg,#6366f1 0%,#1e293b 100%);
    --gradient-light:linear-gradient(135deg,#818cf8 0%,#334155 100%);
  }

  /* Dark Mode */
  body[data-dark="true"] {
    --bg:#0f172a; --bg-secondary:#1e293b; --card:#1e293b;
    --text:#f1f5f9; --text-muted:#94a3b8; --text-light:#64748b;
    --border:#334155; --border-light:#1e293b;
    --shadow-sm:0 1px 2px 0 rgba(0,0,0,.3);
    --shadow:0 4px 6px -1px rgba(0,0,0,.3),0 2px 4px -2px rgba(0,0,0,.3);
    --shadow-lg:0 10px 15px -3px rgba(0,0,0,.3),0 4px 6px -4px rgba(0,0,0,.3);
  }
  body[data-dark="true"] .badge-priority.low { background:#052e16; color:#86efac; border-color:#166534; }
  body[data-dark="true"] .badge-priority.medium { background:#451a03; color:#fcd34d; border-color:#92400e; }
  body[data-dark="true"] .badge-priority.high { background:#450a0a; color:#fca5a5; border-color:#991b1b; }
  body[data-dark="true"] .badge-due.overdue { background:#450a0a; color:#fca5a5; border-color:#991b1b; }
  body[data-dark="true"] .badge-due.today { background:#431407; color:#fdba74; border-color:#9a3412; }
  body[data-dark="true"] .badge-due.upcoming { background:#451a03; color:#fcd34d; border-color:#92400e; }
  body[data-dark="true"] .badge-due.later { background:#052e16; color:#86efac; border-color:#166534; }
  body[data-dark="true"] .search-input,
  body[data-dark="true"] .form-input,
  body[data-dark="true"] select.form-input { background:var(--bg-secondary); color:var(--text); border-color:var(--border); }
  body[data-dark="true"] .login-container { background:linear-gradient(135deg,#0f172a 0%,#1e293b 100%); }
  body[data-dark="true"] .login-box { background:rgba(30,41,59,.9); border-color:rgba(51,65,85,.5); }
  body[data-dark="true"] .modal-content { background:var(--card); }

  * {box-sizing:border-box; margin:0; padding:0}

  body {
    font-family:'Inter',system-ui,-apple-system,sans-serif;
    background:var(--bg);
    color:var(--text);
    line-height:1.6;
  }

  /* Modern Login Screen with Glassmorphism */
  .login-container {
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
    padding:20px;
    position:relative;
    overflow:hidden;
  }

  /* Animated Background Pattern */
  .login-container::before {
    content:'';
    position:absolute;
    top:-50%;
    left:-50%;
    width:200%;
    height:200%;
    background:radial-gradient(circle,rgba(255,255,255,.08) 1px,transparent 1px);
    background-size:50px 50px;
    animation:float 20s linear infinite;
  }

  @keyframes float {
    0% {transform:translate(0,0)}
    100% {transform:translate(50px,50px)}
  }

  /* Floating Orbs */
  .login-container::after {
    content:'';
    position:absolute;
    width:400px;
    height:400px;
    background:radial-gradient(circle,rgba(255,255,255,.15) 0%,transparent 70%);
    border-radius:50%;
    top:10%;
    right:10%;
    animation:pulse 8s ease-in-out infinite;
  }

  @keyframes pulse {
    0%, 100% {transform:scale(1); opacity:.5}
    50% {transform:scale(1.1); opacity:.8}
  }

  .login-orb {
    position:absolute;
    border-radius:50%;
    filter:blur(60px);
    opacity:.4;
    animation:float-orb 15s ease-in-out infinite;
  }

  .login-orb-1 {
    width:300px;
    height:300px;
    background:#a78bfa;
    top:20%;
    left:10%;
    animation-delay:-5s;
  }

  .login-orb-2 {
    width:250px;
    height:250px;
    background:#ec4899;
    bottom:10%;
    right:20%;
    animation-delay:-10s;
  }

  @keyframes float-orb {
    0%, 100% {transform:translate(0,0) scale(1)}
    33% {transform:translate(30px,-30px) scale(1.1)}
    66% {transform:translate(-30px,30px) scale(.9)}
  }

  /* Glassmorphism Login Box */
  .login-box {
    background:rgba(255,255,255,.95);
    backdrop-filter:blur(20px) saturate(180%);
    -webkit-backdrop-filter:blur(20px) saturate(180%);
    padding:48px 40px;
    border-radius:24px;
    box-shadow:
      0 50px 100px -20px rgba(50,50,93,.25),
      0 30px 60px -30px rgba(0,0,0,.3),
      inset 0 -2px 6px 0 rgba(10,37,64,.1);
    width:100%;
    max-width:440px;
    border:1px solid rgba(255,255,255,.6);
    position:relative;
    z-index:10;
    animation:slideUp .6s cubic-bezier(.16,1,.3,1);
  }

  @keyframes slideUp {
    from {
      opacity:0;
      transform:translateY(40px) scale(.95);
    }
    to {
      opacity:1;
      transform:translateY(0) scale(1);
    }
  }

  .login-logo {
    text-align:center;
    margin-bottom:36px;
  }

  .login-logo h1 {
    margin-bottom:8px;
    line-height:1;
  }

  .login-logo p {
    color:var(--text-muted);
    font-size:15px;
    font-weight:500;
  }

  .form-group {
    margin-bottom:20px;
  }

  .form-label {
    display:block;
    font-size:14px;
    font-weight:600;
    color:var(--text);
    margin-bottom:8px;
  }

  .form-input {
    width:100%;
    padding:14px 18px;
    border:2px solid var(--border);
    border-radius:12px;
    font-size:15px;
    font-family:inherit;
    transition:all .3s cubic-bezier(.4,0,.2,1);
    background:var(--card);
    box-shadow:0 1px 2px 0 rgba(0,0,0,.05);
  }

  .form-input:focus {
    outline:none;
    border-color:var(--primary);
    box-shadow:0 0 0 4px rgba(99,102,241,.1), 0 1px 3px 0 rgba(0,0,0,.1);
    transform:translateY(-1px);
  }

  .form-input:hover {
    border-color:var(--primary-light);
  }

  .btn {
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    padding:14px 24px;
    border:none;
    border-radius:12px;
    font-size:15px;
    font-weight:600;
    font-family:inherit;
    cursor:pointer;
    transition:all .3s cubic-bezier(.4,0,.2,1);
    text-decoration:none;
    position:relative;
    overflow:hidden;
  }

  .btn::before {
    content:'';
    position:absolute;
    top:50%;
    left:50%;
    width:0;
    height:0;
    border-radius:50%;
    background:rgba(255,255,255,.3);
    transform:translate(-50%,-50%);
    transition:width .6s,height .6s;
  }

  .btn:hover::before {
    width:300px;
    height:300px;
  }

  .btn-primary {
    background:var(--gradient);
    color:#fff;
    width:100%;
    box-shadow:0 4px 12px rgba(102,126,234,.3);
  }

  .btn-primary:hover {
    background:var(--gradient-light);
    transform:translateY(-2px);
    box-shadow:0 8px 20px rgba(102,126,234,.4);
  }

  .btn-primary:active {
    transform:translateY(0);
  }

  .btn-secondary {
    background:var(--bg-secondary);
    color:var(--text);
    border:2px solid var(--border);
  }

  .btn-secondary:hover {
    background:var(--border-light);
  }

  .btn-ghost {
    background:transparent;
    color:var(--text-muted);
    border:2px solid var(--border);
  }

  .btn-ghost:hover {
    background:var(--bg-secondary);
    color:var(--text);
    border-color:var(--primary);
  }

  .btn-danger {
    background:var(--danger);
    color:#fff;
    box-shadow:0 4px 12px rgba(239,68,68,.3);
  }

  .btn-danger:hover {
    background:#dc2626;
    transform:translateY(-2px);
    box-shadow:0 8px 20px rgba(239,68,68,.4);
  }

  .btn-sm {
    padding:10px 18px;
    font-size:14px;
  }

  .login-footer {
    text-align:center;
    margin-top:28px;
    padding-top:28px;
    border-top:1px solid var(--border);
  }

  .login-footer a {
    color:var(--primary);
    text-decoration:none;
    font-weight:600;
    font-size:14px;
    transition:all .2s;
  }

  .login-footer a:hover {
    color:var(--primary-dark);
    text-decoration:underline;
  }

  .copyright {
    text-align:center;
    font-size:13px;
    color:var(--text-muted);
    margin:0;
    opacity:0.7;
  }

  .login-box .copyright {
    margin-top:16px;
  }

  .content-footer {
    position:sticky !important;
    bottom:0 !important;
    padding:8px 32px !important;
    border-top:1px solid var(--border) !important;
    background:var(--card) !important;
    display:flex !important;
    align-items:center !important;
    justify-content:center !important;
    height:40px !important;
    box-sizing:border-box !important;
    margin-top:auto !important;
    flex-shrink:0 !important;
    visibility:visible !important;
    opacity:1 !important;
    z-index:50 !important;
  }

  /* Main App Layout */
  .app-container {
    display:none;
    min-height:100vh;
  }

  .app-container.active {
    display:flex;
    flex-direction:column;
    animation:fadeIn .4s ease-out;
  }

  @keyframes fadeIn {
    from {opacity:0}
    to {opacity:1}
  }

  /* Sidebar */
  .sidebar {
    position:fixed;
    left:0;
    top:0;
    bottom:0;
    width:280px;
    background:var(--card);
    border-right:1px solid var(--border);
    display:flex;
    flex-direction:column;
    z-index:100;
    box-shadow:2px 0 12px rgba(0,0,0,.05);
  }

  .sidebar-header {
    padding:10px 24px;
    border-bottom:1px solid var(--border);
  }

  .sidebar-logo {
    display:flex;
    align-items:center;
  }

  .sidebar-nav {
    flex:1;
    padding:16px;
    overflow-y:auto;
  }

  .nav-section {
    margin-bottom:24px;
  }

  .nav-section-title {
    font-size:11px;
    font-weight:700;
    text-transform:uppercase;
    color:var(--text-muted);
    padding:0 12px;
    margin-bottom:8px;
    letter-spacing:.08em;
  }

  .nav-item {
    display:flex;
    align-items:center;
    gap:12px;
    padding:12px 14px;
    border-radius:10px;
    color:var(--text);
    text-decoration:none;
    font-size:14px;
    font-weight:500;
    transition:all .2s;
    cursor:pointer;
    margin-bottom:4px;
  }

  .nav-item:hover {
    background:var(--bg-secondary);
    transform:translateX(2px);
  }

  .nav-item.active {
    background:var(--gradient);
    color:#fff;
    box-shadow:0 4px 12px rgba(102,126,234,.3);
  }

  .nav-icon {
    font-size:20px;
  }

  .nav-badge {
    margin-left:auto;
    background:var(--primary);
    color:#fff;
    font-size:11px;
    font-weight:700;
    padding:3px 8px;
    border-radius:12px;
    box-shadow:0 2px 4px rgba(102,126,234,.2);
  }

  .nav-item.active .nav-badge {
    background:rgba(255,255,255,.3);
  }

  .sidebar-footer {
    padding:16px;
    border-top:1px solid var(--border);
  }

  .user-card {
    display:flex;
    align-items:center;
    gap:12px;
    padding:12px;
    background:var(--bg-secondary);
    border-radius:12px;
    transition:all .2s;
  }

  .user-card:hover {
    background:var(--border-light);
  }

  .user-avatar {
    width:44px;
    height:44px;
    border-radius:50%;
    background:var(--gradient);
    display:flex;
    align-items:center;
    justify-content:center;
    color:#fff;
    font-weight:700;
    font-size:18px;
    box-shadow:0 4px 12px rgba(102,126,234,.3);
  }

  .user-info {
    flex:1;
    min-width:0;
  }

  .user-name {
    font-size:14px;
    font-weight:600;
    color:var(--text);
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
  }

  .user-role {
    font-size:12px;
    color:var(--text-muted);
  }

  .user-menu-btn {
    background:transparent;
    border:none;
    color:var(--text-muted);
    cursor:pointer;
    padding:10px;
    border-radius:8px;
    transition:all .2s;
    font-size:26px;
  }

  .user-menu-btn:hover {
    background:var(--card);
    color:var(--danger);
    transform:rotate(90deg);
  }

  /* Main Content */
  .main-content {
    margin-left:280px;
    flex:1;
    display:flex;
    flex-direction:column;
    min-height:100vh;
  }

  .top-bar {
    background:var(--card);
    border-bottom:1px solid var(--border);
    padding:16px 32px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:16px;
    position:sticky;
    top:0;
    z-index:90;
    box-shadow:0 1px 3px rgba(0,0,0,.05);
  }

  .search-bar {
    flex:1;
    max-width:400px;
    position:relative;
  }

  .search-input {
    width:100%;
    padding:11px 16px 11px 42px;
    border:2px solid var(--border);
    border-radius:12px;
    font-size:14px;
    font-family:inherit;
    transition:all .2s;
    background:var(--bg);
  }

  .search-input:focus {
    outline:none;
    border-color:var(--primary);
    box-shadow:0 0 0 3px rgba(99,102,241,.1);
  }

  .search-icon {
    position:absolute;
    left:14px;
    top:50%;
    transform:translateY(-50%);
    color:var(--text-muted);
    font-size:16px;
  }

  .top-actions {
    display:flex;
    align-items:center;
    gap:12px;
  }

  .content-area {
    flex:1;
    padding:32px;
    overflow-y:auto;
    background:var(--bg);
  }

  .page-header {
    margin-bottom:32px;
  }

  .page-title {
    font-size:32px;
    font-weight:800;
    color:var(--text);
    margin-bottom:8px;
    letter-spacing:-.02em;
  }

  .page-subtitle {
    font-size:16px;
    color:var(--text-muted);
  }

  .stats-grid {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
    gap:24px;
    margin-bottom:32px;
  }

  .stat-card {
    background:var(--card);
    padding:24px;
    border-radius:16px;
    border:1px solid var(--border);
    transition:all .3s cubic-bezier(.4,0,.2,1);
    position:relative;
    overflow:hidden;
  }

  .stat-card::before {
    content:'';
    position:absolute;
    top:0;
    left:0;
    right:0;
    height:4px;
    background:var(--gradient);
    transform:scaleX(0);
    transition:transform .3s;
  }

  .stat-card:hover {
    transform:translateY(-4px);
    box-shadow:0 12px 24px rgba(0,0,0,.1);
    border-color:var(--primary-light);
  }

  .stat-card:hover::before {
    transform:scaleX(1);
  }

  .stat-header {
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-bottom:16px;
  }

  .stat-label {
    font-size:13px;
    font-weight:700;
    color:var(--text-muted);
    text-transform:uppercase;
    letter-spacing:.08em;
  }

  .stat-icon {
    width:48px;
    height:48px;
    border-radius:12px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:24px;
    box-shadow:0 4px 12px rgba(0,0,0,.1);
  }

  .stat-icon.blue {background:var(--gradient); color:#fff}
  .stat-icon.green {background:linear-gradient(135deg,#34d399 0%,#10b981 100%); color:#fff}
  .stat-icon.purple {background:var(--gradient-light); color:#fff}
  .stat-icon.orange {background:linear-gradient(135deg,#fbbf24 0%,#f59e0b 100%); color:#fff}

  .stat-value {
    font-size:40px;
    font-weight:800;
    color:var(--text);
    line-height:1;
    letter-spacing:-.02em;
  }

  .stat-change {
    font-size:13px;
    font-weight:600;
    margin-top:8px;
  }

  .stat-change.up {color:var(--success)}
  .stat-change.down {color:var(--danger)}

  /* Project Grid */
  .projects-grid {
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(320px,1fr));
    gap:24px;
  }

  .project-card {
    background:var(--card);
    border:1px solid var(--border);
    border-radius:16px;
    padding:24px;
    cursor:pointer;
    transition:all .3s cubic-bezier(.4,0,.2,1);
    position:relative;
    overflow:hidden;
  }

  .project-card::before {
    content:'';
    position:absolute;
    top:0;
    left:0;
    right:0;
    height:4px;
    background:var(--gradient);
    transform:scaleX(0);
    transition:transform .3s;
  }

  .project-card:hover {
    transform:translateY(-6px);
    box-shadow:0 16px 32px rgba(0,0,0,.12);
    border-color:var(--primary);
  }

  .project-card:hover::before {
    transform:scaleX(1);
  }

  .project-header {
    display:flex;
    align-items:start;
    justify-content:space-between;
    margin-bottom:16px;
  }

  .project-icon {
    width:56px;
    height:56px;
    border-radius:14px;
    background:var(--gradient);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:28px;
    margin-bottom:16px;
    box-shadow:0 8px 16px rgba(102,126,234,.3);
  }

  .project-menu {
    background:transparent;
    border:none;
    color:var(--text-muted);
    cursor:pointer;
    padding:6px 10px;
    border-radius:8px;
    transition:all .2s;
    font-size:20px;
  }

  .project-menu:hover {
    background:var(--bg-secondary);
    color:var(--text);
    transform:rotate(90deg);
  }

  .project-title {
    font-size:20px;
    font-weight:700;
    color:var(--text);
    margin-bottom:8px;
    letter-spacing:-.01em;
  }

  .project-desc {
    font-size:14px;
    color:var(--text-muted);
    line-height:1.6;
    margin-bottom:20px;
    display:-webkit-box;
    -webkit-line-clamp:2;
    -webkit-box-orient:vertical;
    overflow:hidden;
  }

  .project-progress {
    margin-bottom:16px;
  }

  .progress-label {
    display:flex;
    justify-content:space-between;
    font-size:12px;
    font-weight:600;
    color:var(--text-muted);
    margin-bottom:8px;
  }

  .progress-bar-bg {
    height:10px;
    background:var(--bg-secondary);
    border-radius:999px;
    overflow:hidden;
    box-shadow:inset 0 1px 3px rgba(0,0,0,.1);
  }

  .progress-bar {
    height:100%;
    background:linear-gradient(90deg,var(--success),#34d399);
    border-radius:999px;
    transition:width .5s cubic-bezier(.4,0,.2,1);
    box-shadow:0 1px 3px rgba(16,185,129,.3);
  }

  .project-stats {
    display:flex;
    gap:16px;
    padding-top:16px;
    border-top:1px solid var(--border);
  }

  .project-stat {
    flex:1;
    text-align:center;
  }

  .project-stat-value {
    font-size:24px;
    font-weight:700;
    color:var(--text);
    display:block;
    letter-spacing:-.01em;
  }

  .project-stat-label {
    font-size:12px;
    color:var(--text-muted);
    font-weight:500;
    margin-top:2px;
  }

  /* Card Styles */
  .card {
    background:var(--card);
    border:1px solid var(--border);
    border-radius:16px;
    padding:24px;
    margin-bottom:24px;
    box-shadow:0 1px 3px rgba(0,0,0,.05);
  }

  .card-header {
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-bottom:20px;
    padding-bottom:16px;
    border-bottom:1px solid var(--border);
  }

  .card-title {
    font-size:18px;
    font-weight:700;
    color:var(--text);
  }

  /* Modal */
  .modal {
    display:none;
    position:fixed;
    top:0;
    left:0;
    right:0;
    bottom:0;
    background:rgba(0,0,0,.6);
    backdrop-filter:blur(4px);
    z-index:1000;
    align-items:center;
    justify-content:center;
    padding:20px;
  }

  .modal.active {
    display:flex;
    animation:fadeIn .3s ease-out;
  }

  .modal-content {
    background:var(--card);
    border-radius:20px;
    padding:32px;
    width:100%;
    max-width:500px;
    max-height:90vh;
    overflow-y:auto;
    box-shadow:0 25px 50px -12px rgba(0,0,0,.25);
    animation:modalSlide .3s cubic-bezier(.16,1,.3,1);
  }

  @keyframes modalSlide {
    from {
      opacity:0;
      transform:translateY(20px) scale(.95);
    }
    to {
      opacity:1;
      transform:translateY(0) scale(1);
    }
  }

  .modal-header {
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-bottom:24px;
  }

  .modal-title {
    font-size:24px;
    font-weight:700;
    color:var(--text);
  }

  .modal-close {
    background:transparent;
    border:none;
    color:var(--text-muted);
    cursor:pointer;
    font-size:28px;
    padding:4px;
    border-radius:8px;
    transition:all .2s;
    line-height:1;
  }

  .modal-close:hover {
    background:var(--bg-secondary);
    color:var(--text);
    transform:rotate(90deg);
  }

  .empty-state {
    text-align:center;
    padding:64px 32px;
  }

  .empty-icon {
    font-size:72px;
    margin-bottom:16px;
    opacity:.3;
  }

  .empty-title {
    font-size:20px;
    font-weight:700;
    color:var(--text);
    margin-bottom:8px;
  }

  .empty-text {
    font-size:14px;
    color:var(--text-muted);
    margin-bottom:24px;
    line-height:1.6;
  }

  /* Theme Selector */
  .theme-selector {
    display:flex;
    gap:12px;
    flex-wrap:wrap;
  }

  .theme-option {
    width:60px;
    height:60px;
    border-radius:12px;
    cursor:pointer;
    border:3px solid var(--border);
    transition:all .3s;
    position:relative;
    overflow:hidden;
  }

  .theme-option:hover {
    transform:translateY(-4px);
    box-shadow:0 8px 16px rgba(0,0,0,.15);
  }

  .theme-option.active {
    border-color:var(--primary);
    box-shadow:0 0 0 3px rgba(102,126,234,.2);
  }

  .theme-option.active::after {
    content:'\2713';
    position:absolute;
    bottom:4px;
    right:4px;
    background:var(--primary);
    color:#fff;
    width:20px;
    height:20px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:12px;
    font-weight:700;
  }

  .theme-purple {
    background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
  }

  .theme-ocean {
    background:linear-gradient(135deg,#3b82f6 0%,#06b6d4 100%);
  }

  .theme-sunset {
    background:linear-gradient(135deg,#f59e0b 0%,#ef4444 100%);
  }

  .theme-petrol {
    background:linear-gradient(135deg,#0d9488 0%,#0f2b3c 100%);
  }

  .theme-rose {
    background:linear-gradient(135deg,#ec4899 0%,#8b5cf6 100%);
  }

  .theme-midnight {
    background:linear-gradient(135deg,#6366f1 0%,#1e293b 100%);
  }

  .theme-label {
    font-size:13px;
    color:var(--text-muted);
    text-align:center;
    margin-top:8px;
    font-weight:500;
  }

  /* Custom Logo */
  .logo-preview {
    max-width:160px;
    max-height:80px;
    object-fit:contain;
    border-radius:8px;
    border:1px solid var(--border);
    padding:8px;
    background:var(--bg-secondary);
  }

  .logo-upload-area {
    display:flex;
    align-items:center;
    gap:16px;
    flex-wrap:wrap;
  }

  .login-logo-img {
    width:280px;
    max-width:100%;
    height:auto;
    object-fit:contain;
    display:block;
    margin:0 auto;
  }

  .sidebar-logo-img {
    width:100%;
    max-width:230px;
    height:auto;
    object-fit:contain;
    display:block;
  }

  .lang-switcher-settings .lang-btn {
    display:inline-flex;
    align-items:center;
    gap:8px;
  }

  .lang-switcher-settings .lang-flag {
    width:24px;
    height:16px;
  }

  /* Language Switcher */
  .lang-switcher {
    display:flex;
    gap:4px;
    background:var(--bg-secondary);
    padding:4px;
    border-radius:10px;
  }

  .lang-btn {
    padding:6px 12px;
    border:none;
    border-radius:8px;
    font-size:13px;
    font-weight:600;
    cursor:pointer;
    transition:all .2s;
    background:transparent;
    color:var(--text-muted);
  }

  .lang-btn.active {
    background:var(--card);
    color:var(--text);
    box-shadow:var(--shadow-sm);
  }

  .lang-btn:hover:not(.active) {
    color:var(--text);
  }

  .lang-flag {
    width:20px;
    height:14px;
    border-radius:2px;
    vertical-align:middle;
    display:block;
  }

  /* Todo Items */
  .todo-item {
    display:flex;
    gap:16px;
    padding:16px;
    border:1px solid var(--border);
    border-radius:12px;
    margin-bottom:12px;
    transition:all .2s;
    background:var(--card);
  }

  .todo-item:hover {
    border-color:var(--primary);
    box-shadow:0 4px 12px rgba(0,0,0,.08);
  }

  .todo-item.done {
    opacity:.6;
  }

  .todo-checkbox {
    width:24px;
    height:24px;
    border:2px solid var(--border);
    border-radius:8px;
    cursor:pointer;
    transition:all .2s;
    flex-shrink:0;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:14px;
    margin-top:2px;
  }

  .todo-checkbox:hover {
    border-color:var(--primary);
  }

  .todo-checkbox.checked {
    background:var(--gradient);
    border-color:var(--primary);
    color:#fff;
  }

  .todo-content {
    flex:1;
    min-width:0;
  }

  .todo-header {
    display:flex;
    align-items:start;
    gap:8px;
    margin-bottom:8px;
  }

  .todo-text {
    flex:1;
    font-size:15px;
    font-weight:500;
    color:var(--text);
    line-height:1.5;
  }

  .todo-item.done .todo-text {
    text-decoration:line-through;
  }

  .todo-badges {
    display:flex;
    gap:8px;
    flex-wrap:wrap;
  }

  .todo-badge {
    display:inline-flex;
    align-items:center;
    gap:4px;
    padding:4px 10px;
    border-radius:8px;
    font-size:12px;
    font-weight:600;
  }

  .badge-category {
    background:var(--bg-secondary);
    color:var(--text);
  }

  .badge-priority {
    border:1px solid;
  }

  .badge-priority.low {
    background:#f0fdf4;
    color:#166534;
    border-color:#bbf7d0;
  }

  .badge-priority.medium {
    background:#fef3c7;
    color:#92400e;
    border-color:#fde047;
  }

  .badge-priority.high {
    background:#fee2e2;
    color:#991b1b;
    border-color:#fca5a5;
  }

  /* Due Date Badges */
  .badge-due { border:1px solid; font-size:12px; font-weight:600; padding:4px 10px; border-radius:8px; display:inline-flex; align-items:center; gap:4px; }
  .badge-due.overdue { background:#fee2e2; color:#991b1b; border-color:#fca5a5; }
  .badge-due.today { background:#fff7ed; color:#9a3412; border-color:#fdba74; }
  .badge-due.upcoming { background:#fef3c7; color:#92400e; border-color:#fde047; }
  .badge-due.later { background:#f0fdf4; color:#166534; border-color:#bbf7d0; }

  /* Kanban Board */
  .kanban-board { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; min-height:400px; }
  .kanban-column { background:var(--bg-secondary); border-radius:var(--radius-lg); padding:16px; min-height:200px; transition:background .2s, border .2s; border:2px solid transparent; }
  .kanban-column.drag-over { background:rgba(99,102,241,.08); border:2px dashed var(--primary); }
  .kanban-column-header { font-size:14px; font-weight:700; color:var(--text); margin-bottom:16px; padding-bottom:12px; border-bottom:2px solid var(--border); display:flex; justify-content:space-between; align-items:center; }
  .kanban-column-count { background:var(--card); padding:2px 8px; border-radius:8px; font-size:12px; color:var(--text-muted); }
  .kanban-card { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); padding:14px; margin-bottom:10px; cursor:grab; transition:all .2s; }
  .kanban-card:active { cursor:grabbing; }
  .kanban-card:hover { border-color:var(--primary); box-shadow:var(--shadow); }
  .kanban-card.dragging { opacity:.5; }
  .kanban-card-title { font-size:14px; font-weight:500; color:var(--text); margin-bottom:8px; }
  .kanban-card-badges { display:flex; gap:6px; flex-wrap:wrap; }
  @media (max-width:768px) { .kanban-board { grid-template-columns:1fr; } }

  /* Search Results */
  .search-results { position:absolute; top:100%; left:0; right:0; margin-top:4px; background:var(--card); border:1px solid var(--border); border-radius:var(--radius); box-shadow:var(--shadow-lg); max-height:400px; overflow-y:auto; z-index:100; display:none; }
  .search-results.active { display:block; }
  .search-result-item { padding:12px 16px; cursor:pointer; border-bottom:1px solid var(--border-light); transition:background .15s; }
  .search-result-item:hover { background:var(--bg-secondary); }
  .search-result-item:last-child { border-bottom:none; }
  .search-result-type { font-size:11px; font-weight:600; text-transform:uppercase; color:var(--text-light); margin-bottom:2px; }
  .search-result-title { font-size:14px; font-weight:500; color:var(--text); }
  .search-result-context { font-size:12px; color:var(--text-muted); margin-top:2px; }
  .search-no-results { padding:24px 16px; text-align:center; color:var(--text-muted); font-size:14px; }

  /* Activity Feed */
  .activity-item { display:flex; gap:12px; padding:12px 0; border-bottom:1px solid var(--border-light); align-items:start; }
  .activity-item:last-child { border-bottom:none; }
  .activity-icon { width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0; background:var(--bg-secondary); }
  .activity-text { font-size:14px; color:var(--text); line-height:1.5; }
  .activity-time { font-size:12px; color:var(--text-muted); margin-top:2px; }

  .todo-note {
    font-size:14px;
    color:var(--text-muted);
    line-height:1.5;
    margin-top:8px;
    padding-left:12px;
    border-left:3px solid var(--border);
    white-space:pre-line;
  }

  .todo-actions {
    display:flex;
    gap:6px;
    margin-top:8px;
  }

  .todo-action-btn {
    padding:6px 12px;
    background:transparent;
    border:1px solid var(--border);
    border-radius:8px;
    font-size:12px;
    font-weight:600;
    cursor:pointer;
    transition:all .2s;
    color:var(--text-muted);
  }

  .todo-action-btn:hover {
    background:var(--bg-secondary);
    color:var(--text);
    border-color:var(--primary);
  }

  .todo-action-btn.danger:hover {
    background:var(--danger);
    color:#fff;
    border-color:var(--danger);
  }

  .category-section {
    margin-bottom:32px;
  }

  .category-section-title {
    font-size:16px;
    font-weight:700;
    color:var(--text);
    margin-bottom:16px;
    padding-bottom:12px;
    border-bottom:2px solid var(--border);
    display:flex;
    align-items:center;
    gap:8px;
  }

  .category-icon {
    font-size:20px;
  }

  .empty-todos {
    text-align:center;
    padding:48px 24px;
    color:var(--text-muted);
  }

  .empty-todos-icon {
    font-size:48px;
    opacity:.3;
    margin-bottom:12px;
  }

  /* Responsive */
  @media (max-width: 1024px) {
    .sidebar {
      transform:translateX(-100%);
      transition:transform .3s;
    }

    .sidebar.active {
      transform:translateX(0);
    }

    .main-content {
      margin-left:0;
    }
  }

  @media (max-width: 640px) {
    .content-area {
      padding:20px;
    }

    .page-title {
      font-size:24px;
    }

    .projects-grid {
      grid-template-columns:1fr;
    }

    .stats-grid {
      grid-template-columns:1fr;
    }
  }

  /* === Dynamic Animations === */

  /* Staggered card entrance */
  @keyframes cardIn {
    from {opacity:0; transform:translateY(20px)}
    to {opacity:1; transform:translateY(0)}
  }

  .card, .stat-card, .project-card, .todo-item {
    animation:cardIn .4s cubic-bezier(.16,1,.3,1) both;
  }

  .card:nth-child(1) {animation-delay:.05s}
  .card:nth-child(2) {animation-delay:.1s}
  .card:nth-child(3) {animation-delay:.15s}
  .card:nth-child(4) {animation-delay:.2s}
  .card:nth-child(5) {animation-delay:.25s}

  .stat-card:nth-child(1) {animation-delay:.05s}
  .stat-card:nth-child(2) {animation-delay:.1s}
  .stat-card:nth-child(3) {animation-delay:.15s}
  .stat-card:nth-child(4) {animation-delay:.2s}

  .project-card:nth-child(1) {animation-delay:.05s}
  .project-card:nth-child(2) {animation-delay:.1s}
  .project-card:nth-child(3) {animation-delay:.15s}
  .project-card:nth-child(4) {animation-delay:.2s}
  .project-card:nth-child(5) {animation-delay:.25s}
  .project-card:nth-child(6) {animation-delay:.3s}

  .todo-item:nth-child(1) {animation-delay:.03s}
  .todo-item:nth-child(2) {animation-delay:.06s}
  .todo-item:nth-child(3) {animation-delay:.09s}
  .todo-item:nth-child(4) {animation-delay:.12s}
  .todo-item:nth-child(5) {animation-delay:.15s}
  .todo-item:nth-child(6) {animation-delay:.18s}
  .todo-item:nth-child(7) {animation-delay:.21s}
  .todo-item:nth-child(8) {animation-delay:.24s}
  .todo-item:nth-child(9) {animation-delay:.27s}
  .todo-item:nth-child(10) {animation-delay:.3s}

  /* Card hover lift */
  .card {
    transition:transform .25s cubic-bezier(.4,0,.2,1), box-shadow .25s cubic-bezier(.4,0,.2,1);
  }
  .card:hover {
    transform:translateY(-2px);
    box-shadow:0 8px 24px rgba(0,0,0,.08);
  }

  /* Todo checkbox pop */
  @keyframes checkPop {
    0% {transform:scale(1)}
    40% {transform:scale(1.3)}
    100% {transform:scale(1)}
  }
  .todo-checkbox.checked {
    animation:checkPop .3s cubic-bezier(.4,0,.2,1);
  }

  /* Page header slide in */
  @keyframes headerIn {
    from {opacity:0; transform:translateX(-20px)}
    to {opacity:1; transform:translateX(0)}
  }
  .page-header {
    animation:headerIn .5s cubic-bezier(.16,1,.3,1);
  }

  /* Stat value count-up shimmer */
  @keyframes shimmer {
    from {background-position:200% center}
    to {background-position:-200% center}
  }
  .stat-value {
    background:linear-gradient(90deg, var(--text) 40%, var(--primary) 50%, var(--text) 60%);
    background-size:200% auto;
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
    background-clip:text;
    animation:shimmer 2s ease-in-out;
  }

  /* Progress bar fill animation */
  @keyframes progressFill {
    from {width:0}
  }
  .progress-fill {
    animation:progressFill .8s cubic-bezier(.4,0,.2,1) .2s both;
  }

  /* Sidebar nav item hover slide */
  .nav-item::after {
    content:'';
    position:absolute;
    left:0;
    top:0;
    bottom:0;
    width:3px;
    background:var(--gradient);
    border-radius:0 3px 3px 0;
    transform:scaleY(0);
    transition:transform .2s cubic-bezier(.4,0,.2,1);
  }
  .nav-item {position:relative}
  .nav-item:hover::after {
    transform:scaleY(1);
  }
  .nav-item.active::after {
    transform:scaleY(1);
  }

  /* Button press effect */
  .btn:active {
    transform:scale(.96);
    transition:transform .1s;
  }

  /* Smooth view transitions */
  .content-area > div[id] {
    animation:cardIn .35s cubic-bezier(.16,1,.3,1);
  }

  /* Priority badge pulse for high priority */
  @keyframes pulseBadge {
    0%, 100% {box-shadow:0 0 0 0 rgba(239,68,68,.3)}
    50% {box-shadow:0 0 0 4px rgba(239,68,68,0)}
  }
  .priority-high {
    animation:pulseBadge 2s ease-in-out infinite;
  }
</style>
</head>
<body>

<!-- Login Screen -->
<div class="login-container" id="loginScreen">
  <div class="login-orb login-orb-1"></div>
  <div class="login-orb login-orb-2"></div>

  <div class="login-box">
    <div class="login-logo">
      <h1 id="loginLogo"><img src="logo.png" alt="TaskFlow" class="login-logo-img"></h1>
      <p data-i18n="login.subtitle">Projekt & Aufgabenverwaltung</p>
    </div>

    <div class="form-group">
      <label class="form-label" data-i18n="login.username">Benutzername</label>
      <input type="text" class="form-input" id="loginUsername" data-i18n-placeholder="login.username_placeholder" placeholder="Benutzernamen eingeben">
    </div>

    <div class="form-group">
      <label class="form-label" data-i18n="login.password">Passwort</label>
      <input type="password" class="form-input" id="loginPassword" data-i18n-placeholder="login.password_placeholder" placeholder="Passwort eingeben">
    </div>

    <button class="btn btn-primary" onclick="login()">
      <span data-i18n="login.submit">Anmelden</span>
    </button>

    <div class="login-footer">
      <a href="#" onclick="showRegister();return false" data-i18n="login.create_account_link">Neuen Account erstellen &rarr;</a>
    </div>
    <div class="copyright">TaskFlow &copy; 2026 Florian Hesse &middot; <a href="https://comnic-it.de" target="_blank" style="color:inherit;text-decoration:underline">comnic-it.de</a></div>
  </div>
</div>

<!-- Register Screen -->
<div class="login-container" id="registerScreen" style="display:none">
  <div class="login-orb login-orb-1"></div>
  <div class="login-orb login-orb-2"></div>

  <div class="login-box">
    <div class="login-logo">
      <h1 id="registerLogo"><img src="logo.png" alt="TaskFlow" class="login-logo-img"></h1>
      <p data-i18n="register.subtitle">Neuen Account erstellen</p>
    </div>

    <div class="form-group">
      <label class="form-label" data-i18n="register.name">Name</label>
      <input type="text" class="form-input" id="regName" data-i18n-placeholder="register.name_placeholder" placeholder="Vollständiger Name">
    </div>

    <div class="form-group">
      <label class="form-label" data-i18n="register.username">Benutzername</label>
      <input type="text" class="form-input" id="regUsername" data-i18n-placeholder="register.username_placeholder" placeholder="Benutzernamen wählen">
    </div>

    <div class="form-group">
      <label class="form-label" data-i18n="register.password">Passwort</label>
      <input type="password" class="form-input" id="regPassword" data-i18n-placeholder="register.password_placeholder" placeholder="Sicheres Passwort wählen">
    </div>

    <button class="btn btn-primary" onclick="register()" data-i18n="register.submit">Account erstellen</button>

    <div class="login-footer">
      <a href="#" onclick="showLogin();return false" data-i18n="register.back_link">&larr; Zurück zum Login</a>
    </div>
    <div class="copyright">TaskFlow &copy; 2026 Florian Hesse &middot; <a href="https://comnic-it.de" target="_blank" style="color:inherit;text-decoration:underline">comnic-it.de</a></div>
  </div>
</div>

<!-- Main App -->
<div class="app-container" id="appContainer">
  <!-- Sidebar -->
  <div class="sidebar">
    <div class="sidebar-header">
      <div class="sidebar-logo" id="sidebarLogo"><img src="logo.png" alt="TaskFlow" class="sidebar-logo-img"></div>
    </div>

    <div class="sidebar-nav">
      <div class="nav-section">
        <div class="nav-section-title" data-i18n="nav.main_menu">Hauptmenü</div>
        <div class="nav-item active" onclick="showDashboard()">
          <span class="nav-icon">📊</span>
          <span data-i18n="nav.dashboard">Dashboard</span>
        </div>
        <div class="nav-item" onclick="showProjects()">
          <span class="nav-icon">📁</span>
          <span data-i18n="nav.projects">Projekte</span>
          <span class="nav-badge" id="projectCount">0</span>
        </div>
      </div>

      <div class="nav-section">
        <div class="nav-section-title" data-i18n="nav.management">Verwaltung</div>
        <div class="nav-item" onclick="showUsers()">
          <span class="nav-icon">👥</span>
          <span data-i18n="nav.users">Benutzer</span>
        </div>
        <div class="nav-item" onclick="showSettings()">
          <span class="nav-icon">⚙️</span>
          <span data-i18n="nav.settings">Einstellungen</span>
        </div>
      </div>
    </div>

    <div class="sidebar-footer">
      <div class="user-card">
        <div class="user-avatar" id="userAvatar">U</div>
        <div class="user-info">
          <div class="user-name" id="userName">User</div>
          <div class="user-role">Admin</div>
        </div>
        <button class="user-menu-btn" onclick="logout()" data-i18n-title="nav.logout_title" title="Abmelden">⎋</button>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="top-bar">
      <div class="search-bar">
        <span class="search-icon">🔍</span>
        <input type="text" class="search-input" id="globalSearchInput" oninput="onSearchInput()" onfocus="onSearchInput()" data-i18n-placeholder="topbar.search_placeholder" placeholder="Projekte oder Aufgaben suchen...">
        <div class="search-results" id="searchResults"></div>
      </div>

      <div class="top-actions">
        <div class="lang-switcher">
          <button class="lang-btn active" onclick="changeLanguage('de')" id="langBtnDe"><img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 5 3'%3E%3Crect width='5' height='1' fill='%23000'/%3E%3Crect y='1' width='5' height='1' fill='%23D00'/%3E%3Crect y='2' width='5' height='1' fill='%23FFCE00'/%3E%3C/svg%3E" alt="DE" class="lang-flag"></button>
          <button class="lang-btn" onclick="changeLanguage('en')" id="langBtnEn"><img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 7410 3900'%3E%3Crect width='7410' height='3900' fill='%23b22234'/%3E%3Cpath d='M0,450H7410m0,600H0m0,600H7410m0,600H0m0,600H7410m0,600H0' stroke='%23fff' stroke-width='300'/%3E%3Crect width='2964' height='2100' fill='%233c3b6e'/%3E%3Cg fill='%23fff'%3E%3Cg id='s18'%3E%3Cg id='s9'%3E%3Cg id='s5'%3E%3Cg id='s4'%3E%3Cpath id='s' d='M247,90 317.534,307.082 132.873,172.918h228.254L176.466,307.082z'/%3E%3Cuse href='%23s' y='420'/%3E%3Cuse href='%23s' y='840'/%3E%3Cuse href='%23s' y='1260'/%3E%3C/g%3E%3Cuse href='%23s' y='1680'/%3E%3C/g%3E%3Cuse href='%23s4' x='494' y='210'/%3E%3C/g%3E%3Cuse href='%23s9' x='494'/%3E%3C/g%3E%3Cuse href='%23s18' x='988'/%3E%3Cuse href='%23s9' x='1976'/%3E%3Cuse href='%23s5' x='2470'/%3E%3C/g%3E%3C/svg%3E" alt="EN" class="lang-flag"></button>
        </div>
        <button class="btn btn-secondary btn-sm" id="darkModeToggle" onclick="toggleDarkMode()" title="Dark Mode" style="font-size:16px;padding:6px 10px;line-height:1">🌙</button>
        <button class="btn btn-secondary btn-sm" onclick="openFeedback()" title="Bug Report / Feature Request" style="font-size:16px;padding:6px 10px;line-height:1">🐛</button>
        <button class="btn btn-primary btn-sm" onclick="openNewProjectModal()" data-i18n="topbar.new_project">+ Neues Projekt</button>
      </div>
    </div>

    <div class="content-area">
      <!-- Dashboard View -->
      <div id="dashboardView">
        <div class="page-header">
          <h1 class="page-title" data-i18n="dashboard.title">Dashboard</h1>
          <p class="page-subtitle" data-i18n="dashboard.subtitle">Überblick über alle Projekte und Aufgaben</p>
        </div>

        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-header">
              <span class="stat-label" data-i18n="dashboard.stat_projects">Projekte</span>
              <div class="stat-icon blue">📁</div>
            </div>
            <div class="stat-value" id="statProjects">0</div>
            <div class="stat-change up" data-i18n="dashboard.stat_active">Aktiv</div>
          </div>

          <div class="stat-card">
            <div class="stat-header">
              <span class="stat-label" data-i18n="dashboard.stat_open">Offene Aufgaben</span>
              <div class="stat-icon orange">⏰</div>
            </div>
            <div class="stat-value" id="statOpen">0</div>
            <div class="stat-change" data-i18n="dashboard.stat_todo">Zu erledigen</div>
          </div>

          <div class="stat-card">
            <div class="stat-header">
              <span class="stat-label" data-i18n="dashboard.stat_done">Erledigt</span>
              <div class="stat-icon green">✓</div>
            </div>
            <div class="stat-value" id="statDone">0</div>
            <div class="stat-change up" data-i18n="dashboard.stat_this_week">Diese Woche</div>
          </div>

          <div class="stat-card">
            <div class="stat-header">
              <span class="stat-label" data-i18n="dashboard.stat_progress">Fortschritt</span>
              <div class="stat-icon purple">📈</div>
            </div>
            <div class="stat-value" id="statProgress">0%</div>
            <div class="stat-change" data-i18n="dashboard.stat_total">Gesamt</div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h3 class="card-title" data-i18n="dashboard.current_projects">Aktuelle Projekte</h3>
          </div>
          <div class="projects-grid" id="dashboardProjects"></div>
        </div>

        <div class="card">
          <div class="card-header">
            <h3 class="card-title" data-i18n="activity.title">Letzte Aktivitäten</h3>
          </div>
          <div id="activityFeed"></div>
        </div>
      </div>

      <!-- Projects View -->
      <div id="projectsView" style="display:none">
        <div class="page-header">
          <h1 class="page-title" data-i18n="projects.title">Meine Projekte</h1>
          <p class="page-subtitle" data-i18n="projects.subtitle">Verwalte alle deine Projekte an einem Ort</p>
        </div>

        <div class="projects-grid" id="projectsList"></div>
      </div>

      <!-- Users View -->
      <div id="usersView" style="display:none">
        <div class="page-header">
          <div style="display:flex;align-items:start;justify-content:space-between;gap:20px;flex-wrap:wrap">
            <div>
              <h1 class="page-title" data-i18n="users.title">Benutzerverwaltung</h1>
              <p class="page-subtitle" data-i18n="users.subtitle">Alle registrierten Benutzer</p>
            </div>
            <button class="btn btn-primary" onclick="openCreateUserForm()"><span data-i18n="users.create_btn">+ Neuer Benutzer</span></button>
          </div>
        </div>

        <div class="card" id="createUserCard" style="display:none">
          <h3 class="card-title" data-i18n="users.create_title">Neuer Benutzer</h3>
          <div class="form-group">
            <label class="form-label" data-i18n="register.name">Name</label>
            <input type="text" class="form-input" id="newUserName" data-i18n-placeholder="register.name_placeholder" placeholder="Vollständiger Name">
          </div>
          <div class="form-group">
            <label class="form-label" data-i18n="register.username">Benutzername</label>
            <input type="text" class="form-input" id="newUserUsername" data-i18n-placeholder="register.username_placeholder" placeholder="Benutzernamen wählen">
          </div>
          <div class="form-group">
            <label class="form-label" data-i18n="register.password">Passwort</label>
            <input type="password" class="form-input" id="newUserPassword" data-i18n-placeholder="register.password_placeholder" placeholder="Sicheres Passwort wählen">
          </div>
          <div style="display:flex;gap:12px">
            <button class="btn btn-primary" onclick="createUser()"><span data-i18n="users.create_submit">Benutzer erstellen</span></button>
            <button class="btn btn-ghost" onclick="closeCreateUserForm()"><span data-i18n="modal.cancel">Abbrechen</span></button>
          </div>
        </div>

        <div class="card">
          <div id="usersList"></div>
        </div>
      </div>

      <!-- Project Detail View -->
      <div id="projectDetailView" style="display:none">
        <div style="margin-bottom:24px">
          <button class="btn btn-ghost btn-sm" onclick="backToProjects()" data-i18n="project_detail.back">&larr; Zurück zu Projekten</button>
        </div>

        <div class="page-header">
          <div style="display:flex;align-items:start;justify-content:space-between;gap:20px;flex-wrap:wrap">
            <div style="flex:1">
              <h1 class="page-title" id="projectDetailTitle">Projekt</h1>
              <p class="page-subtitle" id="projectDetailDesc" style="white-space:pre-line">Beschreibung</p>
            </div>
            <div style="display:flex;gap:12px;align-items:center">
              <div style="display:flex;gap:4px;background:var(--bg-secondary);padding:4px;border-radius:10px">
                <button class="btn btn-sm" id="listViewBtn" onclick="switchProjectView('list')" style="background:var(--card);box-shadow:var(--shadow-sm)">📋 <span data-i18n="project_detail.list_view">Liste</span></button>
                <button class="btn btn-ghost btn-sm" id="kanbanViewBtn" onclick="switchProjectView('kanban')">📊 <span data-i18n="project_detail.kanban_view">Kanban</span></button>
              </div>
              <button class="btn btn-ghost btn-sm" onclick="editProject()"><span>✏️</span> <span data-i18n="project_detail.edit">Bearbeiten</span></button>
              <button class="btn btn-danger btn-sm" onclick="deleteCurrentProject()"><span>🗑️</span> <span data-i18n="project_detail.delete">Löschen</span></button>
            </div>
          </div>
        </div>

        <div class="stats-grid" style="grid-template-columns:repeat(auto-fit,minmax(200px,1fr))">
          <div class="stat-card">
            <div class="stat-header">
              <span class="stat-label" data-i18n="project_detail.stat_total">Gesamt</span>
              <div class="stat-icon blue">📋</div>
            </div>
            <div class="stat-value" id="projectStatTotal">0</div>
          </div>
          <div class="stat-card">
            <div class="stat-header">
              <span class="stat-label" data-i18n="project_detail.stat_open">Offen</span>
              <div class="stat-icon orange">⏰</div>
            </div>
            <div class="stat-value" id="projectStatOpen">0</div>
          </div>
          <div class="stat-card">
            <div class="stat-header">
              <span class="stat-label" data-i18n="project_detail.stat_done">Erledigt</span>
              <div class="stat-icon green">✓</div>
            </div>
            <div class="stat-value" id="projectStatDone">0</div>
          </div>
          <div class="stat-card">
            <div class="stat-header">
              <span class="stat-label" data-i18n="project_detail.stat_progress">Fortschritt</span>
              <div class="stat-icon purple">📈</div>
            </div>
            <div class="stat-value" id="projectStatProgress">0%</div>
          </div>
        </div>

        <button class="btn btn-primary" id="newTodoToggleBtn" onclick="toggleNewTodoForm()" data-i18n="todos.add_button" style="margin-bottom:16px">+ Aufgabe hinzufügen</button>

        <div class="card" id="newTodoFormCard" style="display:none">
          <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
            <h3 class="card-title" data-i18n="todos.new_title">Neue Aufgabe</h3>
            <button class="btn btn-ghost btn-sm" onclick="toggleNewTodoForm()" style="font-size:18px">&times;</button>
          </div>

          <div style="display:grid;gap:16px">
            <div>
              <label class="form-label" data-i18n="todos.task_label">Aufgabe</label>
              <input type="text" class="form-input" id="newTodoText" data-i18n-placeholder="todos.task_placeholder" placeholder="z.B. API Endpoints implementieren">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px">
              <div>
                <label class="form-label" data-i18n="todos.category">Kategorie</label>
                <select class="form-input" id="newTodoCategory">
                  <option value="Development">💻 Dev</option>
                  <option value="Design">🎨 Design</option>
                  <option value="Content">📝 Content</option>
                  <option value="Testing">🧪 Test</option>
                  <option value="Meeting">👥 Meeting</option>
                  <option value="Other" data-i18n="todos.cat_other">📌 Sonstiges</option>
                </select>
              </div>
              <div>
                <label class="form-label" data-i18n="todos.priority">Priorität</label>
                <select class="form-input" id="newTodoPriority">
                  <option value="low" data-i18n="todos.priority_low">Niedrig</option>
                  <option value="medium" selected data-i18n="todos.priority_medium">Normal</option>
                  <option value="high" data-i18n="todos.priority_high">Hoch</option>
                </select>
              </div>
              <div>
                <label class="form-label" data-i18n="todos.due_date">Fälligkeitsdatum</label>
                <input type="date" class="form-input" id="newTodoDueDate">
              </div>
            </div>

            <div>
              <label class="form-label" data-i18n="todos.note_label">Notiz (optional)</label>
              <textarea class="form-input" id="newTodoNote" data-i18n-placeholder="todos.note_placeholder" placeholder="Weitere Details oder Hinweise..." style="min-height:80px;resize:vertical"></textarea>
            </div>

            <button class="btn btn-primary" onclick="addTodoToProject()" style="width:auto" data-i18n="todos.add_button">+ Aufgabe hinzufügen</button>
          </div>
        </div>

        <div class="card">
          <div class="card-header" style="align-items:start">
            <h3 class="card-title" data-i18n="todos.title">Aufgaben</h3>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
              <select class="form-input" id="filterCategory" onchange="renderProjectTodos()" style="width:auto;padding:8px 12px;font-size:13px">
                <option value="all" data-i18n="todos.filter_all_categories">Alle Kategorien</option>
                <option value="Development">💻 Dev</option>
                <option value="Design">🎨 Design</option>
                <option value="Content">📝 Content</option>
                <option value="Testing">🧪 Test</option>
                <option value="Meeting">👥 Meeting</option>
                <option value="Other" data-i18n="todos.cat_other">📌 Sonstiges</option>
              </select>
              <select class="form-input" id="filterStatus" onchange="renderProjectTodos()" style="width:auto;padding:8px 12px;font-size:13px">
                <option value="all" data-i18n="todos.filter_all_status">Alle Status</option>
                <option value="open" data-i18n="todos.filter_open">Offen</option>
                <option value="done" data-i18n="todos.filter_done">Erledigt</option>
              </select>
            </div>
          </div>

          <div style="display:flex;gap:8px;margin-bottom:20px;background:var(--bg-secondary);padding:6px;border-radius:12px">
            <button class="btn btn-sm" id="viewActiveBtn" onclick="switchTodoView('active')" style="flex:1;background:var(--card);box-shadow:var(--shadow-sm)"><span>📋</span> <span data-i18n="todos.view_active">Aktiv</span></button>
            <button class="btn btn-ghost btn-sm" id="viewArchiveBtn" onclick="switchTodoView('archive')" style="flex:1"><span>📦</span> <span data-i18n="todos.view_archive">Archiv</span></button>
          </div>

          <div id="todoListContainer"></div>
        </div>

        <div id="kanbanContainer" style="display:none"></div>
      </div>

      <!-- Settings View -->
      <div id="settingsView" style="display:none">
        <div class="page-header">
          <h1 class="page-title" data-i18n="settings.title">Einstellungen</h1>
          <p class="page-subtitle" data-i18n="settings.subtitle">Design & Daten</p>
        </div>

        <div class="card">
          <h3 class="card-title" style="margin-bottom:12px" data-i18n="settings.password_title">Passwort ändern</h3>
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:12px">
            <div>
              <label class="form-label" data-i18n="settings.password_current">Aktuelles Passwort</label>
              <input type="password" class="form-input" id="currentPassword" data-i18n-placeholder="settings.password_current_placeholder" placeholder="Aktuelles Passwort">
            </div>
            <div>
              <label class="form-label" data-i18n="settings.password_new">Neues Passwort</label>
              <input type="password" class="form-input" id="newPassword" data-i18n-placeholder="settings.password_new_placeholder" placeholder="Neues Passwort">
            </div>
            <div>
              <label class="form-label" data-i18n="settings.password_confirm">Bestätigen</label>
              <input type="password" class="form-input" id="confirmPassword" data-i18n-placeholder="settings.password_confirm_placeholder" placeholder="Wiederholen">
            </div>
          </div>
          <button class="btn btn-primary btn-sm" onclick="changePassword()"><span data-i18n="settings.password_btn">Passwort ändern</span></button>
        </div>

        <div class="card">
          <h3 class="card-title" style="margin-bottom:12px" data-i18n="settings.theme_title">Farbschema</h3>

          <div class="theme-selector">
            <div>
              <div class="theme-option theme-purple active" data-theme="purple" onclick="changeTheme('purple')"></div>
              <div class="theme-label">Purple Dream</div>
            </div>
            <div>
              <div class="theme-option theme-ocean" data-theme="ocean" onclick="changeTheme('ocean')"></div>
              <div class="theme-label">Ocean Blue</div>
            </div>
            <div>
              <div class="theme-option theme-sunset" data-theme="sunset" onclick="changeTheme('sunset')"></div>
              <div class="theme-label">Sunset</div>
            </div>
            <div>
              <div class="theme-option theme-petrol" data-theme="petrol" onclick="changeTheme('petrol')"></div>
              <div class="theme-label">Petrol</div>
            </div>
            <div>
              <div class="theme-option theme-rose" data-theme="rose" onclick="changeTheme('rose')"></div>
              <div class="theme-label">Rose Pink</div>
            </div>
            <div>
              <div class="theme-option theme-midnight" data-theme="midnight" onclick="changeTheme('midnight')"></div>
              <div class="theme-label">Midnight</div>
            </div>
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px">
          <div class="card" style="margin-bottom:0">
            <h3 class="card-title" style="margin-bottom:12px" data-i18n="settings.backup_title">Daten-Backup</h3>
            <div style="display:flex;gap:12px;flex-wrap:wrap">
              <button class="btn btn-primary" onclick="exportData()"><span>📤</span> <span data-i18n="settings.export_btn">Daten exportieren</span></button>
              <label class="btn btn-secondary" style="margin:0;cursor:pointer">
                <span>📥</span> <span data-i18n="settings.import_btn">Daten importieren</span>
                <input type="file" accept=".json" onchange="importData(event)" style="display:none">
              </label>
            </div>
          </div>

          <div class="card" style="margin-bottom:0">
            <h3 class="card-title" style="margin-bottom:12px" data-i18n="settings.update_title">Updates</h3>
            <p style="color:var(--text-muted);margin-bottom:12px">
              <span data-i18n="settings.update_current">Aktuelle Version:</span>
              <strong id="currentVersion">...</strong>
            </p>
            <div id="updateStatus" style="margin-bottom:12px;display:none"></div>
            <div style="display:flex;gap:12px;flex-wrap:wrap">
              <button class="btn btn-primary" id="checkUpdateBtn" onclick="checkForUpdate()"><span data-i18n="settings.update_check">Update prüfen</span></button>
              <button class="btn btn-secondary" id="installUpdateBtn" onclick="installUpdate()" style="display:none"><span data-i18n="settings.update_install">Update installieren</span></button>
            </div>
          </div>
        </div>

        <div class="card">
          <h3 class="card-title" style="margin-bottom:12px" data-i18n="settings.danger_title">Reset</h3>
          <p style="color:var(--text-muted);margin-bottom:12px" data-i18n="settings.danger_desc">Alle Daten unwiderruflich löschen</p>
          <button class="btn btn-danger" onclick="resetAllData()"><span>🗑️</span> <span data-i18n="settings.danger_btn">Alle Daten zurücksetzen</span></button>
        </div>
      </div>
    </div>

    <footer class="content-footer">
      <div class="copyright">TaskFlow &copy; 2026 Florian Hesse &middot; <a href="https://comnic-it.de" target="_blank" style="color:inherit;text-decoration:underline">comnic-it.de</a></div>
    </footer>
  </div>
</div>

<!-- New Project Modal -->
<div class="modal" id="newProjectModal">
  <div class="modal-content">
    <div class="modal-header">
      <h2 class="modal-title" data-i18n="modal.new_project">Neues Projekt</h2>
      <button class="modal-close" onclick="closeModal('newProjectModal')">×</button>
    </div>

    <div class="form-group">
      <label class="form-label" data-i18n="modal.project_name">Projektname</label>
      <input type="text" class="form-input" id="newProjectName" data-i18n-placeholder="modal.project_name_placeholder" placeholder="z.B. Website Redesign">
    </div>

    <div class="form-group">
      <label class="form-label" data-i18n="modal.project_desc">Beschreibung</label>
      <textarea class="form-input" id="newProjectDesc" data-i18n-placeholder="modal.project_desc_placeholder" placeholder="Kurze Beschreibung des Projekts" style="min-height:100px;resize:vertical"></textarea>
    </div>

    <div style="display:flex;gap:12px;margin-top:24px">
      <button class="btn btn-primary" onclick="createProject()" style="flex:1" data-i18n="modal.create">Projekt erstellen</button>
      <button class="btn btn-ghost" onclick="closeModal('newProjectModal')" data-i18n="modal.cancel">Abbrechen</button>
    </div>
  </div>
</div>

<!-- Edit Todo Modal -->
<div class="modal" id="editTodoModal">
  <div class="modal-content">
    <div class="modal-header">
      <h2 class="modal-title" data-i18n="todos.edit_title">Aufgabe bearbeiten</h2>
      <button class="modal-close" onclick="closeModal('editTodoModal')">×</button>
    </div>
    <input type="hidden" id="editTodoId">

    <div class="form-group">
      <label class="form-label" data-i18n="todos.task_label">Aufgabe</label>
      <input type="text" class="form-input" id="editTodoText" data-i18n-placeholder="todos.task_placeholder" placeholder="Aufgabe">
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px" class="form-group">
      <div>
        <label class="form-label" data-i18n="todos.category">Kategorie</label>
        <select class="form-input" id="editTodoCategory">
          <option value="Development">💻 Dev</option>
          <option value="Design">🎨 Design</option>
          <option value="Content">📝 Content</option>
          <option value="Testing">🧪 Test</option>
          <option value="Meeting">👥 Meeting</option>
          <option value="Other" data-i18n="todos.cat_other">📌 Sonstiges</option>
        </select>
      </div>
      <div>
        <label class="form-label" data-i18n="todos.priority">Priorität</label>
        <select class="form-input" id="editTodoPriority">
          <option value="low" data-i18n="todos.priority_low">Niedrig</option>
          <option value="medium" data-i18n="todos.priority_medium">Normal</option>
          <option value="high" data-i18n="todos.priority_high">Hoch</option>
        </select>
      </div>
      <div>
        <label class="form-label" data-i18n="todos.due_date">Fälligkeitsdatum</label>
        <input type="date" class="form-input" id="editTodoDueDate">
      </div>
    </div>

    <div class="form-group">
      <label class="form-label" data-i18n="todos.note_label">Notiz (optional)</label>
      <textarea class="form-input" id="editTodoNote" data-i18n-placeholder="todos.note_placeholder" placeholder="Weitere Details oder Hinweise..." style="min-height:80px;resize:vertical"></textarea>
    </div>

    <div style="display:flex;gap:12px;margin-top:24px">
      <button class="btn btn-primary" onclick="saveEditTodo()" style="flex:1" data-i18n="todos.edit_save">Speichern</button>
      <button class="btn btn-ghost" onclick="closeModal('editTodoModal')" data-i18n="modal.cancel">Abbrechen</button>
    </div>
  </div>
</div>

<script src="app.js"></script>
</body>
</html>
