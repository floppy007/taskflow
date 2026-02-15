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
        response(true, $projects);
        break;

    case 'createProject':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }

        $name = $input['name'] ?? '';
        $desc = $input['desc'] ?? '';

        if (empty($name)) {
            response(false, null, msg('project_name_required'));
        }

        $projects = loadProjects();
        $newId = count($projects) > 0 ? max(array_column($projects, 'id')) + 1 : 1;

        $newProject = [
            'id' => $newId,
            'name' => $name,
            'desc' => $desc,
            'createdBy' => $_SESSION['user']['id'],
            'createdAt' => date('c'),
            'todos' => []
        ];

        $projects[] = $newProject;
        saveProjects($projects);

        response(true, $newProject, msg('project_created'));
        break;

    case 'updateProject':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }

        $projectId = $input['id'] ?? 0;
        $name = $input['name'] ?? '';
        $desc = $input['desc'] ?? '';

        $projects = loadProjects();
        $found = false;

        foreach ($projects as &$p) {
            if ($p['id'] == $projectId) {
                if (!empty($name)) $p['name'] = $name;
                $p['desc'] = $desc;
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

        $filtered = array_filter($projects, function($p) use ($projectId) {
            return $p['id'] != $projectId;
        });

        if (count($filtered) < count($projects)) {
            saveProjects(array_values($filtered));
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

        foreach ($projects as &$p) {
            if ($p['id'] == $projectId) {
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

    default:
        response(false, null, msg('invalid_action'));
}
?>
