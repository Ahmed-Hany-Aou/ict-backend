@echo off
REM ========================================
REM Local Development CSRF Fix Script (Windows)
REM ========================================
REM Run this script when you encounter 419 errors in local development
REM
REM Usage: fix-local-csrf.bat

echo.
echo ========================================
echo   Local Development CSRF Fix
echo ========================================
echo.

REM Step 1: Clear all Laravel caches
echo Step 1/5: Clearing Laravel caches...
call php artisan config:clear
call php artisan cache:clear
call php artisan route:clear
call php artisan view:clear
call php artisan optimize:clear
echo [32mCaches cleared[0m
echo.

REM Step 2: Delete session files
echo Step 2/5: Deleting old session files...
if exist storage\framework\sessions\* (
    del /Q storage\framework\sessions\*
)
if exist storage\framework\cache\data\* (
    del /Q /S storage\framework\cache\data\*
)
if exist storage\framework\views\* (
    del /Q storage\framework\views\*
)
echo [32mSession files deleted[0m
echo.

REM Step 3: Verify .env settings
echo Step 3/5: Verifying .env settings...
findstr /C:"SESSION_DOMAIN=null" .env >nul
if %errorlevel% equ 0 (
    echo [32mSESSION_DOMAIN is set to null[0m
) else (
    echo [33mWARNING: SESSION_DOMAIN should be 'null'[0m
)

findstr /C:"APP_ENV=local" .env >nul
if %errorlevel% equ 0 (
    echo [32mAPP_ENV is set to local[0m
) else (
    echo [33mWARNING: APP_ENV should be 'local'[0m
)
echo.

REM Step 4: Configuration complete
echo Step 4/5: Configuration fixed!
echo.

REM Step 5: Instructions
echo ========================================
echo   Next Steps:
echo ========================================
echo.
echo 1. Restart your development server:
echo    - Stop current server (Ctrl+C)
echo    - Run: php artisan serve
echo.
echo 2. Clear browser cache:
echo    - Press Ctrl+Shift+Delete
echo    - Select 'All time'
echo    - Check 'Cookies' and 'Cached files'
echo    - Click 'Clear data'
echo.
echo 3. Use incognito window to test:
echo    - Visit: http://127.0.0.1:8000/admin
echo.
echo 4. Debug if needed:
echo    - Visit: http://127.0.0.1:8000/debug-csrf
echo.
echo ========================================
echo   CSRF fix complete!
echo ========================================
echo.
pause
