# Cloudways Deployment Guide

## Quick Deploy Methods (Ranked Best to Worst)

### 🥇 1. Git Deployment (Built-in Cloudways Feature)
**Setup Once:**
1. Cloudways Dashboard → Your App → Deployment via Git
2. Add repo: `https://github.com/dancingfire/eye-drop-chart.git`
3. Branch: `main`
4. Deployment script path: `deploy.sh`
5. Click "Generate Deployment Keys" if private repo

**Deploy:**
- Push to GitHub: `git push origin main`
- Click "Pull" in Cloudways dashboard (or enable auto-deploy)

**Pros:** 
- One-click deploy
- Only changed files transfer
- Can auto-deploy on push
- Free

---

### 🥈 2. GitHub Actions (Fully Automated)
**Setup Once:**
1. In GitHub repo: Settings → Secrets → Actions
2. Add secrets:
   - `FTP_SERVER`: Your Cloudways server IP
   - `FTP_USERNAME`: Your SFTP username
   - `FTP_PASSWORD`: Your SFTP password
   - `SSH_HOST`: Same as FTP_SERVER
   - `SSH_USERNAME`: Same as FTP_USERNAME
   - `SSH_PASSWORD`: Same as FTP_PASSWORD
   - `SSH_PORT`: 22

**Deploy:**
- Just push to main: `git push origin main`
- Auto-deploys in ~2 minutes

**Pros:**
- Fully automated
- Runs tests before deploy
- Can deploy to multiple environments
- Free for public repos

---

### 🥉 3. SFTP/Rsync (Better than FTP)
Use SFTP instead of FTP for encryption and speed.

**Windows (PowerShell):**
```powershell
# Using WinSCP
"C:\Program Files (x86)\WinSCP\WinSCP.com" `
  /command `
  "open sftp://username:password@server.cloudways.com/" `
  "synchronize remote C:\Work\eye-drop-chart /home/master/applications/APP_ID/public_html" `
  "exit"
```

**Using Git Bash on Windows:**
```bash
rsync -avz --exclude 'node_modules' --exclude '.git' --exclude 'storage/logs' \
  ./ username@server.cloudways.com:/home/master/applications/APP_ID/public_html/
```

---

### 🛑 4. FTP (What you're doing now - SLOWEST)
Only use if nothing else works.

---

## Cloudways Connection Details

Get these from Cloudways Dashboard → Your App → Access Details:
- **SFTP Host:** Your server IP
- **SFTP Username:** Shown in dashboard
- **SFTP Password:** Click "Show" or reset
- **SFTP Port:** 22
- **Application Path:** `/home/master/applications/YOUR_APP_ID/public_html`

---

## Post-Deployment Checklist

After ANY deployment method, SSH into server and run:
```bash
cd /home/master/applications/YOUR_APP_ID/public_html
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
chmod -R 755 storage bootstrap/cache
```

Or add these to `deploy.sh` for Git deployment.

---

## Recommended: Use Git Deployment

It's the easiest and fastest. Here's why:
- ✅ Only transfers changed files
- ✅ One-click deploy from Cloudways
- ✅ Can auto-deploy on git push
- ✅ Built into Cloudways (no setup)
- ✅ Rollback support
- ✅ Deployment logs

**5-Minute Setup:**
1. Push your code to GitHub (you already have)
2. Cloudways → Deployment via Git → Add your repo
3. Click "Pull"
4. Done!
