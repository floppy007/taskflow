# TaskFlow - Projekt & Aufgabenverwaltung

Moderne Projekt-Management-Anwendung mit PHP-Backend und JSON-Dateispeicherung.

## 🚀 Features

- ✅ Benutzer-Login & Registrierung
- 📁 Projekte erstellen und verwalten
- ✓ To-Do-Listen mit Kategorien und Prioritäten
- 🎨 6 verschiedene Farbthemen
- 📦 Archiv-Funktion für erledigte Aufgaben
- 📤 Export/Import als JSON
- 💾 Daten werden in JSON-Dateien gespeichert

## 📋 Voraussetzungen

- PHP 7.4 oder höher
- Webserver (Apache, Nginx, oder PHP Built-in Server)
- Schreibrechte für das `/data` Verzeichnis

## 🛠️ Installation

### Variante 1: Mit Apache/Nginx

1. Projekt in den Webserver-Ordner kopieren:
   ```bash
   cp -r taskflow-php /var/www/html/taskflow
   ```

2. Schreibrechte für data-Ordner setzen:
   ```bash
   chmod 755 /var/www/html/taskflow/data
   ```

3. Im Browser öffnen:
   ```
   http://localhost/taskflow
   ```

### Variante 2: Mit PHP Built-in Server (Entwicklung)

1. In das Projekt-Verzeichnis wechseln:
   ```bash
   cd taskflow-php
   ```

2. PHP Server starten:
   ```bash
   php -S localhost:8000
   ```

3. Im Browser öffnen:
   ```
   http://localhost:8000
   ```

## 👤 Standard-Login

- **Benutzername:** admin
- **Passwort:** admin

## 📁 Dateistruktur

```
taskflow-php/
├── index.php          # Haupt-Anwendung (HTML/CSS)
├── app.js             # Frontend-Logik (JavaScript)
├── api.php            # Backend-API
├── data/              # JSON-Datenspeicher
│   ├── users.json     # Benutzerdaten
│   └── projects.json  # Projekte & To-Dos
└── README.md          # Diese Datei
```

## 🔒 Sicherheit

**WICHTIG:** Für Produktiv-Einsatz:

1. **Passwörter ändern:** Standard-Admin-Passwort ändern!

2. **HTTPS verwenden:** Niemals über HTTP in Produktion!

3. **data-Ordner schützen:** 
   ```apache
   # .htaccess in /data
   Deny from all
   ```

4. **Session-Sicherheit:** In `api.php` Session-Settings anpassen:
   ```php
   session_set_cookie_params([
       'secure' => true,
       'httponly' => true,
       'samesite' => 'Strict'
   ]);
   ```

## 📤 Backup

Die JSON-Dateien im `/data` Ordner können einfach kopiert werden:

```bash
# Backup erstellen
cp -r data data_backup_$(date +%Y%m%d)

# Oder über die App: Einstellungen → Export
```

## 🐛 Troubleshooting

**Problem:** "Permission denied" beim Speichern
- **Lösung:** `chmod 755 data` ausführen

**Problem:** "Session konnte nicht gestartet werden"
- **Lösung:** PHP Session-Verzeichnis prüfen (`session.save_path`)

**Problem:** API gibt keine Antwort
- **Lösung:** Fehler-Log prüfen, PHP-Version checken

## 📝 API-Endpoints

Alle Anfragen an `api.php?action=...`:

- `login` - Benutzer anmelden
- `register` - Neuen Benutzer erstellen
- `logout` - Abmelden
- `getSession` - Aktuelle Session prüfen
- `getUsers` - Alle Benutzer abrufen
- `getProjects` - Alle Projekte abrufen
- `createProject` - Neues Projekt erstellen
- `updateProject` - Projekt bearbeiten
- `deleteProject` - Projekt löschen
- `addTodo` - To-Do hinzufügen
- `updateTodo` - To-Do aktualisieren
- `deleteTodo` - To-Do löschen
- `exportData` - Daten exportieren
- `importData` - Daten importieren

## 💡 Tipps

- **Themes wechseln:** Einstellungen → Farbschema
- **Projekte archivieren:** Aufgaben als "erledigt" markieren, dann archivieren
- **Backup:** Regelmäßig über "Export" sichern

## 🔧 Anpassungen

**Farbschemas anpassen:** In `index.php` die CSS-Variablen unter `:root` ändern

**Standard-Port ändern:** 
```bash
php -S localhost:3000
```

## 📜 Lizenz

Frei verwendbar für persönliche und kommerzielle Projekte.

---

**Viel Erfolg mit TaskFlow! 🚀**
