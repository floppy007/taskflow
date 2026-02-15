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
        'update_no_repo' => 'Kein Repository konfiguriert',
        'update_check_failed' => 'Update-Prüfung fehlgeschlagen',
        'update_git_not_found' => 'Git ist nicht installiert',
        'update_no_git' => 'Kein Git-Repository vorhanden',
        'update_success' => 'Update erfolgreich installiert',
        'update_failed' => 'Update fehlgeschlagen',
        'member_added' => 'Mitglied hinzugefügt',
        'member_removed' => 'Mitglied entfernt',
        'member_updated' => 'Rolle aktualisiert',
        'member_exists' => 'Benutzer ist bereits Mitglied',
        'member_not_found' => 'Mitglied nicht gefunden',
        'no_permission' => 'Keine Berechtigung',
        'cannot_remove_owner' => 'Der Eigentümer kann nicht entfernt werden',
        'cannot_change_owner' => 'Die Eigentümer-Rolle kann nicht geändert werden',
        'admin_required' => 'Nur Administratoren können diese Aktion ausführen',
        'role_updated' => 'Rolle aktualisiert',
        'cannot_demote_self' => 'Du kannst dich nicht selbst herabstufen',
        'last_admin' => 'Der letzte Administrator kann nicht herabgestuft werden',
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
        'update_no_repo' => 'No repository configured',
        'update_check_failed' => 'Update check failed',
        'update_git_not_found' => 'Git is not installed',
        'update_no_git' => 'No git repository found',
        'update_success' => 'Update installed successfully',
        'update_failed' => 'Update failed',
        'member_added' => 'Member added',
        'member_removed' => 'Member removed',
        'member_updated' => 'Role updated',
        'member_exists' => 'User is already a member',
        'member_not_found' => 'Member not found',
        'no_permission' => 'No permission',
        'cannot_remove_owner' => 'The owner cannot be removed',
        'cannot_change_owner' => 'The owner role cannot be changed',
        'admin_required' => 'Only administrators can perform this action',
        'role_updated' => 'Role updated',
        'cannot_demote_self' => 'You cannot demote yourself',
        'last_admin' => 'The last administrator cannot be demoted',
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

// Check if installation is needed
if (!file_exists($usersFile)) {
    echo json_encode(['success' => false, 'message' => 'Not installed. Please run install.php']);
    exit;
}

if (!file_exists($projectsFile)) {
    file_put_contents($projectsFile, json_encode([], JSON_PRETTY_PRINT));
}

// Check write permissions on data files - try to fix automatically
foreach ([$usersFile, $projectsFile, $activityFile] as $f) {
    if (file_exists($f) && !is_writable($f)) {
        @chmod($f, 0666);
        if (!is_writable($f)) {
            $owner = function_exists('posix_getpwuid') ? (posix_getpwuid(fileowner($f))['name'] ?? '?') : fileowner($f);
            $webUser = function_exists('posix_getpwuid') ? (posix_getpwuid(posix_geteuid())['name'] ?? '?') : get_current_user();
            response(false, null, "Permission denied: " . basename($f) . " belongs to '$owner', web server runs as '$webUser'. Fix: chown www-data:www-data " . $dataDir . "/*.json");
        }
    }
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

// Migrate users: ensure all users have a role field
function migrateUserRoles(&$users) {
    $changed = false;
    foreach ($users as $i => &$u) {
        if (!isset($u['role'])) {
            // First user becomes admin, rest become user
            $u['role'] = ($i === 0) ? 'admin' : 'user';
            $changed = true;
        }
    }
    return $changed;
}

function isAdmin() {
    if (!isset($_SESSION['user']['id'])) return false;
    // Always check from file to avoid stale session
    $users = loadUsers();
    foreach ($users as $u) {
        if ($u['id'] == $_SESSION['user']['id']) {
            return ($u['role'] ?? 'user') === 'admin';
        }
    }
    return false;
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

// Permission helpers
function getProjectRole($project, $userId) {
    if (!isset($project['members']) || !is_array($project['members'])) {
        // Legacy: project without members array - creator is owner
        return ($project['createdBy'] ?? 0) == $userId ? 'owner' : null;
    }
    foreach ($project['members'] as $m) {
        if ($m['userId'] == $userId) return $m['role'];
    }
    return null;
}

function canManageMembers($project, $userId) {
    return getProjectRole($project, $userId) === 'owner';
}

function canEditProject($project, $userId) {
    return getProjectRole($project, $userId) === 'owner';
}

function canEditTodos($project, $userId) {
    $role = getProjectRole($project, $userId);
    return in_array($role, ['owner', 'editor']);
}

function canViewProject($project, $userId) {
    return getProjectRole($project, $userId) !== null;
}

// Migrate projects: ensure all projects have a members array
function migrateProjectMembers(&$projects) {
    $changed = false;
    foreach ($projects as &$p) {
        if (!isset($p['members']) || !is_array($p['members'])) {
            $p['members'] = [];
            if (!empty($p['createdBy'])) {
                $p['members'][] = ['userId' => (int)$p['createdBy'], 'role' => 'owner'];
            }
            $changed = true;
        }
    }
    return $changed;
}

function response($success, $data = null, $message = '') {
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ]);
    exit;
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
        if (migrateUserRoles($users)) {
            saveUsers($users);
        }
        $user = null;

        foreach ($users as $u) {
            if ($u['username'] === $username && password_verify($password, $u['password'])) {
                $user = $u;
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
            // Refresh role from file in case it was changed
            $users = loadUsers();
            if (migrateUserRoles($users)) {
                saveUsers($users);
            }
            foreach ($users as $u) {
                if ($u['id'] == $_SESSION['user']['id']) {
                    $_SESSION['user']['role'] = $u['role'] ?? 'user';
                    break;
                }
            }
            response(true, $_SESSION['user']);
        } else {
            response(false, null, msg('not_logged_in'));
        }
        break;

    case 'getUsers':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }

        $users = loadUsers();
        if (migrateUserRoles($users)) {
            saveUsers($users);
        }
        foreach ($users as &$u) {
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
        if (!isAdmin()) {
            response(false, null, msg('admin_required'));
        }

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
        $userRole = $input['role'] ?? 'user';
        if (!in_array($userRole, ['admin', 'user'])) $userRole = 'user';

        $newUser = [
            'id' => $newId,
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'name' => $name,
            'role' => $userRole,
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
        if (!isAdmin()) {
            response(false, null, msg('admin_required'));
        }

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

    case 'updateUserRole':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }
        if (!isAdmin()) {
            response(false, null, msg('admin_required'));
        }

        $targetId = $input['id'] ?? 0;
        $newRole = $input['role'] ?? '';

        if (!in_array($newRole, ['admin', 'user'])) {
            response(false, null, msg('invalid_action'));
        }

        // Cannot demote yourself
        if ($targetId == $_SESSION['user']['id'] && $newRole !== 'admin') {
            response(false, null, msg('cannot_demote_self'));
        }

        $users = loadUsers();
        migrateUserRoles($users);

        // Check: don't allow removing last admin
        if ($newRole === 'user') {
            $adminCount = 0;
            foreach ($users as $u) {
                if (($u['role'] ?? 'user') === 'admin') $adminCount++;
            }
            $targetCurrentRole = 'user';
            foreach ($users as $u) {
                if ($u['id'] == $targetId) { $targetCurrentRole = $u['role'] ?? 'user'; break; }
            }
            if ($targetCurrentRole === 'admin' && $adminCount <= 1) {
                response(false, null, msg('last_admin'));
            }
        }

        $found = false;
        foreach ($users as &$u) {
            if ($u['id'] == $targetId) {
                $u['role'] = $newRole;
                $found = true;
                break;
            }
        }

        if ($found) {
            saveUsers($users);
            response(true, null, msg('role_updated'));
        } else {
            response(false, null, msg('user_not_found'));
        }
        break;

    case 'getProjects':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }

        $projects = loadProjects();
        if (migrateProjectMembers($projects)) {
            saveProjects($projects);
        }

        $userId = $_SESSION['user']['id'];
        $filtered = array_values(array_filter($projects, function($p) use ($userId) {
            return canViewProject($p, $userId);
        }));
        response(true, $filtered);
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
                ['userId' => $_SESSION['user']['id'], 'role' => 'owner']
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
        migrateProjectMembers($projects);
        $found = false;

        foreach ($projects as &$p) {
            if ($p['id'] == $projectId) {
                if (!canEditProject($p, $_SESSION['user']['id'])) {
                    response(false, null, msg('no_permission'));
                }
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
        $projects = loadProjects();
        migrateProjectMembers($projects);

        $deletedName = '';
        foreach ($projects as $p) {
            if ($p['id'] == $projectId) {
                if (!canEditProject($p, $_SESSION['user']['id'])) {
                    response(false, null, msg('no_permission'));
                }
                $deletedName = $p['name'];
                break;
            }
        }

        $filtered = array_filter($projects, function($p) use ($projectId) {
            return $p['id'] != $projectId;
        });

        if (count($filtered) < count($projects)) {
            saveProjects(array_values($filtered));
            logActivity('project_deleted', ['projectId' => $projectId, 'projectName' => $deletedName]);
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
        migrateProjectMembers($projects);
        $found = false;

        foreach ($projects as &$p) {
            if ($p['id'] == $projectId) {
                if (!canEditTodos($p, $_SESSION['user']['id'])) {
                    response(false, null, msg('no_permission'));
                }
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
        migrateProjectMembers($projects);
        $found = false;

        foreach ($projects as &$p) {
            if ($p['id'] == $projectId) {
                if (!canEditTodos($p, $_SESSION['user']['id'])) {
                    response(false, null, msg('no_permission'));
                }
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
        migrateProjectMembers($projects);
        $found = false;

        // Permission check
        foreach ($projects as $pc) {
            if ($pc['id'] == $projectId) {
                if (!canEditTodos($pc, $_SESSION['user']['id'])) {
                    response(false, null, msg('no_permission'));
                }
                break;
            }
        }
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

        // Git pull ausführen
        $output = [];
        $returnCode = 0;
        exec("cd " . escapeshellarg($projectDir) . " && $gitPath pull origin main 2>&1", $output, $returnCode);

        $outputStr = implode("\n", $output);

        if ($returnCode === 0) {
            // Neue Version lesen
            $newVersion = json_decode(file_get_contents($projectDir . '/version.json'), true);
            response(true, [
                'message' => msg('update_success'),
                'version' => $newVersion['version'] ?? '?',
                'output' => $outputStr
            ]);
        } else {
            response(false, ['output' => $outputStr], msg('update_failed'));
        }
        break;

    case 'reorderTodos':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }

        $projectId = $input['projectId'] ?? 0;
        $todoIds = $input['todoIds'] ?? [];

        $projects = loadProjects();
        migrateProjectMembers($projects);
        $found = false;

        // Permission check
        foreach ($projects as $pc) {
            if ($pc['id'] == $projectId) {
                if (!canEditTodos($pc, $_SESSION['user']['id'])) {
                    response(false, null, msg('no_permission'));
                }
                break;
            }
        }

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

    case 'addMember':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }

        $projectId = $input['projectId'] ?? 0;
        $targetUserId = $input['userId'] ?? 0;
        $role = $input['role'] ?? 'viewer';

        if (!in_array($role, ['editor', 'viewer'])) {
            $role = 'viewer';
        }

        $projects = loadProjects();
        migrateProjectMembers($projects);
        $found = false;

        foreach ($projects as &$p) {
            if ($p['id'] == $projectId) {
                if (!canManageMembers($p, $_SESSION['user']['id'])) {
                    response(false, null, msg('no_permission'));
                }

                // Check if already a member
                foreach ($p['members'] as $m) {
                    if ($m['userId'] == $targetUserId) {
                        response(false, null, msg('member_exists'));
                    }
                }

                // Verify user exists
                $users = loadUsers();
                $userExists = false;
                $targetUserName = '';
                foreach ($users as $u) {
                    if ($u['id'] == $targetUserId) {
                        $userExists = true;
                        $targetUserName = $u['name'];
                        break;
                    }
                }
                if (!$userExists) {
                    response(false, null, msg('user_not_found'));
                }

                $p['members'][] = ['userId' => (int)$targetUserId, 'role' => $role];
                $found = true;
                logActivity('member_added', ['projectId' => $projectId, 'projectName' => $p['name'], 'targetUser' => $targetUserName]);
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
        $targetUserId = $input['userId'] ?? 0;

        $projects = loadProjects();
        migrateProjectMembers($projects);
        $found = false;

        foreach ($projects as &$p) {
            if ($p['id'] == $projectId) {
                if (!canManageMembers($p, $_SESSION['user']['id'])) {
                    response(false, null, msg('no_permission'));
                }

                // Cannot remove owner
                foreach ($p['members'] as $m) {
                    if ($m['userId'] == $targetUserId && $m['role'] === 'owner') {
                        response(false, null, msg('cannot_remove_owner'));
                    }
                }

                $originalCount = count($p['members']);
                $p['members'] = array_values(array_filter($p['members'], function($m) use ($targetUserId) {
                    return $m['userId'] != $targetUserId;
                }));

                if (count($p['members']) < $originalCount) {
                    $found = true;
                    logActivity('member_removed', ['projectId' => $projectId, 'projectName' => $p['name']]);
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
        $targetUserId = $input['userId'] ?? 0;
        $newRole = $input['role'] ?? '';

        if (!in_array($newRole, ['editor', 'viewer'])) {
            response(false, null, msg('invalid_action'));
        }

        $projects = loadProjects();
        migrateProjectMembers($projects);
        $found = false;

        foreach ($projects as &$p) {
            if ($p['id'] == $projectId) {
                if (!canManageMembers($p, $_SESSION['user']['id'])) {
                    response(false, null, msg('no_permission'));
                }

                foreach ($p['members'] as &$m) {
                    if ($m['userId'] == $targetUserId) {
                        if ($m['role'] === 'owner') {
                            response(false, null, msg('cannot_change_owner'));
                        }
                        $m['role'] = $newRole;
                        $found = true;
                        break 2;
                    }
                }

                response(false, null, msg('member_not_found'));
            }
        }

        if ($found) {
            saveProjects($projects);
            response(true, null, msg('member_updated'));
        } else {
            response(false, null, msg('project_not_found'));
        }
        break;

    default:
        response(false, null, msg('invalid_action'));
}
?>
