# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Laravel 12 + SQLite web application for generating personalized eye drop medication schedules as PDF charts or HTML previews. Used by pharmacies and eye care professionals with custom branding per user.

## Commands

```bash
# Setup
composer run setup    # Install deps, create .env, generate key, migrate, build assets

# Development
composer run dev      # Starts 4 concurrent processes: server, queue, logs, vite

# Testing
composer run test     # PHPUnit test suite

# Individual processes
php artisan serve     # Dev server (port 8000)
npm run dev           # Vite with hot reload
php artisan migrate   # Run migrations
php artisan db:seed   # Seed 8 common eye drops + admin user
```

Default admin login: `admin@example.com` / `password`

## Architecture

### Core Workflow
1. User logs in → dashboard shows chart form (`/dashboard`)
2. User selects medications and adds dosing "blocks" (weeks + doses/day per block)
3. Form submits to `/generate` (PDF download) or `/htmlchart` (HTML preview)
4. `ChartController` expands blocks into a `weeks_schedule` array, renders `chart/pdf.blade.php` via DomPDF

### Schedule Data Structure
Form submits nested arrays; the controller expands them:
```
medications[0][blocks][0][weeks] = 2, doses = 3
medications[0][blocks][1][weeks] = 1, doses = 1
→ weeks_schedule = [3, 3, 1]  (doses/day per week)
```

The PDF template renders 14 days per landscape page. Each medication gets `max(doses)` rows — active days show a checkbox, inactive show "×".

### Key Files
- **Routes**: `routes/web.php` — all routes defined here
- **Chart logic**: `app/Http/Controllers/ChartController.php` — `generate()` and `htmlchart()` share ~95% identical logic (refactor candidate)
- **Form UI**: `resources/views/chart/form.blade.php` — vanilla JS for dynamic medication/block building
- **PDF/HTML template**: `resources/views/chart/pdf.blade.php` — inline CSS required (DomPDF limitation)
- **Middleware**: `app/Http/Middleware/EnsureSuperuser.php` — guards admin routes

### Role System
- **Superuser** (`is_superuser = true`): Access to `/admin/users/*` and `/admin/audit`
- **Regular user**: Chart generation, templates, medications, profile

### Branding
Each user has `company_name`, `logo_path`, and `phone_number` fields. These appear on generated PDFs. Logos stored in `public/storage/` via Laravel storage symlink.

### Audit Logging
`ChartGeneration` model logs each chart export (user, dates, medication count, format). Viewable at `/admin/audit` (superuser only).

## Code Conventions

- All views extend `layouts/app.blade.php`; use `@push('scripts')` for page-specific JS
- PDF template uses inline `<style>` — DomPDF does not support external stylesheets
- Use `\PDF` facade alias (dompdf) — configured in `config/dompdf.php`
- Max 4 medications enforced client-side only; max 4 doses/day validated server-side
- Modify chart layout: `$daysPerPage` in `chart/pdf.blade.php`; paper size in `ChartController`

## Debugging PDFs
1. Use `/htmlchart` to preview without PDF rendering
2. Check `storage/logs/laravel.log` for DomPDF errors
3. Use `dd($meds)` before view render to inspect `weeks_schedule` structure
