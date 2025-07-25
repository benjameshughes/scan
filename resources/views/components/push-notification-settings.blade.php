@props([
    'user' => auth()->user()
])

<div class="bg-white dark:bg-zinc-800 shadow-sm rounded-lg border border-zinc-200 dark:border-zinc-700">
    <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Push Notifications</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Configure how you receive real-time notifications
        </p>
    </div>
    
    <div class="p-6 space-y-4">
        <!-- Browser Permission Status -->
        <div id="push-permission-status" class="hidden">
            <div class="flex items-center space-x-3">
                <div id="permission-indicator" class="w-3 h-3 rounded-full"></div>
                <div>
                    <p id="permission-text" class="text-sm font-medium text-gray-900 dark:text-gray-100"></p>
                    <p id="permission-description" class="text-xs text-gray-500 dark:text-gray-400"></p>
                </div>
            </div>
        </div>

        <!-- Enable Push Notifications Toggle -->
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <span class="text-2xl">🔔</span>
                <div>
                    <label for="notification_push" class="text-sm font-medium text-gray-700 dark:text-gray-200">
                        Push Notifications
                    </label>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Receive instant notifications for important events
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <button id="enable-push-btn" class="hidden px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">
                    Enable
                </button>
                <input 
                    type="checkbox" 
                    id="notification_push" 
                    name="settings[notification_push]"
                    {{ ($user->settings['notification_push'] ?? true) ? 'checked' : '' }}
                    class="rounded border-zinc-300 dark:border-zinc-600 text-blue-600 focus:ring-blue-500"
                    wire:model.live="settings.notification_push"
                >
            </div>
        </div>

        <!-- Notification Types -->
        <div class="space-y-3 pl-6 border-l-2 border-zinc-100 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <span class="text-xl">📦</span>
                    <div>
                        <span class="text-sm text-gray-700 dark:text-gray-200">Empty Bay Alerts</span>
                        <p class="text-xs text-gray-500 dark:text-gray-400">High priority notifications when bays are empty</p>
                    </div>
                </div>
                <span class="text-xs px-2 py-1 bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 rounded-full">
                    High Priority
                </span>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <span class="text-xl">⚠️</span>
                    <div>
                        <span class="text-sm text-gray-700 dark:text-gray-200">Sync Failures</span>
                        <p class="text-xs text-gray-500 dark:text-gray-400">When scans or refills fail to sync</p>
                    </div>
                </div>
                <span class="text-xs px-2 py-1 bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200 rounded-full">
                    Medium Priority
                </span>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <span class="text-xl">📧</span>
                    <div>
                        <span class="text-sm text-gray-700 dark:text-gray-200">Invitations</span>
                        <p class="text-xs text-gray-500 dark:text-gray-400">User invitation notifications</p>
                    </div>
                </div>
                <span class="text-xs px-2 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded-full">
                    Low Priority
                </span>
            </div>
        </div>

        <!-- Test Notification Button -->
        <div class="pt-4 border-t border-zinc-200 dark:border-zinc-700">
            <button 
                id="test-notification-btn"
                type="button"
                class="w-full px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100 dark:bg-blue-900 dark:text-blue-400 dark:border-blue-700 dark:hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <span class="flex items-center justify-center space-x-2">
                    <span>🧪</span>
                    <span>Send Test Notification</span>
                </span>
            </button>
        </div>
    </div>
</div>

<!-- Add user ID to meta for JavaScript -->
<meta name="user-id" content="{{ auth()->id() }}">

<script>
document.addEventListener('DOMContentLoaded', function() {
    const permissionStatus = document.getElementById('push-permission-status');
    const permissionIndicator = document.getElementById('permission-indicator');
    const permissionText = document.getElementById('permission-text');
    const permissionDescription = document.getElementById('permission-description');
    const enablePushBtn = document.getElementById('enable-push-btn');
    const notificationToggle = document.getElementById('notification_push');
    const testNotificationBtn = document.getElementById('test-notification-btn');

    function updatePermissionStatus() {
        if (!('Notification' in window)) {
            permissionStatus.classList.remove('hidden');
            permissionIndicator.className = 'w-3 h-3 rounded-full bg-red-500';
            permissionText.textContent = 'Not Supported';
            permissionDescription.textContent = 'Push notifications are not supported in this browser';
            notificationToggle.disabled = true;
            return;
        }

        permissionStatus.classList.remove('hidden');
        
        switch (Notification.permission) {
            case 'granted':
                permissionIndicator.className = 'w-3 h-3 rounded-full bg-green-500';
                permissionText.textContent = 'Enabled';
                permissionDescription.textContent = 'Browser notifications are allowed';
                enablePushBtn.classList.add('hidden');
                break;
            case 'denied':
                permissionIndicator.className = 'w-3 h-3 rounded-full bg-red-500';
                permissionText.textContent = 'Blocked';
                permissionDescription.textContent = 'Please enable notifications in your browser settings';
                enablePushBtn.classList.add('hidden');
                notificationToggle.disabled = true;
                break;
            case 'default':
                permissionIndicator.className = 'w-3 h-3 rounded-full bg-amber-500';
                permissionText.textContent = 'Permission Required';
                permissionDescription.textContent = 'Click enable to allow browser notifications';
                enablePushBtn.classList.remove('hidden');
                break;
        }
    }

    // Enable push notifications button
    enablePushBtn.addEventListener('click', async function() {
        if (window.pushNotificationManager) {
            const granted = await window.pushNotificationManager.enableNotifications();
            updatePermissionStatus();
            if (granted) {
                notificationToggle.checked = true;
                // Trigger Livewire update if available
                if (window.Livewire) {
                    window.Livewire.dispatch('updateSetting', { key: 'notification_push', value: true });
                }
            }
        }
    });

    // Test notification button
    testNotificationBtn.addEventListener('click', function() {
        if (Notification.permission === 'granted') {
            const testNotification = {
                title: 'Test Notification',
                message: 'Push notifications are working correctly! 🎉',
                type: 'test',
                severity: 'low',
                icon: '🧪',
                timestamp: new Date().toISOString()
            };

            if (window.pushNotificationManager) {
                window.pushNotificationManager.handleNotification(testNotification);
            } else {
                // Fallback to basic browser notification
                new Notification(testNotification.title, {
                    body: testNotification.message,
                    icon: '/icons/icon-192.png'
                });
            }
        } else {
            alert('Please enable push notifications first');
        }
    });

    // Initialize permission status
    updatePermissionStatus();

    // Listen for permission changes
    if ('permissions' in navigator) {
        navigator.permissions.query({ name: 'notifications' }).then(function(result) {
            result.onchange = updatePermissionStatus;
        });
    }
});
</script>