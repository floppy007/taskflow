<?php
/**
 * TaskFlow Installer
 * Copy this single file to your web server and open it in a browser.
 * It will download TaskFlow from GitHub and set up the admin account.
 *
 * Copyright (c) 2026 Florian Hesse
 * https://comnic-it.de
 */

$repoUrl = 'https://github.com/floppy007/taskflow';
$branch = 'master';
$error = '';
$success = false;
$step = 'setup'; // setup, installing, done

$installDir = __DIR__;
$dataDir = $installDir . '/data';

// Check if already fully installed
if (file_exists($installDir . '/index.php') && file_exists($dataDir . '/users.json')) {
    header('Location: index.php');
    exit;
}

// Check if files are downloaded but no user yet
$filesExist = file_exists($installDir . '/index.php') && file_exists($installDir . '/api.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'download') {
        // Step 1: Download from GitHub
        $zipUrl = $repoUrl . '/archive/refs/heads/' . $branch . '.zip';
        $tmpZip = sys_get_temp_dir() . '/taskflow_' . time() . '.zip';

        $ctx = stream_context_create([
            'http' => [
                'timeout' => 30,
                'header' => 'User-Agent: TaskFlow-Installer',
                'follow_location' => true
            ]
        ]);

        $zipData = @file_get_contents($zipUrl, false, $ctx);
        if ($zipData === false) {
            $error = 'Could not download from GitHub. Check your internet connection.';
        } else {
            file_put_contents($tmpZip, $zipData);

            $zip = new ZipArchive();
            if ($zip->open($tmpZip) === true) {
                // Extract to temp dir first
                $tmpDir = sys_get_temp_dir() . '/taskflow_extract_' . time();
                $zip->extractTo($tmpDir);
                $zip->close();

                // Find extracted folder (usually "taskflow-master")
                $folders = glob($tmpDir . '/*', GLOB_ONLYDIR);
                $srcDir = $folders[0] ?? $tmpDir;

                // Copy files to install dir (skip data/*.json and install.php)
                copyDir($srcDir, $installDir);

                // Clean up
                @unlink($tmpZip);
                deleteDir($tmpDir);

                $filesExist = true;
            } else {
                $error = 'Could not extract ZIP file.';
                @unlink($tmpZip);
            }
        }
    }

    if ($action === 'createuser') {
        // Step 2: Create admin user
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
            if (!is_dir($dataDir)) {
                @mkdir($dataDir, 0755, true);
            }

            if (!is_writable($dataDir)) {
                $error = 'The /data directory is not writable. Set permissions: chmod 755 data';
            } else {
                // .htaccess
                $htaccess = $dataDir . '/.htaccess';
                if (!file_exists($htaccess)) {
                    file_put_contents($htaccess, "Order deny,allow\nDeny from all\n");
                }

                $users = [[
                    'id' => 1,
                    'username' => $username,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'name' => $name,
                    'createdAt' => date('c')
                ]];

                $usersOk = file_put_contents($dataDir . '/users.json', json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $projectsOk = file_put_contents($dataDir . '/projects.json', json_encode([], JSON_PRETTY_PRINT));

                if ($usersOk !== false && $projectsOk !== false) {
                    $success = true;
                    @unlink(__FILE__);
                } else {
                    $error = 'Could not write data files.';
                }
            }
        }
    }
}

function copyDir($src, $dst) {
    $dir = opendir($src);
    if (!is_dir($dst)) @mkdir($dst, 0755, true);
    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..') continue;
        $srcPath = $src . '/' . $file;
        $dstPath = $dst . '/' . $file;
        if ($file === 'install.php') continue; // Don't overwrite ourselves
        if (is_dir($srcPath)) {
            // Skip data content files but create the dir
            if ($file === 'data') {
                if (!is_dir($dstPath)) @mkdir($dstPath, 0755, true);
                // Only copy .htaccess and .gitkeep
                foreach (['/.htaccess', '/.gitkeep'] as $keep) {
                    if (file_exists($srcPath . $keep)) {
                        copy($srcPath . $keep, $dstPath . $keep);
                    }
                }
                continue;
            }
            copyDir($srcPath, $dstPath);
        } else {
            copy($srcPath, $dstPath);
        }
    }
    closedir($dir);
}

function deleteDir($dir) {
    if (!is_dir($dir)) return;
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        is_dir($path) ? deleteDir($path) : @unlink($path);
    }
    @rmdir($dir);
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
    text-decoration:none;
    display:inline-block;
    text-align:center;
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
  .success-icon {font-size:48px;margin-bottom:16px;color:#10b981}
  .success-text {font-size:16px;color:#0f172a;font-weight:600;margin-bottom:8px}
  .success-sub {font-size:13px;color:#64748b;margin-bottom:24px}
  .info {
    background:#f0fdf4;
    color:#166534;
    padding:10px 14px;
    border-radius:10px;
    font-size:12px;
    margin-bottom:16px;
    border:1px solid #bbf7d0;
  }
  .steps {display:flex;gap:8px;margin-bottom:24px;justify-content:center}
  .step {
    width:32px;height:32px;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    font-size:13px;font-weight:700;
    background:#e2e8f0;color:#64748b;
  }
  .step.active {background:linear-gradient(135deg,#667eea,#764ba2);color:#fff}
  .step.done {background:#10b981;color:#fff}
  .step-line {width:40px;height:2px;background:#e2e8f0;align-self:center}
  .step-line.done {background:#10b981}
</style>
</head>
<body>
<div class="install-box">
  <div class="logo">
    <?php if (file_exists(__DIR__ . '/logo.png')): ?>
      <img src="logo.png" alt="TaskFlow">
    <?php else: ?>
      <div style="font-size:32px;font-weight:800;text-align:center;margin-bottom:8px;background:linear-gradient(135deg,#667eea,#764ba2);-webkit-background-clip:text;-webkit-text-fill-color:transparent">TaskFlow</div>
    <?php endif; ?>
  </div>

  <?php if ($success): ?>
    <div class="steps">
      <div class="step done">1</div>
      <div class="step-line done"></div>
      <div class="step done">2</div>
    </div>
    <div class="success-box">
      <div class="success-icon">&#10003;</div>
      <div class="success-text">Installation complete!</div>
      <div class="success-sub">TaskFlow has been set up successfully. The installer has been removed.</div>
      <a href="index.php" class="btn" style="width:auto;padding:12px 32px">Open TaskFlow</a>
    </div>

  <?php elseif ($filesExist): ?>
    <div class="steps">
      <div class="step done">1</div>
      <div class="step-line done"></div>
      <div class="step active">2</div>
    </div>
    <div class="title">Create Admin Account</div>
    <div class="subtitle">Set up your administrator login</div>

    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
      <input type="hidden" name="action" value="createuser">
      <div class="form-group">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-input" placeholder="e.g. Max Mustermann" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required autofocus>
      </div>
      <div class="form-group">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-input" placeholder="e.g. admin" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-input" placeholder="Min. 4 characters" required>
      </div>
      <div class="form-group">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="password_confirm" class="form-input" placeholder="Repeat password" required>
      </div>
      <button type="submit" class="btn">Create Account & Finish</button>
    </form>

  <?php else: ?>
    <div class="steps">
      <div class="step active">1</div>
      <div class="step-line"></div>
      <div class="step">2</div>
    </div>
    <div class="title">Installation</div>
    <div class="subtitle">Download TaskFlow from GitHub</div>

    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="info">
      This will download all files from<br>
      <strong><?= htmlspecialchars($repoUrl) ?></strong>
    </div>

    <form method="post">
      <input type="hidden" name="action" value="download">
      <button type="submit" class="btn" onclick="this.textContent='Downloading...';this.disabled=true;this.form.submit()">Download & Install TaskFlow</button>
    </form>
  <?php endif; ?>
</div>
</body>
</html>
