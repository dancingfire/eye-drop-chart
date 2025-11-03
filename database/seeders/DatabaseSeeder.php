<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $now = Carbon::now();
        
        // Create default superuser
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'company_name' => 'Southeast Wellness Pharmacy',
            'is_superuser' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Seed medications
        $meds = [
            ['name' => 'Latanoprost (Xalatan)', 'notes' => 'Evening'],
            ['name' => 'Timolol 0.5% (Timoptic)', 'notes' => 'Morning/Evening'],
            ['name' => 'Dorzolamide (Trusopt)', 'notes' => 'TID'],
            ['name' => 'Brimonidine (Alphagan)', 'notes' => 'BID'],
            ['name' => 'Tobramycin', 'notes' => 'Antibiotic'],
            ['name' => 'Moxifloxacin', 'notes' => 'Antibiotic'],
            ['name' => 'Artificial tears', 'notes' => 'PRN'],
            ['name' => 'Prednisolone (steroid)', 'notes' => 'Per Rx'],
        ];

        foreach ($meds as $m) {
            DB::table('medications')->insert(array_merge($m, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }
}
