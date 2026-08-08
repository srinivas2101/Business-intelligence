# Business Intelligence System — Frontend

React + CRA frontend for the BI system.

## 🚀 Deploy to Vercel

### Step 1: Push to GitHub
```bash
git init
git add .
git commit -m "initial commit"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/business-intelligence.git
git push -u origin main
```

### Step 2: Vercel Settings
- Framework Preset: **Create React App**
- Build Command: `npm run build`
- Output Directory: `build`
- Install Command: `npm install --legacy-peer-deps`

### Step 3: Environment Variable (in Vercel Dashboard)
```
REACT_APP_API_BASE = https://your-php-backend-url.com/backend/api
```

## 🔑 Login
Sign in with the email/password configured in `frontend/src/pages/LoginPage.jsx` (`CREDENTIALS` array). Change these before deploying publicly.

## ⚠️ Backend Note
The PHP backend must be hosted separately (cPanel, InfinityFree, 000webhost, etc.)
and the URL set in the Vercel environment variable above.