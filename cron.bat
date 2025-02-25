@echo off
:loop
php artisan product:fetch
timeout /t 120 /nobreak
goto loop