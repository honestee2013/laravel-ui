<?php

namespace App\Modules\Hr\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Modules\Hr\Services\AttendanceService;
use App\Modules\Hr\Services\PayrollCalculatorService;

use App\Modules\Hr\Http\Requests\StoreDailyAttendanceRequest;
use App\Modules\Hr\Rules\ValidAttendanceSequence; // Import the rule
use Illuminate\Validation\Rule;

use App\Modules\Hr\Models\DailyAttendance;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;



class AttendanceController extends Controller
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function store(StoreDailyAttendanceRequest $request): JsonResponse
    {


        try {
            // Apply the custom sequence rule using the validated data
            $request->validate([
                'attendance_time' => [
                    'required',
                    'date',
                    new ValidAttendanceSequence(
                        $request->employee_id,
                        $request->attendance_time,
                        $request->attendance_type,
                        $request->attendance_date
                    )
                ],
            ]);



            $attendance = $this->attendanceService->record($request->validated());

            return response()->json(['message' => 'Attendance recorded successfully', 'data' => $attendance], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            \Log::error("Attendance recording failed: " . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'An unexpected error occurred.'], 500);
        }
    }


public function batchStore(Request $request): JsonResponse
{
    $data = $request->all();

    foreach ($data as $record) {
        try {
            $validated = validator($record, [
                'employee_id' => ['required', 'exists:employee_profiles,employee_id'],
                'attendance_time' => ['required', 'date'],
                'attendance_type' => ['required', Rule::in(['check-in', 'check-out'])],
                'device_id' => ['nullable'],
                'latitude' => ['nullable', 'numeric'],
                'longitude' => ['nullable', 'numeric'],
            ])->validate();

            // Handle store like in store() method
            $this->attendanceService->record($validated);

        } catch (\Exception $e) {
            \Log::warning('Batch attendance failed', ['error' => $e->getMessage(), 'record' => $record]);
        }
    }

    return response()->json(['message' => 'Batch upload complete'], 200);
}






public function syncFromDevice(Request $request)
{
    $attendances = $request->all();

    foreach ($attendances as $record) {
        DailyAttendance::updateOrCreate(
            [
                'user_id' => $record['user_id'],
                'attendance_time' => $record['attendance_time'],
            ],
            [
                'attendance_type' => $record['attendance_type'],
                'attendance_date' => $record['attendance_date'],
                'device_id' => $record['device_id'] ?? null,
                'latitude' => $record['latitude'] ?? null,
                'longitude' => $record['longitude'] ?? null,
                'notes' => $record['notes'] ?? null,
            ]
        );
    }

    return response()->json(['status' => true, 'message' => 'Synced successfully']);
}



}
