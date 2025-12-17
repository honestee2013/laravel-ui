<?php

namespace App\Modules\Hr\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Hr\Models\Employee;

class UserSyncController extends Controller
{
    public function syncUser()
    {
        // Query employees who have employee profiles
        $employees = Employee::with([
                'department',
                'employeeProfile',
                'employeePosition',
                'user.roles' // Optional: include user if exists
            ])
            ->whereHas('employeeProfile') // Only employees with profiles
            ->get()
            ->map(function ($employee) {
                $employeeProfile = $employee->employeeProfile;
                $user = $employee->user;
                
                // Get user role if user exists
                $role = '';
                if ($user && $user->roles) {
                    $role = $user->roles->first() ? $user->roles->first()->name : '';
                }
                
                // Determine which email and phone to use
                $email = $employeeProfile && $employeeProfile->personal_email 
                    ? $employeeProfile->personal_email 
                    : ($user ? $user->email : $employee->email);
                
                $phone = $employeeProfile && $employeeProfile->personal_phone 
                    ? $employeeProfile->personal_phone 
                    : ($user ? $user->phone : $employee->phone);
                
                return [
                    // Employee core information
                    'employee_id' => $employee->id,
                    'employee_number' => $employee->employee_number,
                    'first_name' => $employee->first_name,
                    'last_name' => $employee->last_name,
                    'full_name' => $employee->first_name . ' ' . $employee->last_name,
                    'official_email' => $employee->email,
                    'official_phone' => $employee->phone,
                    
                    // User account information (if exists)
                    'user_id' => $user ? $user->id : null,
                    'user_exists' => !is_null($user),
                    'username' => $user ? $user->email : null,
                    'role' => $role,
                    
                    // Employee Profile information
                    'profile_picture' => $employeeProfile && $employeeProfile->photo
                        ? asset('storage/' . $employeeProfile->photo)
                        : null,
                    'middle_name' => $employeeProfile ? $employeeProfile->middle_name : null,
                    'preferred_name' => $employeeProfile ? $employeeProfile->preferred_name : null,
                    'personal_email' => $employeeProfile ? $employeeProfile->personal_email : null,
                    'personal_phone' => $employeeProfile ? $employeeProfile->personal_phone : null,
                    'work_phone' => $employeeProfile ? $employeeProfile->work_phone : null,
                    'emergency_contact_name' => $employeeProfile ? $employeeProfile->emergency_contact_name : null,
                    'emergency_contact_phone' => $employeeProfile ? $employeeProfile->emergency_contact_phone : null,
                    'emergency_contact_relationship' => $employeeProfile ? $employeeProfile->emergency_contact_relationship : null,
                    'passport_number' => $employeeProfile ? $employeeProfile->passport_number : null,
                    'passport_expiry_date' => $employeeProfile ? $employeeProfile->passport_expiry_date : null,
                    'national_id_number' => $employeeProfile ? $employeeProfile->national_id_number : null,
                    'bio' => $employeeProfile ? $employeeProfile->bio : null,
                    
                    // Contact information from employee
                    'gender' => $employee->gender,
                    'date_of_birth' => $employee->date_of_birth,
                    'nationality' => $employee->nationality,
                    'marital_status' => $employee->marital_status,
                    'address_street' => $employee->address_street,
                    'address_city' => $employee->address_city,
                    'address_state' => $employee->address_state,
                    'address_postal_code' => $employee->address_postal_code,
                    'address_country' => $employee->address_country,
                    
                    // Department information
                    'department' => $employee->department ? $employee->department->name : null,
                    'department_id' => $employee->department_id,
                    
                    // Position/Designation information
                    'designation' => $employee->employeePosition 
                        ? $employee->employeePosition->position_name 
                        : null,
                    'position_id' => $employee->employeePosition 
                        ? $employee->employeePosition->position_id 
                        : null,
                    
                    // Employment details
                    'status' => $employee->status,
                    'hire_date' => $employee->hire_date,
                    
                    // User information if exists (optional)
                    'user_name' => $user ? $user->name : null,
                    'user_location' => $user ? $user->location : null,
                    'user_about_me' => $user ? $user->about_me : null,
                    'user_company_id' => $user ? $user->company_id : null
                ];
            });

        return response()->json([
            'status' => true,
            'message' => 'Employee data synced successfully.',
            'count' => $employees->count(),
            'data' => $employees,
        ]);
    }
}