# TaskFlow Frontend Dokumentation (`app.js`)

> Vanilla JavaScript Single-Page Application

---

## Initialisierung

| Funktion | Parameter | Beschreibung |
|----------|-----------|-------------|
| `init()` | – | Initialisiert App: Sprache, Theme, Version, Session-Check, URL-Parameter `resetToken` erkennen |

---

## Sprache & Übersetzung

| Funktion | Parameter | Beschreibung |
|----------|-----------|-------------|
| `t(key)` | `key` (string) | Übersetzt einen Key aus der aktuellen Sprachdatei |
| `loadLanguage(lang)` | `lang` (string) | Lädt Sprach-JSON-Datei und aktualisiert Übersetzungen |
| `translatePage()` | – | Aktualisiert alle DOM-Elemente mit `data-i18n`-Attributen |
| `updateLangButtons()` | – | Aktualisiert Sprach-Button-UI |
| `changeLanguage(lang)` | `lang` (string) | Lädt Sprache und speichert Einstellung auf Server |

---

## API-Kommunikation

| Funktion | Parameter | Beschreibung |
|----------|-----------|-------------|
| `apiCall(action, data)` | `action` (string), `data` (object, optional) | Asynchroner POST-Request an api.php mit JSON-Body |

---

## Version & Updates

| Funktion | Parameter | Beschreibung |
|----------|-----------|-------------|
| `loadVersion()` | – | Lädt App-Version und aktualisiert Copyright-Footer |
| `checkForUpdate()` | – | Prüft GitHub auf neuere Version |
| `installUpdate()` | – | Lädt Update herunter und installiert es, Reload nach 2 Sek. |

---

## Authentifizierung

| Funktion | Parameter | Beschreibung |
|----------|-----------|-------------|
| `showLogin()` | – | Zeigt Login-Screen, versteckt App, fokussiert Username-Feld |
| `login()` | – | Authentifiziert User via Username/Passwort aus Formular |
| `logout()` | – | Loggt User aus und kehrt zum Login-Screen zurück |
| `showForgotPassword()` | – | Zeigt "Passwort vergessen"-Formular, versteckt Login |
| `backToLogin()` | – | Zurück zum Login-Screen von Forgot/Reset-Screen |
| `requestPasswordReset()` | – | Sendet Reset-Anfrage an API (Username/E-Mail) |
| `showPasswordResetForm(token)` | `token` (string) | Zeigt Passwort-Reset-Formular mit Token aus URL |
| `submitPasswordReset()` | – | Validiert + sendet neues Passwort mit Token an API |
| `showApp()` | – | Zeigt App-UI, setzt User-Info, lädt Einstellungen |
| `loadUserPreferences()` | – | Lädt Benutzer-Einstellungen (Theme, Dark Mode, Sprache) |

---

## Projektverwaltung

| Funktion | Parameter | Beschreibung |
|----------|-----------|-------------|
| `loadProjectsFromServer()` | – | Lädt alle Projekte vom Server |
| `openNewProjectModal()` | – | Zeigt Modal zum Erstellen eines neuen Projekts |
| `renderColorPicker(containerId, inputId, activeColor)` | 3x string | Rendert Farbauswahl-Punkte für Projektfarbe |
| `selectProjectColor(inputId, containerId, color)` | 3x string | Setzt ausgewählte Farbe und markiert Punkt als aktiv |
| `createProject()` | – | Erstellt neues Projekt mit Name, Beschreibung, Farbe |
| `deleteProject(id)` | `id` (number) | Löscht Projekt nach Bestätigung |
| `deleteCurrentProject()` | – | Löscht das aktuell angezeigte Projekt |
| `openFeedback()` | – | Öffnet GitHub Issues-Seite im Browser |

---

## Navigation & Views

| Funktion | Parameter | Beschreibung |
|----------|-----------|-------------|
| `showDashboard()` | – | Zeigt Dashboard-Ansicht mit Zusammenfassungs-Statistiken |
| `showProjects()` | – | Zeigt Projektlisten-Ansicht |
| `showUsers()` | – | Zeigt Benutzerverwaltung (nur Admin) |
| `showSettings()` | – | Zeigt Einstellungen-Ansicht |
| `hideAllViews()` | – | Versteckt alle Haupt-View-Container |
| `showViewAnimated(id)` | `id` (string) | Zeigt View-Container mit Fade-In-Animation |
| `setActiveNav(index)` | `index` (number) | Markiert Navigations-Element als aktiv |

---

## Rendering: Dashboard & Projekte

| Funktion | Parameter | Beschreibung |
|----------|-----------|-------------|
| `renderDashboard()` | – | Rendert Dashboard mit Statistik-Karten, Projekt-Karten, Aktivitäts-Feed |
| `animateValue(el, target, suffix)` | DOM-Element, Zahl, string | Animiert numerischen Zähler mit Easing |
| `canDeleteProject(p)` | `p` (Projekt-Objekt) | Prüft ob User Projekt löschen darf (Admin oder Owner) |
| `renderProjects()` | – | Rendert Liste aller Projekte mit Statistiken und Aktionen |
| `renderUsers()` | – | Rendert Benutzerliste mit Rollen und Aktionen |

---

## Projekt-Detailansicht

| Funktion | Parameter | Beschreibung |
|----------|-----------|-------------|
| `openProjectDetail(projectId)` | `projectId` (number) | Öffnet Projekt-Detailansicht, rendert Stats und Todos |
| `backToProjects()` | – | Schließt Detailansicht, zurück zur Projektliste |
| `editProject()` | – | Öffnet Projekt-Bearbeitung-Modal mit aktuellen Daten |
| `saveEditProject()` | – | Speichert Änderungen an Name, Beschreibung, Farbe |
| `renderProjectStats()` | – | Rendert und animiert Todo-Zähler für aktuelles Projekt |

---

## Todo-Verwaltung

| Funktion | Parameter | Beschreibung |
|----------|-----------|-------------|
| `toggleNewTodoForm()` | – | Zeigt/Versteckt neues Todo-Formular mit Animation |
| `addTodoToProject()` | – | Erstellt neues Todo mit Text, Kategorie, Priorität, Fälligkeitsdatum, Notiz |
| `switchTodoView(view)` | `view` ('active' / 'archive') | Wechselt zwischen aktiven/archivierten Todos |
| `renderProjectTodos()` | – | Rendert Todos gruppiert nach Kategorie mit Filtern |
| `toggleTodo(todoId)` | `todoId` (number) | Markiert Todo als erledigt/unerledigt |
| `editTodo(todoId)` | `todoId` (number) | Öffnet Todo-Bearbeitung-Modal |
| `saveEditTodo()` | – | Speichert Todo-Änderungen |
| `archiveTodo(todoId)` | `todoId` (number) | Archiviert oder stellt Todo wieder her |
| `deleteTodo(todoId)` | `todoId` (number) | Löscht Todo nach Bestätigung |

---

## Kanban Board

| Funktion | Parameter | Beschreibung |
|----------|-----------|-------------|
| `switchProjectView(view)` | `view` ('list' / 'kanban') | Wechselt zwischen Listen- und Kanban-Ansicht |
| `renderKanbanBoard()` | – | Rendert Kanban-Board mit Todo/In Progress/Done Spalten |
| `dropKanbanCard(event, newStatus)` | Drag-Event, string | Verarbeitet Kanban-Karten-Drop, aktualisiert Status |

---

## Suche

| Funktion | Parameter | Beschreibung |
|----------|-----------|-------------|
| `onSearchInput()` | – | Verarbeitet Such-Input mit 250ms Debounce |
| `performSearch(query)` | `query` (string) | Durchsucht Projekte und Todos, zeigt Ergebnisse |
| `navigateToSearchResult(projectId)` | `projectId` (number) | Navigiert zum Projekt aus Suchergebnis |

---

## Aktivitäts-Feed

| Funktion | Parameter | Beschreibung |
|----------|-----------|-------------|
| `renderActivityFeed()` | – | Lädt und rendert Aktivitätsprotokoll |
| `timeAgo(dateStr)` | `dateStr` (string) | Formatiert Zeitstempel als relative Zeit (z.B. "vor 2 Tagen") |

---

## Drag & Drop (Todo-Sortierung)

| Funktion | Parameter | Beschreibung |
|----------|-----------|-------------|
| `todoDragStart(e)` | `e` (Drag-Event) | Startet Drag für Todo-Neuordnung |
| `todoDragOver(e)` | `e` (Drag-Event) | Verarbeitet Drag-Over für Todo-Neuordnung |
| `todoDragEnd(e)` | `e` (Drag-Event) | Bereinigt nach Drag-Ende |
| `todoDrop(e)` | `e` (Drop-Event) | Verarbeitet Drop, ordnet Todos neu |

---

## UI-Hilfsfunktionen

| Funktion | Parameter | Beschreibung |
|----------|-----------|-------------|
| `escapeHtml(str)` | `str` (string) | Escaped HTML-Sonderzeichen (XSS-Schutz) |
| `showToast(title, msg, type)` | string, string, 'success'/'error'/'warning'/'info' | Zeigt Benachrichtigungs-Toast (auto-dismiss nach 5 Sek.) |
| `showConfirm(message, options)` | string, {title, icon, yesText, noText, danger} | Zeigt Bestätigungs-Dialog, gibt Promise\<boolean\> zurück |
| `closeModal(id)` | `id` (string) | Schließt Modal per ID |

---

## Daten Import/Export

| Funktion | Parameter | Beschreibung |
|----------|-----------|-------------|
| `exportData()` | – | Exportiert alle Projekte/Todos als JSON-Datei-Download |
| `importData(event)` | `event` (File-Input-Event) | Importiert Projekte/Todos aus JSON-Datei mit Bestätigung |

---

## Benutzerverwaltung

| Funktion | Parameter | Beschreibung |
|----------|-----------|-------------|
| `openCreateUserForm()` | – | Zeigt Benutzer-Erstellungs-Formular |
| `closeCreateUserForm()` | – | Versteckt Formular, leert Inputs |
| `createUser()` | – | Erstellt neuen Benutzer aus Formular |
| `deleteUser(id)` | `id` (number) | Löscht Benutzer nach Bestätigung |
| `toggleUserRole(id, newRole)` | id (number), newRole (string) | Wechselt Benutzerrolle zwischen Admin/User |

---

## Passwortverwaltung

| Funktion | Parameter | Beschreibung |
|----------|-----------|-------------|
| `changePassword()` | – | Ändert Benutzerpasswort mit Validierung |

---

## Logo-Verwaltung

| Funktion | Parameter | Beschreibung |
|----------|-----------|-------------|
| `uploadLogo(event)` | `event` (File-Input-Event) | Lädt eigenes Logo hoch (max 2MB, PNG/JPEG/SVG) nach localStorage |
| `removeLogo()` | – | Entfernt eigenes Logo aus localStorage |
| `applyLogo()` | – | Wendet Logo auf Login/Sidebar an (aus localStorage oder Standard) |

---

## Theme & Dark Mode

| Funktion | Parameter | Beschreibung |
|----------|-----------|-------------|
| `changeTheme(theme, save)` | `theme` (string), `save` (boolean, optional) | Ändert Theme-Farbe, speichert optional |
| `loadTheme()` | – | Lädt gespeichertes Theme aus localStorage |
| `toggleDarkMode()` | – | Schaltet Dark Mode um, speichert Einstellung |
| `loadDarkMode()` | – | Lädt Dark-Mode-Einstellung oder erkennt System-Einstellung |
| `updateDarkModeUI(isDark)` | `isDark` (boolean) | Aktualisiert Dark-Mode-Toggle-Button-Icon |

---

## Fälligkeitsdatum

| Funktion | Parameter | Beschreibung |
|----------|-----------|-------------|
| `getDueDateInfo(dueDate)` | `dueDate` (string) | Analysiert Fälligkeitsdatum, gibt {class, label} für Status-Badge zurück |

---

## Mitgliederverwaltung

| Funktion | Parameter | Beschreibung |
|----------|-----------|-------------|
| `openAddMemberModal()` | – | Zeigt Modal zum Hinzufügen von Mitgliedern |
| `addMember()` | – | Fügt Mitglied mit Rollenauswahl hinzu |
| `removeMember(projectId, userId)` | 2x number | Entfernt Mitglied nach Bestätigung |
| `updateMemberRole(projectId, userId, newRole)` | 2x number, string | Aktualisiert Mitgliederrolle |
| `renderMembers(project)` | Projekt-Objekt | Rendert Mitgliederliste mit Rollen-Controls |
| `openManageMembersModal(projectId)` | `projectId` (number) | Öffnet Mitglieder-Verwaltung-Modal |
| `renderManageMembersModal(project)` | Projekt-Objekt | Rendert Mitgliederliste im Verwaltungs-Modal |
| `selectMemberRole(role)` | `role` (string) | Setzt ausgewählte Mitglieder-Rolle im Modal |
| `addMemberFromModal()` | – | Fügt Mitglied aus Modal hinzu |
| `removeMemberFromModal(projectId, userId)` | 2x number | Entfernt Mitglied aus Modal nach Bestätigung |
| `updateMemberFromModal(projectId, userId, newRole)` | 2x number, string | Aktualisiert Rolle aus Modal |

---

## LDAP/AD

| Funktion | Parameter | Beschreibung |
|----------|-----------|-------------|
| `loadLdapConfig()` | – | Lädt LDAP-Konfiguration in Formular-Felder |
| `saveLdapConfig()` | – | Speichert LDAP-Konfiguration |
| `testLdapConnection()` | – | Testet LDAP-Server-Verbindung |
| `importLdapUsers()` | – | Importiert Benutzer aus LDAP nach Bestätigung |

---

## SMTP / E-Mail

| Funktion | Parameter | Beschreibung |
|----------|-----------|-------------|
| `loadSmtpConfig()` | – | Lädt SMTP-Konfiguration in Formular-Felder |
| `saveSmtpConfig()` | – | Speichert SMTP-Konfiguration |
| `testSmtpConnection()` | – | Sendet Test-E-Mail über SMTP |

---

## Benachrichtigungen

| Funktion | Parameter | Beschreibung |
|----------|-----------|-------------|
| `loadNotifications()` | – | Lädt ausstehende Benachrichtigungen vom Server |
| `showPendingNotifications(notifications)` | Array | Zeigt Toast für jede Benachrichtigung, dismissed sie |

---

## Papierkorb (Gelöschte Projekte)

| Funktion | Parameter | Beschreibung |
|----------|-----------|-------------|
| `loadDeletedProjects()` | – | Lädt soft-gelöschte Projekte mit Restore/Löschen-Optionen |
| `restoreProject(id)` | `id` (number) | Stellt soft-gelöschtes Projekt wieder her |
| `permanentDeleteProject(id)` | `id` (number) | Löscht Projekt endgültig nach Bestätigung |

---

## Anhänge (Attachments)

| Funktion | Parameter | Beschreibung |
|----------|-----------|-------------|
| `renderInlineAttachments(todo)` | Todo-Objekt | Rendert Inline-Anhang-Thumbnails/Datei-Icons in Todo-Items |
| `formatFileSize(bytes)` | `bytes` (number) | Formatiert Bytes als lesbare Größe (B, KB, MB) |
| `getFileIcon(filename)` | `filename` (string) | Gibt Emoji-Icon basierend auf Dateierweiterung zurück |
| `isPreviewable(filename)` | `filename` (string) | Prüft ob Dateityp vorschaubar ist |
| `isImageFile(filename)` | `filename` (string) | Prüft ob Datei ein Bildtyp ist |
| `renderAttachments(attachments)` | Array | Rendert Anhang-Liste im Todo-Bearbeitungs-Modal |
| `initAttachmentDropZone()` | – | Initialisiert Drag-Drop-Zone für Anhang-Uploads |
| `handleAttachmentUpload(files)` | FileList | Lädt Dateianhang hoch (max 10MB) via FormData |
| `deleteAttachment(attachmentId)` | `attachmentId` (string) | Löscht Anhang nach Bestätigung |
| `previewAttachmentDirect(projectId, todoId, idx)` | 3x number | Öffnet Anhang-Vorschau aus Todo-Liste |
| `previewAttachmentByIndex(idx)` | `idx` (number) | Öffnet Anhang-Vorschau aus Bearbeitungs-Modal |
| `showAttachmentPreview(att, projectId, todoId)` | Objekt, 2x number | Zeigt Anhang-Vorschau-Overlay (Bild/Video/PDF/Audio) |
| `closeAttachmentPreview()` | – | Schließt Anhang-Vorschau-Overlay |
