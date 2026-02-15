#!/bin/bash
set -e

# Log everything to stdout
set -x

echo "🚀 Starting application setup..."

# 1. 🛠️ Handle Railway Dynamic Port
# Set PORT to 80 if not defined
PORT=${PORT:-80}
echo "Using PORT: $PORT"

# Replace port in configuration files
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/:80/:${PORT}/g" /etc/apache2/sites-available/000-default.conf
if [ -f /etc/apache2/sites-enabled/000-default.conf ]; then
    sed -i "s/:80/:${PORT}/g" /etc/apache2/sites-enabled/000-default.conf
fi

# 2. 🧹 MPM Cleanup
echo "Cleaning up Apache MPMs..."
# Disable event and worker if enabled
a2dismod mpm_event || true
a2dismod mpm_worker || true
# Force removal of config files to be safe
rm -f /etc/apache2/mods-enabled/mpm_event.*
rm -f /etc/apache2/mods-enabled/mpm_worker.*
# Enable prefork
a2enmod mpm_prefork

# 3. ⚙️ Optimize Configuration
# Suppress ServerName warning
echo "ServerName localhost" >> /etc/apache2/apache2.conf

# 4. 🚀 Start Apache
echo "Starting Apache in foreground..."
exec apache2-foreground
