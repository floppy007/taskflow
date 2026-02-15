<?php
/**
 * TaskFlow v1.2 - Installer
 * Copyright (c) 2026 Florian Hesse
 * Fischer Str. 11, 16515 Oranienburg
 * https://comnic-it.de
 */

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if (!$name || !$username || !$password) {
        $error = 'Please fill in all fields.';
    } elseif (strlen($password) < 4) {
        $error = 'Password must be at least 4 characters.';
    } elseif ($password !== $passwordConfirm) {
        $error = 'Passwords do not match.';
    } else {
        // Create data directory if needed
        $dataDir = __DIR__ . '/data';
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }

        // Create .htaccess to protect data folder
        $htaccess = $dataDir . '/.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "Order deny,allow\nDeny from all\n");
        }

        // Create admin user
        $users = [
            [
                'id' => 1,
                'username' => $username,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'name' => $name,
                'createdAt' => date('c')
            ]
        ];

        // Create empty projects
        $projects = [];

        // Write files
        $usersOk = file_put_contents($dataDir . '/users.json', json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $projectsOk = file_put_contents($dataDir . '/projects.json', json_encode($projects, JSON_PRETTY_PRINT));

        if ($usersOk && $projectsOk !== false) {
            $success = true;
            // Delete installer
            @unlink(__FILE__);
        } else {
            $error = 'Could not write data files. Check write permissions for /data directory.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="icon" type="image/png" href="logo.png">
<title>TaskFlow - Installation</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  * {margin:0;padding:0;box-sizing:border-box}
  body {
    font-family:'Inter',sans-serif;
    background:#f8fafc;
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:20px;
  }
  .install-box {
    background:#fff;
    border-radius:16px;
    box-shadow:0 10px 40px rgba(0,0,0,.1);
    padding:40px;
    width:100%;
    max-width:460px;
    animation:slideUp .6s cubic-bezier(.16,1,.3,1);
  }
  @keyframes slideUp {
    from {opacity:0;transform:translateY(30px)}
    to {opacity:1;transform:translateY(0)}
  }
  .logo {text-align:center;margin-bottom:24px}
  .logo img {width:240px;height:auto}
  .title {
    text-align:center;
    font-size:22px;
    font-weight:700;
    color:#0f172a;
    margin-bottom:4px;
  }
  .subtitle {
    text-align:center;
    font-size:14px;
    color:#64748b;
    margin-bottom:28px;
  }
  .form-group {margin-bottom:16px}
  .form-label {
    display:block;
    font-size:13px;
    font-weight:600;
    color:#374151;
    margin-bottom:6px;
  }
  .form-input {
    width:100%;
    padding:10px 14px;
    border:1px solid #e2e8f0;
    border-radius:10px;
    font-size:14px;
    font-family:inherit;
    transition:border-color .2s;
    outline:none;
  }
  .form-input:focus {
    border-color:#667eea;
    box-shadow:0 0 0 3px rgba(102,126,234,.15);
  }
  .btn {
    width:100%;
    padding:12px;
    border:none;
    border-radius:10px;
    font-size:15px;
    font-weight:600;
    font-family:inherit;
    cursor:pointer;
    background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
    color:#fff;
    transition:transform .2s,box-shadow .2s;
    margin-top:8px;
  }
  .btn:hover {
    transform:translateY(-1px);
    box-shadow:0 6px 20px rgba(102,126,234,.3);
  }
  .btn:active {transform:scale(.98)}
  .error {
    background:#fef2f2;
    color:#dc2626;
    padding:10px 14px;
    border-radius:10px;
    font-size:13px;
    margin-bottom:16px;
    border:1px solid #fecaca;
  }
  .success-box {text-align:center;padding:20px 0}
  .success-icon {font-size:48px;margin-bottom:16px}
  .success-text {font-size:16px;color:#0f172a;font-weight:600;margin-bottom:8px}
  .success-sub {font-size:13px;color:#64748b;margin-bottom:24px}
  .steps {
    background:#f8fafc;
    border-radius:10px;
    padding:16px;
    margin-bottom:24px;
    font-size:13px;
    color:#64748b;
  }
  .steps li {margin-bottom:6px}
  .steps li:last-child {margin-bottom:0}
</style>
</head>
<body>
<div class="install-box">
  <div class="logo">
    <?php if (file_exists(__DIR__ . '/logo.png')): ?>
      <img src="logo.png" alt="TaskFlow">
    <?php else: ?>
      <div class="title" style="font-size:32px;margin-bottom:8px">TaskFlow</div>
    <?php endif; ?>
  </div>

  <?php if ($success): ?>
    <div class="success-box">
      <div class="success-icon">&#10003;</div>
      <div class="success-text">Installation complete!</div>
      <div class="success-sub">TaskFlow has been set up successfully. The installer has been removed.</div>
      <a href="index.php" class="btn" style="display:inline-block;text-decoration:none;width:auto;padding:12px 32px">Open TaskFlow</a>
    </div>
  <?php else: ?>
    <div class="title">Installation</div>
    <div class="subtitle">Set up your admin account to get started</div>

    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
      <div class="form-group">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-input" placeholder="e.g. Max Mustermann" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-input" placeholder="e.g. admin" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-input" placeholder="Choose a secure password" required>
      </div>
      <div class="form-group">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="password_confirm" class="form-input" placeholder="Repeat password" required>
      </div>
      <button type="submit" class="btn">Install TaskFlow</button>
    </form>
  <?php endif; ?>
</div>
</body>
</html>
