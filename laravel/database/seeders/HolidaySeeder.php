<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Holiday;
use Spatie\Holidays\Holidays;
use Carbon\Carbon;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Holiday::truncate();

        $startYear = 2025;
        $endYear = now()->year + 5;

        for ($year = $startYear; $year <= $endYear; $year++) {
            $holidaysOfYear = Holidays::for('id', $year)->get();

            foreach ($holidaysOfYear as $holiday) {
                Holiday::firstOrCreate([
                    'date' => $holiday['date'],
                    'description' => $holiday['name'],
                ]);
            }
        }
    }
}