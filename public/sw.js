// Service Worker for Stock Scanner PWA - 2024 Best Practices
const CACHE_VERSION = 'v4';
const STATIC_CACHE = `stock-scanner-static-${CACHE_VERSION}`;
const DYNAMIC_CACHE = `stock-scanner-dynamic-${CACHE_VERSION}`;
const OFFLINE_CACHE = `stock-scanner-offline-${CACHE_VERSION}`;

// Static assets to precache
const PRECACHE_ASSETS = [
  '/manifest.json',
  '/icons/icon-192.png?v=4',
  '/icons/icon-512.png?v=4',
  '/icons/icon-maskable-192.png?v=1',
  '/icons/icon-maskable-512.png?v=1'
];

// Laravel/Livewire specific exclusions - NEVER cache these
const DYNAMIC_EXCLUSIONS = [
  // Livewire requests
  /\/livewire\/message/,
  /\/livewire\/upload-file/,
  /\/livewire\/preview-file/,
  
  // Authentication & CSRF
  /csrf-token/,
  /sanctum\/csrf-cookie/,
  /\/login/,
  /\/logout/,
  /\/register/,
  
  // User-specific dynamic content
  /\/profile/,
  /\/dashboard/,
  /\/api\/user/,
  
  // Admin and sensitive areas
  /\/admin/,
  /\/telescope/,
  /\/_ignition/,
  
  // Dynamic query parameters
  /\?.*_token=/,
  /\?.*timestamp=/,
  /\?.*nonce=/,
  /\?.*wire:/
];

// Check if request should never be cached
const isNeverCache = (request) => {
  const url = request.url;
  
  // Don't cache non-GET requests
  if (request.method !== 'GET') return true;
  
  // Don't cache requests with authentication headers
  if (request.headers.get('Authorization') || 
      request.headers.get('X-Livewire') ||
      request.headers.get('X-CSRF-TOKEN')) return true;
  
  // Don't cache dynamic exclusions
  return DYNAMIC_EXCLUSIONS.some(pattern => pattern.test(url));
};

// Check if request is for static assets
const isStaticAsset = (request) => {
  return request.destination === 'image' ||
         request.destination === 'style' ||
         request.destination === 'script' ||
         request.destination === 'font' ||
         request.url.match(/\.(css|js|png|jpg|jpeg|gif|svg|woff|woff2|ico)(\?.*)?$/);
};

// Install event - cache essential static files only
self.addEventListener('install', event => {
  console.log('SW: Installing...');
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then(cache => {
        console.log('SW: Precaching static assets');
        return cache.addAll(PRECACHE_ASSETS);
      })
      .then(() => {
        console.log('SW: Install complete');
        self.skipWaiting();
      })
      .catch(error => {
        console.error('SW: Install failed:', error);
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
  console.log('SW: Activating...');
  event.waitUntil(
    caches.keys()
      .then(cacheNames => {
        return Promise.all(
          cacheNames.map(cacheName => {
            if (cacheName.startsWith('stock-scanner-') && 
                !cacheName.includes(CACHE_VERSION)) {
              console.log('SW: Deleting old cache:', cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      })
      .then(() => {
        console.log('SW: Activation complete');
        self.clients.claim();
      })
  );
});

// Fetch event - selective caching strategy
self.addEventListener('fetch', event => {
  const { request } = event;
  
  // Never cache dynamic/sensitive content
  if (isNeverCache(request)) {
    console.log('SW: Bypassing cache for:', request.url);
    event.respondWith(
      fetch(request).catch(() => {
        // For failed navigation requests, show offline page
        if (request.destination === 'document') {
          return new Response(`
            <!DOCTYPE html>
            <html>
            <head>
              <title>Offline - Stock Scanner</title>
              <meta name="viewport" content="width=device-width, initial-scale=1">
              <style>
                body { font-family: sans-serif; text-align: center; padding: 50px; background: #f5f5f5; }
                .offline { background: white; padding: 40px; border-radius: 10px; max-width: 400px; margin: 0 auto; }
                .icon { font-size: 64px; margin-bottom: 20px; }
              </style>
            </head>
            <body>
              <div class="offline">
                <div class="icon">📱</div>
                <h1>You're Offline</h1>
                <p>Please check your internet connection and try again.</p>
                <button onclick="location.reload()">Try Again</button>
              </div>
            </body>
            </html>
          `, { 
            status: 503, 
            headers: { 'Content-Type': 'text/html' } 
          });
        }
        return new Response('Offline', { status: 503 });
      })
    );
    return;
  }
  
  // Cache-First strategy for static assets
  if (isStaticAsset(request)) {
    event.respondWith(handleStaticAsset(request));
    return;
  }
  
  // Network-First for everything else (app shell, etc.)
  event.respondWith(handleDynamicContent(request));
});

// Handle static assets with cache-first strategy
const handleStaticAsset = async (request) => {
  try {
    const cache = await caches.open(STATIC_CACHE);
    const cached = await cache.match(request);
    
    if (cached) {
      console.log('SW: Serving from cache:', request.url);
      return cached;
    }
    
    console.log('SW: Fetching static asset:', request.url);
    const response = await fetch(request);
    
    if (response.ok) {
      const responseClone = response.clone();
      cache.put(request, responseClone);
    }
    
    return response;
  } catch (error) {
    console.error('SW: Static asset failed:', error);
    const cached = await caches.match(request);
    return cached || new Response('Asset not available offline', { status: 503 });
  }
};

// Handle dynamic content with network-first strategy
const handleDynamicContent = async (request) => {
  try {
    console.log('SW: Network-first for:', request.url);
    const response = await fetch(request);
    
    // Only cache successful navigation responses briefly for offline fallback
    if (response.ok && request.destination === 'document') {
      const cache = await caches.open(DYNAMIC_CACHE);
      const responseClone = response.clone();
      
      // Cache with short expiration
      cache.put(request, responseClone);
      
      // Clean up old dynamic cache entries
      setTimeout(async () => {
        const keys = await cache.keys();
        if (keys.length > 10) {
          await cache.delete(keys[0]);
        }
      }, 100);
    }
    
    return response;
  } catch (error) {
    console.log('SW: Network failed, trying cache:', request.url);
    
    // Try cache as fallback
    const cached = await caches.match(request);
    if (cached) {
      return cached;
    }
    
    // Return offline page for navigation requests
    if (request.destination === 'document') {
      return new Response(`
        <!DOCTYPE html>
        <html>
        <head>
          <title>Offline - Stock Scanner</title>
          <meta name="viewport" content="width=device-width, initial-scale=1">
          <style>
            body { 
              font-family: -apple-system, BlinkMacSystemFont, sans-serif; 
              text-align: center; 
              padding: 50px 20px; 
              background: #f8fafc;
              color: #1f2937;
            }
            .offline { 
              background: white; 
              padding: 40px; 
              border-radius: 12px; 
              max-width: 400px; 
              margin: 0 auto; 
              box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            }
            .icon { font-size: 64px; margin-bottom: 20px; }
            button {
              background: #2563eb;
              color: white;
              border: none;
              padding: 12px 24px;
              border-radius: 6px;
              font-size: 16px;
              cursor: pointer;
              margin-top: 20px;
            }
            button:hover { background: #1d4ed8; }
          </style>
        </head>
        <body>
          <div class="offline">
            <div class="icon">📱</div>
            <h1>You're Offline</h1>
            <p>Stock Scanner needs an internet connection to load new content.</p>
            <button onclick="location.reload()">Try Again</button>
          </div>
        </body>
        </html>
      `, { 
        status: 503, 
        headers: { 'Content-Type': 'text/html' } 
      });
    }
    
    return new Response('Content not available offline', { status: 503 });
  }
};

// Basic PWA functionality only - push notifications removed

// Handle service worker messages
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});