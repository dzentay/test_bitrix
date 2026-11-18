#!/bin/sh
set -e

php /app/bin/wait-for-db.php
php /app/bin/migrate.php
php /app/bin/seed.php

exec php -S 0.0.0.0:8080 -t /app/public /app/public/index.php