Deployment Checklist — House Rental (shared hosting)

1) Create a GitHub repository
- Create a new repo on github.com (private or public).
- Locally, initialize and push:

```bash
cd /path/to/house_rental
git init
git add .
git commit -m "Initial commit"
git branch -M main
git remote add origin https://github.com/your-username/your-repo.git
git push -u origin main
```

2) Sanitize secrets before pushing
- `includes/db.php` contains DB credentials. Do NOT commit real production credentials.
- Our `.gitignore` excludes `includes/db.php` and `uploads/`.
- Create a template `includes/db.php.example` with placeholders and commit that.

3) Export your local database
```bash
mysqldump -u root -p house_rental > house_rental.sql
```
- Keep the SQL file locally (do not commit) and import it to the hosting DB (phpMyAdmin or CLI).

4) Choose shared hosting and create DB
- In cPanel: MySQL Databases → create database and user, grant privileges.
- Note DB host (often `localhost`), DB name, DB user, DB password.
- Import `house_rental.sql` using phpMyAdmin.

5) Update `includes/db.php` on the server
- Upload a new `includes/db.php` with your host credentials (do not commit this file to GitHub).

6) Upload files to hosting
Option A — FTP (manual): use FileZilla to upload the repository files to `public_html/` or a subfolder.
Option B — Automated from GitHub: use the GitHub Actions FTP workflow (below).

7) GitHub Actions FTP deploy (optional — automatic on push)
- Add repository secrets: `FTP_HOST`, `FTP_USERNAME`, `FTP_PASSWORD`, `FTP_TARGET_DIR` (e.g. `/public_html/`)
- Add workflow `.github/workflows/deploy.yml` (example in repo). On push to `main`, GitHub will upload files to your host.

8) Post-deploy checks
- Ensure `uploads/` directory exists and is writable by web server.
- Install SSL via cPanel (Let's Encrypt) and enable HTTPS.
- Test registration/login/booking, and test email sending.

9) Secure admin area
- Change admin password. Consider adding `.htaccess` basic auth for `/admin` or restricting by IP.

Notes
- GitHub Pages cannot run PHP; you must use a host that supports PHP + MySQL.
- For email delivery in production, use a transactional SMTP provider (SendGrid, Mailgun) and update `includes/notification.php` with credentials.

If you want, I can:
- Create `includes/db.php.example` and `.github/workflows/deploy.yml` in your repo.
- Or generate an FTP GitHub Action you can enable and configure secrets for.
