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
- Forms are always full width
- Container class is max-w-7xl with no y padding

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

- Feature tests for Livewire components
- Unit tests for actions and services
- Use Pest's Laravel helpers for cleaner tests
- Database refreshed between tests automatically
- For new features create a corresponding test

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
- **Container**: Use cards with consistent padding: `bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700`
- **Form Header**: Include descriptive title and optional subtitle in header section with border separation
- **Field Spacing**: Use `space-y-4` for field groups, `space-y-6` for major form sections
- **Field Groups**: Wrap related fields in logical groupings with subtle visual separation
- **Form Actions**: Place primary actions on the right, secondary on the left with consistent spacing

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

#### Responsive Design
- **Mobile First**: Design for mobile screens, enhance for larger viewports
- **Touch Targets**: Minimum 44px touch targets on mobile devices
- **Input Sizing**: Larger inputs on mobile for better usability
- **Form Layout**: Single column on mobile, multi-column on larger screens where appropriate

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