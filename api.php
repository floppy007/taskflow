<?php
/**
 * TaskFlow v1.71 - API
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
        'attachment_uploaded' => 'Datei hochgeladen',
        'attachment_deleted' => 'Anhang gelöscht',
        'attachment_not_found' => 'Anhang nicht gefunden',
        'attachment_too_large' => 'Datei ist zu groß (max. 10 MB)',
        'attachment_upload_failed' => 'Upload fehlgeschlagen',
        'register_disabled' => 'Selbstregistrierung ist deaktiviert',
        'ldap_config_saved' => 'LDAP-Konfiguration gespeichert',
        'ldap_extension_missing' => 'PHP LDAP-Erweiterung ist nicht installiert',
        'ldap_not_configured' => 'LDAP ist nicht konfiguriert',
        'ldap_connect_failed' => 'LDAP-Verbindung fehlgeschlagen',
        'ldap_tls_failed' => 'STARTTLS fehlgeschlagen',
        'ldap_bind_failed' => 'LDAP-Bind fehlgeschlagen (Zugangsdaten prüfen)',
        'ldap_test_success' => 'LDAP-Verbindung erfolgreich',
        'ldap_import_success' => 'LDAP-Import abgeschlossen',
        'ldap_import_failed' => 'LDAP-Import fehlgeschlagen',
        'ldap_no_users' => 'Keine Benutzer im LDAP gefunden',
        'ldap_unreachable' => 'LDAP-Server nicht erreichbar',
        'ldap_no_password_change' => 'Passwortänderung für AD/LDAP-Benutzer nicht möglich',
        'smtp_config_saved' => 'SMTP-Konfiguration gespeichert',
        'smtp_not_configured' => 'SMTP ist nicht konfiguriert oder deaktiviert',
        'smtp_test_success' => 'Test-E-Mail erfolgreich gesendet',
        'smtp_test_failed' => 'Fehler beim Senden der Test-E-Mail',
        'smtp_email_required' => 'E-Mail-Adresse erforderlich',
        'reset_email_sent' => 'Falls ein Konto existiert, wurde ein Reset-Link gesendet',
        'reset_identifier_required' => 'Benutzername oder E-Mail erforderlich',
        'reset_token_invalid' => 'Ungültiger oder abgelaufener Reset-Link',
        'reset_password_required' => 'Neues Passwort erforderlich',
        'reset_success' => 'Passwort erfolgreich zurückgesetzt',
        'email_updated' => 'E-Mail-Adresse aktualisiert',
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
        'attachment_uploaded' => 'File uploaded',
        'attachment_deleted' => 'Attachment deleted',
        'attachment_not_found' => 'Attachment not found',
        'attachment_too_large' => 'File is too large (max. 10 MB)',
        'attachment_upload_failed' => 'Upload failed',
        'register_disabled' => 'Self-registration is disabled',
        'ldap_config_saved' => 'LDAP configuration saved',
        'ldap_extension_missing' => 'PHP LDAP extension is not installed',
        'ldap_not_configured' => 'LDAP is not configured',
        'ldap_connect_failed' => 'LDAP connection failed',
        'ldap_tls_failed' => 'STARTTLS failed',
        'ldap_bind_failed' => 'LDAP bind failed (check credentials)',
        'ldap_test_success' => 'LDAP connection successful',
        'ldap_import_success' => 'LDAP import completed',
        'ldap_import_failed' => 'LDAP import failed',
        'ldap_no_users' => 'No users found in LDAP',
        'ldap_unreachable' => 'LDAP server unreachable',
        'ldap_no_password_change' => 'Password change not available for AD/LDAP users',
        'smtp_config_saved' => 'SMTP configuration saved',
        'smtp_not_configured' => 'SMTP is not configured or disabled',
        'smtp_test_success' => 'Test email sent successfully',
        'smtp_test_failed' => 'Error sending test email',
        'smtp_email_required' => 'Email address required',
        'reset_email_sent' => 'If an account exists, a reset link has been sent',
        'reset_identifier_required' => 'Username or email required',
        'reset_token_invalid' => 'Invalid or expired reset link',
        'reset_password_required' => 'New password required',
        'reset_success' => 'Password reset successfully',
        'email_updated' => 'Email address updated',
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

function loadLdapConfig() {
    global $dataDir;
    $file = $dataDir . '/ldap_config.json';
    if (!file_exists($file)) return null;
    return json_decode(file_get_contents($file), true);
}

function saveLdapConfig($config) {
    global $dataDir;
    $file = $dataDir . '/ldap_config.json';
    file_put_contents($file, json_encode($config, JSON_PRETTY_PRINT), LOCK_EX);
}

function loadSmtpConfig() {
    global $dataDir;
    $file = $dataDir . '/smtp_config.json';
    if (!file_exists($file)) return null;
    return json_decode(file_get_contents($file), true);
}

function saveSmtpConfig($config) {
    global $dataDir;
    $file = $dataDir . '/smtp_config.json';
    file_put_contents($file, json_encode($config, JSON_PRETTY_PRINT), LOCK_EX);
}

function loadPasswordResets() {
    global $dataDir;
    $file = $dataDir . '/password_resets.json';
    if (!file_exists($file)) return [];
    return json_decode(file_get_contents($file), true) ?: [];
}

function savePasswordResets($resets) {
    global $dataDir;
    $file = $dataDir . '/password_resets.json';
    file_put_contents($file, json_encode($resets, JSON_PRETTY_PRINT), LOCK_EX);
}

function cleanExpiredResets() {
    $resets = loadPasswordResets();
    $now = time();
    $resets = array_values(array_filter($resets, function($r) use ($now) {
        return $r['expiresAt'] > $now;
    }));
    savePasswordResets($resets);
    return $resets;
}

function sendSmtpEmail($to, $subject, $body) {
    $config = loadSmtpConfig();
    if (!$config || empty($config['enabled']) || empty($config['host'])) {
        return false;
    }

    $host = $config['host'];
    $port = (int)($config['port'] ?? 587);
    $encryption = $config['encryption'] ?? 'tls';
    $username = $config['username'] ?? '';
    $password = $config['password'] ?? '';
    $fromEmail = $config['from_email'] ?? $username;
    $fromName = $config['from_name'] ?? 'TaskFlow';

    $timeout = 15;
    $errno = 0;
    $errstr = '';

    // Connect
    if ($encryption === 'ssl') {
        $socket = @fsockopen('ssl://' . $host, $port, $errno, $errstr, $timeout);
    } else {
        $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
    }

    if (!$socket) {
        return false;
    }

    stream_set_timeout($socket, $timeout);

    // Helper to read response
    $readResponse = function() use ($socket) {
        $response = '';
        while ($line = fgets($socket, 512)) {
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        return $response;
    };

    // Helper to send command
    $sendCmd = function($cmd) use ($socket, $readResponse) {
        fwrite($socket, $cmd . "\r\n");
        return $readResponse();
    };

    // Read greeting
    $readResponse();

    // EHLO
    $sendCmd('EHLO localhost');

    // STARTTLS if needed
    if ($encryption === 'tls') {
        $sendCmd('STARTTLS');
        $cryptoMethod = STREAM_CRYPTO_METHOD_TLS_CLIENT;
        if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
            $cryptoMethod = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
        }
        if (!@stream_socket_enable_crypto($socket, true, $cryptoMethod)) {
            fclose($socket);
            return false;
        }
        $sendCmd('EHLO localhost');
    }

    // AUTH LOGIN
    if (!empty($username) && !empty($password)) {
        $sendCmd('AUTH LOGIN');
        $sendCmd(base64_encode($username));
        $authResponse = $sendCmd(base64_encode($password));
        if (strpos($authResponse, '235') === false) {
            fclose($socket);
            return false;
        }
    }

    // MAIL FROM
    $sendCmd('MAIL FROM:<' . $fromEmail . '>');

    // RCPT TO
    $rcptResponse = $sendCmd('RCPT TO:<' . $to . '>');
    if (strpos($rcptResponse, '250') === false && strpos($rcptResponse, '251') === false) {
        fclose($socket);
        return false;
    }

    // DATA
    $sendCmd('DATA');

    // Build message
    $boundary = md5(uniqid(time()));
    $headers = "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <" . $fromEmail . ">\r\n";
    $headers .= "To: <" . $to . ">\r\n";
    $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "Content-Transfer-Encoding: base64\r\n";
    $headers .= "Date: " . date('r') . "\r\n";
    $headers .= "Message-ID: <" . md5(uniqid()) . "@" . gethostname() . ">\r\n";

    $message = $headers . "\r\n" . chunk_split(base64_encode($body));

    $dataResponse = $sendCmd($message . "\r\n.");
    $sendCmd('QUIT');
    fclose($socket);

    return strpos($dataResponse, '250') !== false;
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

        // v5: Add attachments array to all todos
        5 => function() {
            $projects = loadProjects();
            $changed = false;
            foreach ($projects as &$p) {
                foreach ($p['todos'] as &$t) {
                    if (!isset($t['attachments'])) {
                        $t['attachments'] = [];
                        $changed = true;
                    }
                }
            }
            if ($changed) saveProjects($projects);
        },

        // v6: Add source field to all users (default: local)
        6 => function() {
            $users = loadUsers();
            $changed = false;
            foreach ($users as &$u) {
                if (!isset($u['source'])) {
                    $u['source'] = 'local';
                    $changed = true;
                }
            }
            if ($changed) saveUsers($users);
        },

        // v7: Add email field to all users (default: empty)
        7 => function() {
            $users = loadUsers();
            $changed = false;
            foreach ($users as &$u) {
                if (!isset($u['email'])) {
                    $u['email'] = '';
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
        $foundUser = null;

        foreach ($users as $u) {
            if ($u['username'] === $username) {
                $foundUser = $u;
                break;
            }
        }

        if (!$foundUser) {
            response(false, null, msg('login_failed'));
        }

        $source = $foundUser['source'] ?? 'local';
        $authenticated = false;

        if ($source === 'ldap') {
            // LDAP authentication
            if (!function_exists('ldap_connect')) {
                response(false, null, msg('ldap_extension_missing'));
            }

            $config = loadLdapConfig();
            if (!$config || empty($config['server'])) {
                response(false, null, msg('ldap_unreachable'));
            }

            $conn = @ldap_connect($config['server'], $config['port']);
            if (!$conn) {
                response(false, null, msg('ldap_unreachable'));
            }

            ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
            ldap_set_option($conn, LDAP_OPT_NETWORK_TIMEOUT, 10);

            if (!empty($config['use_tls'])) {
                if (!@ldap_start_tls($conn)) {
                    @ldap_close($conn);
                    response(false, null, msg('ldap_unreachable'));
                }
            }

            // Bind with user's own DN and password
            $userDn = $foundUser['ldap_dn'] ?? '';
            if (!empty($userDn)) {
                $authenticated = @ldap_bind($conn, $userDn, $password);
            }
            @ldap_close($conn);
        } else {
            // Local authentication
            if (!empty($foundUser['password']) && password_verify($password, $foundUser['password'])) {
                $authenticated = true;
            }
        }

        if ($authenticated) {
            $user = $foundUser;
            $user['role'] = $user['role'] ?? 'admin';
            unset($user['password']);
            $_SESSION['user'] = $user;
            logActivity('user_login');
            response(true, $user, msg('login_success'));
        } else {
            response(false, null, msg('login_failed'));
        }
        break;

    case 'register':
        response(false, null, msg('register_disabled'));
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

        if (($_SESSION['user']['source'] ?? 'local') === 'ldap') {
            response(false, null, msg('ldap_no_password_change'));
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

        $email = $input['email'] ?? '';

        $newId = count($users) > 0 ? max(array_column($users, 'id')) + 1 : 1;
        $newUser = [
            'id' => $newId,
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'name' => $name,
            'role' => $role,
            'source' => 'local',
            'email' => $email,
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
                    'attachments' => [],
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
                    if ($td['id'] == $todoId) {
                        $deletedTodoText = $td['text'];
                        // Delete attachment files
                        $attDir = $dataDir . '/attachments/' . $projectId . '/' . $todoId;
                        if (is_dir($attDir)) {
                            foreach (glob($attDir . '/*') as $f) { unlink($f); }
                            rmdir($attDir);
                        }
                        break;
                    }
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

    case 'uploadAttachment':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }

        $projectId = (int)($_POST['projectId'] ?? 0);
        $todoId = (int)($_POST['todoId'] ?? 0);

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            response(false, null, msg('attachment_upload_failed'));
        }

        $file = $_FILES['file'];
        $maxSize = 10 * 1024 * 1024; // 10 MB
        if ($file['size'] > $maxSize) {
            response(false, null, msg('attachment_too_large'));
        }

        $projects = loadProjects();
        $found = false;

        foreach ($projects as &$p) {
            if ($p['id'] == $projectId) {
                foreach ($p['todos'] as &$t) {
                    if ($t['id'] == $todoId) {
                        // Create attachment directory
                        $attachDir = $dataDir . '/attachments/' . $projectId . '/' . $todoId;
                        if (!is_dir($attachDir)) {
                            mkdir($attachDir, 0755, true);
                        }

                        // Generate unique ID and sanitize filename
                        $attachId = uniqid('att_');
                        $originalName = basename($file['name']);
                        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
                        $storedName = $attachId . '_' . $safeName;

                        if (!move_uploaded_file($file['tmp_name'], $attachDir . '/' . $storedName)) {
                            response(false, null, msg('attachment_upload_failed'));
                        }

                        // Add metadata to todo
                        if (!isset($t['attachments'])) $t['attachments'] = [];
                        $t['attachments'][] = [
                            'id' => $attachId,
                            'filename' => $originalName,
                            'storedName' => $storedName,
                            'size' => $file['size'],
                            'type' => $file['type'],
                            'uploadedBy' => $_SESSION['user']['username'] ?? '',
                            'uploadedAt' => date('c')
                        ];

                        $found = true;
                        break 2;
                    }
                }
            }
        }

        if ($found) {
            saveProjects($projects);
            response(true, null, msg('attachment_uploaded'));
        } else {
            response(false, null, msg('todo_not_found'));
        }
        break;

    case 'deleteAttachment':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }

        $projectId = $input['projectId'] ?? 0;
        $todoId = $input['todoId'] ?? 0;
        $attachmentId = $input['attachmentId'] ?? '';

        $projects = loadProjects();
        $found = false;

        foreach ($projects as &$p) {
            if ($p['id'] == $projectId) {
                foreach ($p['todos'] as &$t) {
                    if ($t['id'] == $todoId) {
                        if (!isset($t['attachments'])) break;

                        foreach ($t['attachments'] as $idx => $att) {
                            if ($att['id'] === $attachmentId) {
                                // Delete file from filesystem
                                $filePath = $dataDir . '/attachments/' . $projectId . '/' . $todoId . '/' . $att['storedName'];
                                if (file_exists($filePath)) {
                                    unlink($filePath);
                                }

                                // Remove from metadata
                                array_splice($t['attachments'], $idx, 1);
                                $found = true;
                                break 3;
                            }
                        }
                    }
                }
            }
        }

        if ($found) {
            saveProjects($projects);
            response(true, null, msg('attachment_deleted'));
        } else {
            response(false, null, msg('attachment_not_found'));
        }
        break;

    case 'getLdapConfig':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }
        requireAdmin();

        $config = loadLdapConfig();
        if ($config && !empty($config['bind_password'])) {
            $config['bind_password'] = '********';
        }
        response(true, $config);
        break;

    case 'saveLdapConfig':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }
        requireAdmin();

        $config = [
            'enabled' => !empty($input['enabled']),
            'server' => $input['server'] ?? '',
            'port' => (int)($input['port'] ?? 389),
            'use_tls' => !empty($input['use_tls']),
            'base_dn' => $input['base_dn'] ?? '',
            'bind_user_dn' => $input['bind_user_dn'] ?? '',
            'bind_password' => $input['bind_password'] ?? '',
            'search_filter' => $input['search_filter'] ?? '(&(objectClass=user)(objectCategory=person))',
            'user_ou' => $input['user_ou'] ?? '',
            'username_attribute' => $input['username_attribute'] ?? 'sAMAccountName',
            'display_name_attribute' => $input['display_name_attribute'] ?? 'displayName',
            'email_attribute' => $input['email_attribute'] ?? 'mail',
        ];

        // Keep existing password if placeholder was sent
        if ($config['bind_password'] === '********' || $config['bind_password'] === '') {
            $existing = loadLdapConfig();
            if ($existing && !empty($existing['bind_password'])) {
                $config['bind_password'] = $existing['bind_password'];
            }
        }

        saveLdapConfig($config);
        response(true, null, msg('ldap_config_saved'));
        break;

    case 'testLdapConnection':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }
        requireAdmin();

        if (!function_exists('ldap_connect')) {
            response(false, null, msg('ldap_extension_missing'));
        }

        $config = loadLdapConfig();
        if (!$config || empty($config['server'])) {
            response(false, null, msg('ldap_not_configured'));
        }

        $conn = @ldap_connect($config['server'], $config['port']);
        if (!$conn) {
            response(false, null, msg('ldap_connect_failed'));
        }

        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($conn, LDAP_OPT_NETWORK_TIMEOUT, 10);

        if (!empty($config['use_tls'])) {
            if (!@ldap_start_tls($conn)) {
                @ldap_close($conn);
                response(false, null, msg('ldap_tls_failed'));
            }
        }

        $bind = @ldap_bind($conn, $config['bind_user_dn'], $config['bind_password']);
        if (!$bind) {
            @ldap_close($conn);
            response(false, null, msg('ldap_bind_failed'));
        }

        // Count users in the configured OU(s)
        $filter = !empty($config['search_filter']) ? $config['search_filter'] : '(&(objectClass=user)(objectCategory=person))';
        $userOus = array_filter(array_map('trim', preg_split('/[\r\n]+/', $config['user_ou'] ?? '')));
        if (empty($userOus)) $userOus = [$config['base_dn']];

        $count = 0;
        foreach ($userOus as $ou) {
            $sr = @ldap_search($conn, $ou, $filter, [$config['username_attribute'] ?? 'sAMAccountName']);
            if ($sr) $count += ldap_count_entries($conn, $sr);
        }

        @ldap_close($conn);
        response(true, ['user_count' => $count], msg('ldap_test_success') . " ($count " . ($count === 1 ? 'Benutzer' : 'Benutzer') . ")");
        break;

    case 'importLdapUsers':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }
        requireAdmin();

        if (!function_exists('ldap_connect')) {
            response(false, null, msg('ldap_extension_missing'));
        }

        $config = loadLdapConfig();
        if (!$config || empty($config['server']) || empty($config['enabled'])) {
            response(false, null, msg('ldap_not_configured'));
        }

        $conn = @ldap_connect($config['server'], $config['port']);
        if (!$conn) {
            response(false, null, msg('ldap_connect_failed'));
        }

        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($conn, LDAP_OPT_NETWORK_TIMEOUT, 10);

        if (!empty($config['use_tls'])) {
            if (!@ldap_start_tls($conn)) {
                @ldap_close($conn);
                response(false, null, msg('ldap_tls_failed'));
            }
        }

        $bind = @ldap_bind($conn, $config['bind_user_dn'], $config['bind_password']);
        if (!$bind) {
            @ldap_close($conn);
            response(false, null, msg('ldap_bind_failed'));
        }

        $filter = !empty($config['search_filter']) ? $config['search_filter'] : '(&(objectClass=user)(objectCategory=person))';
        $usernameAttr = strtolower($config['username_attribute'] ?? 'sAMAccountName');
        $displayAttr = strtolower($config['display_name_attribute'] ?? 'displayName');
        $emailAttr = strtolower($config['email_attribute'] ?? 'mail');

        $userOus = array_filter(array_map('trim', preg_split('/[\r\n]+/', $config['user_ou'] ?? '')));
        if (empty($userOus)) $userOus = [$config['base_dn']];

        // Search all OUs and merge entries
        $allEntries = [];
        foreach ($userOus as $ou) {
            $sr = @ldap_search($conn, $ou, $filter, [$usernameAttr, $displayAttr, $emailAttr, 'dn']);
            if ($sr) {
                $ouEntries = ldap_get_entries($conn, $sr);
                for ($j = 0; $j < $ouEntries['count']; $j++) {
                    $allEntries[] = $ouEntries[$j];
                }
            }
        }
        @ldap_close($conn);

        if (count($allEntries) === 0) {
            response(false, null, msg('ldap_no_users'));
        }

        // Build a pseudo-entries array for processing
        $entries = ['count' => count($allEntries)];
        foreach ($allEntries as $idx => $entry) {
            $entries[$idx] = $entry;
        }

        $users = loadUsers();
        $imported = 0;
        $updated = 0;
        $skipped = 0;

        for ($i = 0; $i < $entries['count']; $i++) {
            $entry = $entries[$i];
            $username = $entry[$usernameAttr][0] ?? null;
            if (!$username) { $skipped++; continue; }

            $displayName = $entry[$displayAttr][0] ?? $username;
            $email = $entry[$emailAttr][0] ?? '';
            $dn = $entry['dn'] ?? '';

            // Check if user already exists
            $existingIdx = null;
            foreach ($users as $idx => $u) {
                if (strtolower($u['username']) === strtolower($username)) {
                    $existingIdx = $idx;
                    break;
                }
            }

            if ($existingIdx !== null) {
                $existingUser = $users[$existingIdx];
                if (($existingUser['source'] ?? 'local') === 'local') {
                    // Local user with same username - skip
                    $skipped++;
                } else {
                    // LDAP user - update name/email/DN
                    $users[$existingIdx]['name'] = $displayName;
                    $users[$existingIdx]['email'] = $email;
                    $users[$existingIdx]['ldap_dn'] = $dn;
                    $updated++;
                }
            } else {
                // New user
                $newId = count($users) > 0 ? max(array_column($users, 'id')) + 1 : 1;
                $users[] = [
                    'id' => $newId,
                    'username' => $username,
                    'password' => null,
                    'name' => $displayName,
                    'role' => 'user',
                    'source' => 'ldap',
                    'ldap_dn' => $dn,
                    'email' => $email,
                    'createdAt' => date('c'),
                    'importedAt' => date('c')
                ];
                $imported++;
            }
        }

        saveUsers($users);
        logActivity('ldap_import', ['imported' => $imported, 'updated' => $updated, 'skipped' => $skipped]);
        response(true, ['imported' => $imported, 'updated' => $updated, 'skipped' => $skipped], msg('ldap_import_success'));
        break;

    case 'downloadAttachment':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }

        $projectId = (int)($_GET['projectId'] ?? 0);
        $todoId = (int)($_GET['todoId'] ?? 0);
        $attachmentId = $_GET['attachmentId'] ?? '';

        $projects = loadProjects();
        $foundAtt = null;

        foreach ($projects as $p) {
            if ($p['id'] == $projectId) {
                foreach ($p['todos'] as $t) {
                    if ($t['id'] == $todoId && isset($t['attachments'])) {
                        foreach ($t['attachments'] as $att) {
                            if ($att['id'] === $attachmentId) {
                                $foundAtt = $att;
                                break 3;
                            }
                        }
                    }
                }
            }
        }

        if (!$foundAtt) {
            response(false, null, msg('attachment_not_found'));
        }

        $filePath = $dataDir . '/attachments/' . $projectId . '/' . $todoId . '/' . $foundAtt['storedName'];
        if (!file_exists($filePath)) {
            response(false, null, msg('attachment_not_found'));
        }

        // Clean any previous output
        if (ob_get_level()) ob_end_clean();

        // Send file with correct headers
        $mimeType = $foundAtt['type'] ?: mime_content_type($filePath) ?: 'application/octet-stream';
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: inline; filename="' . rawurlencode($foundAtt['filename']) . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: private, max-age=3600');
        header('Accept-Ranges: bytes');
        readfile($filePath);
        exit;

    case 'getSmtpConfig':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }
        requireAdmin();

        $config = loadSmtpConfig();
        if ($config && !empty($config['password'])) {
            $config['password'] = '********';
        }
        response(true, $config);
        break;

    case 'saveSmtpConfig':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }
        requireAdmin();

        $config = [
            'enabled' => !empty($input['enabled']),
            'host' => $input['host'] ?? '',
            'port' => (int)($input['port'] ?? 587),
            'encryption' => $input['encryption'] ?? 'tls',
            'username' => $input['username'] ?? '',
            'password' => $input['password'] ?? '',
            'from_email' => $input['from_email'] ?? '',
            'from_name' => $input['from_name'] ?? 'TaskFlow',
        ];

        // Keep existing password if placeholder was sent
        if ($config['password'] === '********' || $config['password'] === '') {
            $existing = loadSmtpConfig();
            if ($existing && !empty($existing['password'])) {
                $config['password'] = $existing['password'];
            }
        }

        saveSmtpConfig($config);
        response(true, null, msg('smtp_config_saved'));
        break;

    case 'testSmtpConfig':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }
        requireAdmin();

        $testEmail = $input['email'] ?? '';
        if (empty($testEmail)) {
            response(false, null, msg('smtp_email_required'));
        }

        $config = loadSmtpConfig();
        if (!$config || empty($config['enabled']) || empty($config['host'])) {
            response(false, null, msg('smtp_not_configured'));
        }

        $subject = 'TaskFlow - SMTP Test';
        $body = '<html><body style="font-family:Arial,sans-serif;padding:20px">'
            . '<h2 style="color:#667eea">TaskFlow SMTP Test</h2>'
            . '<p>This is a test email from TaskFlow to verify your SMTP configuration.</p>'
            . '<p style="color:#64748b;font-size:13px">Sent at: ' . date('Y-m-d H:i:s') . '</p>'
            . '</body></html>';

        $sent = sendSmtpEmail($testEmail, $subject, $body);
        if ($sent) {
            response(true, null, msg('smtp_test_success'));
        } else {
            response(false, null, msg('smtp_test_failed'));
        }
        break;

    case 'requestPasswordReset':
        $identifier = $input['identifier'] ?? '';
        if (empty($identifier)) {
            response(true, null, msg('reset_email_sent'));
        }

        // Always respond the same way to prevent user enumeration
        $users = loadUsers();
        $foundUser = null;

        foreach ($users as $u) {
            if (($u['source'] ?? 'local') !== 'local') continue;
            if ($u['username'] === $identifier || (!empty($u['email']) && strtolower($u['email']) === strtolower($identifier))) {
                $foundUser = $u;
                break;
            }
        }

        if ($foundUser && !empty($foundUser['email'])) {
            $smtpConfig = loadSmtpConfig();
            if ($smtpConfig && !empty($smtpConfig['enabled'])) {
                // Generate token
                $token = bin2hex(random_bytes(32));
                $tokenHash = hash('sha256', $token);

                // Clean expired and remove existing for this user
                $resets = cleanExpiredResets();
                $resets = array_values(array_filter($resets, function($r) use ($foundUser) {
                    return $r['userId'] !== $foundUser['id'];
                }));

                $resets[] = [
                    'userId' => $foundUser['id'],
                    'tokenHash' => $tokenHash,
                    'expiresAt' => time() + 3600,
                    'createdAt' => date('c')
                ];
                savePasswordResets($resets);

                // Build reset URL
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $path = dirname($_SERVER['SCRIPT_NAME']);
                $resetUrl = $protocol . '://' . $host . $path . '/index.php?resetToken=' . $token;

                $subject = 'TaskFlow - ' . ($lang === 'de' ? 'Passwort zurücksetzen' : 'Password Reset');
                $body = '<html><body style="font-family:Arial,sans-serif;padding:20px;background:#f8fafc">'
                    . '<div style="max-width:500px;margin:0 auto;background:#fff;border-radius:12px;padding:32px;box-shadow:0 2px 8px rgba(0,0,0,.1)">'
                    . '<h2 style="color:#667eea;margin-top:0">TaskFlow</h2>'
                    . '<p>' . ($lang === 'de' ? 'Hallo ' . htmlspecialchars($foundUser['name']) . ',' : 'Hello ' . htmlspecialchars($foundUser['name']) . ',') . '</p>'
                    . '<p>' . ($lang === 'de' ? 'Klicke auf den folgenden Link, um dein Passwort zurückzusetzen:' : 'Click the following link to reset your password:') . '</p>'
                    . '<p style="text-align:center;margin:24px 0"><a href="' . htmlspecialchars($resetUrl) . '" style="display:inline-block;padding:12px 32px;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;text-decoration:none;border-radius:8px;font-weight:600">'
                    . ($lang === 'de' ? 'Passwort zurücksetzen' : 'Reset Password')
                    . '</a></p>'
                    . '<p style="color:#64748b;font-size:13px">' . ($lang === 'de' ? 'Dieser Link ist 1 Stunde gültig.' : 'This link is valid for 1 hour.') . '</p>'
                    . '<p style="color:#94a3b8;font-size:12px">' . ($lang === 'de' ? 'Falls du kein Passwort-Reset angefordert hast, ignoriere diese E-Mail.' : 'If you did not request a password reset, please ignore this email.') . '</p>'
                    . '</div></body></html>';

                sendSmtpEmail($foundUser['email'], $subject, $body);
            }
        }

        // Always same response
        response(true, null, msg('reset_email_sent'));
        break;

    case 'resetPassword':
        $token = $input['token'] ?? '';
        $newPassword = $input['password'] ?? '';

        if (empty($token)) {
            response(false, null, msg('reset_token_invalid'));
        }
        if (empty($newPassword)) {
            response(false, null, msg('reset_password_required'));
        }

        $tokenHash = hash('sha256', $token);
        $resets = cleanExpiredResets();

        $foundReset = null;
        foreach ($resets as $r) {
            if (hash_equals($r['tokenHash'], $tokenHash)) {
                $foundReset = $r;
                break;
            }
        }

        if (!$foundReset) {
            response(false, null, msg('reset_token_invalid'));
        }

        // Update password
        $users = loadUsers();
        $updated = false;
        foreach ($users as &$u) {
            if ($u['id'] === $foundReset['userId']) {
                $u['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
                $updated = true;
                break;
            }
        }

        if ($updated) {
            saveUsers($users);

            // Remove used token
            $resets = array_values(array_filter($resets, function($r) use ($foundReset) {
                return $r['userId'] !== $foundReset['userId'];
            }));
            savePasswordResets($resets);

            response(true, null, msg('reset_success'));
        } else {
            response(false, null, msg('reset_token_invalid'));
        }
        break;

    case 'updateUserEmail':
        if (!isset($_SESSION['user'])) {
            response(false, null, msg('not_logged_in'));
        }
        requireAdmin();

        $userId = $input['id'] ?? 0;
        $email = $input['email'] ?? '';

        $users = loadUsers();
        $found = false;
        foreach ($users as &$u) {
            if ($u['id'] == $userId) {
                $u['email'] = $email;
                $found = true;
                break;
            }
        }

        if ($found) {
            saveUsers($users);
            response(true, null, msg('email_updated'));
        } else {
            response(false, null, msg('user_not_found'));
        }
        break;

    default:
        response(false, null, msg('invalid_action'));
}
?>
