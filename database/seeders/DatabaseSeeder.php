<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Applicant;
use App\Models\Scholarship;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin account
        User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@sms.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        // Student account
        $student = User::create([
            'name'     => 'Juan Dela Cruz',
            'email'    => 'student@sms.com',
            'password' => Hash::make('password'),
            'role'     => 'student',
        ]);

        // Secretary account
        User::create([
            'name'     => 'Secretary User',
            'email'    => 'secretary@sms.com',
            'password' => Hash::make('password'),
            'role'     => 'secretary',
        ]);

        // Applicant profile for student
        Applicant::create([
            'user_id'        => $student->id,
            'first_name'     => 'Juan',
            'last_name'      => 'Dela Cruz',
            'middle_name'    => 'Santos',
            'date_of_birth'  => '2002-05-15',
            'gender'         => 'male',
            'contact_number' => '09123456789',
            'address'        => '123 Main Street, Cebu City',
            'school'         => 'University of San Carlos',
            'course'         => 'BS Information Technology',
            'year_level'     => 3,
            'gpa'            => 1.50,
        ]);

        // Sample scholarships
        Scholarship::create([
            'name'        => 'CHED Full Merit Scholarship',
            'description' => 'Full scholarship for academically outstanding students.',
            'amount'      => 30000.00,
            'slots'       => 10,
            'min_gpa'     => 1.50,
            'deadline'    => '2026-12-31',
            'status'      => 'open',
        ]);

        Scholarship::create([
            'name'        => 'DOST-SEI Scholarship',
            'description' => 'Scholarship for science and technology students.',
            'amount'      => 25000.00,
            'slots'       => 5,
            'min_gpa'     => 1.75,
            'deadline'    => '2026-12-31',
            'status'      => 'open',
        ]);

        Scholarship::create([
            'name'        => 'SM Foundation Scholarship',
            'description' => 'Private scholarship from SM Foundation.',
            'amount'      => 15000.00,
            'slots'       => 20,
            'min_gpa'     => 2.00,
            'deadline'    => '2026-12-31',
            'status'      => 'open',
        ]);
    }
}