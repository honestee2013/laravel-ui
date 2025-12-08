<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            'id' => 1,
            'name' => 'admin',
            'email' => 'admin@softui.com',
            'password' => Hash::make('secret'),
        ]);

        // Check if the 'super_admin' role exists
        $superAdminRole = Role::findByName('super_admin', 'web'); // 'web' is the default guard
        if ($superAdminRole) {
            $user->assignRole($superAdminRole);
        } else {
            // Optional: throw an exception or log a warning
            // throw new \Exception('Role "super_admin" not found. Did you run RoleSeeder?');
        }
    }
}