<?php

namespace App\Modules\Hr\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Modules\Hr\Models\ClockEvent;
use App\Modules\Hr\Services\AttendanceAggregator;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;




class ClockEventController extends Controller
{
    public function __construct(
        private AttendanceAggregator $aggregator
    ) {}

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|string|exists:employees,employee_number',
            'attendance_type' => 'required|in:check-in,check-out',
            'attendance_time' => 'required|date_format:Y-m-d H:i:s',
            'attendance_date' => 'required|date_format:Y-m-d',
            'device_id' => 'nullable|string',
            'latitude' => 'nullable|integer',
            'longitude' => 'nullable|integer',
        ]);

        // Convert microdegrees to decimal
        $lat = $data['latitude'] ? round($data['latitude'] / 1_000_000, 8) : null;
        $lng = $data['longitude'] ? round($data['longitude'] / 1_000_000, 8) : null;

        // Map to internal event type
        $eventType = $data['attendance_type'] === 'check-in' ? 'clock_in' : 'clock_out';
        $timestamp = $data['attendance_time'];

        // 🔑 IDEMPOTENCY CHECK: Prevent duplicate events
        $existing = ClockEvent::where('employee_id', $data['employee_id'])
            ->where('timestamp', $timestamp)
            ->where('event_type', $eventType)
            ->exists();

        if ($existing) {
            return response()->json([
                'message' => 'Duplicate event ignored',
                'status' => 'duplicate'
            ], 200);
        }

        // Save raw event
        $event = ClockEvent::create([
            'employee_id' => $data['employee_id'],
            'event_type' => $eventType,
            'timestamp' => $timestamp,
            'method' => 'device',
            'device_id' => $data['device_id'],
            'latitude' => $lat,
            'longitude' => $lng,
            'ip_address' => $request->ip(),
        ]);

        // Trigger daily aggregation
        $this->aggregator->recalculateForDay($data['employee_id'], $data['attendance_date']);

        return response()->json([
            'message' => 'Clock event recorded',
            'event_id' => $event->id
        ], 201);
    }
}
