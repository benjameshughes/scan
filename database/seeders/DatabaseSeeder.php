<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Scan;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting to seed the database...');

        // 1. Create roles and permissions first
        $this->command->info('👥 Creating roles and permissions...');
        $this->call(RolesAndPermissionsSeeder::class);

        // 2. Create admin user
        $this->command->info('👨‍💼 Creating admin user...');
        $admin = User::firstOrCreate(
            ['email' => 'ben@app.com'],
            [
                'name' => 'Ben Hughes',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'status' => 'active',
                'settings' => json_encode([
                    'notifications' => true,
                    'dark_mode' => false,
                    'auto_submit' => true,
                    'scan_sound' => true,
                ]),
            ]
        );

        // 3. Create additional users
        $this->command->info('👥 Creating additional users...');
        User::factory(15)->create();

        // 4. Create products
        $this->command->info('📦 Creating products...');
        Product::factory(100)->create();

        // 5. Create scans (more recent ones)
        $this->command->info('📱 Creating scan history...');
        Scan::factory(500)->create();

        // 6. Re-run roles seeder to assign roles to all users
        $this->command->info('🔐 Assigning user roles...');
        $this->call(RolesAndPermissionsSeeder::class);

        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->table(
            ['Resource', 'Count'],
            [
                ['Users', User::count()],
                ['Products', Product::count()],
                ['Scans', Scan::count()],
            ]
        );

        $this->command->info('🎉 Your table system now has realistic dummy data!');
        $this->command->info('🔑 Admin login: ben@app.com / password');
    }
}
