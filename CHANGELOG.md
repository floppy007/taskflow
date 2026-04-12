# Changelog

Dieses Projekt dokumentiert Releases zweisprachig in Deutsch und Englisch.

---

## v1.72

### Deutsch

#### Härtung & Betrieb
- Session-Handling verbessert: Session-ID wird nach Login regeneriert, Logout beendet Session und Cookie sauber
- API-Debug-Ausgaben sind standardmäßig deaktiviert und lassen sich gezielt über `TASKFLOW_DEBUG=true` aktivieren
- CORS wird nicht mehr pauschal mit `*` geöffnet, sondern nur same-origin zugelassen

#### Update-Verhalten
- In-App-Updates brechen jetzt bei lokalen Änderungen im Git-Arbeitsbaum bewusst ab
- Update-Pfad nutzt `git pull --ff-only` statt potenziell destruktivem Stash-/Checkout-Verhalten
- Datenmigrationen laufen weiterhin automatisch nach erfolgreichen Code-Updates

#### Code & Dokumentation
- unvollständigen/toten Logo-Pfad im Frontend bereinigt und Logo-Anwendung für Login-, Forgot-, Reset- und Sidebar-Bereich vereinheitlicht
- neue technische Doku ergänzt:
  - Architektur
  - Datenmodell
  - Systemüberblick
  - Doku-Index
- README auf die erweiterte Doku verlinkt

### English

#### Hardening & Runtime
- Improved session handling: session ID is regenerated after login, logout now clears session and cookie cleanly
- API debug output is disabled by default and can be explicitly enabled with `TASKFLOW_DEBUG=true`
- CORS is no longer opened globally with `*`; it now allows same-origin requests only

#### Update Behavior
- In-app updates now abort when local Git working tree changes are present
- Update path now uses `git pull --ff-only` instead of potentially destructive stash/checkout behavior
- Data migrations still run automatically after successful code updates

#### Code & Documentation
- cleaned up incomplete/dead frontend logo path and unified logo application for login, forgot-password, reset-password and sidebar areas
- added new technical documentation:
  - architecture
  - data model
  - system overview
  - documentation index
- linked the extended docs from the main README

---

## v1.71

### Deutsch

#### Bugfix: Docker Passwort-Handling
- Admin-Passwort wird bei jedem Container-Start aus ENV erzwungen
- korrupte `users.json` wird automatisch erkannt und neu erstellt
- fehlender Admin-User wird in bestehender `users.json` automatisch angelegt
- Schreibfehler werden erkannt und stoppen den Container
- Passwort-Hash wird nach dem Schreiben zurückgelesen und verifiziert

### English

#### Bugfix: Docker Password Handling
- admin password is enforced from ENV on every container start
- corrupt `users.json` is automatically detected and recreated
- missing admin user is automatically created in existing `users.json`
- write errors are detected and stop container startup
- password hash is read back and verified after writing

---

## v1.70

### Deutsch

#### Neues Feature: Passwort-Reset via E-Mail
- "Passwort vergessen?"-Link auf der Login-Seite
- Benutzer können ihr Passwort selbst per E-Mail zurücksetzen
- sicherer Token-basierter Reset-Flow
- nur für lokale Benutzer, nicht für LDAP-User

#### SMTP-Konfiguration
- SMTP-Einstellungskarte in den Admin-Settings
- Unterstützung für STARTTLS, SSL/TLS und AUTH LOGIN
- Test-E-Mail-Funktion zur Verifikation

### English

#### New Feature: Password Reset via Email
- "Forgot password?" link on the login page
- users can self-reset their password via email
- secure token-based reset flow
- local users only, not LDAP users

#### SMTP Configuration
- SMTP settings card in admin settings
- support for STARTTLS, SSL/TLS and AUTH LOGIN
- test mail function for verification
