# TaskFlow Datenmodell

> Überblick über die JSON-Dateien und die wichtigsten fachlichen Strukturen.

---

## Speicherort

Alle Laufzeitdaten liegen unter:

`data/`

Wichtige Dateien:

- `users.json`
- `projects.json`
- `activity.json`
- `notifications.json`
- `ldap_config.json`
- `smtp_config.json`
- `password_resets.json`
- `migration_version.json`

Zusätzlich:

- `data/attachments/<projectId>/<todoId>/`

---

## `users.json`

Typische Felder:

- `id`
- `username`
- `password`
- `name`
- `role`
- `source`
- `email`
- `createdAt`
- `preferences`

Hinweise:

- lokale Benutzer speichern Passwort-Hashes
- LDAP-Benutzer können ohne lokales Passwortmodell vorkommen
- `preferences` wird per Migration ergänzt, falls bei Altständen nicht vorhanden

---

## `projects.json`

Zentrale Geschäftsdatei. Sie enthält Projekte und ihre eingebetteten Todos.

Projektfelder:

- `id`
- `name`
- `desc`
- `color`
- `createdBy`
- `createdAt`
- `members`
- `todos`
- optional `deletedAt`

Mitgliederstruktur:

- `userId`
- `role`
- `addedAt`

Todo-Struktur:

- `id`
- `text`
- `done`
- `category`
- `priority`
- `note`
- `dueDate`
- `createdAt`
- optional `closedAt`
- optional `closedBy`
- `attachments`

Soft-Delete:

- gelöschte Projekte werden nicht sofort entfernt
- stattdessen wird `deletedAt` gesetzt
- endgültige Löschung erfolgt nach 30 Tagen oder administrativ per Hard-Delete

---

## `activity.json`

Chronologisches Aktivitätsprotokoll.

Typische Felder:

- `id`
- `timestamp`
- `userId`
- `userName`
- `action`
- `details`

---

## `notifications.json`

Benutzerbezogene Benachrichtigungen.

Typische Felder:

- `id`
- `userId`
- `type`
- `data`
- `read`
- `createdAt`

---

## Zusatzdateien

### `ldap_config.json`

Speichert LDAP-/AD-Konfiguration.

### `smtp_config.json`

Speichert SMTP-Konfiguration.

### `password_resets.json`

Temporärer Speicher für Reset-Tokens.

### `migration_version.json`

Speichert den zuletzt ausgeführten Datenmigrationsstand.

---

## Konsequenzen des Datenmodells

Vorteile:

- sehr einfach zu sichern
- leicht zu lesen und zu debuggen
- kein Datenbank-Setup nötig

Nachteile:

- `projects.json` ist zentrale Hot-Spot-Datei
- größere Installationen skalieren damit nur begrenzt
- parallele Schreibzugriffe sind empfindlicher als bei DB-basiertem Modell
