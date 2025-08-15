<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\AcademicYear;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $startYear = 2022;
        $currentYear = now()->month >= 7 ? now()->year : now()->year - 1;

        for ($year = $startYear; $year <= $currentYear; $year++) {
            AcademicYear::firstOrCreate(['year' => $year]);
        }
    }
}
