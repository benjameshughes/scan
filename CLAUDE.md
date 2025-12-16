# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel-based barcode scanning application for warehouse stock management. The application allows users to scan product barcodes, track inventory changes, and sync with Linnworks warehouse management system.

## Key Technologies

- **Laravel 11.x** with PHP 8.4
- **Livewire 3.x + Flux Pro 2.x** for real-time UI components
- **MySQL** database (default)
- **Tailwind CSS 4.x** for styling
- **@zxing/library** for browser-based barcode scanning
- **Laravel Reverb** for WebSocket functionality
- **Spatie Laravel Permission** for role-based access control

### Testing

```bash
# Run all tests
./vendor/bin/pest

# Run specific test file
./vendor/bin/pest tests/Feature/ExampleTest.php

# Run tests with coverage
./vendor/bin/pest --coverage
```

### Code Style

```bash
# Fix code style issues
./vendor/bin/pint

# Check without fixing
./vendor/bin/pint --test
```

### Building Assets

```bash
# Development build with hot reload
npm run dev

# Production build
npm run build
```

### Version Management

```bash
# Bump version (interactive with safety checks and backups)
php artisan version:bump-improved

# Bump specific version type (patch/minor/major)
php artisan version:bump-improved patch

# Dry run to preview version bump
php artisan version:bump-improved patch --dry

# Generate Claude-style commit messages
php artisan version:bump-improved patch --claude

# Rollback to previous version
php artisan version:bump-improved --rollback=2.0.0

# Downgrade to specific version
php artisan version:bump-improved --downgrade=1.9.0
```

## Architecture Overview

### Core Models & Their Relationships

1. **User** → has many **Scans**
   - Uses Spatie permissions for role management (Admin/User)
   - Has settings JSON column for user preferences
   - Status field for active/inactive users

2. **Product** → has many **Scans**
   - Primary barcode + 2 additional barcode fields
   - SKU is the unique identifier
   - Links to Linnworks for inventory sync

3. **Scan** → belongs to **User** and **Product**
   - Records quantity changes (positive/negative)
   - Tracks submission status and timestamps
   - Action field for tracking scan purpose

4. **Invite** → for user invitation system
   - Token-based with expiration
   - Tracks usage and invitation status

### Key Livewire Components

- **Scanner**: Main barcode scanning interface at `/`
- **Dashboard**: Overview of recent scans and statistics
- **ProductsTable**: Product management with search
- **SyncsTable**: View sync history
- **Admin/Users/**: User management components

### Actions

- **LinnworksStockAction**: Handles stock sync with Linnworks API
- **SyncBarcodeAction**: Syncs product barcodes from Linnworks

### Middleware

- **IsInviteValid**: Validates invitation tokens
- Standard Laravel auth middleware
- Role-based access control via Spatie

## Linnworks Integration

The application integrates with Linnworks warehouse management system:

1. **Authentication**: Token stored in cache, auto-refreshed
2. **Stock Updates**: Batch submissions of scans update Linnworks stock levels
3. **Product Sync**: Pull product data and barcodes from Linnworks
4. **API Endpoints**: All in `LinnworksStockAction` class

## Frontend Architecture

### Barcode Scanning Flow

1. User accesses scanner at `/` 
2. JavaScript (`resources/js/barcode-scanner.js`) handles camera access
3. ZXing library decodes barcodes
4. Livewire component processes scan and shows product info
5. User can adjust quantity and submit

### Key JavaScript Features

- Camera selection (prefers rear-facing)
- Torch/flashlight control
- Vibration feedback on successful scan
- Auto-submit option
- Continuous scanning mode

### Styling

- Tailwind CSS 4.x with custom configuration
- Flux UI components for consistent design
- Mobile-first responsive design

## Design Language System

### Core Design Principles

1. **Professional & Clean**: Modern enterprise-grade appearance suitable for warehouse/business environments
2. **Accessibility First**: High contrast ratios, clear typography, keyboard navigation support
3. **Mobile Responsive**: Touch-friendly interfaces that work seamlessly on mobile devices and tablets
4. **Performance Oriented**: Lightweight components with minimal visual overhead

## Queue & Background Jobs

- Default queue driver: database
- Horizon available for production monitoring
- Main job: Stock sync with Linnworks after scan submission

## Security Considerations

- All routes protected by authentication except login/register
- Admin routes require admin role
- API integration uses secure token storage
- Invitation system prevents unauthorized registrations
- Domain restrictions for user registration via ALLOWED_DOMAINS env var