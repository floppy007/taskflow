# TaskFlow Architektur

> Technischer Überblick über Aufbau, Laufzeit und Verantwortung der Hauptdateien.

---

## Kurzbild

TaskFlow ist eine klassische Single-Page-App ohne Framework:

- `index.php` liefert HTML, CSS und die Shell der Oberfläche
- `app.js` enthält die komplette Frontend-Logik
- `api.php` stellt alle fachlichen Aktionen als JSON-API bereit
- `data/*.json` dient als persistenter Dateispeicher
- `install.php` und `docker-entrypoint.sh` übernehmen Erstinitialisierung

Es gibt keine relationale Datenbank. Alle fachlichen Daten liegen als JSON-Dateien im `data/`-Verzeichnis.

---

## Hauptkomponenten

### `index.php`

Rolle:

- Einstiegspunkt der Web-App
- Redirect zum Installer, wenn noch keine `data/users.json` existiert
- enthält das statische Grundlayout und große Teile des CSS
- bindet `app.js` als komplette SPA-Logik ein

### `app.js`

Rolle:

- vollständige Clientlogik
- View-Wechsel, Modale, Formulare, Suche, Kanban, To-do-Handling, Nutzerverwaltung
- Kommunikation mit `api.php`

### `api.php`

Rolle:

- zentrale JSON-API für alle fachlichen Aktionen
- Session- und Berechtigungsprüfung
- Dateispeicher lesen/schreiben
- LDAP-, SMTP-, Update- und Attachment-Handling

### `install.php`

Rolle:

- Ein-Datei-Installer für Bare-Metal-Deployments
- lädt das Repo von GitHub herunter
- erstellt initialen Admin und die Grunddaten

### `docker-entrypoint.sh`

Rolle:

- Container-Erststart und Admin-Bootstrap
- sichert `data/`
- erzwingt bzw. aktualisiert Admin-Daten aus Umgebungsvariablen

Wichtig:

- Im Docker-Betrieb wird der Admin-Account bei Container-Starts aktiv aus den ENV-Variablen nachgezogen.

---

## Laufzeitmodell

### Erststart

Bare Metal:

- `index.php` erkennt fehlende `users.json`
- Redirect auf `install.php`
- Installer lädt Dateien, erzeugt `data/`, Admin und Grunddateien

Docker:

- `docker-entrypoint.sh` legt `data/` an
- Admin wird aus `TASKFLOW_ADMIN_*` erzeugt oder aktualisiert
- Apache startet danach normal

### Normalbetrieb

- Browser lädt `index.php`
- `app.js` initialisiert Sprache, Session, Version und UI
- Frontend ruft `api.php?action=...` auf
- `api.php` liest/schreibt JSON-Dateien und liefert JSON zurück

### Updatepfad

Webserver-Modus:

- Frontend prüft `version.json` aus GitHub
- `api.php` kann per Git ein Update ausführen
- danach laufen Datenmigrationen

Docker-Modus:

- Update erfolgt über neues Image / Container-Neustart
- Daten bleiben im Volume unter `data/`

---

## Berechtigungsmodell

Globale Rollen:

- `admin`
- `user`

Projektrollen:

- `owner`
- `editor`
- `viewer`

---

## Stärken der Architektur

- sehr niedrige Deploy-Komplexität
- keine Datenbank notwendig
- leicht auf kleinem Server oder NAS betreibbar
- Docker und Bare-Metal beide direkt unterstützt
- Datenstruktur ist für Backups sehr transparent

---

## Grenzen der Architektur

- kein idealer Parallelbetrieb bei vielen gleichzeitigen Schreibzugriffen
- JSON-Dateien wachsen mit der Nutzung und sind kein ideales Multi-User-Backend
- `api.php` bündelt sehr viele Verantwortungen in einer Datei
- Frontend und Backend sind stark gekoppelt
