# Railway Deployment Quick Start Checklist

Follow these steps in order to deploy your ICT Learning Platform to Railway.

## ✅ Pre-Deployment Checklist

### Backend Preparation
- [x] Created `Procfile` for Railway
- [x] Created `nixpacks.toml` configuration
- [x] Updated `.env.example` for production
- [x] Updated CORS configuration

### Frontend Preparation
- [x] Added `serve` package to package.json
- [x] Created `nixpacks.toml` configuration
- [x] Created `serve.json` for routing
- [x] Created `.env.example` template

### What You Need to Do
- [ ] Install Railway CLI: `npm install -g @railway/cli`
- [ ] Install serve package in frontend: `cd "C:\MAMP\htdocs\project 29\ict-frontend" && npm install`
- [ ] Commit all changes to Git

---

## 🚀 Deployment Steps

### Step 1: Create Railway Project & Database (5 minutes)
1. Go to https://railway.app/dashboard
2. Click **"New Project"**
3. Select **"Deploy MySQL"**
4. ✅ MySQL database is now created!

### Step 2: Deploy Backend (10 minutes)
1. In same project, click **"+ New"** → **"Empty Service"**
2. Name it "Backend" or "API"
3. Click on the service → **Settings** → **Connect to GitHub**
4. Select your backend repository
5. Go to **Variables** tab and add:

**Copy these variables exactly:**
```bash
APP_NAME=ICT_Learning
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:Z6owch/QgCmAsSUidlBJHmJsn9B3Vceej97Ck51lXa4=

# Database - Railway auto-fills these from MySQL service
DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
LOG_LEVEL=info
BCRYPT_ROUNDS=12
```

6. Go to **Settings** → **Networking** → **Generate Domain**
7. Copy your backend URL (e.g., `https://ict-backend-abc123.up.railway.app`)
8. Add one more variable:
```bash
APP_URL=https://ict-backend-abc123.up.railway.app
FRONTEND_URL=https://your-frontend-will-go-here.up.railway.app
```

✅ Backend is deployed!

### Step 3: Deploy Frontend (10 minutes)
1. In same project, click **"+ New"** → **"Empty Service"**
2. Name it "Frontend" or "App"
3. Click on service → **Settings** → **Connect to GitHub**
4. Select your frontend repository
5. Go to **Variables** tab and add:
```bash
REACT_APP_API_URL=https://ict-backend-abc123.up.railway.app/api
```
(Use the backend URL from Step 2)

6. Go to **Settings** → **Networking** → **Generate Domain**
7. Copy your frontend URL (e.g., `https://ict-frontend-xyz789.up.railway.app`)

✅ Frontend is deployed!

### Step 4: Update Backend with Frontend URL (2 minutes)
1. Go back to **Backend service** → **Variables**
2. Update `FRONTEND_URL`:
```bash
FRONTEND_URL=https://ict-frontend-xyz789.up.railway.app
```
3. Update `SANCTUM_STATEFUL_DOMAINS` (add new variable):
```bash
SANCTUM_STATEFUL_DOMAINS=ict-frontend-xyz789.up.railway.app
```

✅ Services are connected!

### Step 5: Verify Deployment (5 minutes)
1. Visit your frontend URL
2. Try logging in
3. Check if data loads from API
4. Check Railway logs for any errors

---

## 📱 Your Live URLs

After deployment, you'll have:

- **Frontend (Users visit this)**: `https://ict-frontend-xyz789.up.railway.app`
- **Backend API**: `https://ict-backend-abc123.up.railway.app`
- **Database**: Managed by Railway (no public access needed)

---

## 🔧 Troubleshooting

### "Can't connect to database"
- Go to Backend → Variables
- Verify MySQL variables are referenced correctly: `${{MySQL.MYSQLHOST}}`

### "CORS error" in browser
- Check `FRONTEND_URL` is set in backend
- Verify `REACT_APP_API_URL` in frontend points to backend

### "Build failed"
- Check Railway logs (click service → Deployments → View Logs)
- Verify `nixpacks.toml` and `Procfile` exist in backend
- Verify `serve` package is in frontend package.json

### Frontend shows blank page
- Check browser console for errors
- Verify `REACT_APP_API_URL` is correct
- Make sure `serve.json` exists for routing

---

## 🔄 Updating Your App

### Update Backend Code:
```bash
cd "C:\MAMP\htdocs\project 28\ict-backend"
git add .
git commit -m "Update backend"
git push
```
Railway will auto-deploy! ✨

### Update Frontend Code:
```bash
cd "C:\MAMP\htdocs\project 29\ict-frontend"
git add .
git commit -m "Update frontend"
git push
```
Railway will auto-deploy! ✨

---

## 💰 Railway Pricing

**Free Tier**: $5 credit/month
- Good for testing and small projects
- All services count toward this limit

**Pro Plan**: $20/month + usage
- More resources and credits
- Production-ready

Start with free tier and upgrade when needed!

---

## ✅ Final Checklist

- [ ] MySQL database created
- [ ] Backend deployed with domain
- [ ] Frontend deployed with domain
- [ ] All environment variables set
- [ ] CORS configured
- [ ] Frontend can reach backend API
- [ ] Users can access the app

**You're live! 🎉**

---

## Need Help?

1. Read the full guide: `RAILWAY_DEPLOYMENT_GUIDE.md`
2. Check Railway docs: https://docs.railway.app
3. View logs in Railway dashboard
4. Test API endpoints with curl or Postman

---

**Total Time: ~30 minutes**
**Difficulty: Easy** ⭐⭐☆☆☆
