#!/bin/bash
set -e

DB_HOST="${DB_HOST:-db}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-root}"
DB_NAME="${DB_NAME:-aplikasi_antrian}"
SQL_FILE="/var/www/html/database/aplikasi_antrian.sql"

echo "Waiting for MySQL at ${DB_HOST}..."
for i in $(seq 1 30); do
    if php -r "try { new PDO('mysql:host=${DB_HOST};dbname=${DB_NAME}', '${DB_USER}', '${DB_PASS}'); echo 'DB connected\n'; exit(0); } catch(Exception \$e) { exit(1); }" 2>/dev/null; then
        echo "MySQL is up"
        break
    fi
    echo "MySQL is unavailable - attempt $i/30"
    sleep 2
done

php -r "
try {
    \$db = new PDO('mysql:host=${DB_HOST};dbname=${DB_NAME}', '${DB_USER}', '${DB_PASS}');
    \$stmt = \$db->query(\"SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='${DB_NAME}' AND table_name='queue_setting'\");
    \$exists = (int)\$stmt->fetchColumn();
    if (\$exists === 1) {
        echo \"Database already initialized. Skipping import.\n\";
        exit(0);
    }
} catch(Exception \$e) {}
" 2>/dev/null

if [ $? -ne 0 ]; then
    echo "Importing database schema..."
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$SQL_FILE" 2>/dev/null || {
        echo "Warning: SQL import had issues. Trying alternative method..."
        php -r "
            require '/var/www/html/vendor/autoload.php';
            \$sql = file_get_contents('${SQL_FILE}');
            \$lines = array_filter(array_map('trim', explode(';', \$sql)));
            try {
                \$db = new PDO('mysql:host=${DB_HOST};dbname=${DB_NAME}', '${DB_USER}', '${DB_PASS}');
                foreach (\$lines as \$line) {
                    if (empty(\$line) || strpos(\$line, '--') === 0) continue;
                    \$db->exec(\$line);
                }
                echo 'Database imported successfully via PHP.\n';
            } catch(Exception \$e) {
                echo 'Error: ' . \$e->getMessage() . '\n';
            }
        "
    }
    echo "Database initialized successfully."
fi

echo "Starting Apache..."
exec apache2-foreground
