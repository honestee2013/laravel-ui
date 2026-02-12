<?php

namespace App\Modules\Hr\Services;

use App\Modules\Hr\Models\{
    ClockEvent,
    Attendance,
    AttendancePolicy,
    WorkPattern,
    Shift,
    ShiftSchedule,
    Employee,
    EmployeePosition,
    AttendanceSession
};
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceCalculator
{
    /**
     * Calculate attendance for a specific employee and date
     * Creates/updates attendance record AND attendance sessions
     */
    public function calculateForDay(string $employeeNumber, Carbon $date): array
    {
        return DB::transaction(function () use ($employeeNumber, $date) {
            // 1. Get employee and position
            $employee = Employee::where('employee_number', $employeeNumber)->first();
            if (!$employee) {
                throw new \Exception("Employee not found: {$employeeNumber}");
            }

            $position = $employee->employeePosition;
            if (!$position) {
                throw new \Exception("Employee position not found for: {$employeeNumber}");
            }

            // 2. Get applicable policy and pattern
            $policy = $this->getApplicablePolicy($position, $date);
            $pattern = $position->workPattern;

            // 3. Get expected schedule
            $schedule = $this->getExpectedSchedule($employee, $position, $pattern, $date);

            // 4. Get clock events
            $events = ClockEvent::where('employee_id', $employeeNumber)
                                ->whereDate('timestamp', $date)
                                ->orderBy('timestamp')
                                ->get();

            // 5. Process events into sessions
            $sessionData = $this->processClockEvents($events);
            $sessions = $sessionData['sessions'];
            $totalHours = $sessionData['total_hours'];
            $firstClockIn = $sessionData['first_clock_in'];
            $lastClockOut = $sessionData['last_clock_out'];

            // 6. Get or create attendance record
            $attendance = $this->getOrCreateAttendanceRecord($employee, $date);

            // 7. DELETE existing sessions for this attendance (fresh calculation)
            AttendanceSession::where('attendance_id', $attendance->id)->delete();

            // 8. CREATE new sessions from processed events
            foreach ($sessions as $session) {
                AttendanceSession::create([
                    'attendance_id' => $attendance->id,
                    'clock_in_event_id' => $session['clock_in_event_id'] ?? null,
                    'clock_out_event_id' => $session['clock_out_event_id'] ?? null,
                    'start_time' => $session['start'],
                    'end_time' => $session['end'],
                    'duration_hours' => $session['duration'],
                    'session_type' => 'work',
                    'is_overnight' => $session['is_overnight'] ?? false,
                    'notes' => $session['notes'] ?? null,
                ]);
            }

            // 9. Calculate attendance metrics using policy
            $calculation = $this->calculateAttendanceMetrics(
                totalHours: $totalHours,
                firstClockIn: $firstClockIn,
                lastClockOut: $lastClockOut,
                schedule: $schedule,
                policy: $policy,
                employee: $employee,
                date: $date,
                sessions: $sessions
            );


            // 10. Update attendance record with calculation results
            $attendance->update([
                'status' => $calculation['status'],
                'net_hours' => $calculation['total_hours'],
                'regular_hours' => $calculation['regular_hours'],
                'overtime_hours' => $calculation['overtime_hours'],
                'double_time_hours' => $calculation['double_time_hours'],
                'minutes_late' => $calculation['minutes_late'],
                'minutes_early_departure' => $calculation['minutes_early_departure'],
                'missed_break_minutes' => $calculation['missed_break_minutes'],
                'needs_review' => $calculation['needs_review'],
                'attendance_policy_id' => $policy?->id,
                'work_pattern_id' => $pattern?->id,
                'calculation_metadata' => json_encode($calculation['breakdown']),
                'calculation_version' => '1.0',
                'calculation_method' => 'auto',
                'sessions' => json_encode(array_map(function ($s) {
                    return [
                        'start' => $s['start'] ? $s['start']->format('H:i') : null,
                        'end' => $s['end'] ? $s['end']->format('H:i') : null,
                        'duration' => $s['duration']
                    ];
                }, $sessions)),
            ]);

            return [
                'success' => true,
                'attendance_id' => $attendance->id,
                'calculation' => $calculation,
                'sessions_created' => count($sessions)
            ];
        });
    }

    /**
     * Process raw clock events into work sessions
     */
    protected function processClockEvents($events): array
    {
        $sessions = [];
        $totalHours = 0.0;
        $firstClockIn = null;
        $lastClockOut = null;

        $inSession = false;
        $sessionStart = null;
        $sessionStartEvent = null;

        foreach ($events as $event) {
            if ($event->event_type === 'clock_in' && !$inSession) {
                $inSession = true;
                $sessionStart = $event->timestamp;
                $sessionStartEvent = $event;

                if (!$firstClockIn) {
                    $firstClockIn = $event->timestamp;
                }
            } elseif ($event->event_type === 'clock_out' && $inSession) {
                $sessionEnd = $event->timestamp;
                $duration = $sessionStart->diffInMinutes($sessionEnd) / 60.0;

                $sessions[] = [
                    'clock_in_event_id' => $sessionStartEvent->id,
                    'clock_out_event_id' => $event->id,
                    'start' => $sessionStart,
                    'end' => $sessionEnd,
                    'duration' => round($duration, 2),
                    'is_overnight' => $sessionStart->format('Y-m-d') !== $sessionEnd->format('Y-m-d'),
                    'notes' => null
                ];

                $totalHours += $duration;
                $lastClockOut = $event->timestamp;
                $inSession = false;
                $sessionStart = null;
                $sessionStartEvent = null;
            }
        }

        // Handle orphaned clock-in (no matching clock-out)
        if ($inSession && $sessionStart && $sessionStartEvent) {
            $sessions[] = [
                'clock_in_event_id' => $sessionStartEvent->id,
                'clock_out_event_id' => null,
                'start' => $sessionStart,
                'end' => null,
                'duration' => 0.0,
                'is_overnight' => false,
                'notes' => 'Missing clock-out'
            ];
        }

        return [
            'sessions' => $sessions,
            'total_hours' => round($totalHours, 2),
            'first_clock_in' => $firstClockIn,
            'last_clock_out' => $lastClockOut
        ];
    }

    /**
     * Calculate attendance metrics based on policy
     */
    protected function calculateAttendanceMetrics(
        float $totalHours,
        ?Carbon $firstClockIn,
        ?Carbon $lastClockOut,
        ?array $schedule,
        ?AttendancePolicy $policy,
        Employee $employee,
        Carbon $date,
        array $sessions
    ): array {
        $result = [
            'status' => 'absent',
            'total_hours' => $totalHours,
            'regular_hours' => 0.0,
            'overtime_hours' => 0.0,
            'double_time_hours' => 0.0,
            'minutes_late' => 0,
            'minutes_early_departure' => 0,
            'missed_break_minutes' => 0,
            'violations' => [],
            'breakdown' => [],
            'needs_review' => false
        ];

        // If no schedule, mark as unscheduled
        if (!$schedule) {
            $result['status'] = 'unscheduled';
            $result['needs_review'] = true;
            return $result;
        }

        // If no policy, use defaults
        if (!$policy) {
            $policy = $this->getDefaultPolicyValues();
        }

        // If no hours and it's a work day → absent
        if ($totalHours == 0) {
            $result['status'] = 'absent';
            $result['needs_review'] = true;
            return $result;
        }

        // Check lateness
        if ($firstClockIn) {
            $latenessCheck = $this->checkLateness(
                $firstClockIn,
                $schedule['start_time'],
                $policy->grace_period_minutes,
                $date
            );

            if ($latenessCheck['is_late']) {
                $result['minutes_late'] = $latenessCheck['minutes_late'];
                $result['violations'][] = [
                    'type' => 'late_arrival',
                    'minutes' => $latenessCheck['minutes_late']
                ];
            }
        }

        // Check early departure
        if ($lastClockOut) {
            $earlyDepartureCheck = $this->checkEarlyDeparture(
                $lastClockOut,
                $schedule['end_time'],
                $policy->early_departure_grace_minutes,
                $date
            );

            if ($earlyDepartureCheck['is_early']) {
                $result['minutes_early_departure'] = $earlyDepartureCheck['minutes_early'];
                $result['violations'][] = [
                    'type' => 'early_departure',
                    'minutes' => $earlyDepartureCheck['minutes_early']
                ];
            }
        }

        // Calculate overtime breakdown
        $overtimeCalculation = $this->calculateOvertime(
            totalHours: $totalHours,
            policy: $policy,
            date: $date,
            employeeId: $employee->id
        );

        $result['regular_hours'] = $overtimeCalculation['regular_hours'];
        $result['overtime_hours'] = $overtimeCalculation['overtime_hours'];
        $result['double_time_hours'] = $overtimeCalculation['double_time_hours'];
        $result['breakdown']['overtime_calculation'] = $overtimeCalculation['breakdown'];

        // Check break compliance
        if ($policy->requires_break_after_hours > 0 && $policy->break_duration_minutes > 0) {
            $breakCheck = $this->checkBreakCompliance(
                $sessions,
                $policy->requires_break_after_hours,
                $policy->break_duration_minutes
            );

            if ($breakCheck['missed_break']) {
                $result['missed_break_minutes'] = $breakCheck['missed_minutes'];
                $result['violations'][] = [
                    'type' => 'missed_break',
                    'minutes' => $breakCheck['missed_minutes']
                ];
            }
        }

        // Apply unpaid break deduction if configured
        if ($policy->unpaid_break_minutes > 0 && $totalHours > 0) {
            $result['total_hours'] = max(0, $totalHours - ($policy->unpaid_break_minutes / 60));
            $result['breakdown']['unpaid_break_deducted'] = $policy->unpaid_break_minutes;
        }

        // Determine final status
        $result['status'] = $this->determineStatus(
            $result['total_hours'],
            $result['minutes_late'],
            $result['minutes_early_departure'],
            count($result['violations']),
            $schedule['shift']->duration_hours ?? 8.0
        );

        $result['needs_review'] = !empty($result['violations']) ||
                                  $result['status'] === 'incomplete' ||
                                  $result['status'] === 'half_day' ||
                                  $result['status'] === 'unscheduled';


        // Add violation to the breakdown
        $result['breakdown']['violations'] = $result['violations'];

        return $result;
    }

    /**
     * Get or create attendance record
     */
    protected function getOrCreateAttendanceRecord(Employee $employee, Carbon $date): Attendance
    {
        $attendance = Attendance::where('employee_number', $employee->employee_number)
                                ->whereDate('date', $date)
                                ->first();

        if (!$attendance) {
            $attendance = Attendance::create([
                'employee_id' => $employee->id,
                'employee_number' => $employee->employee_number,
                'company' => $employee->department->company->name ?? 'N/A',
                'department' => $employee->department->name ?? 'N/A',
                'date' => $date,
                'status' => 'pending',
                'is_approved' => false,
                'net_hours' => 0.00,
            ]);
        }

        return $attendance;
    }








    /**
     * Get the applicable attendance policy (with fallback logic)
     */
    protected function getApplicablePolicy(EmployeePosition $position, Carbon $date): ?AttendancePolicy
    {
        // Priority 1: Employee-specific policy
        if ($position->attendance_policy_id) {
            $policy = AttendancePolicy::find($position->attendance_policy_id);
            if ($policy && $this->isPolicyActive($policy, $date)) {
                return $policy;
            }
        }

        // Priority 2: Department default policy (you need to add this to Department model)
        /*
        if ($position->department && $position->department->default_attendance_policy_id) {
            $policy = AttendancePolicy::find($position->department->default_attendance_policy_id);
            if ($policy && $this->isPolicyActive($policy, $date)) {
                return $policy;
            }
        }
        */

        // Priority 3: Company default policy
        $defaultPolicy = AttendancePolicy::where('is_default', true)
            ->where('is_active', true)
            ->whereDate('effective_date', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('expiration_date')
                    ->orWhereDate('expiration_date', '>=', $date);
            })
            ->first();

        return $defaultPolicy;
    }

    /**
     * Check if policy is active for given date
     */
    protected function isPolicyActive(AttendancePolicy $policy, Carbon $date): bool
    {
        if (!$policy->is_active) {
            return false;
        }

        if ($date->lt(Carbon::parse($policy->effective_date))) {
            return false;
        }

        if ($policy->expiration_date && $date->gt(Carbon::parse($policy->expiration_date))) {
            return false;
        }

        return true;
    }

    /**
     * Get expected schedule for the day
     */
    protected function getExpectedSchedule(
        Employee $employee,
        EmployeePosition $position,
        ?WorkPattern $pattern,
        Carbon $date
    ): ?array {
        $dateString = $date->toDateString();
        $dayOfWeek = $date->dayOfWeekIso; // 1=Monday, 7=Sunday

        // Priority 1: Specific ShiftSchedule for the date
        $shiftSchedule = ShiftSchedule::where('employee_id', $employee->id)
            ->whereDate('schedule_date', $dateString)
            ->first();

        if ($shiftSchedule) {
            return [
                'type' => 'specific_schedule',
                'schedule' => $shiftSchedule,
                'start_time' => $shiftSchedule->start_time_override
                    ? Carbon::parse($shiftSchedule->start_time_override)
                    : Carbon::parse($shiftSchedule->shift->start_time),
                'end_time' => $shiftSchedule->end_time_override
                    ? Carbon::parse($shiftSchedule->end_time_override)
                    : Carbon::parse($shiftSchedule->shift->end_time),
                'shift' => $shiftSchedule->shift
            ];
        }


        // Priority 2: WorkPattern for the day of week
        if ($pattern && in_array($dayOfWeek, explode(",", $pattern->applicable_days))) {
            $shift = $pattern->shift;

            $baseDate = Carbon::parse($date);

            $startTimeString = $pattern->override_start_time ?: $shift->start_time;
            $endTimeString = $pattern->override_end_time ?: $shift->end_time;

            return [
                'start_time' => $baseDate->copy()->setTimeFromTimeString($startTimeString),
                'end_time' => $baseDate->copy()->setTimeFromTimeString($endTimeString),

                'type' => 'work_pattern',
                'pattern' => $pattern,
                'shift' => $shift,
                'is_overnight' => $shift->is_overnight
            ];
        }


        // Priority 3: Employee's default shift from position
        if ($position->shift_id) {
            $shift = Shift::find($position->shift_id);

            return [
                'type' => 'default_shift',
                'shift' => $shift,
                'start_time' => Carbon::parse($shift->start_time),
                'end_time' => Carbon::parse($shift->end_time),
                'is_overnight' => $shift->is_overnight
            ];
        }

        return null;
    }

    /**
     * Core calculation logic
     */
    protected function calculateAttendance(
        $events,
        ?array $schedule,
        ?AttendancePolicy $policy,
        ?WorkPattern $pattern,
        Employee $employee,
        Carbon $date
    ): array {
        $result = [
            'status' => 'absent',
            'total_hours' => 0.0,
            'regular_hours' => 0.0,
            'overtime_hours' => 0.0,
            'double_time_hours' => 0.0,
            'minutes_late' => 0,
            'minutes_early_departure' => 0,
            'missed_break_minutes' => 0,
            'violations' => [],
            'breakdown' => [],
            'needs_review' => false
        ];

        // If no schedule, mark as unscheduled
        if (!$schedule) {
            $result['status'] = 'unscheduled';
            $result['needs_review'] = true;
            return $result;
        }

        // If no policy, use defaults
        if (!$policy) {
            $policy = $this->getDefaultPolicyValues();
        }

        // Calculate total hours from clock events
        $sessions = $this->processClockEvents($events);
        $totalHours = $sessions['total_hours'];
        $result['total_hours'] = $totalHours;
        $result['breakdown']['sessions'] = $sessions['sessions'];

        // Check if it's a work day (has schedule but no hours could mean absence)
        if ($totalHours == 0) {
            $result['status'] = 'absent';
            $result['needs_review'] = true;
            return $result;
        }

        // Check lateness
        $latenessCheck = $this->checkLateness(
            $sessions['first_clock_in'],
            $schedule['start_time'],
            $policy->grace_period_minutes,
            $date
        );

        if ($latenessCheck['is_late']) {
            $result['minutes_late'] = $latenessCheck['minutes_late'];
            $result['violations'][] = [
                'type' => 'late_arrival',
                'minutes' => $latenessCheck['minutes_late']
            ];
        }

        // Check early departure
        $earlyDepartureCheck = $this->checkEarlyDeparture(
            $sessions['last_clock_out'],
            $schedule['end_time'],
            $policy->early_departure_grace_minutes,
            $date
        );

        if ($earlyDepartureCheck['is_early']) {
            $result['minutes_early_departure'] = $earlyDepartureCheck['minutes_early'];
            $result['violations'][] = [
                'type' => 'early_departure',
                'minutes' => $earlyDepartureCheck['minutes_early']
            ];
        }

        // Calculate overtime breakdown
        $overtimeCalculation = $this->calculateOvertime(
            $totalHours,
            $policy,
            $date,
            $employee->id
        );

        $result['regular_hours'] = $overtimeCalculation['regular_hours'];
        $result['overtime_hours'] = $overtimeCalculation['overtime_hours'];
        $result['double_time_hours'] = $overtimeCalculation['double_time_hours'];
        $result['breakdown']['overtime_calculation'] = $overtimeCalculation['breakdown'];

        // Check break compliance
        $breakCheck = $this->checkBreakCompliance(
            $sessions['sessions'],
            $policy->requires_break_after_hours,
            $policy->break_duration_minutes
        );

        if ($breakCheck['missed_break']) {
            $result['missed_break_minutes'] = $breakCheck['missed_minutes'];
            $result['violations'][] = [
                'type' => 'missed_break',
                'minutes' => $breakCheck['missed_minutes']
            ];
        }

        // Determine final status
        $result['status'] = $this->determineStatus(
            $totalHours,
            $result['minutes_late'],
            $result['minutes_early_departure'],
            count($result['violations']),
            $schedule['shift']->duration_hours ?? 8.0
        );

        $result['needs_review'] = !empty($result['violations']) ||
            $result['status'] === 'incomplete' ||
            $result['status'] === 'half_day';


        // Add violation to the breakdown
        $result['breakdown']['violations'] = $result['violations'];


        return $result;
    }



    /**
     * Check for lateness
     */
    protected function checkLateness(?Carbon $actualStart, Carbon $scheduledStart, int $graceMinutes, Carbon $date): array
    {
        if (!$actualStart) {
            return ['is_late' => false, 'minutes_late' => 0];
        }

        $graceTime = $scheduledStart->copy()->addMinutes($graceMinutes);

        if ($actualStart->greaterThan($graceTime)) {
            $minutesLate = $actualStart->diffInMinutes($graceTime);
            return ['is_late' => true, 'minutes_late' => $minutesLate];
        }

        return ['is_late' => false, 'minutes_late' => 0];
    }

    /**
     * Check for early departure
     */
    protected function checkEarlyDeparture(?Carbon $actualEnd, Carbon $scheduledEnd, int $graceMinutes, Carbon $date): array
    {
        if (!$actualEnd) {
            return ['is_early' => false, 'minutes_early' => 0];
        }

        $graceTime = $scheduledEnd->copy()->subMinutes($graceMinutes);

        if ($actualEnd->lessThan($graceTime)) {
            $minutesEarly = $graceTime->diffInMinutes($actualEnd);
            return ['is_early' => true, 'minutes_early' => $minutesEarly];
        }

        return ['is_early' => false, 'minutes_early' => 0];
    }

    /**
     * Calculate overtime breakdown
     */
    protected function calculateOvertime(float $totalHours, AttendancePolicy $policy, Carbon $date, int $employeeId): array
    {
        $regularHours = 0.0;
        $overtimeHours = 0.0;
        $doubleTimeHours = 0.0;
        $breakdown = [];

        // Daily overtime
        if ($totalHours > $policy->overtime_daily_threshold_hours) {
            $overtimeHours = $totalHours - $policy->overtime_daily_threshold_hours - ($policy->unpaid_break_minutes/60); // Convert min to hour
            $regularHours = $policy->overtime_daily_threshold_hours;

            // Apply max daily overtime limit
            if ($policy->max_daily_overtime_hours > 0 && $overtimeHours > $policy->max_daily_overtime_hours) {
                $overtimeHours = $policy->max_daily_overtime_hours;
                $breakdown['daily_overtime_capped'] = true;
            }

            // Check for double time
            if (
                $policy->double_time_threshold_hours > 0 &&
                $totalHours > $policy->double_time_threshold_hours
            ) {

                $doubleTimeHours = $totalHours - $policy->double_time_threshold_hours;
                $overtimeHours -= $doubleTimeHours;

                // Ensure overtime hours don't go negative
                if ($overtimeHours < 0) {
                    $doubleTimeHours += $overtimeHours;
                    $overtimeHours = 0;
                }
            }
        } else {
            $regularHours = $totalHours;
        }

        // Weekly overtime (simplified - in reality need to check past 7 days)
        // You'll need to implement this with a weekly aggregation
        $breakdown['daily_threshold'] = $policy->overtime_daily_threshold_hours;
        $breakdown['weekly_threshold'] = $policy->overtime_weekly_threshold_hours;
        $breakdown['max_daily_overtime'] = $policy->max_daily_overtime_hours;
        $breakdown['double_time_threshold'] = $policy->double_time_threshold_hours;

        return [
            'regular_hours' => round($regularHours, 2),
            'overtime_hours' => round($overtimeHours, 2),
            'double_time_hours' => round($doubleTimeHours, 2),
            'breakdown' => $breakdown
        ];
    }

    /**
     * Check break compliance
     */
    protected function checkBreakCompliance(array $sessions, float $requiresBreakAfterHours, int $breakDurationMinutes): array
    {
        if (!$requiresBreakAfterHours || $requiresBreakAfterHours == 0) {
            return ['missed_break' => false, 'missed_minutes' => 0];
        }

        $totalSessionHours = 0;
        foreach ($sessions as $session) {
            $totalSessionHours += $session['duration'];

            if ($totalSessionHours > $requiresBreakAfterHours) {
                // Check if there's a break of at least required duration
                $hasBreak = $this->checkForBreak($sessions, $breakDurationMinutes);
                if (!$hasBreak) {
                    return ['missed_break' => true, 'missed_minutes' => $breakDurationMinutes];
                }
            }
        }

        return ['missed_break' => false, 'missed_minutes' => 0];
    }

    /**
     * Check for breaks in sessions
     */
    protected function checkForBreak(array $sessions, int $requiredMinutes): bool
    {
        // This is simplified - you'd need actual break events
        // For now, assume breaks are included in sessions as gaps
        return false;
    }

    /**
     * Determine final status
     */
    protected function determineStatus(
        float $totalHours,
        int $minutesLate,
        int $minutesEarly,
        int $violationCount,
        float $expectedHours
    ): string {

        if ($totalHours == 0) {
            return 'absent';
        }

        if ($minutesLate > 0) {
            return 'late';
        }

        if ($minutesEarly > 0) {
            return 'early_departure';
        }

        if ($totalHours < ($expectedHours * 0.5)) {
            return 'half_day';
        }

        if ($totalHours < $expectedHours) {
            return 'incomplete';
        }

        return 'present';
    }

    /**
     * Get default policy values when no policy is assigned
     */
    protected function getDefaultPolicyValues(): object
    {
        return (object) [
            'grace_period_minutes' => 5,
            'early_departure_grace_minutes' => 5,
            'overtime_daily_threshold_hours' => 8.0,
            'overtime_weekly_threshold_hours' => 40.0,
            'max_daily_overtime_hours' => 4.0,
            'overtime_multiplier' => 1.5,
            'double_time_threshold_hours' => 12.0,
            'double_time_multiplier' => 2.0,
            'requires_break_after_hours' => 5.0,
            'break_duration_minutes' => 30,
            'unpaid_break_minutes' => 0
        ];
    }

    /**
     * Update or create attendance record with calculation results
     */
    protected function updateAttendanceRecord(
        Employee $employee,
        Carbon $date,
        array $calculation,
        ?AttendancePolicy $policy,
        ?WorkPattern $pattern
    ): Attendance {
        $attendance = Attendance::where('employee_number', $employee->employee_number)
            ->whereDate('date', $date)
            ->first();

        if (!$attendance) {
            $attendance = new Attendance();
            $attendance->employee_id = $employee->id;
            $attendance->employee_number = $employee->employee_number;
            $attendance->date = $date;
        }

        // Update with calculation results
        $attendance->status = $calculation['status'];
        $attendance->net_hours = $calculation['total_hours'];
        $attendance->regular_hours = $calculation['regular_hours'];
        $attendance->overtime_hours = $calculation['overtime_hours'];
        $attendance->double_time_hours = $calculation['double_time_hours'];
        $attendance->minutes_late = $calculation['minutes_late'];
        $attendance->minutes_early_departure = $calculation['minutes_early_departure'];
        $attendance->missed_break_minutes = $calculation['missed_break_minutes'];
        $attendance->needs_review = $calculation['needs_review'];
        $attendance->attendance_policy_id = $policy?->id;
        $attendance->work_pattern_id = $pattern?->id;
        $attendance->calculation_metadata = json_encode($calculation['breakdown']);
        $attendance->calculation_version = '1.0';
        $attendance->calculation_method = 'auto';

        $attendance->save();

        return $attendance;
    }
}
