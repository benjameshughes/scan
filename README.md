# 📦 Laravel Stock Scanner

**Version: 2.0.0**

A modern, full-featured Laravel application for warehouse stock management with real-time barcode scanning, inventory tracking, and Linnworks integration.

---

## 🚀 Features

### 📱 **Barcode Scanning**
- **Camera-based scanning** using ZXing library with automatic barcode detection
- **Manual barcode entry** with validation and product lookup
- **Mobile-optimized interface** with torch/flashlight control on supported devices
- **Continuous scanning mode** with vibration feedback and audio alerts
- **Auto-submit functionality** for streamlined warehouse operations
- **Auto-accept mode** for rapid stock updates with full audit trail

### 📊 **Inventory Management**
- **Real-time stock level tracking** with increase/decrease operations
- **Product management** with multiple barcode support (primary + 2 additional)
- **Scan history** with comprehensive audit trails and filtering
- **Empty bay notifications** to alert administrators when restocking is needed
- **Bulk operations** for efficient inventory management
- **Stock history viewer** with detailed movement tracking from Linnworks
- **Manual full sync** capability for administrators to refresh all product data
- **Real-time progress tracking** during sync operations with accurate inventory counts

### 🔗 **Linnworks Integration**
- **Automated stock synchronization** with Linnworks warehouse management system
- **Background job processing** for reliable API communication
- **Token-based authentication** with automatic refresh handling
- **Batch scan submissions** to optimize API usage
- **Product data synchronization** including SKUs and barcodes
- **Configurable API constants** for optimized performance and page sizes
- **Stock history API** integration for viewing detailed movement records
- **Inventory count API** for accurate progress tracking
- **Update Details button** for instant product information refresh

### 👥 **User Management**
- **Role-based access control** using Spatie Laravel Permission
- **Fine-grained permission system** with individual user controls
- **Unified user interface** combining user and invitation management
- **Email invitation system** with secure token-based registration
- **Admin permission inheritance** with visual feedback
- **User settings** for notifications and scanning preferences

### 🎨 **Modern UI/UX**
- **Dark mode support** with seamless theme switching
- **Responsive design** optimized for mobile and desktop
- **Flux UI components** for consistent, professional interface
- **Real-time updates** using Livewire for dynamic interactions
- **Modern table system** with auto-discovery, search, and filtering
- **Professional design language** suitable for enterprise environments
- **ATM-style UI effects** for visual feedback during operations
- **Skeleton loading states** for improved perceived performance
- **Modern modal system** using Flux UI components with proper z-index management

---

## 🛠️ Technology Stack

### **Backend**
- **Laravel 11.x** with PHP 8.4
- **SQLite** database (configurable for other databases)
- **Laravel Reverb** for WebSocket functionality
- **Laravel Horizon** for queue monitoring
- **Spatie Laravel Permission** for role management

### **Frontend**
- **Livewire 3.x** for reactive components
- **Flux 2.x** UI component library
- **Tailwind CSS 4.x** for styling
- **@zxing/library** for barcode scanning
- **Alpine.js** for enhanced interactions

### **Infrastructure**
- **Queue system** with database driver (Horizon ready)
- **Background job processing** for external API calls
- **Email system** with queued notifications
- **WebSocket support** for real-time features

---

## 📋 Requirements

- **PHP 8.4+**
- **Composer**
- **Node.js & NPM**
- **SQLite** (or preferred database)
- **HTTPS connection** (required for camera access)

---

## ⚡ Quick Start

### 1. **Clone & Setup**
```bash
git clone <repository-url>
cd stock-scan
composer install
npm install
```

### 2. **Environment Configuration**
```bash
cp .env.example .env
php artisan key:generate
```

### 3. **Database Setup**
```bash
php artisan migrate
php artisan db:seed
```

### 4. **Start Development**
```bash
# Standard development (recommended)
composer dev

# Or full development with queue monitoring
composer horizon
```

### 5. **Build Assets**
```bash
npm run dev  # Development with hot reload
npm run build  # Production build
```

---

## 🔧 Configuration

### **Linnworks Integration**
Configure your Linnworks API credentials in `.env`:
```env
LINNWORKS_APP_ID=your_app_id
LINNWORKS_TOKEN=your_token
LINNWORKS_SECRET=your_secret
LINNWORKS_SERVER=your_server_url
```

### **Email Configuration**
Set up email delivery for invitations and notifications:
```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_FROM_ADDRESS=noreply@yourdomain.com
```

### **Domain Restrictions**
Restrict user registration to specific domains:
```env
ALLOWED_DOMAINS=yourdomain.com,anotherdomain.com
```

---

## 📖 Usage Guide

### **For Warehouse Staff**
1. **Access Scanner**: Navigate to the scanner interface
2. **Scan Products**: Use camera or manual entry to scan barcodes
3. **Adjust Quantities**: Increase or decrease stock levels
4. **Submit Changes**: Confirm updates for Linnworks synchronization
5. **Report Empty Bays**: Alert administrators when restocking is needed

### **For Administrators**
1. **User Management**: Create users, assign roles, and manage permissions
2. **Invitation System**: Send secure email invitations for account setup
3. **Permission Control**: Grant fine-grained access to system features
4. **Monitor Activity**: Review scan history and system notifications
5. **System Configuration**: Manage Linnworks integration and settings

---

## 🎯 Key Workflows

### **Product Scanning Flow**
```
Scanner Access → Barcode Detection → Product Lookup → Quantity Adjustment → Linnworks Sync
```

### **User Onboarding Flow**
```
Admin Creates User → Email Invitation → User Registration → Permission Assignment → System Access
```

### **Empty Bay Notification Flow**
```
Staff Reports Empty Bay → Notification Queue → Admin Email Alert → Restocking Action
```

---

## 🧪 Testing

### **Run Tests**
```bash
./vendor/bin/pest                    # All tests
./vendor/bin/pest --coverage         # With coverage
./vendor/bin/pest tests/Feature/     # Feature tests only
```

### **Code Quality**
```bash
./vendor/bin/pint                    # Fix code style
./vendor/bin/pint --test             # Check style without fixing
```

---

## 🚀 Deployment

### **Production Setup**
```bash
# Install dependencies
composer install --optimize-autoloader --no-dev

# Build assets
npm run build

# Configure environment
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Start queue workers
php artisan horizon
```

### **Version Management**
```bash
# Interactive version bump with safety checks
php artisan version:bump-improved

# Bump specific type
php artisan version:bump-improved patch|minor|major

# Preview changes (dry run)
php artisan version:bump-improved patch --dry
```

---

## 🔒 Security Features

- **Invitation-based registration** prevents unauthorized access
- **Domain restrictions** for user registration
- **Role-based permissions** with granular control
- **Secure token generation** for invitations and API access
- **HTTPS enforcement** for camera and sensitive operations
- **Input validation** and SQL injection protection

---

## 🎨 Design System

The application follows a comprehensive design language system with:

- **Professional color palette** with dark mode support
- **Consistent typography** and spacing scales
- **Accessible interfaces** meeting WCAG AA standards
- **Mobile-first responsive design**
- **Modern component architecture** using Flux UI

---

## 📊 Performance Features

- **Background job processing** for external API calls
- **Queue system** with monitoring and failure handling
- **Optimized database queries** with eager loading
- **Asset bundling** and caching strategies
- **Real-time updates** without page refreshes

---

## 🤝 Contributing

This is a private warehouse management system. For support or feature requests, please contact the development team.

---

## 📄 License

This project is proprietary software. All rights reserved.

---

## 📞 Support

For technical support or questions:
- **Documentation**: Check `/CLAUDE.md` for development guidelines
- **Issues**: Contact system administrator
- **Feature Requests**: Submit through appropriate channels

---

## 🙏 Credits & Acknowledgments

This project is built on the shoulders of giants. We gratefully acknowledge the following open source projects and their contributors:

### **Core Framework & Libraries**
- **[Laravel](https://laravel.com/)** - The PHP framework for web artisans (Taylor Otwell & contributors)
- **[Livewire](https://livewire.laravel.com/)** - A full-stack framework for Laravel (Caleb Porzio & contributors)
- **[PHP](https://www.php.net/)** - Server-side scripting language
- **[Composer](https://getcomposer.org/)** - Dependency manager for PHP

### **Frontend Technologies**
- **[Flux UI](https://fluxui.dev/)** - Modern Laravel UI components
- **[Tailwind CSS](https://tailwindcss.com/)** - Utility-first CSS framework (Adam Wathan & contributors)
- **[Alpine.js](https://alpinejs.dev/)** - Lightweight JavaScript framework (Caleb Porzio)
- **[ZXing Library](https://github.com/zxing-js/library)** - Barcode scanning JavaScript library
- **[Vite](https://vitejs.dev/)** - Frontend build tool and development server

### **Laravel Ecosystem**
- **[Laravel Reverb](https://reverb.laravel.com/)** - WebSocket server for Laravel
- **[Laravel Horizon](https://laravel.com/docs/horizon)** - Queue monitoring dashboard
- **[Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)** - Role and permission management
- **[Laravel Pint](https://laravel.com/docs/pint)** - Code style fixer
- **[Pest PHP](https://pestphp.com/)** - Testing framework (Nuno Maduro & contributors)

### **Development Tools**
- **[Node.js](https://nodejs.org/)** - JavaScript runtime environment
- **[NPM](https://www.npmjs.com/)** - Package manager for JavaScript
- **[SQLite](https://www.sqlite.org/)** - Lightweight database engine
- **[Git](https://git-scm.com/)** - Version control system

### **Security & Infrastructure**
- **[OpenSSL](https://www.openssl.org/)** - Cryptography and SSL/TLS toolkit
- **[Let's Encrypt](https://letsencrypt.org/)** - Free SSL/TLS certificates (when applicable)
- **[Symfony Components](https://symfony.com/)** - PHP framework components used by Laravel

### **Email & Communication**
- **[SwiftMailer](https://swiftmailer.symfony.com/)** / **[Symfony Mailer](https://symfony.com/doc/current/mailer.html)** - Email sending library
- **[Markdown](https://daringfireball.net/projects/markdown/)** - Text formatting syntax (John Gruber)

### **Development Experience**
- **[PHPStan](https://phpstan.org/)** - Static analysis tool for PHP
- **[PHP CS Fixer](https://cs.symfony.com/)** - Code standards fixer
- **[Faker](https://fakerphp.github.io/)** - Data generation library
- **[Carbon](https://carbon.nesbot.com/)** - Date manipulation library (Brian Nesbitt)

### **Browser Technologies**
- **[WebRTC](https://webrtc.org/)** - Real-time communication for camera access
- **[Progressive Web App](https://web.dev/progressive-web-apps/)** standards
- **[Web APIs](https://developer.mozilla.org/en-US/docs/Web/API)** - Modern browser capabilities

### **Special Thanks**
- **The PHP Community** - For continuous innovation and support
- **The Laravel Community** - For excellent documentation and ecosystem
- **Open Source Contributors** - Who make projects like this possible
- **Spatie** - For excellent Laravel packages and developer tools
- **All Package Maintainers** - Who keep the ecosystem secure and up-to-date

---

## 📜 License Compliance

This project uses open source software under various licenses including MIT, Apache 2.0, and BSD licenses. We respect and comply with all license requirements. For specific license information, please refer to individual package documentation and the `composer.json` file.

---

**Built with ❤️ using Laravel, Livewire, and the incredible open source ecosystem.**