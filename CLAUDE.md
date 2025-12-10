# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel-based barcode scanning application for warehouse stock management. The application allows users to scan product barcodes, track inventory changes, and sync with Linnworks warehouse management system.

## Key Technologies

- **Laravel 11.x** with PHP 8.4
- **Livewire 3.x + Flux 2.x** for real-time UI components
- **SQLite** database (default)
- **Tailwind CSS 4.x** for styling
- **@zxing/library** for browser-based barcode scanning
- **Laravel Reverb** for WebSocket functionality
- **Spatie Laravel Permission** for role-based access control

## Development Commands

### Starting Development Environment

```bash
# Standard development mode (recommended for most work)
composer dev

# Full development mode with Horizon and Reverb (for queue/websocket work)
composer horizon
```

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

### Database Commands

```bash
# Run migrations
php artisan migrate

# Refresh database with seeders
php artisan migrate:fresh --seed

# Create new migration
php artisan make:migration create_example_table
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

### Table System

**Modern Table System (Recommended)**
- Use `App\Tables\TableComponent` as base class for all new tables
- Create table components in `app/Livewire/` directory structure
- Extends the base component with model-specific functionality
- Provides auto-discovery of columns, search, filtering, and pagination
- Supports bulk actions, CRUD operations, and export functionality

**Implementation Guidelines:**
- Create new table components by extending `TableComponent`
- Define `$model`, `$searchable`, and `$title` properties
- Override `table()` method to customize columns, filters, and actions
- Use the modern table system for all new tables
- Tables should follow the design language system (see below)

**Pagination System:**
- **Full Pagination** (`pagination.custom`): Complete page navigation with page numbers
  - Use for: Full-width tables, main content areas, product lists, user management
  - Features: Page numbers, first/last links, "Showing X to Y of Z results"
  - Layout: Responsive with mobile-optimized Previous/Next only view
- **Simple Pagination** (`pagination.simple`): Compact Previous/Next navigation
  - Use for: Dashboard cards, sidebar lists, narrow columns, modal content
  - Features: Previous/Next buttons with "Page X of Y" indicator
  - Layout: Always compact, 60% smaller than full pagination
- Set as defaults in `AppServiceProvider` for consistency across application
- Both pagination views follow design system standards with proper dark mode and accessibility
- Manual pagination calls:
  - Full: `{{ $data->links('pagination.custom') }}`
  - Simple: `{{ $data->links('pagination.simple') }}`
  - Automatic: `{{ $data->links() }}` (uses defaults based on pagination type)

**Legacy System:**
- Custom table system in `app/Tables/` (legacy, avoid for new tables)
- Used for products, users, and syncs tables (being migrated)

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

Required environment variables:
```
LINNWORKS_APP_ID=
LINNWORKS_TOKEN=
LINNWORKS_SECRET=
LINNWORKS_SERVER=
```

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

### Color Palette

#### Primary Colors
- **Primary Blue**: `blue-600` (#2563eb) / `blue-700` (#1d4ed8) - Main actions, links, primary buttons
- **Primary Blue Light**: `blue-50` (#eff6ff) / `blue-100` (#dbeafe) - Light backgrounds, highlights
- **Primary Blue Dark**: `blue-800` (#1e40af) / `blue-900` (#1e3a8a) - Dark mode primary, emphasis

#### Neutral Colors (Primary Palette)
- **Zinc Base**: `zinc-50` (#fafafa) - Light mode backgrounds
- **Zinc Light**: `zinc-100` (#f4f4f5) - Card backgrounds, subtle borders
- **Zinc Medium**: `zinc-200` (#e4e4e7) - Borders, dividers
- **Zinc Dark**: `zinc-700` (#3f3f46) - Dark mode text
- **Zinc Deeper**: `zinc-800` (#27272a) - Dark mode backgrounds
- **Zinc Darkest**: `zinc-900` (#18181b) - Dark mode deep backgrounds

#### Gray Accents (Secondary)
- **Gray Light**: `gray-50` (#f9fafb) - Alternative light backgrounds
- **Gray Medium**: `gray-400` (#9ca3af) - Muted text, placeholders
- **Gray Dark**: `gray-600` (#4b5563) - Secondary text
- **Gray Deeper**: `gray-800` (#1f2937) - Dark mode secondary text

#### Status Colors
- **Success**: `green-600` (#16a34a) / `green-100` (#dcfce7) - Success states, positive indicators
- **Warning**: `amber-600` (#d97706) / `amber-100` (#fef3c7) - Warning states, pending status
- **Error**: `red-600` (#dc2626) / `red-100` (#fee2e2) - Error states, destructive actions
- **Info**: `sky-600` (#0284c7) / `sky-100` (#e0f2fe) - Informational content

### Typography Scale

#### Font Families
- **Primary**: `font-sans` (Inter, system-ui, sans-serif) - Body text, UI elements
- **Monospace**: `font-mono` (JetBrains Mono, Menlo, monospace) - Code, data display

#### Text Sizes & Weights
- **Display Large**: `text-3xl font-bold` (30px) - Page titles, hero headings
- **Display Medium**: `text-2xl font-semibold` (24px) - Section headers
- **Heading Large**: `text-xl font-semibold` (20px) - Card titles, subsection headers
- **Heading Medium**: `text-lg font-medium` (18px) - Component headers
- **Body Large**: `text-base font-normal` (16px) - Primary body text
- **Body Medium**: `text-sm font-normal` (14px) - Secondary text, form labels
- **Body Small**: `text-xs font-normal` (12px) - Captions, metadata
- **Caption**: `text-xs font-medium` (12px) - Small labels, badges

### Layout & Spacing System
- Application layout uses `max-w-7xl` container with horizontal padding
- Forms within the layout should be full width (`w-full`) to utilize available space
- Exception: Authentication forms may use centered narrow containers (`max-w-md mx-auto`) for UX

#### Border Radius Standards
- **Small**: `rounded-md` (6px) - Buttons, form inputs, small cards
- **Medium**: `rounded-lg` (8px) - Cards, modals, major UI sections
- **Large**: `rounded-xl` (12px) - Large containers, feature sections
- **Full**: `rounded-full` - Avatars, icon buttons, badges

#### Shadow Hierarchy
- **Subtle**: `shadow-sm` - Form inputs, minimal elevation
- **Default**: `shadow` - Cards, dropdowns, standard elevation
- **Medium**: `shadow-md` - Modals, elevated panels
- **Large**: `shadow-lg` - Major overlays, important alerts

#### Spacing Scale (Consistent 4px Grid)
- **Tight**: `space-y-2` (8px) - Compact lists, form groups
- **Default**: `space-y-4` (16px) - Standard component spacing
- **Comfortable**: `space-y-6` (24px) - Section spacing
- **Loose**: `space-y-8` (32px) - Major section breaks

### Component Design Standards

#### Cards & Containers
```html
<!-- Standard Card -->
<div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
  <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Card Title</h3>
  </div>
  <div class="p-6">Card Content</div>
</div>
```

#### Buttons
- **Primary**: `bg-blue-600 hover:bg-blue-700 text-white` - Main actions
- **Secondary**: `bg-zinc-100 hover:bg-zinc-200 text-zinc-900 dark:bg-zinc-700 dark:hover:bg-zinc-600 dark:text-zinc-100` - Secondary actions
- **Danger**: `bg-red-600 hover:bg-red-700 text-white` - Destructive actions
- **Ghost**: `text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-zinc-800` - Subtle actions

#### Form Elements
- **Inputs**: `border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 rounded-md focus:ring-blue-500 focus:border-blue-500`
- **Labels**: `text-sm font-medium text-gray-700 dark:text-gray-200`
- **Help Text**: `text-xs text-gray-500 dark:text-gray-400`
- **Error Text**: `text-xs text-red-600 dark:text-red-400`

#### Tables
- **Header**: `bg-zinc-50 dark:bg-zinc-800 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider`
- **Row**: `border-b border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800`
- **Cell**: `px-6 py-4 text-sm text-gray-900 dark:text-gray-100`

#### Status Indicators
- **Badge Success**: `bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded-full px-2 py-1 text-xs font-medium`
- **Badge Warning**: `bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200 rounded-full px-2 py-1 text-xs font-medium`
- **Badge Error**: `bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 rounded-full px-2 py-1 text-xs font-medium`
- **Badge Info**: `bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded-full px-2 py-1 text-xs font-medium`

### Dark Mode Standards
- Always use light and dark tailwind classes

#### Background Hierarchy
- **Page Background**: `bg-zinc-900` - Main page background
- **Card Background**: `bg-zinc-800` - Card and component backgrounds  
- **Input Background**: `bg-zinc-700` - Form inputs, interactive elements
- **Subtle Background**: `bg-zinc-600` - Hover states, disabled elements

#### Text Hierarchy
- **Primary Text**: `text-gray-100` - Main content, headings
- **Secondary Text**: `text-gray-200` - Subheadings, important secondary content
- **Muted Text**: `text-gray-400` - Labels, metadata, less important text
- **Disabled Text**: `text-gray-500` - Disabled states, placeholder text

### Accessibility Requirements

#### Contrast Ratios
- All text must meet WCAG AA standards (4.5:1 for normal text, 3:1 for large text)
- Interactive elements must have clear focus indicators
- Color should not be the only way to convey information

#### Interactive States
- **Focus**: `focus:ring-2 focus:ring-blue-500 focus:outline-none` or `focus:ring-1 focus:ring-blue-500 focus:border-blue-500`
- **Hover**: Subtle color shifts (50-100 units in Tailwind scale)
- **Active**: More pronounced color shifts (100-200 units)
- **Disabled**: `opacity-50 cursor-not-allowed`

#### Navigation Standards
- **Navigation Background**: `bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700`
- **Navigation Links**: `text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100`
- **Active Navigation**: `text-blue-600 dark:text-blue-400 border-b-2 border-blue-600 dark:border-blue-400`
- **Mobile Menu**: `bg-zinc-50 dark:bg-zinc-700 border-t border-zinc-200 dark:border-zinc-700`

#### Form Validation
- **Error Text**: `text-red-600 dark:text-red-400 text-xs mt-1`
- **Error Borders**: `border-red-300 dark:border-red-600 focus:border-red-500 focus:ring-red-500`
- **Success Borders**: `border-green-300 dark:border-green-600 focus:border-green-500 focus:ring-green-500`
- **Help Text**: `text-gray-500 dark:text-gray-400 text-xs mt-1`

### Animation & Transitions

#### Standard Transitions
- **Default**: `transition-colors duration-200` - Color changes
- **Transform**: `transition-transform duration-200` - Scale, position changes
- **Opacity**: `transition-opacity duration-300` - Fade in/out
- **All**: `transition-all duration-200` - Multiple property changes

#### Loading States
- **Skeleton**: `animate-pulse bg-zinc-200 dark:bg-zinc-700` - Content loading
- **Spinner**: Custom spinner component using `animate-spin`

### Implementation Guidelines

1. **Consistency**: Always use the defined color palette and spacing system
2. **Dark Mode**: All components must support both light and dark modes
3. **Responsiveness**: Design mobile-first, enhance for larger screens
4. **Performance**: Minimize custom CSS, leverage Tailwind utilities
5. **Accessibility**: Test with keyboard navigation and screen readers
6. **Progressive Enhancement**: Ensure functionality without JavaScript when possible

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

## Common Tasks

### Adding a New Table

1. Create table class extending `app/Tables/Table.php`
2. Define columns in the table class
3. Create Livewire component extending `TableComponent`
4. Add route and navigation item

### Adding Scanner Features

1. Modify `resources/js/barcode-scanner.js` for camera handling
2. Update `app/Livewire/Scanner.php` for processing logic
3. Edit `resources/views/livewire/scanner.blade.php` for UI

### Modifying Linnworks Integration

1. All API logic in `app/Actions/LinnworksStockAction.php`
2. Token refresh handled automatically via cache
3. Add new endpoints as methods in the action class

## Database Structure

Key tables:
- `users`: Authentication and permissions
- `products`: SKU, name, and multiple barcodes
- `scans`: Barcode scan records with quantities
- `invites`: User invitation tokens
- `external_emails`: Notification recipients

## Testing Guidelines

### **MANDATORY: Create Pest Tests for All New Features**

**When to Create Tests:**
- Every new feature implementation
- Every new component or service
- Every bug fix that could regress
- Every API endpoint or integration

**Test Types Required:**
- **Feature Tests** for Livewire components (`tests/Feature/`)
- **Unit Tests** for actions, services, and models (`tests/Unit/`)
- **Integration Tests** for API interactions and external services

**Test File Naming:**
- Livewire components: `tests/Feature/ComponentNameTest.php`
- Actions/Services: `tests/Unit/Actions/ActionNameTest.php`
- Models: `tests/Unit/Models/ModelNameTest.php`

**IMPORTANT: Use Pest Framework (NOT PHPUnit or custom PHP files)**
- All tests must use Pest syntax and helpers
- Use `./vendor/bin/pest` to run tests
- Database refreshed between tests automatically
- Leverage Pest's Laravel helpers for cleaner test code

**Test Examples:**
```php
// Feature test for Livewire component
it('can paginate through failed scans', function () {
    $scans = Scan::factory()->count(15)->create(['submitted_at' => null]);
    
    Livewire::test(FailedScanList::class)
        ->assertSee($scans->first()->barcode)
        ->call('nextPage')
        ->assertSee($scans->get(10)->barcode);
});

// Unit test for pagination view
it('renders simple pagination correctly', function () {
    $scans = Scan::factory()->count(20)->create();
    $paginated = $scans->paginate(5);
    
    $view = view('pagination.simple', ['paginator' => $paginated]);
    
    expect($view->render())
        ->toContain('Previous')
        ->toContain('Next')
        ->toContain('Page 1 of 4');
});
```

**DO NOT:**
- Create custom PHP test files (like `test-pagination.php`)
- Skip tests for "simple" features
- Use manual testing as a substitute for automated tests
- Test only happy paths (include edge cases and error scenarios)

**Example Test Files in Codebase:**
- `tests/Feature/PaginationTest.php` - Comprehensive pagination system tests
- `tests/Feature/UserManagementTest.php` - User management and invitation tests
- `tests/Feature/ProductScannerTest.php` - Barcode scanning component tests

## UI Development Guidelines

### Design System Compliance (MANDATORY)

**ALWAYS consult the Design Language System before designing or redesigning UI:**
- Follow the established color palette, typography scale, and spacing system
- Use the defined component standards for cards, buttons, forms, and tables
- Ensure dark mode compatibility using the specified color hierarchy
- Apply accessibility requirements including proper contrast ratios and focus states
- Use standard transitions and animations as defined in the system

### Current System Issues & Standards

**Button Standards:**
- **Primary Actions**: Use `bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600` 
- **Secondary Actions**: Use `bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-700 dark:hover:bg-zinc-600`
- **Danger Actions**: Use `bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-600`
- **Ghost Actions**: Use `text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-zinc-800`
- Replace inconsistent gray-based buttons with zinc-based equivalents

**Form Design Standards:**

#### Form Layout & Structure
- **Container Width**: Use `w-full` for all forms (app layout provides max-width constraint)
- **Card Container**: `bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700`
- **Form Header**: Include descriptive title and optional subtitle in header section with border separation
- **Field Spacing**: Use `space-y-4` for field groups, `space-y-6` for major form sections
- **Field Groups**: Wrap related fields in logical groupings with subtle visual separation
- **Form Actions**: Place primary actions on the right, secondary on the left with consistent spacing
- **Grid Layout**: Use `grid grid-cols-1 md:grid-cols-2 gap-4` for side-by-side fields on larger screens

#### Input Field Standards
- **Base Styling**: `border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 rounded-md`
- **Focus States**: `focus:ring-blue-500 focus:border-blue-500 focus:ring-1` (single ring for subtlety)
- **Text Colors**: `text-gray-900 dark:text-gray-100` for input text
- **Placeholder Colors**: `placeholder-gray-500 dark:placeholder-gray-400`
- **Disabled States**: `bg-zinc-50 dark:bg-zinc-800 text-gray-500 dark:text-gray-400 cursor-not-allowed`

#### Label & Help Text Standards
- **Labels**: `text-sm font-medium text-gray-700 dark:text-gray-200 mb-1` (always above inputs)
- **Required Indicators**: Use red asterisk `text-red-500` after label text
- **Help Text**: `text-xs text-gray-500 dark:text-gray-400 mt-1` below inputs
- **Error Text**: `text-xs text-red-600 dark:text-red-400 mt-1` with error icon when applicable

#### Form Validation Standards
- **Error State Inputs**: `border-red-300 dark:border-red-600 focus:border-red-500 focus:ring-red-500`
- **Success State Inputs**: `border-green-300 dark:border-green-600 focus:border-green-500 focus:ring-green-500`
- **Error Messages**: Display immediately below field with consistent styling and icons
- **Inline Validation**: Validate on blur for better UX, not on every keystroke
- **Form-level Errors**: Display at top of form in error alert component

#### Specific Input Types
- **Text Inputs**: Standard base styling with appropriate `type` attributes
- **Textareas**: Use `resize-vertical` for better control, minimum 3 rows
- **Select Dropdowns**: Consistent arrow styling and dropdown container design
- **Checkboxes**: `rounded` style with blue accent color matching design system
- **Radio Buttons**: `rounded-full` with consistent spacing and group layout
- **File Uploads**: Custom styled with drag-and-drop areas where appropriate

#### Form Button Standards
- **Primary Submit**: Use `bg-blue-600 hover:bg-blue-700` positioned on right
- **Secondary Actions**: Use `bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-700 dark:hover:bg-zinc-600` 
- **Cancel/Back**: Use `variant="ghost"` for Flux buttons
- **Destructive Actions**: Use `bg-red-600 hover:bg-red-700` with confirmation dialogs
- **Button Spacing**: `space-x-3` between action buttons

#### Loading & Disabled States
- **Loading Inputs**: Show subtle spinner and disable interaction
- **Disabled Forms**: Apply `opacity-50` and `pointer-events-none` to entire form
- **Progressive Enhancement**: Forms must work without JavaScript
- **Loading Buttons**: Show spinner icon and "Processing..." text

#### Accessibility Requirements
- **Labels**: Every input must have an associated label (visible or aria-label)
- **Error Association**: Use `aria-describedby` to link errors to inputs
- **Focus Management**: Maintain logical tab order, focus first error on submission
- **Screen Reader Support**: Use proper ARIA attributes and live regions for dynamic content
- **Keyboard Navigation**: All interactive elements must be keyboard accessible

#### Form Width & Container Standards

**Application Layout Structure:**
```html
<!-- App layout (layout/app.blade.php) -->
<main class="py-8">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        {{ $slot }} <!-- Form content goes here -->
    </div>
</main>
```

**Form Container Standards:**
```html
<!-- Standard Form Pattern -->
<div class="w-full">
    <div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
        <!-- Form content -->
    </div>
</div>
```

**Form Width Guidelines:**
- **Management Forms**: Use `w-full` (utilizes full width within max-w-7xl app container)
- **Authentication Forms**: Use `max-w-md mx-auto` for centered, narrow presentation
- **Modal Forms**: Use appropriate width constraints within modal containers
- **Never use**: `max-w-2xl`, `max-w-4xl` on main form containers (redundant with app layout)

#### Responsive Design
- **Mobile First**: Design for mobile screens, enhance for larger viewports
- **Touch Targets**: Minimum 44px touch targets on mobile devices
- **Input Sizing**: Larger inputs on mobile for better usability
- **Form Layout**: Single column on mobile, multi-column on larger screens where appropriate
- **Grid Breakpoints**: Use `md:grid-cols-2` or `lg:grid-cols-3` for larger screens

**Navigation Standards:**
- Use consistent zinc-based colors for navigation backgrounds
- Implement proper hover states with zinc color transitions
- Ensure responsive navigation follows mobile-first approach

**Table Development Standards:**
- Use the modern `App\Tables\TableComponent` system for all new tables
- Create table components in the `app/Livewire/` directory structure
- Follow consistent header styling: `bg-zinc-50 dark:bg-zinc-800`
- Use consistent row hover states: `hover:bg-zinc-50 dark:hover:bg-zinc-700`
- Implement proper border colors: `border-zinc-200 dark:border-zinc-700`

**Card & Container Standards:**
- **Standard Card**: `bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700`
- **Card Headers**: Include proper border separation and consistent padding
- **Content Spacing**: Use consistent `p-6` for card content, `px-6 py-4` for headers

**Status Indicator Standards:**
- **Success**: `bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200`
- **Warning**: `bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200` 
- **Error**: `bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200`
- **Info**: `bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200`
- **Neutral**: `bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-200`

**Component Design Requirements:**
- All components must support both light and dark modes
- Use the defined shadow hierarchy: `shadow-sm` for cards, `shadow-lg` for modals
- Follow border radius standards: `rounded-md` for inputs/buttons, `rounded-lg` for cards
- Maintain consistent spacing using the 4px grid system
- Apply proper status indicators using the defined badge styles

**Flux UI Integration:**
- Prefer Flux UI components when available (buttons, inputs, dropdowns, etc.)
- Ensure Flux components follow the established color scheme
- Use Flux icons consistently throughout the application - Most Flux UI components have an icon prop to utilise
- Leverage Flux's dark mode support for seamless theme switching
- Use basic/free fluxui components. Design the premium/licensed in according to the design spec and create a reusable component if not available

### Migration Priorities

**High Priority Issues to Address:**
1. Convert legacy gray-based buttons to zinc-based system
2. Standardize form input border and background colors
3. Update table components to use modern TableComponent system
4. Ensure all cards follow standard styling patterns
5. Implement consistent status badge styling across all components
6. UI elements follow the design guidelines
7. For new features write tests

**Quality Assurance:**
- Test all UI changes in both light and dark modes
- Verify accessibility compliance with screen readers
- Ensure responsive behavior on mobile devices
- Validate color contrast ratios meet WCAG AA standards

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.15
- laravel/framework (LARAVEL) - v11
- laravel/horizon (HORIZON) - v5
- laravel/nightwatch (NIGHTWATCH) - v1
- laravel/prompts (PROMPTS) - v0
- laravel/pulse (PULSE) - v1
- laravel/reverb (REVERB) - v1
- livewire/flux (FLUXUI_FREE) - v2
- livewire/livewire (LIVEWIRE) - v3
- livewire/volt (VOLT) - v1
- laravel/breeze (BREEZE) - v2
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v3
- phpunit/phpunit (PHPUNIT) - v11
- tailwindcss (TAILWINDCSS) - v4
- laravel-echo (ECHO) - v1


## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure - don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.


=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs
- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms


=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.


=== herd rules ===

## Laravel Herd

- The application is served by Laravel Herd and will be available at: https?://[kebab-case-project-dir].test. Use the `get-absolute-url` tool to generate URLs for the user to ensure valid URLs.
- You must not run any commands to make the site available via HTTP(s). It is _always_ available through Laravel Herd.


=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] <name>` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.


=== laravel/v11 rules ===

## Laravel 11

- Use the `search-docs` tool to get version specific documentation.
- Laravel 11 brought a new streamlined file structure which this project now uses.

### Laravel 11 Structure
- No middleware files in `app/Http/Middleware/`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- **No app\Console\Kernel.php** - use `bootstrap/app.php` or `routes/console.php` for console configuration.
- **Commands auto-register** - files in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database
- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models
- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

### New Artisan Commands
- List Artisan commands using Boost's MCP tool, if available. New commands available in Laravel 11:
    - `php artisan make:enum`
    - `php artisan make:class`
    - `php artisan make:interface`


=== fluxui-free/core rules ===

## Flux UI Free

- This project is using the free edition of Flux UI. It has full access to the free components and variants, but does not have access to the Pro components.
- Flux UI is a component library for Livewire. Flux is a robust, hand-crafted, UI component library for your Livewire applications. It's built using Tailwind CSS and provides a set of components that are easy to use and customize.
- You should use Flux UI components when available.
- Fallback to standard Blade components if Flux is unavailable.
- If available, use Laravel Boost's `search-docs` tool to get the exact documentation and code snippets available for this project.
- Flux UI components look like this:

<code-snippet name="Flux UI Component Usage Example" lang="blade">
    <flux:button variant="primary"/>
</code-snippet>


### Available Components
This is correct as of Boost installation, but there may be additional components within the codebase.

<available-flux-components>
avatar, badge, brand, breadcrumbs, button, callout, checkbox, dropdown, field, heading, icon, input, modal, navbar, profile, radio, select, separator, switch, text, textarea, tooltip
</available-flux-components>


=== livewire/core rules ===

## Livewire Core
- Use the `search-docs` tool to find exact version specific documentation for how to write Livewire & Livewire tests.
- Use the `php artisan make:livewire [Posts\\CreatePost]` artisan command to create new components
- State should live on the server, with the UI reflecting it.
- All Livewire requests hit the Laravel backend, they're like regular HTTP requests. Always validate form data, and run authorization checks in Livewire actions.

## Livewire Best Practices
- Livewire components require a single root element.
- Use `wire:loading` and `wire:dirty` for delightful loading states.
- Add `wire:key` in loops:

    ```blade
    @foreach ($items as $item)
        <div wire:key="item-{{ $item->id }}">
            {{ $item->name }}
        </div>
    @endforeach
    ```

- Prefer lifecycle hooks like `mount()`, `updatedFoo()` for initialization and reactive side effects:

<code-snippet name="Lifecycle hook examples" lang="php">
    public function mount(User $user) { $this->user = $user; }
    public function updatedSearch() { $this->resetPage(); }
</code-snippet>


## Testing Livewire

<code-snippet name="Example Livewire component test" lang="php">
    Livewire::test(Counter::class)
        ->assertSet('count', 0)
        ->call('increment')
        ->assertSet('count', 1)
        ->assertSee(1)
        ->assertStatus(200);
</code-snippet>


    <code-snippet name="Testing a Livewire component exists within a page" lang="php">
        $this->get('/posts/create')
        ->assertSeeLivewire(CreatePost::class);
    </code-snippet>


=== livewire/v3 rules ===

## Livewire 3

### Key Changes From Livewire 2
- These things changed in Livewire 2, but may not have been updated in this application. Verify this application's setup to ensure you conform with application conventions.
    - Use `wire:model.live` for real-time updates, `wire:model` is now deferred by default.
    - Components now use the `App\Livewire` namespace (not `App\Http\Livewire`).
    - Use `$this->dispatch()` to dispatch events (not `emit` or `dispatchBrowserEvent`).
    - Use the `components.layouts.app` view as the typical layout path (not `layouts.app`).

### New Directives
- `wire:show`, `wire:transition`, `wire:cloak`, `wire:offline`, `wire:target` are available for use. Use the documentation to find usage examples.

### Alpine
- Alpine is now included with Livewire, don't manually include Alpine.js.
- Plugins included with Alpine: persist, intersect, collapse, and focus.

### Lifecycle Hooks
- You can listen for `livewire:init` to hook into Livewire initialization, and `fail.status === 419` for the page expiring:

<code-snippet name="livewire:load example" lang="js">
document.addEventListener('livewire:init', function () {
    Livewire.hook('request', ({ fail }) => {
        if (fail && fail.status === 419) {
            alert('Your session expired');
        }
    });

    Livewire.hook('message.failed', (message, component) => {
        console.error(message);
    });
});
</code-snippet>


=== volt/core rules ===

## Livewire Volt

- This project uses Livewire Volt for interactivity within its pages. New pages requiring interactivity must also use Livewire Volt. There is documentation available for it.
- Make new Volt components using `php artisan make:volt [name] [--test] [--pest]`
- Volt is a **class-based** and **functional** API for Livewire that supports single-file components, allowing a component's PHP logic and Blade templates to co-exist in the same file
- Livewire Volt allows PHP logic and Blade templates in one file. Components use the `@livewire("volt-anonymous-fragment-eyJuYW1lIjoidm9sdC1hbm9ueW1vdXMtZnJhZ21lbnQtYmQ5YWJiNTE3YWMyMTgwOTA1ZmUxMzAxODk0MGJiZmIiLCJwYXRoIjoic3RvcmFnZVwvZnJhbWV3b3JrXC92aWV3c1wvMTUxYWRjZWRjMzBhMzllOWIxNzQ0ZDRiMWRjY2FjYWIuYmxhZGUucGhwIn0=", Livewire\Volt\Precompilers\ExtractFragments::componentArguments([...get_defined_vars(), ...array (
)]))
</code-snippet>


### Volt Class Based Component Example
To get started, define an anonymous class that extends Livewire\Volt\Component. Within the class, you may utilize all of the features of Livewire using traditional Livewire syntax:


<code-snippet name="Volt Class-based Volt Component Example" lang="php">
use Livewire\Volt\Component;

new class extends Component {
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }
} ?>

<div>
    <h1>{{ $count }}</h1>
    <button wire:click="increment">+</button>
</div>
</code-snippet>


### Testing Volt & Volt Components
- Use the existing directory for tests if it already exists. Otherwise, fallback to `tests/Feature/Volt`.

<code-snippet name="Livewire Test Example" lang="php">
use Livewire\Volt\Volt;

test('counter increments', function () {
    Volt::test('counter')
        ->assertSee('Count: 0')
        ->call('increment')
        ->assertSee('Count: 1');
});
</code-snippet>


<code-snippet name="Volt Component Test Using Pest" lang="php">
declare(strict_types=1);

use App\Models\{User, Product};
use Livewire\Volt\Volt;

test('product form creates product', function () {
    $user = User::factory()->create();

    Volt::test('pages.products.create')
        ->actingAs($user)
        ->set('form.name', 'Test Product')
        ->set('form.description', 'Test Description')
        ->set('form.price', 99.99)
        ->call('create')
        ->assertHasNoErrors();

    expect(Product::where('name', 'Test Product')->exists())->toBeTrue();
});
</code-snippet>


### Common Patterns


<code-snippet name="CRUD With Volt" lang="php">
<?php

use App\Models\Product;
use function Livewire\Volt\{state, computed};

state(['editing' => null, 'search' => '']);

$products = computed(fn() => Product::when($this->search,
    fn($q) => $q->where('name', 'like', "%{$this->search}%")
)->get());

$edit = fn(Product $product) => $this->editing = $product->id;
$delete = fn(Product $product) => $product->delete();

?>

<!-- HTML / UI Here -->
</code-snippet>

<code-snippet name="Real-Time Search With Volt" lang="php">
    <flux:input
        wire:model.live.debounce.300ms="search"
        placeholder="Search..."
    />
</code-snippet>

<code-snippet name="Loading States With Volt" lang="php">
    <flux:button wire:click="save" wire:loading.attr="disabled">
        <span wire:loading.remove>Save</span>
        <span wire:loading>Saving...</span>
    </flux:button>
</code-snippet>


=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.


=== pest/core rules ===

## Pest

### Testing
- If you need to verify a feature is working, write or update a Unit / Feature test.

### Pest Tests
- All tests must be written using Pest. Use `php artisan make:test --pest <name>`.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files - these are core to the application.
- Tests should test all of the happy paths, failure paths, and weird paths.
- Tests live in the `tests/Feature` and `tests/Unit` directories.
- Pest tests look and behave like this:
<code-snippet name="Basic Pest Test Example" lang="php">
it('is true', function () {
    expect(true)->toBeTrue();
});
</code-snippet>

### Running Tests
- Run the minimal number of tests using an appropriate filter before finalizing code edits.
- To run all tests: `php artisan test`.
- To run all tests in a file: `php artisan test tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --filter=testName` (recommended after making a change to a related file).
- When the tests relating to your changes are passing, ask the user if they would like to run the entire test suite to ensure everything is still passing.

### Pest Assertions
- When asserting status codes on a response, use the specific method like `assertForbidden` and `assertNotFound` instead of using `assertStatus(403)` or similar, e.g.:
<code-snippet name="Pest Example Asserting postJson Response" lang="php">
it('returns all', function () {
    $response = $this->postJson('/api/docs', []);

    $response->assertSuccessful();
});
</code-snippet>

### Mocking
- Mocking can be very helpful when appropriate.
- When mocking, you can use the `Pest\Laravel\mock` Pest function, but always import it via `use function Pest\Laravel\mock;` before using it. Alternatively, you can use `$this->mock()` if existing tests do.
- You can also create partial mocks using the same import or self method.

### Datasets
- Use datasets in Pest to simplify tests which have a lot of duplicated data. This is often the case when testing validation rules, so consider going with this solution when writing tests for validation rules.

<code-snippet name="Pest Dataset Example" lang="php">
it('has emails', function (string $email) {
    expect($email)->not->toBeEmpty();
})->with([
    'james' => 'james@laravel.com',
    'taylor' => 'taylor@laravel.com',
]);
</code-snippet>


=== tailwindcss/core rules ===

## Tailwind Core

- Use Tailwind CSS classes to style HTML, check and use existing tailwind conventions within the project before writing your own.
- Offer to extract repeated patterns into components that match the project's conventions (i.e. Blade, JSX, Vue, etc..)
- Think through class placement, order, priority, and defaults - remove redundant classes, add classes to parent or child carefully to limit repetition, group elements logically
- You can use the `search-docs` tool to get exact examples from the official documentation when needed.

### Spacing
- When listing items, use gap utilities for spacing, don't use margins.

    <code-snippet name="Valid Flex Gap Spacing Example" lang="html">
        <div class="flex gap-8">
            <div>Superior</div>
            <div>Michigan</div>
            <div>Erie</div>
        </div>
    </code-snippet>


### Dark Mode
- If existing pages and components support dark mode, new pages and components must support dark mode in a similar way, typically using `dark:`.


=== tailwindcss/v4 rules ===

## Tailwind 4

- Always use Tailwind CSS v4 - do not use the deprecated utilities.
- `corePlugins` is not supported in Tailwind v4.
- In Tailwind v4, you import Tailwind using a regular CSS `@import` statement, not using the `@tailwind` directives used in v3:

<code-snippet name="Tailwind v4 Import Tailwind Diff" lang="diff">
   - @tailwind base;
   - @tailwind components;
   - @tailwind utilities;
   + @import "tailwindcss";
</code-snippet>


### Replaced Utilities
- Tailwind v4 removed deprecated utilities. Do not use the deprecated option - use the replacement.
- Opacity values are still numeric.

| Deprecated |	Replacement |
|------------+--------------|
| bg-opacity-* | bg-black/* |
| text-opacity-* | text-black/* |
| border-opacity-* | border-black/* |
| divide-opacity-* | divide-black/* |
| ring-opacity-* | ring-black/* |
| placeholder-opacity-* | placeholder-black/* |
| flex-shrink-* | shrink-* |
| flex-grow-* | grow-* |
| overflow-ellipsis | text-ellipsis |
| decoration-slice | box-decoration-slice |
| decoration-clone | box-decoration-clone |


=== tests rules ===

## Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test` with a specific filename or filter.
</laravel-boost-guidelines>