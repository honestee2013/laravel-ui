<?php

namespace App\Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Modules\Access\Models\Role;
use App\Modules\Access\Models\Permission;
use QuickerFaster\CodeGen\Services\AccessControl\AccessControlPermissionService;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // Create user
        $user = User::create([
            'id' => 1,
            'name' => 'Super Admin',
            'email' => 'admin@softui.com',
            'password' => Hash::make('secret'),
            'created_at' => now(),
            'updated_at' => now(),
            'user_type' => 'staff',
        ]);

        // Seed permissions
        AccessControlPermissionService::seedPermissionNames();
        

        // Create or get the role with correct guard
        $role = Role::firstOrCreate(
            ['name' => 'super_admin', 'guard_name' => 'web']
        );


        $permissions = Permission::where('guard_name', 'web')->get();

        // Assign permissions to role
        $role->syncPermissions($permissions);

        // Assign role to user using correct guard
        $user->assignRole($role);

        // auth()->login($user);
        // dd(auth()->user()->roles);



    }










}
