# 🔐 Authentication & User Management Guide

## Default Login Credentials

After running the seeder, you can log in with:

**Email:** `admin@example.com`  
**Password:** `password`

⚠️ **Important:** Change this password immediately after first login!

## User Roles

### Superuser (Administrator)
- Can create, edit, and delete users
- Can manage the medication library
- Can generate eye drop charts
- Has full system access

### Regular User
- Can generate eye drop charts with their own branding
- Can save and load templates
- Cannot access admin functions
- Cannot manage other users or medications

## User Branding Features

Each user account includes:

1. **Company Name** - Displayed on PDF charts (defaults to "Southeast Wellness Pharmacy" if not set)
2. **Logo Image** - Displayed at top of PDF charts (optional, max 2MB)
3. **Superuser Flag** - Grants admin access

### Uploading a Logo

1. Go to Admin → Users
2. Edit your user account
3. Upload an image (JPG, PNG, or GIF, max 2MB)
4. Logo will appear on all PDFs you generate

**Logo Guidelines:**
- Recommended size: 200x60 pixels
- Max file size: 2MB
- Formats: JPG, PNG, GIF
- Transparent backgrounds work best

## Admin Functions

### User Management (Superusers Only)

**Access:** Admin → Users

**Create New User:**
1. Click "+ Add User"
2. Fill in name, email, password
3. Optionally add company name and logo
4. Check "Superuser" if they need admin access
5. Click "Create User"

**Edit User:**
1. Click "Edit" next to user name
2. Update fields as needed
3. Leave password blank to keep current password
4. Click "Update User"

**Delete User:**
1. Click "Delete" next to user name
2. Confirm deletion
3. You cannot delete yourself or the last superuser

### Medication Library (Superusers Only)

**Access:** Admin → Medications

This is the shared medication library used by all users when creating charts.

## Security Features

### Protected Routes
- All chart-related routes require authentication
- Admin routes require superuser status
- Unauthorized access returns 403 error

### Password Requirements
- Minimum 8 characters
- Must be confirmed on creation/change
- Hashed in database (bcrypt)

### Safe Deletions
- Cannot delete your own account
- Cannot delete the last superuser
- Deleting user also deletes their logo file

## Multi-Tenant Setup

Each user's PDFs show their own:
- Company name
- Company logo (if uploaded)
- Default phone number (currently hardcoded, can be made per-user)

This allows multiple pharmacies/clinics to use the same system with their own branding.

## First-Time Setup

1. **Install Dependencies:**
   ```bash
   composer install
   npm install
   ```

2. **Configure Environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Run Migrations:**
   ```bash
   php artisan migrate
   ```

4. **Seed Database:**
   ```bash
   php artisan db:seed
   ```
   This creates the default admin user and medication library.

5. **Link Storage:**
   ```bash
   php artisan storage:link
   ```
   Required for logo uploads to work.

6. **Build Assets:**
   ```bash
   npm run build
   ```

7. **Start Server:**
   ```bash
   php artisan serve
   ```

8. **Login:**
   Visit `http://localhost:8000` and login with:
   - Email: admin@example.com
   - Password: password

9. **Change Admin Password:**
   Click your name → Profile → Update Password

10. **Update Your Branding:**
    Admin → Users → Edit admin user → Add company name and logo

## Deployment Notes

### Cloudways Deployment

The `deploy.sh` script now includes:
- Running migrations (includes user table updates)
- Cache optimization
- Permission setting
- Storage link creation

### Environment Variables

Ensure production `.env` has:
```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=your-generated-key

# Database
DB_CONNECTION=sqlite
DB_DATABASE=/full/path/to/database.sqlite

# File Storage
FILESYSTEM_DISK=public
```

### Post-Deployment Checklist

After deploying to production:

1. ✅ Run migrations: `php artisan migrate --force`
2. ✅ Seed database: `php artisan db:seed --force`
3. ✅ Link storage: `php artisan storage:link`
4. ✅ Set permissions: `chmod -R 755 storage bootstrap/cache`
5. ✅ Create `storage/app/public/logos` directory
6. ✅ Login and change admin password
7. ✅ Create additional user accounts
8. ✅ Update admin user with your company branding

## Troubleshooting

### "403 Forbidden" on Admin Pages
- You're logged in as a regular user (not superuser)
- Contact your admin to upgrade your account

### Logo Not Showing on PDF
- Check file uploaded successfully (Admin → Users → Edit)
- Verify storage link exists: `ls -la public/storage`
- Check file permissions in `storage/app/public/logos`

### Cannot Upload Logo
- Check file size (max 2MB)
- Verify `storage/app/public` is writable
- Run `php artisan storage:link`

### Database Errors After Update
- Run: `php artisan migrate`
- Check `database/database.sqlite` permissions

## API Endpoints

All endpoints require authentication (session cookie).

### Chart Generation
- `POST /generate` - Generate PDF download
- `POST /htmlchart` - HTML preview of chart

### Templates
- `GET /templates` - List user's templates
- `POST /templates` - Save new template
- `GET /templates/{id}` - Load template
- `DELETE /templates/{id}` - Delete template

### Admin (Superuser Only)
- `GET /admin/users` - List users
- `POST /admin/users` - Create user
- `GET /admin/users/{id}/edit` - Edit form
- `PUT /admin/users/{id}` - Update user
- `DELETE /admin/users/{id}` - Delete user

- `GET /admin/medications` - List medications
- `POST /admin/medications` - Create medication
- (etc.)

## Security Best Practices

1. **Change Default Password** - Never use `password` in production
2. **Use Strong Passwords** - Minimum 12 characters with mixed case, numbers, symbols
3. **Limit Superusers** - Only grant to trusted administrators
4. **Regular Backups** - Backup `database/database.sqlite` and `storage/app/public/logos`
5. **HTTPS Only** - Never run in production without SSL/TLS
6. **Update Regularly** - Keep Laravel and dependencies up to date
7. **Monitor Logs** - Check `storage/logs/laravel.log` for suspicious activity

## Future Enhancements

Possible additions:
- [ ] Company phone number per user (currently hardcoded)
- [ ] Custom PDF header/footer text per user
- [ ] User activity logging
- [ ] Password reset via email
- [ ] Two-factor authentication
- [ ] API tokens for programmatic access
- [ ] User permissions beyond superuser/regular
- [ ] Bulk user import/export
