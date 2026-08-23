# Stock Scanner

Scan stuff in. Scan stuff out. Move stuff between locations. That's it.

Built this because I kept forgetting to update stock levels manually and got sick of walking to a computer every time. Phone camera, a few taps, done. Linnworks stays in sync without me thinking about it.

30,000+ scans in the first year. Works on shit Android phones. No Zebra scanners needed.

## What it does

- **Scan** - phone camera or type it in. Barcodes, SKUs, whatever. Increase or decrease stock, hit submit, Linnworks updates in the background
- **Bay refill** - scan a product, pick a location, enter quantity. Bay refilled, Linnworks knows about it
- **Stock moves** - move stock from one location to another in a few taps instead of walking to a desktop and doing it manually
- **Products** - syncs from Linnworks daily. Supports multiple barcodes per product. CSV import if you need it
- **Locations** - manage your warehouse locations. Get notified when a bay is empty

Everything syncs to Linnworks via background jobs. If the API is slow or down, scans queue up and process when it's back. Nothing gets lost.

## Stack

Laravel, Livewire, Tailwind, SQLite. ZXing for the camera scanning. Reverb for real-time updates. Horizon for queue monitoring. Spatie for permissions.

## Setup

```bash
composer install && npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
composer dev
```

Needs HTTPS for camera access. Stick your Linnworks API credentials in `.env`.

## Linnworks config

```env
LINNWORKS_APP_ID=your_app_id
LINNWORKS_TOKEN=your_token
LINNWORKS_SECRET=your_secret
LINNWORKS_SERVER=your_server_url
```

## Users

Invite-only. Domain-restricted registration. Role-based permissions via Spatie. No public signup.

```env
ALLOWED_DOMAINS=yourdomain.com
```

## Tests

```bash
./vendor/bin/pest
```

239 tests. They pass. If they don't, something's broken and it's probably your fault.

## Production

```bash
composer install --optimize-autoloader --no-dev
npm run build
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan migrate --force
php artisan horizon
```

## Version bumps

```bash
php artisan version:bump-improved patch|minor|major
php artisan version:bump-improved patch --dry  # preview
```

## License

Do what you want with it. Self-host it, break it, fix it, improve it. If you make money from it, buy me a mocha.
