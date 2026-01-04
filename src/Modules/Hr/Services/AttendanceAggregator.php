<?php

namespace App\Modules\Hr\Services;

use App\Modules\Hr\Models\ClockEvent;
use App\Modules\Hr\Models\Attendance;
use App\Modules\Hr\Models\AttendanceSession;
use App\Modules\Hr\Models\LeaveRequest;
use App\Modules\Hr\Models\Holiday;
use App\Modules\Hr\Models\Employee;
use App\Modules\Hr\Models\ShiftSchedule;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceAggregator
{
    /**
     * Recalculate attendance for a specific employee and day
     * Now includes leave detection and unplanned absence handling
     */
    public function recalculateForDay(string $employeeNumber, string $date): void
    {
        DB::transaction(function () use ($employeeNumber, $date) {
            // 1. Normalize date
            $normalizedDate = Carbon::parse($date)->format("Y-m-d H:i:s");
            $dateOnly = Carbon::parse($date)->toDateString();

            // 2. Check if it's a company holiday
            $isHoliday = $this->isCompanyHoliday($dateOnly);

            // 3. Check for approved leave
            $hasApprovedLeave = LeaveRequest::where('employee_id', $employeeNumber)
                ->where('status', 'Approved')
                ->whereDate('start_date', '<=', $dateOnly)
                ->whereDate('end_date', '>=', $dateOnly)
                ->exists();

            // 4. Get or create attendance record
            $attendance = Attendance::where('employee_id', $employeeNumber)
                ->where('date', $normalizedDate)
                ->first();

            if (!$attendance) {
                $employee = Employee::where("employee_number", $employeeNumber)->first();
                $attendance = Attendance::create([
                    'employee_id' => $employeeNumber,
                    'company' => $employee? $employee->department->company->name : "N/A",
                    'department' => $employee? $employee->department->name : "N/A",
                    'date' => $normalizedDate,
                    'status' => 'pending',
                    'is_approved' => false,
                    'net_hours' => 0.00,
                ]);
            } else {
                // Clear existing sessions for fresh calculation
                AttendanceSession::where('attendance_id', $attendance->id)->delete();
            }

            // 5. Get all clock events for the day
            $events = ClockEvent::where('employee_id', $employeeNumber)
                ->whereDate('timestamp', $normalizedDate)
                ->orderBy('timestamp')
                ->orderBy('id')
                ->get();

            // ============================================
            // SCENARIO 1: HOLIDAY
            // ============================================
            if ($isHoliday) {
                $this->handleHolidayAttendance($attendance, $dateOnly);
                return;
            }

            // ============================================
            // SCENARIO 2: APPROVED LEAVE
            // ============================================
            if ($hasApprovedLeave) {
                $this->handleLeaveAttendance($attendance, $employeeNumber, $dateOnly);
                return;
            }

            // ============================================
            // SCENARIO 3: NO CLOCK EVENTS (UNPLANNED ABSENCE)
            // ============================================
            if ($events->isEmpty()) {
                $this->handleUnplannedAbsence($attendance, $dateOnly);
                return;
            }

            // ============================================
            // SCENARIO 4: NORMAL WORK DAY WITH CLOCK EVENTS
            // ============================================
            $this->processClockEvents($attendance, $events, $dateOnly);
        });
    }

    /**
     * Handle company holiday attendance
     */
    private function handleHolidayAttendance(Attendance $attendance, string $date): void
    {
        $holiday = Holiday::whereDate('date', $date)->first();

        $attendance->update([
            'status' => 'holiday',
            'net_hours' => 0.00,
            'is_approved' => true,
            'notes' => $holiday ? "Company Holiday: {$holiday->name}" : "Company Holiday",
            'needs_review' => false,
            'is_unplanned' => false,
            'absence_type' => null,
        ]);

        Log::info("Marked attendance as holiday", [
            'employee_id' => $attendance->employee_id,
            'date' => $date,
            'attendance_id' => $attendance->id
        ]);
    }

    /**
     * Handle approved leave attendance
     */
    private function handleLeaveAttendance(Attendance $attendance, string $employeeNumber, string $date): void
    {
        $leaveRequest = LeaveRequest::where('employee_id', $employeeNumber)
            ->where('status', 'Approved')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->first();

        if (!$leaveRequest) {
            return;
        }

        // Get shift info for standard hours (could be from ShiftSchedule)
        $standardHours = 8.00; // Default, should come from employee's shift

        $attendance->update([
            'status' => 'leave',
            'leave_request_id' => $leaveRequest->id,
            'net_hours' => $standardHours,
            'is_approved' => true,
            'notes' => "On Leave: {$leaveRequest->leaveType->name}",
            'needs_review' => false,
            'is_unplanned' => false,
            'absence_type' => 'planned_leave',
            'hours_deducted' => $leaveRequest->leaveType->deducts_from_balance ? $standardHours : 0,
            'is_paid_absence' => $leaveRequest->leaveType->is_paid ?? true,
        ]);

        Log::info("Marked attendance as leave", [
            'employee_id' => $attendance->employee_id,
            'date' => $date,
            'leave_request_id' => $leaveRequest->id,
            'attendance_id' => $attendance->id
        ]);
    }

    /**
     * Handle unplanned absence (no clock events, no approved leave)
     */
    private function handleUnplannedAbsence(Attendance $attendance, string $date): void
    {
        $attendance->update([
            'status' => 'absent',
            'net_hours' => 0.00,
            'is_approved' => false, // Needs manager approval
            'notes' => 'No show - unplanned absence',
            'needs_review' => true,
            'is_unplanned' => true,
            'absence_type' => 'unplanned_absent',
            'hours_deducted' => 8.00, // Full day deduction
            'is_paid_absence' => false, // Typically unpaid
        ]);

        Log::warning("Detected unplanned absence", [
            'employee_id' => $attendance->employee_id,
            'date' => $date,
            'attendance_id' => $attendance->id
        ]);

        // Trigger notification (to be implemented)
        // event(new UnplannedAbsenceDetected($attendance));
    }

    /**
     * Process normal clock events and create sessions
     */
    private function processClockEvents(Attendance $attendance, $events, string $date): void
    {
        $sessions = [];
        $totalHours = 0.0;
        $currentState = 'out';
        $currentSessionStart = null;
        $sessionStartEvent = null;
        $notes = [];

        foreach ($events as $event) {
            if ($event->event_type === 'clock_in') {
                if ($currentState === 'out') {
                    $currentState = 'in';
                    $currentSessionStart = $event->timestamp;
                    $sessionStartEvent = $event;
                } else {
                    $notes[] = "Duplicate clock-in at {$event->timestamp->format('H:i')}";
                    Log::warning("Duplicate clock-in detected", [
                        'employee_id' => $attendance->employee_id,
                        'event_id' => $event->id,
                        'timestamp' => $event->timestamp
                    ]);
                }
            } elseif ($event->event_type === 'clock_out') {
                if ($currentState === 'in' && $currentSessionStart && $sessionStartEvent) {
                    if ($event->timestamp->greaterThan($currentSessionStart)) {
                        $duration = $currentSessionStart->diffInMinutes($event->timestamp) / 60.0;
                        $duration = round($duration, 2);
                        $totalHours += $duration;

                        // 1. Store in JSON (backward compatibility)
                        $sessions[] = [
                            'start' => $currentSessionStart->format('H:i'),
                            'end' => $event->timestamp->format('H:i'),
                            'duration' => $duration
                        ];

                        // 2. Create AttendanceSession record
                        AttendanceSession::create([
                            'attendance_id' => $attendance->id,
                            'clock_in_event_id' => $sessionStartEvent->id,
                            'clock_out_event_id' => $event->id,
                            'start_time' => $currentSessionStart,
                            'end_time' => $event->timestamp,
                            'duration_hours' => $duration,
                            'session_type' => 'work',
                            'is_overnight' => $currentSessionStart->format('Y-m-d') !== $event->timestamp->format('Y-m-d'),
                        ]);

                        $currentState = 'out';
                        $currentSessionStart = null;
                        $sessionStartEvent = null;
                    } else {
                        $notes[] = "Invalid clock-out (before clock-in) at {$event->timestamp->format('H:i')}";
                    }
                } else {
                    $notes[] = "Orphaned clock-out at {$event->timestamp->format('H:i')}";
                }
            }
        }

        // Handle orphaned clock-in (session started but not ended)
        if ($currentState === 'in' && $currentSessionStart && $sessionStartEvent) {
            $notes[] = "Open session started at {$currentSessionStart->format('H:i')}";

            // Create partial session for tracking
            AttendanceSession::create([
                'attendance_id' => $attendance->id,
                'clock_in_event_id' => $sessionStartEvent->id,
                'clock_out_event_id' => null,
                'start_time' => $currentSessionStart,
                'end_time' => null,
                'duration_hours' => 0.00,
                'session_type' => 'work',
                'is_overnight' => false,
                'notes' => 'Session not closed',
            ]);
        }

        // Determine status based on sessions
        $status = match (true) {
            $currentState === 'in' => 'incomplete',
            $totalHours >= 8.0 => 'complete',
            $totalHours > 0 && $totalHours < 8.0 => 'half_day',
            default => 'incomplete'
        };

        // Check for tardiness against shift schedule
        $isLate = $this->checkForTardiness($attendance->employee_id, $date, $events);
        if ($isLate) {
            $status = 'late';
            $notes[] = 'Late arrival detected';
        }

        $finalNote = !empty($notes) ? implode('; ', $notes) : null;

        $attendance->update([
            'net_hours' => round($totalHours, 2),
            'sessions' => !empty($sessions) ? json_encode($sessions) : null,
            'status' => $status,
            'notes' => $finalNote,
            'needs_review' => !empty($notes) || $isLate,
            'is_unplanned' => false,
            'absence_type' => $status === 'half_day' ? 'half_day' : null,
        ]);
    }

    /**
     * Check if date is a company holiday
     */
    // Update the isCompanyHoliday method in AttendanceAggregator
    private function isCompanyHoliday(string $date): bool
    {
        $dateObj = Carbon::parse($date);

        // Check for exact date match
        $exactMatch = Holiday::whereDate('date', $date)
            ->where('is_active', true)
            ->where(function ($query) use ($dateObj) {
                $query->where('business_impact', 'office_closed')
                    ->orWhere('business_impact', 'reduced_staff');
            })
            ->exists();

        if ($exactMatch) {
            return true;
        }

        // Check for observed date (if holiday falls on weekend)
        $observedMatch = Holiday::whereDate('observed_date', $date)
            ->where('is_active', true)
            ->whereNotNull('observed_date')
            ->where(function ($query) use ($dateObj) {
                $query->where('business_impact', 'office_closed')
                    ->orWhere('business_impact', 'reduced_staff');
            })
            ->exists();

        return $observedMatch;
    }

    // New method to get holiday details
    private function getHolidayDetails(string $date): ?array
    {
        $holiday = Holiday::whereDate('date', $date)
            ->orWhereDate('observed_date', $date)
            ->where('is_active', true)
            ->first();

        if (!$holiday) {
            return null;
        }

        return [
            'id' => $holiday->id,
            'name' => $holiday->name,
            'type' => $holiday->holiday_type,
            'is_paid' => $holiday->is_paid_holiday,
            'business_impact' => $holiday->business_impact,
            'calendar_id' => $holiday->calendar_id,
        ];
    }

    /**
     * Check for tardiness against shift schedule
     */
    private function checkForTardiness(string $employeeNumber, string $date, $events): bool
    {
        // Get employee's shift for the day
        // This requires ShiftSchedule model implementation
        // For now, using a simple rule: clock-in after 9:00 AM is late

        $firstClockIn = $events->where('event_type', 'clock_in')->first();
        if (!$firstClockIn) {
            return false;
        }

        $clockInTime = Carbon::parse($firstClockIn->timestamp);
        $lateThreshold = Carbon::parse($date . ' 09:00:00');

        return $clockInTime->greaterThan($lateThreshold);
    }

    /**
     * Batch recalculate for date range (for payroll processing)
     */
    public function recalculateDateRange(string $employeeNumber, string $startDate, string $endDate): void
    {
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $this->recalculateForDay($employeeNumber, $date->format('Y-m-d'));
        }

        Log::info("Recalculated attendance range", [
            'employee_id' => $employeeNumber,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days_processed' => $period->count()
        ]);
    }
}