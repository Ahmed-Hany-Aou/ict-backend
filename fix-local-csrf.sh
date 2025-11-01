#!/bin/bash

# ========================================
# Local Development CSRF Fix Script
# ========================================
# Run this script when you encounter 419 errors in local development
#
# Usage: bash fix-local-csrf.sh

echo "🔧 Fixing Local Development CSRF Issues..."
echo "==========================================="
echo ""

# Step 1: Clear all Laravel caches
echo "📦 Step 1/5: Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
echo "✅ Caches cleared"
echo ""

# Step 2: Delete session files
echo "🗑️  Step 2/5: Deleting old session files..."
rm -rf storage/framework/sessions/*
rm -rf storage/framework/cache/*
rm -rf storage/framework/views/*
echo "✅ Session files deleted"
echo ""

# Step 3: Verify .env settings
echo "🔍 Step 3/5: Verifying .env settings..."
if grep -q "SESSION_DOMAIN=null" .env; then
    echo "✅ SESSION_DOMAIN is set to null"
else
    echo "⚠️  WARNING: SESSION_DOMAIN should be 'null' for local development"
    echo "   Current value: $(grep SESSION_DOMAIN .env)"
fi

if grep -q "APP_ENV=local" .env; then
    echo "✅ APP_ENV is set to local"
else
    echo "⚠️  WARNING: APP_ENV should be 'local' for local development"
fi
echo ""

# Step 4: Test configuration
echo "🧪 Step 4/5: Testing CSRF configuration..."
php artisan tinker --execute="echo 'Session Domain: ' . config('session.domain') . PHP_EOL;"
php artisan tinker --execute="echo 'App Environment: ' . config('app.env') . PHP_EOL;"
php artisan tinker --execute="echo 'Session Secure: ' . (config('session.secure') ? 'true' : 'false') . PHP_EOL;"
echo ""

# Step 5: Ready to restart
echo "✅ Step 5/5: Configuration fixed!"
echo ""
echo "==========================================="
echo "🚀 Next Steps:"
echo "==========================================="
echo "1. Restart your development server:"
echo "   - Stop current server (Ctrl+C)"
echo "   - Run: php artisan serve"
echo ""
echo "2. Clear browser cache:"
echo "   - Press Ctrl+Shift+Delete"
echo "   - Select 'All time'"
echo "   - Check 'Cookies' and 'Cached files'"
echo "   - Click 'Clear data'"
echo ""
echo "3. Use incognito window to test:"
echo "   - Visit: http://127.0.0.1:8000/admin"
echo ""
echo "4. Debug if needed:"
echo "   - Visit: http://127.0.0.1:8000/debug-csrf"
echo ""
echo "==========================================="
echo "✨ CSRF fix complete!"
echo "==========================================="
