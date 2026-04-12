# TaskFlow Systemüberblick

> Zusammenführung der bestehenden Doku mit einem Blick auf den tatsächlichen Code-Stand.

---

## Vorhandene Doku

Im Repo existieren bereits diese spezialisierten Dokumente:

- [app.md](app.md): Frontend-Funktionsübersicht für `app.js`
- [api.md](api.md): Action-/Endpoint-Übersicht für `api.php`
- [install.md](install.md): Installer-, Docker- und Einstiegspfade

Diese Datei ergänzt die drei Dokumente um:

- Gesamtarchitektur
- reale Laufzeitpfade
- Datenablage
- technische Grenzen und Betriebsnotizen

---

## Technischer Stack

- PHP-Anwendung ohne Framework
- Vanilla JavaScript im Frontend
- JSON-Dateispeicher statt Datenbank
- Apache/PHP im Bare-Metal- oder Docker-Betrieb
- optionale LDAP-/AD-Anbindung
- optionale SMTP-Anbindung

---

## Wichtige Dateien im Root

- `index.php`: Einstieg und UI-Shell
- `app.js`: komplette Frontend-Anwendungslogik
- `api.php`: komplette Backend-API
- `install.php`: Ein-Datei-Installer
- `Dockerfile`: Image für Apache/PHP
- `docker-compose.yml`: lokales Compose-Setup
- `docker-entrypoint.sh`: Docker-Bootstrap
- `version.json`: Versions- und Repo-Metadaten
- `lang/*.json`: Sprachdateien

---

## Betriebsmodi

### Bare Metal

- Installer lädt Code von GitHub
- `data/` liegt lokal auf dem Webserver
- Updates können aus der App angestoßen werden

### Docker

- Container auf Basis `php:8.2-apache`
- Daten im Volume unter `/var/www/html/data`
- Admin aus `TASKFLOW_ADMIN_*`
- LDAP-Unterstützung ist im Image direkt eingebaut

Wichtig:

- Der Docker-Entrypoint setzt den konfigurierten Admin bei Container-Starts aktiv nach
- Das ist praktisch für Bootstrap, aber auch eine bewusste Betriebsentscheidung: Container-ENV hat Vorrang
- In-App-Git-Updates sind bewusst konservativer und brechen bei Dirty-Worktrees ab

---

## Fachliche Module im Code

- Auth & Session
- Benutzer und Rollen
- Projekte und Mitglieder
- To-dos, Kanban und Archiv
- Anhänge
- Benachrichtigungen
- LDAP / SMTP / Passwort-Reset
- In-App Update und Datenmigration

---

## Praktische Lesereihenfolge für den Code

1. `README.md`
2. `docs/system-overview.md`
3. `docs/architecture.md`
4. `docs/data-model.md`
5. `docs/api.md`
6. `docs/app.md`
7. `docker-entrypoint.sh`
8. `api.php`
9. `app.js`
