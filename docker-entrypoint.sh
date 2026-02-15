#!/bin/sh
set -e

# 1. 🛠️ Handle Railway Dynamic Port
# Railway provides a PORT environment variable. We must update Apache to listen on it.
if [ -n "$PORT" ]; then
    echo "Configuring Apache to listen on PORT $PORT..."
    sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
    sed -i "s/:80/:${PORT}/" /etc/apache2/sites-available/000-default.conf
    sed -i "s/:80/:${PORT}/" /etc/apache2/sites-enabled/000-default.conf || true
else
    echo "No PORT environment variable found, defaulting to 80."
fi

# 2. 🧹 MPM Cleanup (Fix "More than one MPM loaded" error)
echo "Cleaning up Apache MPMs..."
rm -f /etc/apache2/mods-enabled/mpm_event.load
rm -f /etc/apache2/mods-enabled/mpm_event.conf
rm -f /etc/apache2/mods-enabled/mpm_worker.load
rm -f /etc/apache2/mods-enabled/mpm_worker.conf

# Ensure Prefork is enabled (best for PHP)
if [ ! -e /etc/apache2/mods-enabled/mpm_prefork.load ]; then
    ln -s /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load || true
fi
if [ ! -e /etc/apache2/mods-enabled/mpm_prefork.conf ]; then
    ln -s /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf || true
fi

# 3. ⚙️ Optimize Configuration
echo "ServerName localhost" >> /etc/apache2/apache2.conf

# 4. 🚀 Start Apache
echo "Starting Apache..."
exec apache2-foreground
