# 📋 Eye Drop Chart Generator

A Laravel application for creating personalized eye drop medication schedules with custom branding. Ideal for pharmacies, ophthalmology clinics, and eye care professionals.

## Features

- 🔐 **User Authentication** - Secure login system with role-based access
- 👥 **Multi-User Support** - Each user can have custom company branding
- 🏢 **Custom Branding** - Add company logo and name to all generated PDFs
- 📅 **Flexible Scheduling** - Day-based scheduling (1-70 days) with 0-4 doses per day
- 💊 **Medication Library** - Pre-populated with common eye drops (admin can manage)
- 📄 **PDF Generation** - Professional landscape charts with 14 days per page
- 🔖 **Templates** - Save and reuse common medication schedules
- 🕒 **Time-of-Day Labels** - Morning, Midday, Supper, Bedtime dosing
- 🗓️ **Surgery Date Tracking** - Display surgery date prominently on charts

## Quick Start

### Requirements

- PHP 8.2+
- Composer
- Node.js & NPM
- SQLite (or MySQL/PostgreSQL)

### Installation

```bash
# Clone repository
git clone https://github.com/dancingfire/eye-drop-chart.git
cd eye-drop-chart

# Run setup script (installs dependencies, creates DB, runs migrations)
composer run setup

# Start development server
composer run dev
```

Visit `http://localhost:8000` and login with:
- **Email:** admin@example.com
- **Password:** password

### Manual Setup

If `composer run setup` doesn't work:

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan db:seed
php artisan storage:link
npm install
npm run build
php artisan serve
```

## Usage

### Creating a Chart

1. **Login** to your account
2. **Set start date** and optional surgery date
3. **Add medications** from the dropdown
4. **Build schedule blocks**:
   - Number of days for this block
   - Doses per day (0-4x daily)
5. **Add more medications** (up to 4)
6. **Generate PDF** or save as template

### Example Schedule

**Prednisolone (Steroid)**
- Days 1-7: 4x daily (intensive post-op)
- Days 8-14: 3x daily (tapering)
- Days 15-21: 2x daily
- Days 22-28: 1x daily
- Days 29-35: 0x daily (stopped)

### Managing Users (Superusers Only)

1. Go to **Admin → Users**
2. Click **+ Add User**
3. Fill in details:
   - Name and email
   - Password
   - Company name (shown on PDFs)
   - Logo image (max 2MB)
   - Superuser checkbox (for admin access)
4. Click **Create User**

### Managing Medications (Superusers Only)

1. Go to **Admin → Medications**
2. Add/edit/delete medications in the library
3. Changes apply to all users

## Architecture

**Stack:** Laravel 12, SQLite, Bootstrap 5, dompdf  
**Pattern:** MVC with Blade templating  
**Auth:** Laravel Breeze with custom superuser middleware

### Key Files

- `app/Http/Controllers/ChartController.php` - Chart generation logic
- `app/Http/Controllers/UserController.php` - User CRUD operations
- `resources/views/chart/form.blade.php` - Dynamic schedule builder
- `resources/views/chart/pdf.blade.php` - PDF template with user branding
- `routes/web.php` - All route definitions with middleware

### Database Tables

- `users` - User accounts with branding fields
- `medications` - Shared medication library
- `schedule_templates` - Saved medication schedules (JSON)

## Documentation

- [📖 Authentication & User Management](AUTHENTICATION.md)
- [🚀 Deployment Guide](DEPLOYMENT.md)
- [👨‍💻 AI Agent Instructions](.github/copilot-instructions.md)

## Common Tasks

### Change Admin Password

1. Click your name → **Profile**
2. Go to **Update Password** section
3. Enter current and new password
4. Click **Save**

### Add Your Company Branding

1. **Admin → Users**
2. Click **Edit** next to admin user
3. Add company name
4. Upload logo image
5. Click **Update User**

### Deploy to Cloudways

1. Push to GitHub: `git push origin main`
2. In Cloudways dashboard:
   - Enable **Deployment via Git**
   - Add repository URL
   - Set deployment script to `deploy.sh`
   - Click **Pull**

See [DEPLOYMENT.md](DEPLOYMENT.md) for detailed instructions.

## Development

### Run Development Server

```bash
composer run dev
```

This starts 4 concurrent processes:
- Laravel server (port 8000)
- Queue worker
- Log viewer (pail)
- Vite dev server (hot reload)

### Run Tests

```bash
composer run test
```

### Database Seeding

```bash
# Seed with default admin and medications
php artisan db:seed

# Fresh database (warning: deletes all data)
php artisan migrate:fresh --seed
```

## Security

- All routes protected with authentication middleware
- Admin routes require superuser status
- Passwords hashed with bcrypt
- CSRF protection on all forms
- Logo uploads validated (type, size)
- Safe user deletion (prevent self-delete, last superuser)

**Default Credentials:**  
⚠️ Change immediately after first login!

## Troubleshooting

### "403 Forbidden" on Admin Pages
You need superuser status. Have an admin edit your user account.

### Logo Not Showing
Run `php artisan storage:link` to create symbolic link.

### Migration Errors
```bash
php artisan migrate:fresh --seed
```

### PDF Generation Fails
Check `storage/logs/laravel.log` for dompdf errors.

## Contributing

1. Fork the repository
2. Create feature branch: `git checkout -b feature/my-feature`
3. Commit changes: `git commit -m "Add my feature"`
4. Push to branch: `git push origin feature/my-feature`
5. Open pull request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

For issues or questions:
- Open a [GitHub Issue](https://github.com/dancingfire/eye-drop-chart/issues)
- Email: [Contact developer]
- Documentation: See `AUTHENTICATION.md` and `DEPLOYMENT.md`
