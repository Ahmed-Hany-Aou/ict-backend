# Railway Deployment Guide - ICT Learning Platform

This guide will help you deploy your Laravel backend, React frontend, and MySQL database to Railway.

## Prerequisites

- Railway account (already done ✓)
- Git installed on your computer
- Both projects should be in Git repositories

---

## Part 1: Deploy MySQL Database

### Step 1: Create Database Service

1. Go to [railway.app](https://railway.app) and log in
2. Click **"New Project"**
3. Select **"Deploy MySQL"**
4. Railway will provision a MySQL database
5. Click on the **MySQL service** to view it
6. Go to the **Variables** tab
7. **IMPORTANT**: Copy these variables (you'll need them):
   - `MYSQLHOST`
   - `MYSQLPORT`
   - `MYSQLDATABASE`
   - `MYSQLUSER`
   - `MYSQLPASSWORD`

---

## Part 2: Deploy Laravel Backend

### Step 1: Initialize Git Repository (if not already done)

```bash
cd "C:\MAMP\htdocs\project 28\ict-backend"
git init
git add .
git commit -m "Initial commit - Laravel backend"
```

### Step 2: Deploy to Railway

1. In the **same Railway project** (where you created MySQL), click **"+ New"**
2. Select **"GitHub Repo"** or **"Empty Service"**
   - If using GitHub: Connect your repository and select the backend repo
   - If using Empty Service: Continue with next steps

3. If you selected **Empty Service**:
   - Click on the new service
   - Go to **Settings** tab
   - Click **"Connect to GitHub"** and select your backend repository
   - Or use **Railway CLI** to deploy:
     ```bash
     npm install -g @railway/cli
     railway login
     railway link
     railway up
     ```

### Step 3: Configure Environment Variables

1. Click on your **backend service**
2. Go to **Variables** tab
3. Click **"+ New Variable"** and add these:

```
APP_NAME=ICT_Learning
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:Z6owch/QgCmAsSUidlBJHmJsn9B3Vceej97Ck51lXa4=
APP_URL=${{RAILWAY_PUBLIC_DOMAIN}}

SANCTUM_STATEFUL_DOMAINS=${{RAILWAY_PUBLIC_DOMAIN}}

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

CACHE_STORE=database
QUEUE_CONNECTION=database

LOG_CHANNEL=stack
LOG_LEVEL=info

BCRYPT_ROUNDS=12
```

**Note**: Railway automatically references the MySQL service using `${{MySQL.VARIABLE_NAME}}` format.

### Step 4: Generate Application Key

If you need a new `APP_KEY`:
```bash
php artisan key:generate --show
```
Copy the output and update the `APP_KEY` variable in Railway.

### Step 5: Enable Public Domain

1. Click on your backend service
2. Go to **Settings** tab
3. Scroll to **Networking**
4. Click **"Generate Domain"**
5. Copy the generated URL (e.g., `https://your-backend-abc123.up.railway.app`)

### Step 6: Update Environment Variables with Domain

1. Go back to **Variables** tab
2. Update `APP_URL` to your generated domain
3. Update `SANCTUM_STATEFUL_DOMAINS` to include your frontend domain (you'll get this later)

### Step 7: Run Migrations

Railway will automatically run migrations on deployment thanks to the Procfile.

If you need to run them manually:
1. Go to your backend service
2. Click **"View Logs"** to see if migrations ran successfully
3. Or use Railway CLI:
   ```bash
   railway run php artisan migrate --force
   ```

---

## Part 3: Deploy React Frontend

### Step 1: Update Frontend Environment Variables

1. Edit `C:\MAMP\htdocs\project 29\ict-frontend\.env`
2. Replace with your Railway backend URL:
   ```
   REACT_APP_API_URL=https://your-backend-abc123.up.railway.app/api
   ```

### Step 2: Install Dependencies and Update

```bash
cd "C:\MAMP\htdocs\project 29\ict-frontend"
npm install
```

### Step 3: Initialize Git Repository (if not already done)

```bash
cd "C:\MAMP\htdocs\project 29\ict-frontend"
git init
git add .
git commit -m "Initial commit - React frontend"
```

### Step 4: Deploy to Railway

1. In the **same Railway project**, click **"+ New"**
2. Select **"GitHub Repo"** or **"Empty Service"**
3. Connect your frontend repository

### Step 5: Configure Environment Variables

1. Click on your **frontend service**
2. Go to **Variables** tab
3. Add:
   ```
   REACT_APP_API_URL=${{backend.RAILWAY_PUBLIC_DOMAIN}}/api
   ```

   Or use the explicit backend URL:
   ```
   REACT_APP_API_URL=https://your-backend-abc123.up.railway.app/api
   ```

### Step 6: Enable Public Domain

1. Click on your frontend service
2. Go to **Settings** tab
3. Scroll to **Networking**
4. Click **"Generate Domain"**
5. Copy the generated URL (e.g., `https://your-frontend-xyz789.up.railway.app`)

### Step 7: Update Backend CORS Settings

1. Go back to your **backend service**
2. Go to **Variables** tab
3. Update `SANCTUM_STATEFUL_DOMAINS` to include your frontend domain:
   ```
   SANCTUM_STATEFUL_DOMAINS=your-frontend-xyz789.up.railway.app
   ```

---

## Part 4: Configure CORS in Laravel

Your Laravel backend needs to allow requests from the frontend domain.

### Option 1: Using Railway Variables (Recommended)

Already configured in `config/cors.php` if using Laravel Sanctum.

### Option 2: Manual Configuration

If needed, update `config/cors.php` to allow your frontend domain.

---

## Part 5: Seed Database (Optional)

If you have seeders to populate initial data:

### Using Railway CLI:
```bash
cd "C:\MAMP\htdocs\project 28\ict-backend"
railway link  # Select your backend service
railway run php artisan db:seed
```

### Or Add to Procfile:
Update the Procfile to include seeding:
```
web: php artisan migrate --force && php artisan db:seed --force && php artisan serve --host=0.0.0.0 --port=$PORT
```

---

## Part 6: Test Your Deployment

### Test Backend:
1. Visit: `https://your-backend-abc123.up.railway.app`
2. You should see a response or Laravel welcome page

### Test API Endpoints:
```bash
curl https://your-backend-abc123.up.railway.app/api/health
```

### Test Frontend:
1. Visit: `https://your-frontend-xyz789.up.railway.app`
2. Your React app should load
3. Try logging in or accessing features that use the API

---

## Part 7: Monitoring and Debugging

### View Logs:
1. Click on any service in Railway
2. Click **"View Logs"** or the **Deployments** tab
3. Check for errors or issues

### Common Issues:

#### 1. Database Connection Errors
- Verify all MySQL environment variables are correctly referenced
- Check that services are in the same Railway project

#### 2. CORS Errors
- Update `SANCTUM_STATEFUL_DOMAINS` with frontend domain
- Verify frontend is sending requests to correct backend URL

#### 3. 500 Errors
- Check `APP_KEY` is set correctly
- Verify all required environment variables are present
- Check logs for specific error messages

#### 4. Build Failures
- Review build logs in Railway
- Ensure all dependencies are in `composer.json` / `package.json`
- Check that `nixpacks.toml` and `Procfile` are configured correctly

---

## Environment Variables Summary

### Backend Required Variables:
```
APP_NAME
APP_ENV
APP_DEBUG
APP_KEY
APP_URL
SANCTUM_STATEFUL_DOMAINS
DB_CONNECTION
DB_HOST
DB_PORT
DB_DATABASE
DB_USERNAME
DB_PASSWORD
```

### Frontend Required Variables:
```
REACT_APP_API_URL
```

---

## Useful Railway Commands

```bash
# Install Railway CLI
npm install -g @railway/cli

# Login to Railway
railway login

# Link to a project/service
railway link

# Deploy
railway up

# Run commands in Railway environment
railway run php artisan migrate
railway run php artisan db:seed

# View logs
railway logs

# Open service in browser
railway open
```

---

## Custom Domain (Optional)

### For Backend:
1. Go to backend service → **Settings** → **Networking**
2. Click **"Add Custom Domain"**
3. Enter your domain (e.g., `api.yourdomain.com`)
4. Add the CNAME record to your DNS provider
5. Update `APP_URL` environment variable

### For Frontend:
1. Go to frontend service → **Settings** → **Networking**
2. Click **"Add Custom Domain"**
3. Enter your domain (e.g., `app.yourdomain.com`)
4. Add the CNAME record to your DNS provider
5. Update `REACT_APP_API_URL` to use the backend custom domain

---

## Final Checklist

- [ ] MySQL database created and running
- [ ] Backend deployed and accessible
- [ ] Frontend deployed and accessible
- [ ] Environment variables configured for all services
- [ ] Database migrations ran successfully
- [ ] CORS configured correctly
- [ ] Frontend can communicate with backend API
- [ ] Public domains generated (or custom domains added)
- [ ] Testing complete - users can access the application

---

## Support

If you encounter issues:
1. Check Railway logs for each service
2. Verify environment variables are correct
3. Test API endpoints individually
4. Check network tab in browser for CORS issues
5. Review Railway documentation: https://docs.railway.app

---

**Your application is now live on Railway! 🚀**

Backend URL: `https://your-backend-abc123.up.railway.app`
Frontend URL: `https://your-frontend-xyz789.up.railway.app`
