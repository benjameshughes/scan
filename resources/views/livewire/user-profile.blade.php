<div class="w-full space-y-6" 
     @theme-color-changed.window="$store.theme.set($event.detail.color)">
    
    <!-- Profile Information Component -->
    <livewire:profile.profile-information-form />

    <!-- Change Password Component -->
    <livewire:profile.change-password-form />

    <!-- User Settings Component -->
    <livewire:profile.user-settings-form />

    <!-- User Roles & Permissions Component -->
    <livewire:profile.user-roles-permissions />
</div>