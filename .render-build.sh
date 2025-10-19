#!/usr/bin/env bash
set -e

apt-get update
apt-get install -y libpq-dev
docker-php-ext-install pdo pdo_pgsql pgsql
