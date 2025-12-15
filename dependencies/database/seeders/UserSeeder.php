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

        $superAdmin = User::create([
            'id' => 1,
            'name' => 'super admin',
            'email' => 'admin@softui.com',
            'password' => Hash::make('secret'),
        ]);

        $dmin = User::create([
            'id' => 2,
            'name' => 'admin',
            'email' => 'hrhead@agriwatts.ng',
            'password' => Hash::make('HRHead@2025'),
        ]);


        // Check if the 'super_admin' role exists
        $superAdminRole = Role::findByName('super_admin', 'web'); // 'web' is the default guard
        $adminRole = Role::findByName('admin', 'web'); // 'web' is the default guard
        
        if ($superAdminRole) {
            $superAdmin->assignRole($superAdminRole);
        } else {
            // Optional: throw an exception or log a warning
            throw new \Exception('Role "super_admin" not found. Did you run RoleSeeder?');
        }

        if ($adminRole) {
            $dmin->assignRole($adminRole);
        } else {
            // Optional: throw an exception or log a warning
            throw new \Exception('Role "super_admin" not found. Did you run RoleSeeder?');
        }

    }
}