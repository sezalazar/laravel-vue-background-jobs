<?php

namespace Database\Seeders;

use App\Models\BackgroundJob;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BackgroundJobSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BackgroundJob::factory()->count(10)->withAdmin()->create();
    }
}
