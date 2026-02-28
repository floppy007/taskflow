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

    if (!file_exists($usersFile)) {
        // Erster Start: Admin-User erstellen
        $user = [
            "id" => 1,
            "username" => $adminUser,
            "password" => password_hash($adminPass, PASSWORD_DEFAULT),
            "name" => $adminName,
            "role" => "admin",
            "source" => "local",
            "createdAt" => date("c")
        ];
        file_put_contents($usersFile, json_encode([$user], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        file_put_contents($projectsFile, "[]");
        file_put_contents($activityFile, "[]");
        echo ">>> TaskFlow: Admin-User \"$adminUser\" erstellt.\n";
    } else {
        // Bestehende Daten: Admin-Passwort aus ENV aktualisieren
        $users = json_decode(file_get_contents($usersFile), true) ?: [];
        $updated = false;
        foreach ($users as &$u) {
            if ($u["username"] === $adminUser && ($u["source"] ?? "local") === "local") {
                if (!password_verify($adminPass, $u["password"] ?? "")) {
                    $u["password"] = password_hash($adminPass, PASSWORD_DEFAULT);
                    $updated = true;
                }
                if ($u["name"] !== $adminName) {
                    $u["name"] = $adminName;
                    $updated = true;
                }
                break;
            }
        }
        unset($u);
        if ($updated) {
            file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            echo ">>> TaskFlow: Admin-User \"$adminUser\" aktualisiert.\n";
        } else {
            echo ">>> TaskFlow: Bestehende Daten gefunden, keine Aenderungen.\n";
        }
    }
'

chown -R www-data:www-data "$DATA_DIR"

# Apache starten
exec apache2-foreground
