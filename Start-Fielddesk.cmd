@echo off
setlocal
cd /d "%~dp0"
if exist "storage\framework\fielddesk-local\php.ini" (
    set "PHPRC=%CD%\storage\framework\fielddesk-local"
)
echo Keep this window open while using Fielddesk.
php artisan serve --host=127.0.0.1 --port=8010 --no-reload
if errorlevel 1 pause
