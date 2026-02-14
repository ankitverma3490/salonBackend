Write-Host "🚀 Starting PHP Backend Server..." -ForegroundColor Cyan
Write-Host "Server running at http://localhost:8000" -ForegroundColor Green
Write-Host "Press Ctrl+C to stop" -ForegroundColor Yellow

& "C:\xampp\php\php.exe" -S 127.0.0.1:8000 router.php
