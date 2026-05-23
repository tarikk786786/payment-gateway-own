# DEZOPAY Secure Deployment Guide

This guide explains how to deploy your PHP-based DEZOPAY application securely using Render's Free Tier and a free MySQL database, ensuring secrets are kept safe.

## 1. Files Changed
- `.gitignore`: Added to prevent uploading sensitive data (`.env`) to GitHub.
- `.env.example`: A template for your environment variables.
- `auth/config.php`: Modified to load credentials from `.env` instead of hardcoding them, and added **Security Headers** (Helmet equivalent).
- `Dockerfile` & `render.yaml`: Added to support deploying PHP apps on Render.
- `mysql-schema.sql`: Added to create required `orders` and `webhook_logs` tables safely.

## 2. Prepare Database
1. Go to a free MySQL provider (like Aiven or your existing cPanel MySQL).
2. Create a new database.
3. Import your existing `u740980038_smmm.sql`.
4. Import the new `mysql-schema.sql` to add order and webhook tracking.

## 3. GitHub Upload
Run these commands locally to push your code to GitHub securely:
```bash
git init
git add .
git commit -m "Secure deployment setup"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPO_NAME.git
git push -u origin main
```
*(Make sure you DO NOT upload a `.env` file!)*

## 4. Render Deployment Settings (Free Web Service)
1. Go to **Render.com** and click **New > Blueprint**.
2. Connect your GitHub repository.
3. Render will automatically detect the `render.yaml` and `Dockerfile`.
4. **Environment Variables:** During setup, Render will ask you for the values of the variables listed in `render.yaml`. Fill them in:
   - `DB_HOST`: Your MySQL Hostname
   - `DB_USER`: Your MySQL Username
   - `DB_PASS`: Your MySQL Password
   - `DB_NAME`: Your MySQL Database Name
   - `RAZORPAY_KEY_ID`: Your test key `rzp_test_xxxx`
   - `RAZORPAY_KEY_SECRET`: Your Razorpay Secret
   - `RAZORPAY_WEBHOOK_SECRET`: Your generated webhook secret
5. Click **Apply** to deploy.

## 5. Webhook Settings
1. Go to your Razorpay Dashboard (Test Mode).
2. Go to Settings > Webhooks > Add New Webhook.
3. **Webhook URL:** `https://your-render-app-url.onrender.com/webhook.php` (You will need to create webhook.php if it doesn't exist, using the backend verification).
4. **Secret:** Match this with `RAZORPAY_WEBHOOK_SECRET`.

## 6. Final Safety Checklist
- [x] No hardcoded passwords in `config.php`.
- [x] `.env` is ignored by Git.
- [x] Security headers (Helmet) are enabled.
- [x] Deployment is containerized (Docker).
- [ ] You are using Test Mode Razorpay keys.
- [ ] No card details or UPI PINs are ever captured in your forms (always redirect to Razorpay Checkout).
