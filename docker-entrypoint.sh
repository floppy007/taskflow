#!/bin/bash
set -e

DATA_DIR="/var/www/html/data"

# Data-Verzeichnis sicherstellen
mkdir -p "$DATA_DIR"
chown www-data:www-data "$DATA_DIR"

# .htaccess für Data-Schutz
if [ ! -f "$DATA_DIR/.htaccess" ]; then
    echo -e "Order Deny,Allow\nDeny from all" > "$DATA_DIR/.htaccess"
fi

# Admin-User anlegen oder aktualisieren (komplett via PHP für sicheres Escaping)
php -r '
    $dataDir = getenv("DATA_DIR") ?: "/var/www/html/data";
    $usersFile = "$dataDir/users.json";
    $projectsFile = "$dataDir/projects.json";
    $activityFile = "$dataDir/activity.json";

    $adminUser = getenv("TASKFLOW_ADMIN_USER") ?: "admin";
    $adminPass = getenv("TASKFLOW_ADMIN_PASS") ?: "admin";
    $adminName = getenv("TASKFLOW_ADMIN_NAME") ?: "Administrator";

    echo ">>> TaskFlow: Admin-User=\"$adminUser\", Pass-Laenge=" . strlen($adminPass) . "\n";

    if (!file_exists($usersFile)) {
        // Erster Start: Admin-User erstellen
        $hash = password_hash($adminPass, PASSWORD_DEFAULT);
        $user = [
            "id" => 1,
            "username" => $adminUser,
            "password" => $hash,
            "name" => $adminName,
            "role" => "admin",
            "source" => "local",
            "createdAt" => date("c")
        ];
        $written = file_put_contents($usersFile, json_encode([$user], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        if ($written === false) {
            echo ">>> FEHLER: Konnte $usersFile nicht schreiben!\n";
            exit(1);
        }
        file_put_contents($projectsFile, "[]");
        file_put_contents($activityFile, "[]");

        // Verify
        if (password_verify($adminPass, $hash)) {
            echo ">>> TaskFlow: Admin-User \"$adminUser\" erstellt und verifiziert.\n";
        } else {
            echo ">>> FEHLER: Passwort-Verify nach Hash fehlgeschlagen!\n";
            exit(1);
        }
    } else {
        // Bestehende Daten: Admin-Passwort aus ENV IMMER erzwingen
        $raw = file_get_contents($usersFile);
        $users = json_decode($raw, true);
        if (!is_array($users)) {
            echo ">>> FEHLER: users.json ist ungueltig/korrupt! Erstelle neu.\n";
            $hash = password_hash($adminPass, PASSWORD_DEFAULT);
            $user = [
                "id" => 1,
                "username" => $adminUser,
                "password" => $hash,
                "name" => $adminName,
                "role" => "admin",
                "source" => "local",
                "createdAt" => date("c")
            ];
            file_put_contents($usersFile, json_encode([$user], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            echo ">>> TaskFlow: users.json neu erstellt.\n";
        } else {
            $found = false;
            foreach ($users as &$u) {
                if ($u["username"] === $adminUser && ($u["source"] ?? "local") === "local") {
                    $found = true;
                    // Passwort IMMER aus ENV setzen (bei jedem Container-Start)
                    $u["password"] = password_hash($adminPass, PASSWORD_DEFAULT);
                    $u["name"] = $adminName;
                    break;
                }
            }
            unset($u);

            if (!$found) {
                // Admin-User existiert nicht in users.json - anlegen
                $maxId = 0;
                foreach ($users as $u) {
                    if (($u["id"] ?? 0) > $maxId) $maxId = $u["id"];
                }
                $users[] = [
                    "id" => $maxId + 1,
                    "username" => $adminUser,
                    "password" => password_hash($adminPass, PASSWORD_DEFAULT),
                    "name" => $adminName,
                    "role" => "admin",
                    "source" => "local",
                    "createdAt" => date("c")
                ];
                echo ">>> TaskFlow: Admin-User \"$adminUser\" war nicht vorhanden, wurde angelegt.\n";
            }

            $written = file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            if ($written === false) {
                echo ">>> FEHLER: Konnte $usersFile nicht schreiben!\n";
                exit(1);
            }

            // Verify: Datei neu lesen und Passwort pruefen
            $check = json_decode(file_get_contents($usersFile), true);
            $verified = false;
            foreach ($check as $u) {
                if ($u["username"] === $adminUser) {
                    $verified = password_verify($adminPass, $u["password"]);
                    break;
                }
            }
            if ($verified) {
                echo ">>> TaskFlow: Admin-Passwort fuer \"$adminUser\" gesetzt und verifiziert.\n";
            } else {
                echo ">>> FEHLER: Passwort-Verify nach Schreiben fehlgeschlagen!\n";
                exit(1);
            }
        }
    }
'

chown -R www-data:www-data "$DATA_DIR"

# Apache starten
exec apache2-foreground
