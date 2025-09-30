<?php

namespace App\Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class UserStatusCategorySeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now(); // Get the current timestamp

        // Categories specifically for the 'users' module, as used by the focused StatusSeeder
        $userModuleCategories = [
            [
                'name' => 'account_status',
                //// 'display_name' => 'Account Status',
                'description' => 'General lifecycle states for user accounts (e.g., active, inactive, suspended).',
            ],
            [
                'name' => 'user_profile_status',
                //// 'display_name' => 'User Profile Status',
                'description' => 'States related to the completeness or specific conditions of a user\'s profile information.',
                
            ],
            [
                'name' => 'user_verification_status',
                // 'display_name' => 'User Verification Status',
                'description' => 'States for various user verification processes (e.g., email, phone, identity/KYC).',
                
            ],
            [
                'name' => 'user_security_status',
                // 'display_name' => 'User Security Status',
                'description' => 'States related to user account security (e.g., password reset, 2FA, locked).',
                
            ],
            [
                'name' => 'user_compliance_status',
                // 'display_name' => 'User Compliance Status',
                'description' => 'States related to user acceptance of terms, privacy consents, and other compliance matters.',
                
            ],
        ];

        foreach ($userModuleCategories as $category) {
            // Add timestamps to each category record
            $categoryData = array_merge($category, [
                'created_at' => $now,
                'updated_at' => $now,
                'editable' => 'No',
            ]);

            // Use updateOrInsert to prevent duplicates if the seeder is run multiple times.
            // It will check for existing records based on 'name' AND 'module'.
            DB::table('user_status_categories')->updateOrInsert(
                ['name' => $categoryData['name']], //'module_name' => $categoryData['module_name']], // Unique keys for lookup
                $categoryData // Data to insert or update
            );
        }
    }
}