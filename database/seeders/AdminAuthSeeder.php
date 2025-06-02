<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class AdminAuthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'test@mail.com',
            'password' => Hash::make('Test1234!'),
        ]);

        Role::create([
            'name' => 'admin',
        ]);
        
        UserRole::create([
            'user_id' => 1,
            'role_id' => 1,
        ]);
    }
}
