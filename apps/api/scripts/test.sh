#!/usr/bin/env sh
set -eu

mkdir -p database

testing_db="database/testing-$$.sqlite"
touch "$testing_db"

cleanup() {
    rm -f "$testing_db"
}

trap cleanup EXIT INT TERM

php artisan config:clear --ansi
DB_TEST_DATABASE="$testing_db" php artisan test --env=testing "$@"
