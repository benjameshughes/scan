<?php

namespace Database\Seeders;

use App\Models\Scan;
use Database\Factories\ScanFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ScanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Generate 1000 scans
        Scan::factory(1000)->create();
    }
}
