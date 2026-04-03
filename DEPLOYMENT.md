# Deployment Guide

This project is ready for GitHub-triggered deployments to a Linux server over SSH.

## What the workflow does

Every push to `master` or `main` will:

1. Install Node.js dependencies in GitHub Actions
2. Build the Vite frontend assets
3. Upload the application to your server over SSH
4. Run `scripts/deploy.sh` on the server
5. Install PHP dependencies, run migrations, rebuild Laravel caches, and bring the app back up

## Server requirements

Your server should have:

- PHP 8.1 or newer
- Composer
- MySQL or MariaDB
- SSH access
- `rsync`
- A web server such as Apache or Nginx

## Web root

Best option:

- Point your domain to the project's `public/` directory

Apache fallback:

- This project already includes a root [`.htaccess`](/Users/iliassjaffal/Desktop/watchug-movie-and-tv-show-streaming-platform%201.0.3/watchug-movie-and-tv-show-streaming-platform/.htaccess) file that rewrites traffic into `public/`, which can help on shared Apache-style hosting when the document root cannot be changed

## First server setup

Create your app folder on the server, for example:

```bash
mkdir -p /var/www/watchug
```

Upload or create the production `.env` file on the server:

```bash
cp .env.example .env
```

Then fill in your real production values:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://your-domain.com`
- Your real database, mail, storage, OneSignal, Stripe, and Google credentials

Generate an app key only if your production `.env` does not already have one:

```bash
php artisan key:generate
```

Make sure the deploy user can write to `storage/` and `bootstrap/cache/`.

## GitHub secrets

Add these repository secrets in GitHub:

- `DEPLOY_HOST`: your server IP or hostname
- `DEPLOY_PORT`: usually `22`
- `DEPLOY_USER`: the SSH user that owns the app files
- `DEPLOY_PATH`: absolute path to the app on the server, such as `/var/www/watchug`
- `DEPLOY_SSH_KEY`: the private SSH key GitHub Actions should use

## Recommended Git setup

Before the first push, make sure you do not commit your real `.env` file or built frontend files:

```bash
git rm --cached .env
git rm -r --cached public/build
```

Then add your GitHub remote and push:

```bash
git remote add origin git@github.com:YOUR-USERNAME/YOUR-REPO.git
git push -u origin master
```

## Manual deploy on the server

You can also run the same deploy script manually:

```bash
bash scripts/deploy.sh
```
