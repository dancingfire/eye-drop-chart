<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

         $now = Carbon::now();
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
