<?php

namespace Database\Seeders;

use App\Models\Complaint;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Roads & Infrastructure', 'Water Supply', 'Sanitation & Waste',
            'Electricity & Street Lights', 'Drainage & Sewage',
        ];

        foreach ($names as $i => $name) {
            $dept = Department::firstOrCreate(['name' => $name]);

            User::firstOrCreate(['email' => 'staff'.($i + 1).'@example.com'], [
                'name' => $name.' Officer',
                'password' => 'password',
                'role' => 'staff',
                'department_id' => $dept->id,
            ]);
        }

        User::firstOrCreate(['email' => 'admin@example.com'], [
            'name' => 'City Admin', 'password' => 'password', 'role' => 'admin',
        ]);

        $citizen = User::firstOrCreate(['email' => 'citizen@example.com'], [
            'name' => 'Demo Citizen', 'password' => 'password', 'role' => 'citizen',
        ]);

        if (Complaint::count() === 0) {
            $sample = [
                ['Large pothole on main road', 'Roads & Potholes', 'Near City Bus Stand'],
                ['Garbage not collected for a week', 'Garbage & Sanitation', 'Ward 5, Market Lane'],
                ['Street light not working', 'Street Lights', 'Gandhi Chowk'],
                ['Water pipe leakage', 'Water Supply', 'Shivaji Nagar'],
            ];

            foreach ($sample as [$title, $cat, $addr]) {
                $c = $citizen->complaints()->create([
                    'title' => $title, 'category' => $cat, 'address' => $addr,
                    'description' => 'Sample complaint created by the seeder for demo purposes.',
                    'status' => 'pending',
                ]);
                $c->updates()->create(['user_id' => $citizen->id, 'status' => 'pending', 'remarks' => 'Complaint submitted.']);
            }
        }
    }
}
