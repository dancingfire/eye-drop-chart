# Eye Drop Chart Application - AI Agent Instructions

## Project Overview
This is a Laravel 12 + SQLite application for generating personalized eye drop medication schedules. Users build custom multi-week schedules with varying doses per day, then generate PDF charts or HTML previews.

## Architecture & Data Flow

### Core Workflow
1. **Admin Panel** (`/admin/medications`): CRUD for medication library (name + notes)
2. **Chart Form** (`/`): Dynamic JavaScript UI to build per-medication schedules
3. **Chart Generation**: Two routes produce identical output in different formats:
   - `/generate` → PDF download (via dompdf)
   - `/htmlchart` → HTML preview in browser

### Schedule Data Structure
The form submits nested arrays like:
```php
medications[0][id] = 5
medications[0][blocks][0][weeks] = 2
medications[0][blocks][0][doses] = 3
medications[0][blocks][1][weeks] = 1
medications[0][blocks][1][doses] = 1
```

Each medication gets a **weeks_schedule array** expanded from blocks. For example, the above becomes `[3, 3, 1]` (3 doses/day for weeks 0-1, 1 dose/day for week 2).

### PDF Chart Logic (`chart.pdf` blade)
- Renders 14 days per page (landscape letter format)
- Each medication gets `max(doses)` rows with checkboxes
- Week index calculated as `floor(dayIndex / 7)` from each medication's start date
- Active cells show empty checkbox, inactive show "×"

## Development Workflow

### Initial Setup
```bash
composer run setup  # installs deps, creates .env, runs migrations, builds assets
composer run dev    # starts 4 concurrent processes: server, queue, logs, vite
```

### Database
- SQLite (`database/database.sqlite`)
- Run migrations: `php artisan migrate`
- Seed medications: `php artisan db:seed` (adds 8 common eye drops)

### Frontend Stack
- **No bundled CSS framework** - uses Bootstrap 5 via CDN in `layouts/app.blade.php`
- Tailwind 4 + Vite configured but **not actively used** (v4 alpha)
- Dynamic form uses vanilla JavaScript (`chart/form.blade.php` @push('scripts'))

## Code Conventions

### Controllers
- `ChartController`: Duplicated logic in `generate()` and `htmlchart()` methods—only difference is PDF vs view return
- Uses `\PDF` facade alias for dompdf (configured in `config/dompdf.php`)
- Validation limits: max 4 medications, max 4 doses/day, min 1 week per block

### Blade Views
- Layout pattern: `@extends('layouts.app')` for all views
- Uses `@push('scripts')` for page-specific JavaScript
- PDF template has inline CSS (dompdf doesn't support external stylesheets well)

### Models
- Minimal: `Medication` model only has `fillable = ['name', 'notes']`
- No relationships or scopes defined

## Common Tasks

### Adding New Medications
Create via admin UI or directly in seeder using:
```php
DB::table('medications')->insert([
    'name' => 'Drug Name',
    'notes' => 'Dosing note',
    'created_at' => now(),
    'updated_at' => now(),
]);
```

### Modifying Chart Layout
- Days per page: Change `$daysPerPage = 14` in `chart/pdf.blade.php`
- Cell styling: Modify inline `<style>` in same file
- Paper size/orientation: Set in `ChartController` → `setPaper('letter', 'landscape')`

### Debugging PDF Issues
1. Use `/htmlchart` route to preview without PDF rendering
2. Check `storage/logs/laravel.log` for dompdf errors
3. Verify `weeks_schedule` array structure with `dd($meds)` before view render

## Known Issues & Quirks

- **Duplicate Code**: `generate()` and `htmlchart()` share 95% identical logic—refactor candidate
- **Max Constraints**: Form JavaScript enforces max 4 medications client-side only (no backend check beyond validation)
- **Font Limitation**: dompdf uses DejaVu Sans which lacks some currency symbols (€, £) when `convert_entities` is enabled

## Key Files Reference

- **Routes**: `routes/web.php` (all routes defined here)
- **Main Controller**: `app/Http/Controllers/ChartController.php`
- **Form UI**: `resources/views/chart/form.blade.php` (includes schedule-building JS)
- **PDF Template**: `resources/views/chart/pdf.blade.php` (shared by PDF and HTML routes)
- **Seeder**: `database/seeders/DatabaseSeeder.php` (includes sample medications)
- **PDF Config**: `config/dompdf.php` (remote access disabled for security)
