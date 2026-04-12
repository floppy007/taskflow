# TaskFlow Installation & Docker Dokumentation

---

## Installer (`install.php`)

### PHP-Funktionen

| Funktion | Parameter | Beschreibung | Rückgabe |
|----------|-----------|-------------|----------|
| `ghContext()` | – | Erstellt HTTP-Stream-Context für GitHub-API-Requests mit Timeout und User-Agent | Stream-Context-Ressource |
| `downloadFile($owner, $repo, $branch, $path, $destDir)` | Owner, Repo, Branch, Pfad, Zielverzeichnis | Lädt eine Datei von GitHub Raw-Content-URL herunter und speichert sie | boolean (true=Erfolg) |

### Installer-Ablauf

1. Prüft ob `users.json` bereits existiert (falls ja: Redirect zu `index.php`)
2. Zeigt Installations-Formular (Admin-Benutzername, Passwort, Name)
3. Lädt alle benötigten Dateien von GitHub herunter
4. Erstellt `data/`-Verzeichnis mit `.htaccess`-Schutz
5. Erstellt initialen Admin-Benutzer in `users.json`
6. Initialisiert leere `projects.json` und `activity.json`

---

## Docker Entrypoint (`docker-entrypoint.sh`)

### Operationen

| Schritt | Beschreibung |
|---------|-------------|
| `mkdir -p "$DATA_DIR"` | Stellt sicher, dass `/var/www/html/data` existiert |
| `chown www-data:www-data "$DATA_DIR"` | Setzt korrekte Berechtigungen für Webserver |
| `.htaccess`-Erstellung | Schützt Data-Verzeichnis vor direktem Web-Zugriff |
| PHP User-Setup | Erstellt oder aktualisiert Admin-User komplett via PHP (sicheres Escaping, keine Shell-Expansion) |
| Erster Start | Erstellt `users.json`, `projects.json`, `activity.json` mit Admin aus Umgebungsvariablen |
| Folgestarts | Erzwingt Admin-Passwort/Name aus Umgebungsvariablen bei jedem Container-Start |
| Korrupte Daten | Erkennt ungültige `users.json` und erstellt sie automatisch neu |
| Fehlender Admin | Legt Admin-User an, falls er in bestehender `users.json` fehlt |
| Verifikation | Liest geschriebene Datei zurück und prüft Passwort-Hash; bricht bei Fehler ab |
| `exec apache2-foreground` | Startet Apache im Vordergrund |

### Umgebungsvariablen

| Variable | Standard | Beschreibung |
|----------|----------|-------------|
| `TASKFLOW_ADMIN_USER` | `admin` | Admin-Benutzername |
| `TASKFLOW_ADMIN_PASS` | `admin` | Admin-Passwort |
| `TASKFLOW_ADMIN_NAME` | `Administrator` | Admin-Anzeigename |

---

## Betriebsnotizen

### Session-Verhalten

- Session-Cookies werden mit `HttpOnly` und `SameSite=Lax` gestartet
- nach erfolgreichem Login wird die Session-ID regeneriert
- beim Logout wird die Session vollständig beendet

### API / CORS / Debug

- Debug-Ausgaben in `api.php` sind standardmäßig deaktiviert
- `TASKFLOW_DEBUG=true` aktiviert PHP-Fehlerausgabe bewusst für Diagnosezwecke
- CORS wird nicht mehr pauschal mit `*` geöffnet, sondern nur same-origin zugelassen

### Update-Verhalten

- In-App-Updates brechen ab, wenn lokale Änderungen im Git-Arbeitsbaum vorhanden sind
- es werden keine lokalen Anpassungen mehr automatisch gestasht oder verworfen
- für Docker bleibt der empfohlene Weg weiterhin: neues Image ziehen und Container neu starten

---

## Frontend-Einstieg (`index.php`)

### Logik

| Element | Beschreibung |
|---------|-------------|
| Redirect-Check (Zeile 2-6) | Wenn `data/users.json` nicht existiert und `install.php` vorhanden ist, wird zum Installer umgeleitet |
| Keine eigenen PHP-Funktionen | Datei enthält nur HTML/CSS/JS-Frontend und den Redirect-Check |
| Version-Info | Kommentar zeigt aktuelle Version und Copyright |
