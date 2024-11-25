<?php

namespace Database\Seeders;

use App\Models\JobLog;
use Illuminate\Database\Seeder;

class JobLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        JobLog::factory()->count(50)->create();
    }
}
