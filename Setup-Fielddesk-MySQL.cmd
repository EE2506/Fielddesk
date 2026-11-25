@echo off
setlocal
cd /d "%~dp0"
if exist "storage\framework\fielddesk-local\php.ini" (
    set "PHPRC=%CD%\storage\framework\fielddesk-local"
)
php artisan fielddesk:mysql
pause
