#!/bin/sh
set -e

echo "Configuring Apache MPMs..."

# Agesssively disable conflicting MPMs
rm -f /etc/apache2/mods-enabled/mpm_event.load
rm -f /etc/apache2/mods-enabled/mpm_event.conf
rm -f /etc/apache2/mods-enabled/mpm_worker.load
rm -f /etc/apache2/mods-enabled/mpm_worker.conf

# Ensure MPM Prefork is enabled
if [ ! -e /etc/apache2/mods-enabled/mpm_prefork.load ]; then
    ln -s /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load || true
fi
if [ ! -e /etc/apache2/mods-enabled/mpm_prefork.conf ]; then
    ln -s /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf || true
fi

# Suppress ServerName warning
echo "ServerName localhost" >> /etc/apache2/apache2.conf

echo "Starting Apache..."
exec apache2-foreground
