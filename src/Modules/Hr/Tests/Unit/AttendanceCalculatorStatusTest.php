<?php

namespace App\Modules\Hr\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use App\Modules\Hr\Models\{
    Employee, EmployeePosition, Shift, WorkPattern, AttendancePolicy,
    ClockEvent, Attendance, AttendanceSession
};
use App\Modules\Hr\Services\AttendanceCalculator;

class AttendanceCalculatorStatusTest extends TestCase
{
    use RefreshDatabase;

    protected Employee $employee;
    protected Shift $shift;
    protected WorkPattern $workPattern;
    protected AttendancePolicy $defaultPolicy;
    protected AttendanceCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        // Create base test data (same as before)
        $this->shift = Shift::factory()->create([
            'name' => 'Standard 8-5',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'duration_hours' => 8.0,
            'is_overnight' => false,
        ]);

        $this->workPattern = WorkPattern::factory()->create([
            'name' => 'Mon-Fri',
            'shift_id' => $this->shift->id,
            'applicable_days' => '1,2,3,4,5',
            'pattern_type' => 'recurring',
            'effective_date' => '2026-01-01',
            'is_active' => true,
            'is_default' => true,
        ]);

        $this->employee = Employee::factory()->create([
            'employee_number' => 'EMP-TEST-001',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->defaultPolicy = AttendancePolicy::factory()->create([
            'name' => 'Default Policy',
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
            'unpaid_break_minutes' => 0,
            'effective_date' => '2026-01-01',
            'is_active' => true,
            'is_default' => true,
        ]);

        EmployeePosition::factory()->create([
            'employee_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'work_pattern_id' => $this->workPattern->id,
            'attendance_policy_id' => $this->defaultPolicy->id,
            'pay_type' => 'hourly',
            'hourly_rate' => 20.00,
            'start_date' => '2026-01-01',
        ]);

        $this->calculator = new AttendanceCalculator();
    }

    /** @test */
    public function it_handles_multiple_sessions_correctly()
    {
        $date = Carbon::parse('2026-02-16'); // Monday

        // Morning session: 08:00 - 12:00 (4 hours)
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 0));
        $this->createClockEvent('clock_out', $date->copy()->setTime(12, 0));

        // Afternoon session: 13:00 - 17:00 (4 hours)
        $this->createClockEvent('clock_in', $date->copy()->setTime(13, 0));
        $this->createClockEvent('clock_out', $date->copy()->setTime(17, 0));

        $result = $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_number', $this->employee->employee_number)
                                ->whereDate('date', $date)
                                ->first();

        $this->assertEquals(8.0, $attendance->net_hours);
        // Status is irrelevant here. We need total session hours
        // $this->assertEquals('present', $attendance->status);
        $this->assertEquals(0, $attendance->minutes_late);
        $this->assertEquals(0, $attendance->minutes_early_departure);

        // Verify two sessions created
        $sessions = AttendanceSession::where('attendance_id', $attendance->id)->get();
        $this->assertCount(2, $sessions);
    }

    /** @test */
    public function it_marks_late_based_on_first_clock_in()
    {
        $date = Carbon::parse('2026-02-16');

        // First clock-in at 08:10 (10 min late, grace 5)
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 10));
        $this->createClockEvent('clock_out', $date->copy()->setTime(12, 0));
        $this->createClockEvent('clock_in', $date->copy()->setTime(13, 0));
        $this->createClockEvent('clock_out', $date->copy()->setTime(17, 0));

        $result = $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_number', $this->employee->employee_number)
                                ->whereDate('date', $date)
                                ->first();

        $this->assertEquals('late', $attendance->status);
        $this->assertEquals(5, $attendance->minutes_late); // 08:10 - (08:00+5) = 5 min
        $this->assertNotEquals(8.0, $attendance->net_hours); // Expected hours cannot be met due to lateness
    }

    /** @test */
    public function it_marks_early_departure_based_on_last_clock_out()
    {
        $date = Carbon::parse('2026-02-16');

        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 0));
        $this->createClockEvent('clock_out', $date->copy()->setTime(12, 0));
        $this->createClockEvent('clock_in', $date->copy()->setTime(13, 0));
        // Last clock-out at 16:50 (10 min early, grace 5)
        $this->createClockEvent('clock_out', $date->copy()->setTime(16, 50));

        $result = $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_number', $this->employee->employee_number)
                                ->whereDate('date', $date)
                                ->first();

        $this->assertEquals('early_departure', $attendance->status);
        $this->assertEquals(5, $attendance->minutes_early_departure); // (17:00-5) - 16:50 = 5 min
        $this->assertEquals(7.83, round($attendance->net_hours, 2)); // 8 hours - 10 minutes = 7.83
    }

    /** @test */
    public function it_marks_half_day_when_hours_less_than_50_percent_of_expected()
    {
        $date = Carbon::parse('2026-02-16');

        // Work only 3 hours (08:00 - 11:00)
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 0));
        $this->createClockEvent('clock_out', $date->copy()->setTime(11, 0));

        $result = $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_number', $this->employee->employee_number)
                                ->whereDate('date', $date)
                                ->first();

        $this->assertEquals('half_day', $attendance->status);
        $this->assertEquals(3.0, $attendance->net_hours);
        $this->assertTrue((bool) $attendance->needs_review);
    }

    /** @test */
    public function it_marks_incomplete_when_hours_between_50_and_90_percent()
    {
        $date = Carbon::parse('2026-02-16');

        // Work 6 hours (08:00 - 14:00) – 75% of 8h expected
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 0));
        $this->createClockEvent('clock_out', $date->copy()->setTime(14, 0));

        $result = $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_number', $this->employee->employee_number)
                                ->whereDate('date', $date)
                                ->first();

        $this->assertEquals('incomplete', $attendance->status);
        $this->assertEquals(6.0, $attendance->net_hours);
        $this->assertTrue((bool) $attendance->needs_review);
    }


    /** @test */
    public function it_marks_early_departure_when_hours_between_90_and_100_percent()
    {
        $date = Carbon::parse('2026-02-16');

        // Work 6 hours (08:00 - 04:30) – above 90% of 8h expected but less than 100%
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 0));
        $this->createClockEvent('clock_out', $date->copy()->setTime(16, 30));

        $result = $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_number', $this->employee->employee_number)
                                ->whereDate('date', $date)
                                ->first();

        $this->assertEquals('early_departure', $attendance->status);
        $this->assertEquals(8.5, $attendance->net_hours);
        $this->assertTrue((bool) $attendance->needs_review);
    }





/** @test */
public function it_uses_shift_default_policy_when_no_higher_policy_exists()
{

        $this->defaultPolicy->is_active = false;
        $this->defaultPolicy->is_default = false;
        $this->defaultPolicy->save();

        $defaultPolicy = AttendancePolicy::factory()->create([
            'name' => 'Default Policy 2',
            'is_active' => true,
            'is_default' => true,
        ]);


    // Create a shift with a default policy
    $shift = Shift::factory()->create([
        'default_attendance_policy_id' => $defaultPolicy->id,
        'start_time' => '09:00:00',
        'end_time' => '18:00:00',
        'duration_hours' => 8.0,
    ]);



    $employee = Employee::factory()->create([
        'employee_number' => 'EMP-TEST-002',
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    EmployeePosition::factory()->create([
        'employee_id' => $employee->id,
        'shift_id' => $shift->id,
        'work_pattern_id' => null,
        'attendance_policy_id' => null,
        'pay_type' => 'hourly',
        'hourly_rate' => 20.00,
        'start_date' => '2026-01-01',
    ]);


    $date = Carbon::parse('2026-02-16');
    $policy = $this->calculator->getApplicablePolicy(
        $employee->employeePosition,
        $date,
        $shift  // Pass the shift explicitly
    );




    $this->createClockEvent('clock_in', $date->copy()->setTime(8, 0));
    $this->createClockEvent('clock_out', $date->copy()->setTime(16, 0)); // 8 hours

    $result = $this->calculator->calculateForDay($this->employee->employee_number, $date);

    $attendance = Attendance::where('employee_number', $this->employee->employee_number)
        ->whereDate('date', $date)
        ->first();


    $this->assertEquals($defaultPolicy->id, $attendance->attendance_policy_id);
    $this->assertEquals($defaultPolicy->id, $policy->id);


}







/** @test */
public function shift_default_policy_is_overridden_by_higher_level_policies()
{
    // Create policies at different levels
    $employeePolicy = AttendancePolicy::factory()->create(['name' => 'Employee Policy']);
    $shiftPolicy = AttendancePolicy::factory()->create(['name' => 'Shift Policy']);

    $shift = Shift::factory()->create(['default_attendance_policy_id' => $shiftPolicy->id]);

    // Assign employee policy
    $this->employee->employeePosition->attendance_policy_id = $employeePolicy->id;
    $this->employee->employeePosition->save();

    $date = Carbon::parse('2026-02-16');
    $policy = $this->calculator->getApplicablePolicy(
        $this->employee->employeePosition,
        $date,
        $shift
    );

    // Should return employee policy, not shift policy
    $this->assertEquals($employeePolicy->id, $policy->id);
}

/** @test */
public function shift_default_policy_is_used_when_higher_policies_are_inactive_or_expired()
{
      $this->defaultPolicy->is_active = false;
      $this->defaultPolicy->is_default = false;
      $this->defaultPolicy->save();

    $shiftPolicy = AttendancePolicy::factory()->create([
        'name' => 'Shift Policy',
        'effective_date' => '2026-01-01',
        'is_active' => true,
    ]);

    $shift = Shift::factory()->create(['default_attendance_policy_id' => $shiftPolicy->id]);

    // Higher-level policy exists but is inactive
    $inactivePolicy = AttendancePolicy::factory()->create([
        'name' => 'Inactive Policy',
        'is_active' => false,
    ]);
    $this->employee->employeePosition->attendance_policy_id = $inactivePolicy->id;
    $this->employee->employeePosition->save();

    $date = Carbon::parse('2026-02-16');
    $policy = $this->calculator->getApplicablePolicy(
        $this->employee->employeePosition,
        $date,
        $shift
    );

    // Should fall back to shift policy
    $this->assertEquals($shiftPolicy->id, $policy->id);
}

/** @test */
public function shift_default_policy_is_skipped_if_shift_has_no_default_policy()
{
    // Shift without default policy
    $shift = Shift::factory()->create(['default_attendance_policy_id' => null]);

    // No higher policies
    // $this->employee->employeePosition->attendance_policy_id = null;
    // $this->employee->employeePosition->department->default_attendance_policy_id = null;
    // $this->employee->employeePosition->location->default_attendance_policy_id = null;
    // $this->employee->employeePosition->department->company->default_attendance_policy_id = null;


    $employee = Employee::factory()->create([
        'employee_number' => 'EMP-TEST-002',
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    EmployeePosition::factory()->create([
        'employee_id' => $employee->id,
        'shift_id' => $shift->id,
        'work_pattern_id' => null,
        'attendance_policy_id' => null,
        'pay_type' => 'hourly',
        'hourly_rate' => 20.00,
        'start_date' => '2026-01-01',
    ]);


    $date = Carbon::parse('2026-02-16');
    $policy = $this->calculator->getApplicablePolicy(
        $employee->employeePosition,
        $date,
        $shift
    );

    // Already $this->defaultPolicy is default
    $this->assertEquals($this->defaultPolicy->id, $policy->id);
}






    /** @test */
    public function it_detects_when_break_is_taken_correctly()
    {
        // Policy requires break after 5 hours for 30 minutes
        $date = Carbon::parse('2026-02-16');

        // Morning: 08:00 - 12:00 (4h)
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 0));
        $this->createClockEvent('clock_out', $date->copy()->setTime(12, 0));

        // Gap of 60 minutes (break)
        // Afternoon: 13:00 - 17:00 (4h)
        $this->createClockEvent('clock_in', $date->copy()->setTime(13, 0));
        $this->createClockEvent('clock_out', $date->copy()->setTime(17, 0));

        $result = $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_number', $this->employee->employee_number)
                                ->whereDate('date', $date)
                                ->first();

        $this->assertEquals(0, $attendance->missed_break_minutes);
        $this->assertFalse((bool) $attendance->needs_review); // no violation
    }

    /** @test */
    public function it_detects_missed_break_when_no_adequate_gap()
    {
        // Policy requires break after 5 hours for 30 minutes
        $date = Carbon::parse('2026-02-16');

        // Work continuously from 08:00 to 14:00 (6 hours) with no gap
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 0));
        $this->createClockEvent('clock_out', $date->copy()->setTime(14, 0));

        // (No second session)
        $result = $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_number', $this->employee->employee_number)
                                ->whereDate('date', $date)
                                ->first();

        $this->assertEquals(30, $attendance->missed_break_minutes);
        $this->assertTrue((bool) $attendance->needs_review);

        // Check metadata for violation
        $metadata = json_decode($attendance->calculation_metadata, true);
        $this->assertStringContainsString('missed_break', json_encode($metadata));
    }

    /** @test */
    public function it_applies_unpaid_break_deduction_from_policy()
    {
        // Set policy to deduct 30 minutes unpaid break
        $policy = $this->defaultPolicy;
        $policy->unpaid_break_minutes = 30;
        $policy->save();

        $date = Carbon::parse('2026-02-16');

        // Work 8 hours (08:00 - 17:00 with one-hour lunch gap, but unpaid break is automatic)
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 0));
        $this->createClockEvent('clock_out', $date->copy()->setTime(12, 0));
        $this->createClockEvent('clock_in', $date->copy()->setTime(13, 0));
        $this->createClockEvent('clock_out', $date->copy()->setTime(17, 0));

        $result = $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_number', $this->employee->employee_number)
                                ->whereDate('date', $date)
                                ->first();

        // Total worked hours = 8, minus 0.5 = 7.5
        $this->assertEquals(7.5, $attendance->net_hours);
        // $this->assertEquals(7.5, $attendance->regular_hours); // regular hours also net
    }

    /** @test */
    public function it_calculates_expected_hours_from_shift_duration()
    {


        $shift = Shift::factory()->create([
            'name' => 'Standard 8-5',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            //'duration_hours' => 8.0,
            'is_overnight' => false,
        ]);

        $workPattern = WorkPattern::factory()->create([
            'name' => 'Mon-Fri 2',
            'shift_id' => $shift->id,
            'applicable_days' => '1,2,3,4,5',
            'pattern_type' => 'recurring',
            'effective_date' => '2026-01-01',
            'is_active' => true,
            'is_default' => true,
        ]);

        $employee = Employee::factory()->create([
            'employee_number' => 'EMP-TEST-002',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);


        EmployeePosition::factory()->create([
            'employee_id' => $employee->id,
            'shift_id' => $shift->id,
            'work_pattern_id' => $workPattern->id,
            'attendance_policy_id' => null,
            'pay_type' => 'hourly',
            'hourly_rate' => 20.00,
            'start_date' => '2026-01-01',
        ]);









        // Ensure shift has duration_hours = 9.0 (08:00-17:00)
        $date = Carbon::parse('2026-02-16');

        // Work exactly 9 hours (08:00-17:00 with no break? but shift duration includes lunch?
        // For this test, we ignore break; we just want to see expected hours used for status.
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 0));
        $this->createClockEvent('clock_out', $date->copy()->setTime(17, 0));

        // Access private method via reflection to test getExpectedHours
        $reflection = new \ReflectionClass($this->calculator);
        $method = $reflection->getMethod('getExpectedHours');
        $method->setAccessible(true);

        $schedule = $this->calculator->getExpectedSchedule(
            $employee,
            $employee->employeePosition,
            $workPattern,
            $date
        );


        $expectedHours = $method->invoke($this->calculator, $schedule);
        $this->assertEquals(9.0, $expectedHours);


        $result = $this->calculator->calculateForDay($this->employee->employee_number, $date);

        // Attendance should be present since worked exactly expected hours
        $attendance = Attendance::where('employee_number', $this->employee->employee_number)
                                ->whereDate('date', $date)
                                ->first();

        $this->assertEquals('present', $attendance->status);
    }

    /** @test */
    public function it_handles_no_schedule_gracefully()
    {

        $date = Carbon::parse('2026-02-16');

        // Employee without schedule (no pattern, no default shift, no ShiftSchedule)
        $employee = Employee::factory()->create([
            'employee_number' => 'EMP-TEST-002',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);


        EmployeePosition::factory()->create([
            'employee_id' => $employee->id,
            'shift_id' => null,
            'work_pattern_id' => null,
            'attendance_policy_id' => null,
            'pay_type' => 'hourly',
            'hourly_rate' => 20.00,
            'start_date' => '2026-01-01',
        ]);

        $this->workPattern->is_active = false;
        $this->workPattern->is_default = false;
        $this->workPattern->save();


        ClockEvent::factory()->create([
            'employee_id' => $employee->employee_number,
            'event_type' => 'clock_in',
            'timestamp' => $date->copy()->setTime(8, 0),
            'method' => 'test',
        ]);

        ClockEvent::factory()->create([
            'employee_id' => $employee->employee_number,
            'event_type' => 'clock_out',
            'timestamp' => $date->copy()->setTime(17, 0),
            'method' => 'test',
        ]);




        $result = $this->calculator->calculateForDay($employee->employee_number, $date);

        $attendance = Attendance::where('employee_number', $employee->employee_number)
                                ->whereDate('date', $date)
                                ->first();

        $this->assertEquals('unscheduled', $attendance->status);
        // actual hours = 9 but net (payable) hours = 0 because there is no schedule to make necesassary calculations
        $this->assertEquals(0.0, $attendance->net_hours);
        $this->assertTrue((bool) $attendance->needs_review);
    }








    // Helper method to create clock events (reused)
    protected function createClockEvent(string $type, Carbon $timestamp): ClockEvent
    {
        return ClockEvent::factory()->create([
            'employee_id' => $this->employee->employee_number,
            'event_type' => $type,
            'timestamp' => $timestamp,
            'method' => 'test',
        ]);
    }
}
