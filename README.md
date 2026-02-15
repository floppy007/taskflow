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

### 1. Download & Deploy

Copy or clone the project to your web server directory:

```bash
git clone https://github.com/floppy007/taskflow.git /var/www/html/taskflow
```

Set write permissions for the data folder (Linux/Mac):
```bash
chmod 755 /var/www/html/taskflow/data
```

### 2. Run the Setup Wizard

Open TaskFlow in your browser:
```
http://localhost/taskflow
```

You will be automatically redirected to the **setup wizard** (`install.php`). Enter your admin account details:

- Full name
- Username
- Password (+ confirmation)

Click **"Install TaskFlow"** to complete the setup.

> The installer creates the admin user, initializes the data files, and **deletes itself automatically** after successful installation.

### Alternative: PHP Built-in Server (Development)

```bash
cd taskflow
php -S localhost:8000
```

Then open `http://localhost:8000` in your browser.

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

#### 1. Herunterladen & Bereitstellen

Projekt auf den Webserver klonen oder kopieren:

```bash
git clone https://github.com/floppy007/taskflow.git /var/www/html/taskflow
```

Schreibrechte für den data-Ordner setzen (Linux/Mac):
```bash
chmod 755 /var/www/html/taskflow/data
```

#### 2. Setup-Assistent ausführen

TaskFlow im Browser öffnen:
```
http://localhost/taskflow
```

Du wirst automatisch zum **Setup-Assistenten** (`install.php`) weitergeleitet. Gib deine Admin-Zugangsdaten ein:

- Vollständiger Name
- Benutzername
- Passwort (+ Bestätigung)

Klicke auf **"Install TaskFlow"** um die Einrichtung abzuschließen.

> Der Installer erstellt den Admin-Benutzer, initialisiert die Datendateien und **löscht sich automatisch** nach erfolgreicher Installation.

#### Alternative: PHP Built-in Server (Entwicklung)

```bash
cd taskflow
php -S localhost:8000
```

Dann `http://localhost:8000` im Browser öffnen.

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
