<?php

namespace App\Modules\Hr\Services;

use App\Modules\Hr\Models\ClockEvent;
use App\Modules\Hr\Models\Attendance;
use Carbon\Carbon;

use App\Modules\Hr\Http\Controllers\ClockEventController;

use Illuminate\Support\Facades\DB;




class AttendanceAggregator
{
    public function recalculateForDay(string $employeeId, string $date): void
    {
        DB::transaction(function () use ($employeeId, $date) {
            // 🔑 1. Normalize date ONCE
            // $normalizedDate = Carbon::parse($date)->toDateString(); // "2025-08-01"
            $normalizedDate = Carbon::parse($date)->format("Y-m-d H:i:s");// "2025-08-01 00:00:00" to avoid sqlite issues

            // 🔑 2. Explicitly find existing record
            $attendance = Attendance::where('employee_id', $employeeId)
                ->where('date', $normalizedDate) // Consistent format
                ->first();

            // 🔑 3. Calculate sessions (same as before)
            $events = ClockEvent::where('employee_id', $employeeId)
                ->whereDate('timestamp', $normalizedDate)
                ->orderBy('timestamp')
                ->orderBy('id')
                ->get();



            $sessions = [];
            $totalHours = 0.0;
            $currentState = 'out';
            $currentSessionStart = null;
            $notes = [];

            foreach ($events as $event) {
                if ($event->event_type === 'clock_in') {
                    if ($currentState === 'out') {
                        $currentState = 'in';
                        $currentSessionStart = $event->timestamp;
                    } else {
                        $notes[] = "Ignored duplicate clock-in at {$event->timestamp->format('H:i')}";
                    }
                }
                elseif ($event->event_type === 'clock_out') {
                    if ($currentState === 'in' && $currentSessionStart) {
                        if ($event->timestamp->greaterThan($currentSessionStart)) {
                            $duration = $currentSessionStart->diffInMinutes($event->timestamp) / 60.0;
                            $totalHours += $duration;
                            $sessions[] = [
                                'start' => $currentSessionStart->format('H:i'),
                                'end' => $event->timestamp->format('H:i'),
                                'duration' => round($duration, 2)
                            ];
                            $currentState = 'out';
                            $currentSessionStart = null;
                        } else {
                            $notes[] = "Ignored invalid clock-out (before clock-in) at {$event->timestamp->format('H:i')}";
                        }
                    } else {
                        $notes[] = "Ignored clock-out without prior clock-in at {$event->timestamp->format('H:i')}";
                    }
                }
            }

            // Final status
            $status = match (true) {
                $currentState === 'in' => 'incomplete',
                count($sessions) > 0 => 'complete',
                default => 'incomplete'
            };

            // Add note if there were invalid events
            $finalNote = !empty($notes) ? implode('; ', $notes) : null;



            // 🔑 4. Update or Create explicitly
            if ($attendance) {
                $attendance->update([
                    'net_hours' => round($totalHours, 2),
                    'sessions' => !empty($sessions) ? json_encode($sessions) : null,
                    'status' => $status,
                    'is_approved' => false,
                    'notes' => $finalNote,
                    'needs_review' => !empty($notes),
                ]);
            } else {
                Attendance::create([
                    'employee_id' => $employeeId,
                    'date' => $normalizedDate, // Explicitly normalized
                    'net_hours' => round($totalHours, 2),
                    'sessions' => !empty($sessions) ? json_encode($sessions) : null,
                    'status' => $status,
                    'is_approved' => false,
                    'notes' => $finalNote,
                    'needs_review' => !empty($notes),
                ]);
            }



        });
    }
}
