<p align="center">
  <img src="logo.png" alt="TaskFlow" width="400">
</p>

<p align="center">
  <strong>Project & Task Management</strong><br>
  Modern project management app with PHP backend and JSON file storage.
</p>

---

## Features

- User login & registration
- Create and manage projects
- To-do lists with categories and priorities
- 6 color themes
- Archive function for completed tasks
- Export/Import as JSON
- In-app update system (git pull)
- Dynamic animations & smooth transitions
- Multi-language support (DE/EN)

## Requirements

- PHP 7.4 or higher
- Web server (Apache, Nginx, or PHP built-in server)
- Write permissions for the `/data` directory

## Installation

### Option 1: With Apache/Nginx

1. Copy the project to your web server directory:
   ```bash
   cp -r taskflow /var/www/html/taskflow
   ```

2. Set write permissions for the data folder:
   ```bash
   chmod 755 /var/www/html/taskflow/data
   ```

3. Open in your browser:
   ```
   http://localhost/taskflow
   ```

### Option 2: PHP Built-in Server (Development)

1. Navigate to the project directory:
   ```bash
   cd taskflow
   ```

2. Start the PHP server:
   ```bash
   php -S localhost:8000
   ```

3. Open in your browser:
   ```
   http://localhost:8000
   ```

## Default Login

- **Username:** admin
- **Password:** admin

## File Structure

```
taskflow/
├── index.php          # Main application (HTML/CSS)
├── app.js             # Frontend logic (JavaScript)
├── api.php            # Backend API
├── version.json       # Version info for update system
├── logo.png           # Application logo
├── lang/
│   ├── de.json        # German translations
│   └── en.json        # English translations
├── data/              # JSON data storage
│   ├── users.json     # User data
│   └── projects.json  # Projects & to-dos
└── README.md
```

## Security

**IMPORTANT:** For production use:

1. **Change passwords:** Change the default admin password!

2. **Use HTTPS:** Never run over HTTP in production!

3. **Protect the data folder:**
   ```apache
   # .htaccess in /data
   Deny from all
   ```

4. **Session security:** Adjust session settings in `api.php`:
   ```php
   session_set_cookie_params([
       'secure' => true,
       'httponly' => true,
       'samesite' => 'Strict'
   ]);
   ```

## API Endpoints

All requests to `api.php?action=...`:

| Action | Description |
|--------|-------------|
| `login` | User login |
| `register` | Create new user |
| `logout` | Sign out |
| `getSession` | Check current session |
| `getUsers` | Get all users |
| `createUser` | Create user (admin) |
| `deleteUser` | Delete user (admin) |
| `changePassword` | Change password |
| `getProjects` | Get all projects |
| `createProject` | Create new project |
| `updateProject` | Edit project |
| `deleteProject` | Delete project |
| `addTodo` | Add to-do |
| `updateTodo` | Update to-do |
| `deleteTodo` | Delete to-do |
| `exportData` | Export data |
| `importData` | Import data |
| `getVersion` | Get current version |
| `checkUpdate` | Check for updates |
| `doUpdate` | Install update (git pull) |

## Updates

TaskFlow includes a built-in update system. Go to **Settings > Updates** and click "Check for update". If a new version is available, click "Install update" to pull the latest changes from GitHub.

User data (`data/users.json`, `data/projects.json`) is excluded from updates via `.gitignore`.

## License

Copyright (c) 2026 Florian Hesse. All rights reserved.

---

**Made with TaskFlow**
