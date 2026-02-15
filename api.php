<?php
/**
 * TaskFlow v1.2 - API
 * Copyright (c) 2026 Florian Hesse
 * Fischer Str. 11, 16515 Oranienburg
 * https://comnic-it.de
 * Alle Rechte vorbehalten.
 */
// Error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Start session
session_start();

// i18n - Determine language
$lang = $_GET['lang'] ?? 'de';
if (!in_array($lang, ['de', 'en'])) {
    $lang = 'de';
}

$messages = [
    'de' => [
        'login_required' => 'Benutzername und Passwort erforderlich',
        'login_success' => 'Login erfolgreich',
        'login_failed' => 'Falsche Anmeldedaten',
        'register_required' => 'Alle Felder erforderlich',
        'register_exists' => 'Benutzername bereits vergeben',
        'register_success' => 'Account erfolgreich erstellt',
        'logout_success' => 'Logout erfolgreich',
        'not_logged_in' => 'Nicht angemeldet',
        'project_name_required' => 'Projektname erforderlich',
        'project_created' => 'Projekt erstellt',
        'project_updated' => 'Projekt aktualisiert',
        'project_not_found' => 'Projekt nicht gefunden',
        'project_deleted' => 'Projekt gelöscht',
        'todo_text_required' => 'Aufgabentext erforderlich',
        'todo_added' => 'Aufgabe hinzugefügt',
        'todo_updated' => 'Aufgabe aktualisiert',
        'todo_not_found' => 'Aufgabe nicht gefunden',
        'todo_deleted' => 'Aufgabe gelöscht',
        'import_invalid' => 'Ungültiges Datenformat',
        'import_success' => 'Daten importiert',
        'invalid_action' => 'Ungültige Aktion',
        'password_required' => 'Aktuelles und neues Passwort erforderlich',
        'password_wrong' => 'Aktuelles Passwort ist falsch',
        'password_changed' => 'Passwort erfolgreich geändert',
        'user_created' => 'Benutzer erfolgreich erstellt',
        'user_deleted' => 'Benutzer gelöscht',
        'user_delete_self' => 'Du kannst dich nicht selbst löschen',
        'user_not_found' => 'Benutzer nicht gefunden',
        'no_permission' => 'Keine Berechtigung',
        'role_updated' => 'Rolle geändert',
        'last_admin' => 'Letzter Admin kann nicht geändert werden',
        'update_no_repo' => 'Kein Repository konfiguriert',
        'update_check_failed' => 'Update-Prüfung fehlgeschlagen',
        'update_git_not_found' => 'Git ist nicht installiert',
        'update_no_git' => 'Kein Git-Repository vorhanden',
        'update_success' => 'Update erfolgreich installiert',
        'update_failed' => 'Update fehlgeschlagen',
        'member_added' => 'Mitglied hinzugefügt',
        'member_removed' => 'Mitglied entfernt',
        'member_role_updated' => 'Mitgliedsrolle geändert',
        'member_already_exists' => 'Benutzer ist bereits Mitglied',
        'member_not_found' => 'Mitglied nicht gefunden',
        'cannot_remove_owner' => 'Eigentümer kann nicht entfernt werden',
        'project_restored' => 'Projekt wiederhergestellt',
    ],
    'en' => [
        'login_required' => 'Username and password required',
        'login_success' => 'Login successful',
        'login_failed' => 'Invalid credentials',
        'register_required' => 'All fields required',
        'register_exists' => 'Username already taken',
        'register_success' => 'Account created successfully',
        'logout_success' => 'Logout successful',
        'not_logged_in' => 'Not logged in',
        'project_name_required' => 'Project name required',
        'project_created' => 'Project created',
        'project_updated' => 'Project updated',
        'project_not_found' => 'Project not found',
        'project_deleted' => 'Project deleted',
        'todo_text_required' => 'Task text required',
        'todo_added' => 'Task added',
        'todo_updated' => 'Task updated',
        'todo_not_found' => 'Task not found',
        'todo_deleted' => 'Task deleted',
        'import_invalid' => 'Invalid data format',
        'import_success' => 'Data imported',
        'invalid_action' => 'Invalid action',
        'password_required' => 'Current and new password required',
        'password_wrong' => 'Current password is incorrect',
        'password_changed' => 'Password changed successfully',
        'user_created' => 'User created successfully',
        'user_deleted' => 'User deleted',
        'user_delete_self' => 'You cannot delete yourself',
        'user_not_found' => 'User not found',
        'no_permission' => 'No permission',
        'role_updated' => 'Role changed',
        'last_admin' => 'Last admin cannot be changed',
        'update_no_repo' => 'No repository configured',
        'update_check_failed' => 'Update check failed',
        'update_git_not_found' => 'Git is not installed',
        'update_no_git' => 'No git repository found',
        'update_success' => 'Update installed successfully',
        'update_failed' => 'Update failed',
        'member_added' => 'Member added',
        'member_removed' => 'Member removed',
        'member_role_updated' => 'Member role updated',
        'member_already_exists' => 'User is already a member',
        'member_not_found' => 'Member not found',
        'cannot_remove_owner' => 'Owner cannot be removed',
        'project_restored' => 'Project restored',
    ],
];

function msg($key) {
    global $lang, $messages;
    return $messages[$lang][$key] ?? $key;
}

// Ensure data directory exists
$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

// File paths
$usersFile = $dataDir . '/users.json';
$projectsFile = $dataDir . '/projects.json';
$activityFile = $dataDir . '/activity.json';
$notificationsFile = $dataDir . '/notifications.json';

// Check if installation is needed
if (!file_exists($usersFile)) {
    echo json_encode(['success' => false, 'message' => 'Not installed. Please run install.php']);
    exit;
}

if (!file_exists($projectsFile)) {
    file_put_contents($projectsFile, json_encode([], JSON_PRETTY_PRINT));
}

// Helper functions
function loadUsers() {
    global $usersFile;
    return json_decode(file_get_contents($usersFile), true);
}

function saveUsers($users) {
    global $usersFile;
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
}

function loadProjects() {
    global $projectsFile;
    return json_decode(file_get_contents($projectsFile), true);
}

function saveProjects($projects) {
    global $projectsFile;
    file_put_contents($projectsFile, json_encode($projects, JSON_PRETTY_PRINT));
}

// Activity Log
function loadActivity() {
    global $activityFile;
    if (!file_exists($activityFile)) return [];
    return json_decode(file_get_contents($activityFile), true) ?: [];
}

function saveActivity($activity) {
    global $activityFile;
    file_put_contents($activityFile, json_encode($activity, JSON_PRETTY_PRINT), LOCK_EX);
}

function logActivity($action, $details = []) {
    $activity = loadActivity();
    $entry = array_merge([
        'id' => count($activity) > 0 ? max(array_column($activity, 'id')) + 1 : 1,
        'timestamp' => date('c'),
        'userId' => $_SESSION['user']['id'] ?? 0,
        'userName' => $_SESSION['user']['name'] ?? '',
        'action' => $action
    ], $details);
    array_unshift($activity, $entry);
    $activity = array_slice($activity, 0, 200);
    saveActivity($activity);
}

// Notifications
function loadNotifications() {
    global $notificationsFile;
    if (!file_exists($notificationsFile)) return [];
    return json_decode(file_get_contents($notificationsFile), true) ?: [];
}

function saveNotifications($notifications) {
    global $notificationsFile;
    file_put_contents($notificationsFile, json_encode($notifications, JSON_PRETTY_PRINT), LOCK_EX);
}

function addNotification($userId, $type, $data) {
    $notifications = loadNotifications();
    $newId = count($notifications) > 0 ? max(array_column($notifications, 'id')) + 1 : 1;
    $notification = array_merge([
        'id' => $newId,
        'userId' => $userId,
        'type' => $type,
        'timestamp' => date('c'),
        'read' => false
    ], $data);
    $notifications[] = $notification;
    saveNotifications($notifications);
}

// ============================================================
// Data Migration System
// ============================================================
// Each migration has a version number and runs exactly once.
// After a git update, new migrations are picked up automatically.
// data/migration_version.json tracks which migrations have run.

function getMigrationVersion() {
    global $dataDir;
    $file = $dataDir . '/migration_version.json';
    if (!file_exists($file)) return 0;
    $data = json_decode(file_get_contents($file), true);
    return $data['version'] ?? 0;
}

function setMigrationVersion($version) {
    global $dataDir;
    $file = $dataDir . '/migration_version.json';
    file_put_contents($file, json_encode([
        'version' => $version,
        'migratedAt' => date('c')
    ], JSON_PRETTY_PRINT), LOCK_EX);
}

/**
 * Define all migrations here. Each key is a version number.
 * Migrations run in order, only if current version < key.
 * Each migration receives no arguments and must load/save data itself.
 */
function getDataMigrations() {
    return [
        // v1: Add members array to projects (owner = createdBy)
        1 => function() {
            $projects = loadProjects();
            $changed = false;
            foreach ($projects as &$p) {
                if (!isset($p['members'])) {
                    $p['members'] = [
                        ['userId' => $p['createdBy'], 'role' => 'owner', 'addedAt' => $p['createdAt'] ?? date('c')]
                    ];
                    $changed = true;
                }
            }
            if ($changed) saveProjects($projects);
        },

        // v2: Ensure every project member has addedAt field
        2 => function() {
            $projects = loadProjects();
            $changed = false;
            foreach ($projects as &$p) {
                if (!isset($p['members'])) continue;
                foreach ($p['members'] as &$m) {
                    if (!isset($m['addedAt'])) {
                        $m['addedAt'] = $p['createdAt'] ?? date('c');
                        $changed = true;
                    }
                }
            }
            if ($changed) saveProjects($projects);
        },

        // v3: Ensure every user has a role field (legacy installs may lack it)
        3 => function() {
            $users = loadUsers();
            $changed = false;
            foreach ($users as &$u) {
                if (!isset($u['role'])) {
                    $u['role'] = 'admin'; // legacy users default to admin
                    $changed = true;
                }
            }
            if ($changed) saveUsers($users);
        },

        // v4: Ensure every user has a preferences object
        4 => function() {
            $users = loadUsers();
            $changed = false;
            foreach ($users as &$u) {
                if (!isset($u['preferences'])) {
                    $u['preferences'] = [];
                    $changed = true;
                }
            }
            if ($changed) saveUsers($users);
        },
    ];
}

function runPendingMigrations() {
    $currentVersion = getMigrationVersion();
    $migrations = getDataMigrations();
    $latestVersion = empty($migrations) ? 0 : max(array_keys($migrations));

    if ($currentVersion >= $latestVersion) return; // nothing to do

    ksort($migrations);
    foreach ($migrations as $version => $migrationFn) {
        if ($version > $currentVersion) {
            $migrationFn();
            setMigrationVersion($version);
        }
    }
}

// Run migrations on every request (fast no-op if already current)
runPendingMigrations();

function response($success, $data = null, $message = '') {
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ]);
    exit;
}

function requireAdmin() {
    if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? 'admin') !== 'admin') {
        response(false, null, msg('no_permission'));
    }
}

// Get request data
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? '';

// Handle actions
switch ($action) {
    case 'login':
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';

        if (empty($username) || empty($password)) {
            response(false, null, msg('login_required'));
        }

        $users = loadUsers();
        $user = null;

        foreach ($users as $u) {
            if ($u['username'] === $username && password_verify($password, $u['password'])) {
                $user = $u;
                $user['role'] = $u['role'] ?? 'admin';
                unset($user['password']);
                break;
            }
        }

        if ($user) {
            $_SESSION['user'] = $user;
            logActivity('user_login');
            response(true, $user, msg('login_success'));
        } else {
            response(false, null, msg('login_failed'));
        }
        break;

    case 'register':
        $name = $input['name'] ?? '';
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';

        if (empty($name) || empty($username) || empty($password)) {
            response(false, null, msg('register_required'));
        }

        $users = loadUsers();

        foreach ($users as $u) {
            if ($u['username'] === $username) {
                response(false, null, msg('register_exists'));
            }
        }

        $newId = count($users) > 0 ? max(array_column($users, 'id')) + 1 : 1;

        $newUser = [
            'id' => $newId,
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'name' => $name,
            'role' => 'user',
            'createdAt' => date('c')
        ];

        $users[] = $newUser;
        saveUsers($users);

        unset($newUser['password']);
        response(true, $newUser, msg('register_success'));
        break;

    case 'logout':
        session_destroy();
        response(true, null, msg('logout_success'));
        break;

    case 'getSession':
        if (isset($_SESSION['user'])) {
            $sessionData = $_SESSION['user'];
            $sessionData['role'] = $sessionData['role'] ?? 'admin';
            $notifications = loadNotifications();
            $unread = array_filter($notifications, function($n) use ($sessionData) {
                return $n['userId'] == $sessionData['id'] && !$n['read'];
            });
            $sessionData['unreadNotifications'] = count($unread);
            response(true, $sessionData);
        } else {
            response(false, null, msg('not_logged_in'));
        }
        break;

    case 'getUsers':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }
        requireAdmin();

        $users = loadUsers();
        foreach ($users as &$u) {
            $u['role'] = $u['role'] ?? 'admin';
            unset($u['password']);
        }
        response(true, $users);
        break;

    case 'changePassword':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }

        $currentPassword = $input['currentPassword'] ?? '';
        $newPassword = $input['newPassword'] ?? '';

        if (empty($currentPassword) || empty($newPassword)) {
            response(false, null, msg('password_required'));
        }

        $users = loadUsers();
        $found = false;

        foreach ($users as &$u) {
            if ($u['id'] == $_SESSION['user']['id']) {
                if (!password_verify($currentPassword, $u['password'])) {
                    response(false, null, msg('password_wrong'));
                }
                $u['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
                $found = true;
                break;
            }
        }

        if ($found) {
            saveUsers($users);
            response(true, null, msg('password_changed'));
        } else {
            response(false, null, msg('not_logged_in'));
        }
        break;

    case 'createUser':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }
        requireAdmin();

        $name = $input['name'] ?? '';
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';
        $role = $input['role'] ?? 'user';
        if (!in_array($role, ['admin', 'user'])) $role = 'user';

        if (empty($name) || empty($username) || empty($password)) {
            response(false, null, msg('register_required'));
        }

        $users = loadUsers();

        foreach ($users as $u) {
            if ($u['username'] === $username) {
                response(false, null, msg('register_exists'));
            }
        }

        $newId = count($users) > 0 ? max(array_column($users, 'id')) + 1 : 1;
        $newUser = [
            'id' => $newId,
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'name' => $name,
            'role' => $role,
            'createdAt' => date('c')
        ];

        $users[] = $newUser;
        saveUsers($users);

        unset($newUser['password']);
        response(true, $newUser, msg('user_created'));
        break;

    case 'deleteUser':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }
        requireAdmin();

        $deleteId = $input['id'] ?? 0;

        if ($deleteId == $_SESSION['user']['id']) {
            response(false, null, msg('user_delete_self'));
        }

        $users = loadUsers();
        $originalCount = count($users);
        $users = array_values(array_filter($users, function($u) use ($deleteId) {
            return $u['id'] != $deleteId;
        }));

        if (count($users) < $originalCount) {
            saveUsers($users);
            response(true, null, msg('user_deleted'));
        } else {
            response(false, null, msg('user_not_found'));
        }
        break;

    case 'getProjects':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }

        $projects = loadProjects();

        // Purge projects deleted more than 30 days ago
        $purged = false;
        $projects = array_values(array_filter($projects, function($p) use (&$purged) {
            if (isset($p['deletedAt'])) {
                $deletedTime = strtotime($p['deletedAt']);
                if ($deletedTime && (time() - $deletedTime) > 30 * 86400) {
                    $purged = true;
                    return false; // permanently remove
                }
            }
            return true;
        }));
        if ($purged) saveProjects($projects);

        // Filter out soft-deleted projects for normal listing
        $projects = array_values(array_filter($projects, function($p) {
            return !isset($p['deletedAt']);
        }));

        $userId = $_SESSION['user']['id'];
        $userRole = $_SESSION['user']['role'] ?? 'user';

        // Admins see all projects, others only their own
        if ($userRole !== 'admin') {
            $projects = array_values(array_filter($projects, function($p) use ($userId) {
                if ($p['createdBy'] == $userId) return true;
                if (isset($p['members'])) {
                    foreach ($p['members'] as $m) {
                        if ($m['userId'] == $userId) return true;
                    }
                }
                return false;
            }));
        }

        // Enrich members with user names
        $allUsers = loadUsers();
        $userMap = [];
        foreach ($allUsers as $u) {
            $userMap[$u['id']] = $u['name'];
        }
        foreach ($projects as &$p) {
            if (isset($p['members'])) {
                foreach ($p['members'] as &$m) {
                    $m['userName'] = $userMap[$m['userId']] ?? ('User #' . $m['userId']);
                }
            }
        }

        response(true, $projects);
        break;

    case 'createProject':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }

        $name = $input['name'] ?? '';
        $desc = $input['desc'] ?? '';
        $color = $input['color'] ?? '';

        if (empty($name)) {
            response(false, null, msg('project_name_required'));
        }

        $projects = loadProjects();
        $newId = count($projects) > 0 ? max(array_column($projects, 'id')) + 1 : 1;

        $newProject = [
            'id' => $newId,
            'name' => $name,
            'desc' => $desc,
            'color' => $color,
            'createdBy' => $_SESSION['user']['id'],
            'createdAt' => date('c'),
            'members' => [
                ['userId' => $_SESSION['user']['id'], 'role' => 'owner', 'addedAt' => date('c')]
            ],
            'todos' => []
        ];

        $projects[] = $newProject;
        saveProjects($projects);
        logActivity('project_created', ['projectId' => $newId, 'projectName' => $name]);

        response(true, $newProject, msg('project_created'));
        break;

    case 'updateProject':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }

        $projectId = $input['id'] ?? 0;
        $name = $input['name'] ?? '';
        $desc = $input['desc'] ?? '';
        $color = $input['color'] ?? null;

        $projects = loadProjects();
        $found = false;

        foreach ($projects as &$p) {
            if ($p['id'] == $projectId) {
                if (!empty($name)) $p['name'] = $name;
                $p['desc'] = $desc;
                if ($color !== null) $p['color'] = $color;
                $found = true;
                break;
            }
        }

        if ($found) {
            saveProjects($projects);
            response(true, null, msg('project_updated'));
        } else {
            response(false, null, msg('project_not_found'));
        }
        break;

    case 'deleteProject':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }

        $projectId = $input['id'] ?? 0;
        $userId = $_SESSION['user']['id'];
        $userRole = $_SESSION['user']['role'] ?? 'user';
        $projects = loadProjects();
        $found = false;

        foreach ($projects as &$p) {
            if ($p['id'] == $projectId) {
                // Permission: only owner or admin can delete
                $isOwner = false;
                if (isset($p['members'])) {
                    foreach ($p['members'] as $m) {
                        if ($m['userId'] == $userId && $m['role'] === 'owner') {
                            $isOwner = true;
                            break;
                        }
                    }
                }
                if (!$isOwner && $userRole !== 'admin') {
                    response(false, null, msg('no_permission'));
                }

                // Soft-delete: mark with deletedAt instead of removing
                $p['deletedAt'] = date('c');
                $p['deletedBy'] = $userId;
                $found = true;
                logActivity('project_deleted', ['projectId' => $p['id'], 'projectName' => $p['name']]);
                break;
            }
        }

        if ($found) {
            saveProjects($projects);
            response(true, null, msg('project_deleted'));
        } else {
            response(false, null, msg('project_not_found'));
        }
        break;

    case 'addTodo':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }

        $projectId = $input['projectId'] ?? 0;
        $text = $input['text'] ?? '';
        $category = $input['category'] ?? 'Other';
        $priority = $input['priority'] ?? 'medium';
        $note = $input['note'] ?? '';
        $dueDate = $input['dueDate'] ?? null;

        if (empty($text)) {
            response(false, null, msg('todo_text_required'));
        }

        $projects = loadProjects();
        $found = false;

        foreach ($projects as &$p) {
            if ($p['id'] == $projectId) {
                $newTodoId = count($p['todos']) > 0 ? max(array_column($p['todos'], 'id')) + 1 : 1;

                $newTodo = [
                    'id' => $newTodoId,
                    'text' => $text,
                    'category' => $category,
                    'priority' => $priority,
                    'note' => $note,
                    'dueDate' => $dueDate,
                    'status' => 'todo',
                    'done' => false,
                    'archived' => false,
                    'createdAt' => date('c'),
                    'createdBy' => $_SESSION['user']['username'] ?? ''
                ];

                $p['todos'][] = $newTodo;
                $found = true;
                break;
            }
        }

        if ($found) {
            saveProjects($projects);
            logActivity('todo_created', ['projectId' => $projectId, 'projectName' => $p['name'] ?? '', 'todoText' => $text]);
            response(true, null, msg('todo_added'));
        } else {
            response(false, null, msg('project_not_found'));
        }
        break;

    case 'updateTodo':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }

        $projectId = $input['projectId'] ?? 0;
        $todoId = $input['todoId'] ?? 0;
        $updates = $input['updates'] ?? [];

        $projects = loadProjects();
        $found = false;

        foreach ($projects as &$p) {
            if ($p['id'] == $projectId) {
                foreach ($p['todos'] as &$t) {
                    if ($t['id'] == $todoId) {
                        foreach ($updates as $key => $value) {
                            $t[$key] = $value;
                        }
                        if (isset($updates['done'])) {
                            if ($updates['done']) {
                                $t['closedBy'] = $_SESSION['user']['username'] ?? '';
                                $t['closedAt'] = date('c');
                                logActivity('todo_completed', ['projectId' => $projectId, 'todoText' => $t['text'] ?? '']);
                            } else {
                                unset($t['closedBy'], $t['closedAt']);
                            }
                        }
                        $found = true;
                        break 2;
                    }
                }
            }
        }

        if ($found) {
            saveProjects($projects);
            response(true, null, msg('todo_updated'));
        } else {
            response(false, null, msg('todo_not_found'));
        }
        break;

    case 'deleteTodo':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }

        $projectId = $input['projectId'] ?? 0;
        $todoId = $input['todoId'] ?? 0;

        $projects = loadProjects();
        $found = false;
        $deletedTodoText = '';

        foreach ($projects as &$p) {
            if ($p['id'] == $projectId) {
                foreach ($p['todos'] as $td) {
                    if ($td['id'] == $todoId) { $deletedTodoText = $td['text']; break; }
                }
                $originalCount = count($p['todos']);
                $p['todos'] = array_values(array_filter($p['todos'], function($t) use ($todoId) {
                    return $t['id'] != $todoId;
                }));

                if (count($p['todos']) < $originalCount) {
                    $found = true;
                    break;
                }
            }
        }

        if ($found) {
            saveProjects($projects);
            logActivity('todo_deleted', ['projectId' => $projectId, 'todoText' => $deletedTodoText]);
            response(true, null, msg('todo_deleted'));
        } else {
            response(false, null, msg('todo_not_found'));
        }
        break;

    case 'exportData':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }

        $users = loadUsers();
        foreach ($users as &$u) {
            unset($u['password']);
        }

        $data = [
            'users' => $users,
            'projects' => loadProjects(),
            'exportedAt' => date('c')
        ];

        response(true, $data);
        break;

    case 'importData':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }

        $data = $input;

        if (!isset($data['users']) || !isset($data['projects'])) {
            response(false, null, msg('import_invalid'));
        }

        // Hash passwords if they're not already hashed
        foreach ($data['users'] as &$u) {
            if (!isset($u['password']) || strlen($u['password']) < 60) {
                $u['password'] = password_hash($u['password'] ?? 'admin', PASSWORD_DEFAULT);
            }
        }

        saveUsers($data['users']);
        saveProjects($data['projects']);

        response(true, null, msg('import_success'));
        break;

    case 'getVersion':
        $versionFile = __DIR__ . '/version.json';
        if (file_exists($versionFile)) {
            $version = json_decode(file_get_contents($versionFile), true);
            response(true, $version);
        } else {
            response(true, ['version' => '0.0.0', 'date' => '']);
        }
        break;

    case 'checkUpdate':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }

        $localVersion = json_decode(file_get_contents(__DIR__ . '/version.json'), true);
        $repoUrl = $localVersion['repo'] ?? '';

        if (empty($repoUrl)) {
            response(true, ['update_available' => false, 'local' => $localVersion['version'], 'remote' => $localVersion['version'], 'message' => msg('update_no_repo')]);
            break;
        }

        $rawUrl = str_replace('github.com', 'raw.githubusercontent.com', $repoUrl);
        $rawUrl = rtrim($rawUrl, '/') . '/main/version.json';

        $ctx = stream_context_create(['http' => ['timeout' => 10, 'header' => 'User-Agent: TaskFlow-Updater']]);
        $remoteJson = @file_get_contents($rawUrl, false, $ctx);

        if ($remoteJson === false) {
            response(false, null, msg('update_check_failed'));
            break;
        }

        $remoteVersion = json_decode($remoteJson, true);
        if (!$remoteVersion || !isset($remoteVersion['version'])) {
            response(false, null, msg('update_check_failed'));
            break;
        }

        $updateAvailable = version_compare($remoteVersion['version'], $localVersion['version'], '>');
        response(true, [
            'update_available' => $updateAvailable,
            'local' => $localVersion['version'],
            'remote' => $remoteVersion['version'],
            'remote_date' => $remoteVersion['date'] ?? ''
        ]);
        break;

    case 'doUpdate':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }

        $gitPath = 'git';
        $projectDir = __DIR__;

        // Prüfe ob git verfügbar ist
        $output = [];
        $returnCode = 0;
        exec("$gitPath --version 2>&1", $output, $returnCode);
        if ($returnCode !== 0) {
            response(false, null, msg('update_git_not_found'));
            break;
        }

        // Prüfe ob es ein Git-Repo ist
        if (!is_dir($projectDir . '/.git')) {
            response(false, null, msg('update_no_git'));
            break;
        }

        // Lokale Änderungen verwerfen und sauber updaten
        exec("cd " . escapeshellarg($projectDir) . " && $gitPath stash 2>&1");

        // Git pull ausführen
        $output = [];
        $returnCode = 0;
        exec("cd " . escapeshellarg($projectDir) . " && $gitPath pull origin main 2>&1", $output, $returnCode);

        if ($returnCode !== 0) {
            // Pull fehlgeschlagen - Stash wiederherstellen
            exec("cd " . escapeshellarg($projectDir) . " && $gitPath stash pop 2>&1");
            response(false, ['output' => implode("\n", $output)], msg('update_failed'));
            break;
        }

        // Stash pop versuchen, bei Konflikten sauber aufräumen
        $stashOutput = [];
        $stashReturn = 0;
        exec("cd " . escapeshellarg($projectDir) . " && $gitPath stash pop 2>&1", $stashOutput, $stashReturn);

        if ($stashReturn !== 0) {
            // Konflikte -> aufräumen: Merge abbrechen und Stash verwerfen
            exec("cd " . escapeshellarg($projectDir) . " && $gitPath checkout . 2>&1");
            exec("cd " . escapeshellarg($projectDir) . " && $gitPath stash drop 2>&1");
        }

        // Run data migrations after code update
        runPendingMigrations();

        // Neue Version lesen
        $newVersion = json_decode(file_get_contents($projectDir . '/version.json'), true);
        response(true, [
            'message' => msg('update_success'),
            'version' => $newVersion['version'] ?? '?',
            'output' => implode("\n", $output)
        ]);
        break;

    case 'reorderTodos':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }

        $projectId = $input['projectId'] ?? 0;
        $todoIds = $input['todoIds'] ?? [];

        $projects = loadProjects();
        $found = false;

        foreach ($projects as &$p) {
            if ($p['id'] == $projectId) {
                $todosById = [];
                foreach ($p['todos'] as $td) {
                    $todosById[$td['id']] = $td;
                }
                $reordered = [];
                foreach ($todoIds as $id) {
                    if (isset($todosById[$id])) {
                        $reordered[] = $todosById[$id];
                        unset($todosById[$id]);
                    }
                }
                // Append any remaining todos not in the reorder list
                foreach ($todosById as $td) {
                    $reordered[] = $td;
                }
                $p['todos'] = $reordered;
                $found = true;
                break;
            }
        }

        if ($found) {
            saveProjects($projects);
            response(true);
        } else {
            response(false, null, msg('project_not_found'));
        }
        break;

    case 'getActivity':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }
        $count = $input['count'] ?? 20;
        $activity = loadActivity();
        response(true, array_slice($activity, 0, (int)$count));
        break;

    case 'updateUserRole':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }
        requireAdmin();

        $userId = $input['id'] ?? 0;
        $newRole = $input['role'] ?? '';
        if (!in_array($newRole, ['admin', 'user'])) {
            response(false, null, msg('invalid_action'));
        }

        $users = loadUsers();

        // Prevent demoting the last admin
        if ($newRole === 'user') {
            $adminCount = 0;
            foreach ($users as $u) {
                if (($u['role'] ?? 'admin') === 'admin') $adminCount++;
            }
            $targetUser = null;
            foreach ($users as $u) {
                if ($u['id'] == $userId) { $targetUser = $u; break; }
            }
            if ($targetUser && ($targetUser['role'] ?? 'admin') === 'admin' && $adminCount <= 1) {
                response(false, null, msg('last_admin'));
            }
        }

        $found = false;
        foreach ($users as &$u) {
            if ($u['id'] == $userId) {
                $u['role'] = $newRole;
                $found = true;
                break;
            }
        }

        if ($found) {
            saveUsers($users);
            // Update session if changing own role
            if ($userId == $_SESSION['user']['id']) {
                $_SESSION['user']['role'] = $newRole;
            }
            response(true, null, msg('role_updated'));
        } else {
            response(false, null, msg('user_not_found'));
        }
        break;

    case 'addMember':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }

        $projectId = $input['projectId'] ?? 0;
        $memberUserId = $input['userId'] ?? 0;
        $memberRole = $input['role'] ?? 'editor';
        if (!in_array($memberRole, ['editor', 'viewer'])) $memberRole = 'editor';

        $projects = loadProjects();
        $found = false;

        foreach ($projects as &$p) {
            if ($p['id'] == $projectId) {
                // Check permission: only owner or admin can add members
                $isOwner = false;
                foreach ($p['members'] as $m) {
                    if ($m['userId'] == $_SESSION['user']['id'] && $m['role'] === 'owner') {
                        $isOwner = true;
                        break;
                    }
                }
                if (!$isOwner && ($_SESSION['user']['role'] ?? 'user') !== 'admin') {
                    response(false, null, msg('no_permission'));
                }

                // Check if already a member
                foreach ($p['members'] as $m) {
                    if ($m['userId'] == $memberUserId) {
                        response(false, null, msg('member_already_exists'));
                    }
                }

                $p['members'][] = [
                    'userId' => (int)$memberUserId,
                    'role' => $memberRole,
                    'addedAt' => date('c')
                ];
                $found = true;

                // Create notification for the added user
                addNotification((int)$memberUserId, 'project_added', [
                    'projectId' => $p['id'],
                    'projectName' => $p['name'],
                    'byUser' => $_SESSION['user']['name'] ?? ''
                ]);

                logActivity('member_added', ['projectId' => $p['id'], 'projectName' => $p['name'], 'memberUserId' => $memberUserId]);
                break;
            }
        }

        if ($found) {
            saveProjects($projects);
            response(true, null, msg('member_added'));
        } else {
            response(false, null, msg('project_not_found'));
        }
        break;

    case 'removeMember':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }

        $projectId = $input['projectId'] ?? 0;
        $memberUserId = $input['userId'] ?? 0;

        $projects = loadProjects();
        $found = false;

        foreach ($projects as &$p) {
            if ($p['id'] == $projectId) {
                // Check permission
                $isOwner = false;
                foreach ($p['members'] as $m) {
                    if ($m['userId'] == $_SESSION['user']['id'] && $m['role'] === 'owner') {
                        $isOwner = true;
                        break;
                    }
                }
                if (!$isOwner && ($_SESSION['user']['role'] ?? 'user') !== 'admin') {
                    response(false, null, msg('no_permission'));
                }

                // Cannot remove owner
                foreach ($p['members'] as $m) {
                    if ($m['userId'] == $memberUserId && $m['role'] === 'owner') {
                        response(false, null, msg('cannot_remove_owner'));
                    }
                }

                $originalCount = count($p['members']);
                $p['members'] = array_values(array_filter($p['members'], function($m) use ($memberUserId) {
                    return $m['userId'] != $memberUserId;
                }));

                if (count($p['members']) < $originalCount) {
                    $found = true;
                } else {
                    response(false, null, msg('member_not_found'));
                }
                break;
            }
        }

        if ($found) {
            saveProjects($projects);
            response(true, null, msg('member_removed'));
        } else {
            response(false, null, msg('project_not_found'));
        }
        break;

    case 'updateMemberRole':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }

        $projectId = $input['projectId'] ?? 0;
        $memberUserId = $input['userId'] ?? 0;
        $newRole = $input['role'] ?? '';
        if (!in_array($newRole, ['editor', 'viewer'])) {
            response(false, null, msg('invalid_action'));
        }

        $projects = loadProjects();
        $found = false;

        foreach ($projects as &$p) {
            if ($p['id'] == $projectId) {
                // Check permission
                $isOwner = false;
                foreach ($p['members'] as $m) {
                    if ($m['userId'] == $_SESSION['user']['id'] && $m['role'] === 'owner') {
                        $isOwner = true;
                        break;
                    }
                }
                if (!$isOwner && ($_SESSION['user']['role'] ?? 'user') !== 'admin') {
                    response(false, null, msg('no_permission'));
                }

                foreach ($p['members'] as &$m) {
                    if ($m['userId'] == $memberUserId && $m['role'] !== 'owner') {
                        $m['role'] = $newRole;
                        $found = true;
                        break;
                    }
                }
                break;
            }
        }

        if ($found) {
            saveProjects($projects);
            response(true, null, msg('member_role_updated'));
        } else {
            response(false, null, msg('member_not_found'));
        }
        break;

    case 'getNotifications':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }

        $notifications = loadNotifications();
        $userId = $_SESSION['user']['id'];
        $unread = array_values(array_filter($notifications, function($n) use ($userId) {
            return $n['userId'] == $userId && !$n['read'];
        }));

        response(true, $unread);
        break;

    case 'dismissNotifications':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }

        $notifications = loadNotifications();
        $userId = $_SESSION['user']['id'];
        $ids = $input['ids'] ?? [];

        foreach ($notifications as &$n) {
            if ($n['userId'] == $userId) {
                if (empty($ids) || in_array($n['id'], $ids)) {
                    $n['read'] = true;
                }
            }
        }

        saveNotifications($notifications);
        response(true);
        break;

    case 'getAllUsers':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }

        $users = loadUsers();
        $result = [];
        foreach ($users as $u) {
            $result[] = ['id' => $u['id'], 'name' => $u['name'], 'username' => $u['username']];
        }
        response(true, $result);
        break;

    case 'savePreferences':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }

        $prefs = $input['preferences'] ?? [];
        $allowed = ['theme', 'darkMode', 'lang'];
        $cleanPrefs = [];
        foreach ($allowed as $key) {
            if (isset($prefs[$key])) {
                $cleanPrefs[$key] = $prefs[$key];
            }
        }

        $users = loadUsers();
        foreach ($users as &$u) {
            if ($u['id'] == $_SESSION['user']['id']) {
                if (!isset($u['preferences'])) $u['preferences'] = [];
                $u['preferences'] = array_merge($u['preferences'], $cleanPrefs);
                break;
            }
        }
        saveUsers($users);
        response(true);
        break;

    case 'getPreferences':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }

        $users = loadUsers();
        $prefs = [];
        foreach ($users as $u) {
            if ($u['id'] == $_SESSION['user']['id']) {
                $prefs = $u['preferences'] ?? [];
                break;
            }
        }
        response(true, $prefs);
        break;

    case 'getDeletedProjects':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }

        $userId = $_SESSION['user']['id'];
        $userRole = $_SESSION['user']['role'] ?? 'user';
        $projects = loadProjects();
        $allUsers = loadUsers();
        $userMap = [];
        foreach ($allUsers as $u) {
            $userMap[$u['id']] = $u['name'];
        }

        $deleted = [];
        foreach ($projects as $p) {
            if (!isset($p['deletedAt'])) continue;

            // Admins see all, others only own projects (owner or createdBy)
            $isOwner = false;
            if (isset($p['members'])) {
                foreach ($p['members'] as $m) {
                    if ($m['userId'] == $userId && $m['role'] === 'owner') {
                        $isOwner = true;
                        break;
                    }
                }
            }
            if ($userRole !== 'admin' && !$isOwner && $p['createdBy'] != $userId) continue;

            $p['deletedByName'] = $userMap[$p['deletedBy'] ?? 0] ?? '?';
            $daysLeft = 30 - (int)floor((time() - strtotime($p['deletedAt'])) / 86400);
            $p['daysLeft'] = max(0, $daysLeft);
            $deleted[] = $p;
        }

        response(true, $deleted);
        break;

    case 'restoreProject':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }

        $projectId = $input['id'] ?? 0;
        $userId = $_SESSION['user']['id'];
        $userRole = $_SESSION['user']['role'] ?? 'user';
        $projects = loadProjects();
        $found = false;

        foreach ($projects as &$p) {
            if ($p['id'] == $projectId && isset($p['deletedAt'])) {
                // Permission: admin or owner
                $isOwner = false;
                if (isset($p['members'])) {
                    foreach ($p['members'] as $m) {
                        if ($m['userId'] == $userId && $m['role'] === 'owner') {
                            $isOwner = true;
                            break;
                        }
                    }
                }
                if ($userRole !== 'admin' && !$isOwner && $p['createdBy'] != $userId) {
                    response(false, null, msg('no_permission'));
                }

                unset($p['deletedAt']);
                unset($p['deletedBy']);
                $found = true;
                logActivity('project_restored', ['projectId' => $p['id'], 'projectName' => $p['name']]);
                break;
            }
        }

        if ($found) {
            saveProjects($projects);
            response(true, null, msg('project_restored'));
        } else {
            response(false, null, msg('project_not_found'));
        }
        break;

    case 'permanentDeleteProject':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }
        requireAdmin();

        $projectId = $input['id'] ?? 0;
        $projects = loadProjects();
        $originalCount = count($projects);

        $projects = array_values(array_filter($projects, function($p) use ($projectId) {
            return $p['id'] != $projectId;
        }));

        if (count($projects) < $originalCount) {
            saveProjects($projects);
            response(true, null, msg('project_deleted'));
        } else {
            response(false, null, msg('project_not_found'));
        }
        break;

    default:
        response(false, null, msg('invalid_action'));
}
?>
