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

# Admin-User beim ersten Start anlegen
if [ ! -f "$DATA_DIR/users.json" ]; then
    ADMIN_USER="${TASKFLOW_ADMIN_USER:-admin}"
    ADMIN_PASS="${TASKFLOW_ADMIN_PASS:-admin}"
    ADMIN_NAME="${TASKFLOW_ADMIN_NAME:-Administrator}"

    HASH=$(php -r "echo password_hash('$ADMIN_PASS', PASSWORD_DEFAULT);")

    cat > "$DATA_DIR/users.json" <<EOF
[
    {
        "id": 1,
        "username": "$ADMIN_USER",
        "password": "$HASH",
        "name": "$ADMIN_NAME",
        "role": "admin",
        "createdAt": "$(date -Iseconds)"
    }
]
EOF

    echo "[] " > "$DATA_DIR/projects.json"
    echo "[]" > "$DATA_DIR/activity.json"

    chown -R www-data:www-data "$DATA_DIR"
    echo ">>> TaskFlow: Admin-User '$ADMIN_USER' erstellt."
else
    echo ">>> TaskFlow: Bestehende Daten gefunden, überspringe Setup."
fi

# Apache starten
exec apache2-foreground
