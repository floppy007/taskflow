# TaskFlow API Dokumentation (`api.php`)

> Alle Endpoints werden via `api.php?action=<action>` aufgerufen.

---

## Authentifizierung & Session

### `login`
- **Methode:** POST
- **Parameter:** `username`, `password`
- **Beschreibung:** Authentifiziert Benutzer (lokal oder LDAP). Setzt Session.
- **Rückgabe:** User-Objekt (id, username, name, role, source)

### `register`
- **Methode:** POST
- **Parameter:** –
- **Beschreibung:** Selbstregistrierung deaktiviert – gibt immer Fehler zurück.
- **Rückgabe:** Fehlermeldung

### `logout`
- **Methode:** POST
- **Parameter:** –
- **Beschreibung:** Zerstört die aktuelle Session.
- **Rückgabe:** Erfolgsbestätigung

### `getSession`
- **Methode:** GET
- **Parameter:** –
- **Beschreibung:** Gibt aktuelle Session-Daten inkl. ungelesener Benachrichtigungen zurück.
- **Rückgabe:** User-Objekt mit `unreadNotifications`-Zähler

### `changePassword`
- **Methode:** POST
- **Parameter:** `currentPassword`, `newPassword`
- **Beschreibung:** Ändert Passwort des eingeloggten Users. Schlägt bei LDAP-Usern fehl.
- **Rückgabe:** Erfolgsmeldung

---

## Benutzerverwaltung (Admin)

### `getUsers`
- **Methode:** GET
- **Parameter:** –
- **Berechtigung:** Admin
- **Beschreibung:** Listet alle Benutzer (ohne Passwörter).
- **Rückgabe:** Array von User-Objekten

### `createUser`
- **Methode:** POST
- **Parameter:** `name`, `username`, `password`
- **Optional:** `role` (Standard: 'user'), `email`
- **Berechtigung:** Admin
- **Beschreibung:** Erstellt neuen lokalen Benutzer.
- **Rückgabe:** Neues User-Objekt

### `updateUserEmail`
- **Methode:** POST
- **Parameter:** `id`, `email`
- **Berechtigung:** Admin
- **Beschreibung:** Setzt E-Mail-Adresse eines Benutzers.
- **Rückgabe:** Erfolgsmeldung

### `deleteUser`
- **Methode:** POST
- **Parameter:** `id`
- **Berechtigung:** Admin
- **Beschreibung:** Löscht Benutzer (kann sich nicht selbst löschen).
- **Rückgabe:** Erfolgsmeldung

### `updateUserRole`
- **Methode:** POST
- **Parameter:** `id`, `role` ('admin' oder 'user')
- **Berechtigung:** Admin
- **Beschreibung:** Ändert Benutzerrolle. Verhindert Degradierung des letzten Admins.
- **Rückgabe:** Erfolgsmeldung

### `getAllUsers`
- **Methode:** GET
- **Parameter:** –
- **Beschreibung:** Gibt vereinfachte Benutzerliste (id, name, username) zurück – für Mitglieder-Auswahl.
- **Rückgabe:** Array von `{id, name, username}`

---

## Projektverwaltung

### `getProjects`
- **Methode:** GET
- **Parameter:** –
- **Beschreibung:** Gibt Projekte des Users zurück (Admin sieht alle, User sieht eigene + Mitglied-Projekte). Soft-Deletes älter als 30 Tage werden bereinigt.
- **Rückgabe:** Array von Projekt-Objekten

### `createProject`
- **Methode:** POST
- **Parameter:** `name`
- **Optional:** `desc`, `color`
- **Beschreibung:** Erstellt neues Projekt. Ersteller wird automatisch als Mitglied hinzugefügt.
- **Rückgabe:** Neues Projekt-Objekt

### `updateProject`
- **Methode:** POST
- **Parameter:** `id`
- **Optional:** `name`, `desc`, `color`
- **Beschreibung:** Aktualisiert Projekt-Metadaten.
- **Rückgabe:** Erfolgsmeldung

### `deleteProject`
- **Methode:** POST
- **Parameter:** `id`
- **Beschreibung:** Soft-Delete des Projekts (markiert mit `deletedAt`). Nur Projekt-Owner oder Admin.
- **Rückgabe:** Erfolgsmeldung

### `getDeletedProjects`
- **Methode:** GET
- **Parameter:** –
- **Beschreibung:** Listet soft-gelöschte Projekte mit verbleibenden Tagen bis zur endgültigen Löschung (30-Tage-Fenster).
- **Rückgabe:** Array von gelöschten Projekten mit `daysLeft`-Feld

### `restoreProject`
- **Methode:** POST
- **Parameter:** `id`
- **Beschreibung:** Stellt soft-gelöschtes Projekt wieder her. Nur Owner oder Admin.
- **Rückgabe:** Erfolgsmeldung

### `permanentDeleteProject`
- **Methode:** POST
- **Parameter:** `id`
- **Berechtigung:** Admin
- **Beschreibung:** Löscht Projekt dauerhaft aus dem System.
- **Rückgabe:** Erfolgsmeldung

---

## Projektmitglieder

### `addMember`
- **Methode:** POST
- **Parameter:** `projectId`, `userId`
- **Optional:** `role` (Standard: 'editor', Optionen: 'editor', 'viewer')
- **Beschreibung:** Fügt Benutzer zum Projekt hinzu. Nur Owner oder Admin. Sendet Benachrichtigung.
- **Rückgabe:** Erfolgsmeldung

### `removeMember`
- **Methode:** POST
- **Parameter:** `projectId`, `userId`
- **Beschreibung:** Entfernt Benutzer aus Projekt. Nur Owner oder Admin. Owner kann nicht entfernt werden.
- **Rückgabe:** Erfolgsmeldung

### `updateMemberRole`
- **Methode:** POST
- **Parameter:** `projectId`, `userId`, `role` ('editor' oder 'viewer')
- **Beschreibung:** Ändert Mitglieder-Rolle. Nur Owner oder Admin. Owner-Rolle kann nicht geändert werden.
- **Rückgabe:** Erfolgsmeldung

---

## Todo/Aufgaben-Verwaltung

### `addTodo`
- **Methode:** POST
- **Parameter:** `projectId`, `text`
- **Optional:** `category` (Standard: 'Other'), `priority` (Standard: 'medium'), `note`, `dueDate`
- **Beschreibung:** Erstellt neue Aufgabe im Projekt.
- **Rückgabe:** Erfolgsmeldung

### `updateTodo`
- **Methode:** POST
- **Parameter:** `projectId`, `todoId`, `updates` (Objekt mit zu ändernden Feldern)
- **Beschreibung:** Aktualisiert Todo-Felder. Setzt automatisch `closedBy`/`closedAt` bei Erledigung, löscht bei Wiedereröffnung.
- **Rückgabe:** Erfolgsmeldung

### `deleteTodo`
- **Methode:** POST
- **Parameter:** `projectId`, `todoId`
- **Beschreibung:** Löscht Todo und zugehörige Anhang-Dateien.
- **Rückgabe:** Erfolgsmeldung

### `reorderTodos`
- **Methode:** POST
- **Parameter:** `projectId`, `todoIds` (Array von IDs in neuer Reihenfolge)
- **Beschreibung:** Sortiert Todos innerhalb eines Projekts neu.
- **Rückgabe:** Erfolgsmeldung

---

## Anhänge (Attachments)

### `uploadAttachment`
- **Methode:** POST (multipart/form-data)
- **Parameter:** `projectId` (POST), `todoId` (POST), `file` (FILE)
- **Max. Größe:** 10 MB
- **Beschreibung:** Lädt Dateianhang zu einem Todo hoch. Erstellt Verzeichnisstruktur. Bereinigt Dateiname.
- **Rückgabe:** Erfolgsmeldung

### `deleteAttachment`
- **Methode:** POST
- **Parameter:** `projectId`, `todoId`, `attachmentId`
- **Beschreibung:** Entfernt Anhang-Datei und Metadaten.
- **Rückgabe:** Erfolgsmeldung

### `downloadAttachment`
- **Methode:** GET
- **Parameter:** `projectId`, `todoId`, `attachmentId`
- **Beschreibung:** Gibt Anhang-Datei zum Download zurück mit passenden Headern.
- **Rückgabe:** Dateiinhalt (binär)

---

## Benachrichtigungen

### `getNotifications`
- **Methode:** GET
- **Parameter:** –
- **Beschreibung:** Gibt ungelesene Benachrichtigungen des aktuellen Users zurück.
- **Rückgabe:** Array von ungelesenen Benachrichtigungen

### `dismissNotifications`
- **Methode:** POST
- **Parameter:** –
- **Optional:** `ids` (Array von Notification-IDs, leer = alle des Users)
- **Beschreibung:** Markiert Benachrichtigungen als gelesen.
- **Rückgabe:** Erfolgsmeldung

---

## Benutzereinstellungen

### `getPreferences`
- **Methode:** GET
- **Parameter:** –
- **Beschreibung:** Gibt Benutzer-Einstellungen zurück (theme, darkMode, lang).
- **Rückgabe:** Preferences-Objekt

### `savePreferences`
- **Methode:** POST
- **Parameter:** `preferences` (Objekt mit theme/darkMode/lang)
- **Beschreibung:** Speichert Benutzer-Einstellungen. Erlaubte Keys: theme, darkMode, lang.
- **Rückgabe:** Erfolgsmeldung

---

## Daten Import/Export

### `exportData`
- **Methode:** GET
- **Parameter:** –
- **Beschreibung:** Exportiert alle Benutzer (ohne Passwörter) und Projekte als JSON.
- **Rückgabe:** `{users, projects, exportedAt}`

### `importData`
- **Methode:** POST
- **Parameter:** `users`, `projects`
- **Beschreibung:** Importiert Benutzer- und Projektdaten. Hasht automatisch Klartext-Passwörter.
- **Rückgabe:** Erfolgsmeldung

---

## Version & Updates

### `getVersion`
- **Methode:** GET
- **Parameter:** –
- **Beschreibung:** Gibt aktuelle App-Version aus version.json zurück.
- **Rückgabe:** `{version, date, repo}`

### `checkUpdate`
- **Methode:** GET
- **Parameter:** –
- **Beschreibung:** Vergleicht lokale Version mit Remote-Version auf GitHub.
- **Rückgabe:** `{update_available (bool), local, remote, remote_date}`

### `doUpdate`
- **Methode:** POST
- **Parameter:** –
- **Beschreibung:** Führt `git pull` aus. Stasht lokale Änderungen, zieht von origin/main, triggert Datenmigrationen.
- **Rückgabe:** `{message, version, output}`

---

## LDAP/AD Integration

### `getLdapConfig`
- **Methode:** GET
- **Parameter:** –
- **Berechtigung:** Admin
- **Beschreibung:** Gibt LDAP-Konfiguration zurück (bind_password maskiert als '********').
- **Rückgabe:** LDAP-Config-Objekt

### `saveLdapConfig`
- **Methode:** POST
- **Parameter:** `enabled`, `server`, `port`, `use_tls`, `base_dn`, `bind_user_dn`, `bind_password`, `search_filter`, `user_ou`, `username_attribute`, `display_name_attribute`, `email_attribute`
- **Berechtigung:** Admin
- **Beschreibung:** Speichert LDAP-Konfiguration. Behält bestehendes Passwort bei '********'.
- **Rückgabe:** Erfolgsmeldung

### `testLdapConnection`
- **Methode:** POST
- **Parameter:** – (nutzt gespeicherte Config)
- **Berechtigung:** Admin
- **Beschreibung:** Testet LDAP-Verbindung und zählt verfügbare Benutzer.
- **Rückgabe:** `{user_count}` mit Erfolgsmeldung

### `importLdapUsers`
- **Methode:** POST
- **Parameter:** – (nutzt gespeicherte Config)
- **Berechtigung:** Admin
- **Beschreibung:** Importiert/synchronisiert Benutzer aus LDAP. Erstellt neue, aktualisiert bestehende, überspringt lokale mit Konflikten.
- **Rückgabe:** `{imported, updated, skipped}` Zähler

---

## SMTP / E-Mail

### `getSmtpConfig`
- **Methode:** GET
- **Parameter:** –
- **Berechtigung:** Admin
- **Beschreibung:** Gibt SMTP-Konfiguration zurück (Passwort maskiert als '********').
- **Rückgabe:** SMTP-Config-Objekt

### `saveSmtpConfig`
- **Methode:** POST
- **Parameter:** `enabled`, `host`, `port`, `encryption`, `username`, `password`, `from_email`, `from_name`
- **Berechtigung:** Admin
- **Beschreibung:** Speichert SMTP-Konfiguration. Behält bestehendes Passwort bei '********'.
- **Rückgabe:** Erfolgsmeldung

### `testSmtpConfig`
- **Methode:** POST
- **Parameter:** `email`
- **Berechtigung:** Admin
- **Beschreibung:** Sendet Test-E-Mail über die gespeicherte SMTP-Konfiguration.
- **Rückgabe:** Erfolgsmeldung

---

## Passwort-Reset

### `requestPasswordReset`
- **Methode:** POST
- **Parameter:** `identifier` (Username oder E-Mail)
- **Berechtigung:** Public (keine Anmeldung nötig)
- **Beschreibung:** Generiert Reset-Token und sendet E-Mail mit Reset-Link. Anti-Enumeration: immer gleiche Antwort. Nur lokale User. Token 1h gültig, SHA-256 gehashed gespeichert.
- **Rückgabe:** Immer Erfolgsmeldung (verhindert User-Enumeration)

### `resetPassword`
- **Methode:** POST
- **Parameter:** `token`, `password`
- **Berechtigung:** Public (keine Anmeldung nötig)
- **Beschreibung:** Validiert Token (hash_equals gegen Timing-Attacks), setzt neues Passwort, löscht Token.
- **Rückgabe:** Erfolgsmeldung oder Fehlermeldung bei ungültigem/abgelaufenem Token

---

## Aktivitätsprotokoll

### `getActivity`
- **Methode:** GET
- **Parameter:** –
- **Optional:** `count` (Standard: 20)
- **Beschreibung:** Gibt letzte Aktivitätsprotokoll-Einträge zurück (max. 200 gespeichert).
- **Rückgabe:** Array von Aktivitäts-Einträgen

---

## Hilfsfunktionen (intern)

| Funktion | Beschreibung |
|----------|-------------|
| `msg($key)` | Gibt lokalisierte Nachricht zurück (de/en) |
| `loadUsers()` / `saveUsers()` | Lädt/Speichert users.json |
| `loadProjects()` / `saveProjects()` | Lädt/Speichert projects.json |
| `loadActivity()` / `saveActivity()` | Lädt/Speichert Aktivitätsprotokoll |
| `loadNotifications()` / `saveNotifications()` | Lädt/Speichert Benachrichtigungen |
| `loadLdapConfig()` / `saveLdapConfig()` | Lädt/Speichert LDAP-Konfiguration |
| `loadSmtpConfig()` / `saveSmtpConfig()` | Lädt/Speichert SMTP-Konfiguration |
| `loadPasswordResets()` / `savePasswordResets()` | Lädt/Speichert Password-Reset-Tokens |
| `cleanExpiredResets()` | Entfernt abgelaufene Reset-Tokens |
| `sendSmtpEmail($to, $subject, $body)` | Sendet HTML-E-Mail via SMTP-Socket (STARTTLS, SSL, AUTH LOGIN) |
| `addNotification()` | Erstellt neuen Benachrichtigungs-Eintrag |
| `logActivity()` | Protokolliert Aktion im Aktivitätslog |
| `response()` | Sendet JSON-Antwort und beendet |
| `requireAdmin()` | Erzwingt Admin-Berechtigung |
| `runPendingMigrations()` | Führt Datenmigrationen aus |

---

## Datenmigrations-System

Migrationen laufen automatisch beim Start wenn die Version veraltet ist:

| Version | Migration |
|---------|-----------|
| v1 | Members-Array zu Projekten hinzufügen |
| v2 | `addedAt`-Feld für Mitglieder sicherstellen |
| v3 | `role`-Feld für Benutzer sicherstellen |
| v4 | `preferences`-Objekt für Benutzer sicherstellen |
| v5 | `attachments`-Array zu Todos hinzufügen |
| v6 | `source`-Feld (local/ldap) zu Benutzern hinzufügen |
| v7 | `email`-Feld zu Benutzern hinzufügen |
