<p align="center">
  <img src="logo.png" alt="TaskFlow" width="400">
</p>

<p align="center">
  <strong>Project & Task Management</strong><br>
  Modern project management app with PHP backend and JSON file storage.
</p>

---

> **[Deutsch](#deutsch)** | **[English](#english)**

---

<a id="english"></a>

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
- Setup wizard for easy first-time installation

## Requirements

- PHP 7.4 or higher
- Web server (Apache, Nginx, or PHP built-in server)
- Write permissions for the `/data` directory

## Installation

### 1. Upload Installer

Download [`install.php`](https://raw.githubusercontent.com/floppy007/taskflow/main/install.php) and copy it to your web server directory (e.g. `/var/www/html/taskflow/`).

### 2. Open in Browser

```
http://localhost/taskflow/install.php
```

### 3. Download Files

Click **"Download & Install TaskFlow"** — the installer will automatically download all files from GitHub.

### 4. Create Admin Account

Enter your admin credentials (name, username, password) and click **"Create Account & Finish"**.

> The installer **deletes itself automatically** after successful setup.

### Alternative: Manual Installation

```bash
git clone https://github.com/floppy007/taskflow.git /var/www/html/taskflow
chmod 755 /var/www/html/taskflow/data
```

Then open `http://localhost/taskflow` and complete the setup wizard.

## File Structure

```
taskflow/
├── install.php        # Setup wizard (auto-deletes after install)
├── index.php          # Main application (HTML/CSS)
├── app.js             # Frontend logic (JavaScript)
├── api.php            # Backend API
├── version.json       # Version info for update system
├── logo.png           # Application logo
├── lang/
│   ├── de.json        # German translations
│   └── en.json        # English translations
├── data/              # JSON data storage (created by installer)
│   ├── .htaccess      # Access protection
│   ├── users.json     # User data
│   └── projects.json  # Projects & to-dos
└── README.md
```

## Security

**IMPORTANT:** For production use:

1. **Use HTTPS:** Never run over HTTP in production!

2. **Protect the data folder:** The installer creates a `.htaccess` in `/data` automatically. Verify it exists:
   ```apache
   Order deny,allow
   Deny from all
   ```

3. **Verify installer is removed:** After installation, make sure `install.php` no longer exists. If it does, delete it manually.

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

Free to use for personal and commercial projects. The copyright footer must not be modified or removed.

---

<a id="deutsch"></a>

## Deutsch

### Funktionen

- Benutzer-Login & Registrierung
- Projekte erstellen und verwalten
- To-Do-Listen mit Kategorien und Prioritäten
- 6 verschiedene Farbthemen
- Archiv-Funktion für erledigte Aufgaben
- Export/Import als JSON
- In-App Update-System (git pull)
- Dynamische Animationen & sanfte Übergänge
- Mehrsprachig (DE/EN)
- Setup-Assistent für einfache Erstinstallation

### Voraussetzungen

- PHP 7.4 oder höher
- Webserver (Apache, Nginx oder PHP Built-in Server)
- Schreibrechte für das `/data` Verzeichnis

### Installation

#### 1. Installer hochladen

[`install.php`](https://raw.githubusercontent.com/floppy007/taskflow/main/install.php) herunterladen und auf den Webserver kopieren (z.B. `/var/www/html/taskflow/`).

#### 2. Im Browser öffnen

```
http://localhost/taskflow/install.php
```

#### 3. Dateien herunterladen

Auf **"Download & Install TaskFlow"** klicken — der Installer lädt alle Dateien automatisch von GitHub herunter.

#### 4. Admin-Account erstellen

Admin-Zugangsdaten eingeben (Name, Benutzername, Passwort) und auf **"Create Account & Finish"** klicken.

> Der Installer **löscht sich automatisch** nach erfolgreicher Einrichtung.

#### Alternative: Manuelle Installation

```bash
git clone https://github.com/floppy007/taskflow.git /var/www/html/taskflow
chmod 755 /var/www/html/taskflow/data
```

Dann `http://localhost/taskflow` öffnen und den Setup-Assistenten durchlaufen.

### Sicherheit

**WICHTIG:** Für den Produktiv-Einsatz:

1. **HTTPS verwenden:** Niemals über HTTP in Produktion betreiben!
2. **data-Ordner schützen:** Der Installer erstellt automatisch eine `.htaccess` im `/data`-Ordner. Prüfe, ob sie vorhanden ist.
3. **Installer gelöscht?** Nach der Installation sicherstellen, dass `install.php` nicht mehr existiert. Falls doch, manuell löschen.
4. **Session-Sicherheit:** In `api.php` die Session-Einstellungen anpassen.

### Updates

TaskFlow hat ein eingebautes Update-System. Unter **Einstellungen > Updates** auf "Update prüfen" klicken. Falls eine neue Version verfügbar ist, kann sie direkt per "Update installieren" von GitHub geladen werden.

Benutzerdaten (`data/users.json`, `data/projects.json`) werden bei Updates nicht überschrieben.

### Lizenz

Frei verwendbar für persönliche und kommerzielle Projekte. Der Copyright-Footer darf nicht verändert oder entfernt werden.

---

**Made with TaskFlow**
