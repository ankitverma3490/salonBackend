# Quick Setup Script for PHP/MySQL Backend

Write-Host "🚀 Salon Booking Platform - Backend Setup" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

# Check if XAMPP is installed
$xamppPath = "C:\xampp"
if (Test-Path $xamppPath) {
    Write-Host "✅ XAMPP found at $xamppPath" -ForegroundColor Green
} else {
    Write-Host "❌ XAMPP not found. Please install XAMPP first." -ForegroundColor Red
    Write-Host "   Download from: https://www.apachefriends.org/" -ForegroundColor Yellow
    exit 1
}

# Check if MySQL is running
Write-Host ""
Write-Host "Checking MySQL status..." -ForegroundColor Yellow
$mysqlProcess = Get-Process mysqld -ErrorAction SilentlyContinue
if ($mysqlProcess) {
    Write-Host "✅ MySQL is running" -ForegroundColor Green
} else {
    Write-Host "❌ MySQL is not running" -ForegroundColor Red
    Write-Host "   Please start MySQL from XAMPP Control Panel" -ForegroundColor Yellow
    Write-Host "   Then run this script again" -ForegroundColor Yellow
    exit 1
}

# Check if Apache is running
Write-Host "Checking Apache status..." -ForegroundColor Yellow
$apacheProcess = Get-Process httpd -ErrorAction SilentlyContinue
if ($apacheProcess) {
    Write-Host "✅ Apache is running" -ForegroundColor Green
} else {
    Write-Host "❌ Apache is not running" -ForegroundColor Red
    Write-Host "   Please start Apache from XAMPP Control Panel" -ForegroundColor Yellow
    Write-Host "   Then run this script again" -ForegroundColor Yellow
    exit 1
}

Write-Host ""
Write-Host "📋 Next Steps:" -ForegroundColor Cyan
Write-Host "1. Open phpMyAdmin: http://localhost/phpmyadmin" -ForegroundColor White
Write-Host "2. Create database: salon_booking" -ForegroundColor White
Write-Host "3. Import: backend/database.sql" -ForegroundColor White
Write-Host "4. Edit: backend/config.php (set DB credentials)" -ForegroundColor White
Write-Host "5. Test API: http://localhost/backend/api/salons" -ForegroundColor White
Write-Host ""

# Ask if user wants to open phpMyAdmin
$response = Read-Host "Would you like to open phpMyAdmin now? (Y/N)"
if ($response -eq 'Y' -or $response -eq 'y') {
    Start-Process "http://localhost/phpmyadmin"
    Write-Host "✅ Opening phpMyAdmin..." -ForegroundColor Green
}

Write-Host ""
Write-Host "📖 For detailed instructions, see: MIGRATION_GUIDE.md" -ForegroundColor Cyan
Write-Host ""
