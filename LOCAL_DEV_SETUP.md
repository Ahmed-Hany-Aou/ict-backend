# Local Development Setup - CSRF Fix Guide

## 🎯 Problem
In local development, you may encounter **419 Page Expired** errors when using Filament admin panel. This is due to CSRF token validation issues specific to local environment setup.

**Note:** Production (Railway) works perfectly - this is ONLY a local development issue.

## ✅ Solution

### Quick Fix (When You Get 419 Errors)

Run this command to reset everything:

```bash
# Stop your server first (Ctrl+C), then run:
php artisan config:clear && \
php artisan cache:clear && \
php artisan route:clear && \
php artisan view:clear && \
rm -rf storage/framework/sessions/* && \
php artisan serve
```

Then:
1. Clear browser cache (Ctrl+Shift+Delete → All time)
2. Use incognito window
3. Visit: http://127.0.0.1:8000/admin

---

## 🔧 Permanent Fix

### 1. Verify Your `.env` Settings

Your `.env` file should have these **exact** settings for local development:

```env
APP_ENV=local
APP_URL=http://127.0.0.1:8000

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=false
SESSION_SAME_SITE=lax

SANCTUM_STATEFUL_DOMAINS=127.0.0.1:8000,localhost:8000
```

### 2. TrustProxies Middleware

The `app/Http/Middleware/TrustProxies.php` is configured to:
- **Local:** Don't trust any proxies (`$proxies = null`)
- **Production:** Trust all proxies (`$proxies = '*'`)

This is automatically handled - no changes needed.

### 3. Bootstrap Configuration

The `bootstrap/app.php` file should NOT have duplicate proxy configuration. It should only have:

```php
->withMiddleware(function (Middleware $middleware) {
    // TrustProxies configuration removed - using App\Http\Middleware\TrustProxies instead
})
```

---

## 🐛 Debugging CSRF Issues

### Check Current Configuration

Visit this debug route to see your current CSRF/session config:

```
http://127.0.0.1:8000/debug-csrf
```

You should see:
```json
{
  "csrf_token": "some-token",
  "session_status": true,
  "env": {
    "app_env": "local",
    "session_domain": null,
    "session_secure": false
  }
}
```

### Test CSRF Validation

Open browser console and run:

```javascript
fetch('/test-csrf', {
  method: 'POST',
  headers: {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({test: true})
}).then(r => r.json()).then(console.log)
```

Should return: `{"status": "success", "message": "CSRF validation passed!"}`

---

## 🚨 Common Issues & Solutions

### Issue 1: Still Getting 419 After Config Changes

**Solution:**
```bash
# Nuclear option - clear EVERYTHING
php artisan optimize:clear
rm -rf storage/framework/sessions/*
rm -rf storage/framework/cache/*
rm -rf storage/framework/views/*

# Restart server
php artisan serve
```

Then clear browser cache completely.

### Issue 2: 419 Only on Livewire/Filament Pages

**Solution:**
1. Check `config/livewire.php` has: `'app_url' => env('APP_URL', null)`
2. Ensure `.env` has `APP_URL=http://127.0.0.1:8000`
3. Clear config: `php artisan config:clear`

### Issue 3: Cookies Not Being Set

**Solution:**
1. Open DevTools → Application → Cookies
2. Delete all cookies for `127.0.0.1`
3. Use incognito window
4. Verify `SESSION_DOMAIN=null` in `.env`

### Issue 4: Production Works, Local Doesn't

**This is expected!** Production uses:
- Railway's proxy infrastructure
- HTTPS (secure cookies)
- Production-optimized session handling

Local uses:
- No proxies
- HTTP (non-secure cookies)
- File-based sessions

The configurations are intentionally different.

---

## 📋 Pre-Deployment Checklist

Before deploying to production, ensure:

- [ ] `.env.local.example` is NOT committed
- [ ] Production `.env` has proper Railway URLs
- [ ] `APP_ENV=production` in production
- [ ] Debug routes (`/debug-csrf`, `/test-csrf`) are removed or secured

---

## 🎯 Why This Happens

The 419 error in local development is caused by:

1. **Proxy Confusion**: Local doesn't use proxies, but middleware expects them
2. **Session Domain Mismatch**: `127.0.0.1` vs `localhost` cookie scoping
3. **Cached Configuration**: Old configs persisting after changes
4. **Browser Cookie Cache**: Old session cookies conflicting with new ones

Our fix addresses all four issues.

---

## 💡 Tips

1. **Always use `127.0.0.1:8000`** - don't mix with `localhost:8000`
2. **Use incognito** for testing - avoids cached cookies
3. **Clear sessions** after major config changes
4. **Trust the production** - if it works there, local is just config

---

## 📞 Still Having Issues?

If none of the above works:

1. Check the debug route output: `http://127.0.0.1:8000/debug-csrf`
2. Verify browser cookies in DevTools
3. Check Laravel logs: `storage/logs/laravel.log`
4. Ensure you're using PHP 8.1+ and Laravel 11.x

The configuration provided has been tested and works. 99% of issues are from cached config or browser cookies.
