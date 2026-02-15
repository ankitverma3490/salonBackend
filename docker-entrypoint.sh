#!/bin/bash
set -e

# Turn on debug mode to see every command
set -x

echo "🚀 Starting detailed application setup..."

# 1. 🛠️ Handle Railway Dynamic Port
# Use the PORT environment variable provided by Railway (defaults to 8080)
# railway often defaults to 8080 or respects the user's PORT variable
RAW_PORT=${PORT:-8080}
# Sanitize PORT to ensure it only contains digits (strips potential hidden chars)
PORT=$(echo "$RAW_PORT" | tr -cd '0-9')

# Fallback if sanitization results in empty string
if [ -z "$PORT" ]; then
    PORT=8080
fi

echo "Configuring Apache to listen on PORT: $PORT (from raw: '$RAW_PORT')"

# Overwrite ports.conf to simple Listen directive
echo "Listen $PORT" > /etc/apache2/ports.conf

# Overwrite default site configuration to ensure correct VirtualHost
cat > /etc/apache2/sites-available/000-default.conf <<EOF
<VirtualHost *:$PORT>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html

    <Directory /var/www/html>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/error.log
    CustomLog \${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

# Ensure the site is enabled
a2ensite 000-default

# 2. 🧹 MPM Cleanup (Aggressive)
echo "Cleaning up Apache MPMs..."
# Disable all MPMs first
a2dismod mpm_event || true
a2dismod mpm_worker || true
a2dismod mpm_prefork || true
# Remove config files to prevent conflicts
rm -f /etc/apache2/mods-enabled/mpm_event.*
rm -f /etc/apache2/mods-enabled/mpm_worker.*
rm -f /etc/apache2/mods-enabled/mpm_prefork.*

# Enable ONLY mpm_prefork
echo "Enabling mpm_prefork..."
# We use the available module and link it manually if a2enmod fails, but a2enmod should work now
if [ -f /etc/apache2/mods-available/mpm_prefork.load ]; then
    ln -s /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load
    ln -s /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf
else
    echo "ERROR: mpm_prefork module not found!"
    exit 1
fi

# 3. ⚙️ Optimize Configuration
# Suppress ServerName warning
echo "ServerName localhost" >> /etc/apache2/apache2.conf

# 4. 🚀 Start Apache
echo "Starting Apache in foreground..."
rm -f /var/run/apache2/apache2.pid # Remove any stale PID file
exec apache2-foreground
