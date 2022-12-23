<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash; 

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $user =  User::create([
            'name' => 'Demo Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('admin123')
        ]);
        $customer =  Customer::create([
            'first_name' => 'Demo',
            'last_name' => 'User',
            'email' => 'user@demo.com',
            'gender' => 'Male',
            'dob' => '1997-08-14',
            'bio' => 'Extreme User',
            'password' => Hash::make('user123')
        ]);
    }
}
