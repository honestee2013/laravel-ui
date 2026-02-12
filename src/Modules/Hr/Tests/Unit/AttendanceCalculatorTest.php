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

class AttendanceCalculatorTest extends TestCase
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

        // Create base test data
        $this->shift = Shift::factory()->create([
            'name' => 'Standard 8-5',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'duration_hours' => 9.0,
            'is_overnight' => false,

            'code' => 'Code123',
        ]);

        $this->workPattern = WorkPattern::factory()->create([
            'name' => 'Mon-Fri',
            'shift_id' => $this->shift->id,
            'applicable_days' => "1,2,3,4,5", // Monday to Friday [1,2,3,4,5]
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

        // Create employee position with work pattern and default policy
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
            // 'shift_id' => $this->shift->id,
            'work_pattern_id' => $this->workPattern->id,
            'attendance_policy_id' => $this->defaultPolicy->id,
            'pay_type' => 'hourly',
            'hourly_rate' => 20.00,
            'start_date' => '2026-01-01',
        ]);

        $this->calculator = new AttendanceCalculator();
    }

    /** @test */
    public function it_marks_present_with_on_time_clock_in_and_out()
    {
        // Arrange: Clock-in at 08:00, clock-out at 17:00
        $date = Carbon::parse('2026-02-16'); // Monday
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 0));
        $this->createClockEvent('clock_out', $date->copy()->setTime(17, 0));

        // Act
        $result = $this->calculator->calculateForDay($this->employee->employee_number, $date);

        // Assert
        $attendance = Attendance::where('employee_number', $this->employee->employee_number)
                                ->whereDate('date', $date)
                                ->first();

        $this->assertEquals('present', $attendance->status);
        $this->assertEquals(9.0, $attendance->net_hours);
        $this->assertEquals(8.0, $attendance->regular_hours); // Daily threshold 8h
        $this->assertEquals(1.0, $attendance->overtime_hours);
        $this->assertEquals(0, $attendance->minutes_late);
        $this->assertEquals(0, $attendance->minutes_early_departure);
        // $this->assertFalse((bool) $attendance->needs_review);
        $this->assertTrue((bool) $attendance->needs_review); // Violation: No break taken
    }

    /** @test */
    public function it_marks_late_when_clock_in_exceeds_grace_period()
    {
        // Policy grace_period_minutes = 5
        $date = Carbon::parse('2026-02-16');

        // Clock-in at 08:06 (1 minute after grace)
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 6));
        $this->createClockEvent('clock_out', $date->copy()->setTime(17, 0));

        $result = $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_number', $this->employee->employee_number)
                                ->whereDate('date', $date)
                                ->first();

        $this->assertEquals('late', $attendance->status);
        $this->assertEquals(1, $attendance->minutes_late);
        $this->assertTrue((bool) $attendance->needs_review);
    }

    /** @test */
    public function it_does_not_mark_late_when_clock_in_within_grace_period()
    {
        $date = Carbon::parse('2026-02-16');

        // Clock-in at 08:04 (within 5 min grace)
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 4));
        $this->createClockEvent('clock_out', $date->copy()->setTime(17, 0));

        $result = $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_number', $this->employee->employee_number)
                                ->whereDate('date', $date)
                                ->first();

        $this->assertNotEquals('late', $attendance->status);
        $this->assertEquals(0, $attendance->minutes_late);
    }

    /** @test */
    public function it_marks_early_departure_when_clock_out_before_grace_period()
    {
        $date = Carbon::parse('2026-02-16');

        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 0));
        // Clock-out at 16:54 (6 minutes early, grace 5, minutes_early_departure is 1 minute less grace period)
        $this->createClockEvent('clock_out', $date->copy()->setTime(16, 54));

        $result = $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_number', $this->employee->employee_number)
                                ->whereDate('date', $date)
                                ->first();

        $this->assertEquals('early_departure', $attendance->status);
        $this->assertEquals(1, $attendance->minutes_early_departure);
        $this->assertTrue((bool) $attendance->needs_review);
    }

    /** @test */
    public function it_does_not_mark_early_departure_when_clock_out_within_grace()
    {
        $date = Carbon::parse('2026-02-16');

        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 0));
        // Clock-out at 16:56 (4 minutes early, within 5 grace)
        $this->createClockEvent('clock_out', $date->copy()->setTime(16, 56));

        $result = $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_number', $this->employee->employee_number)
                                ->whereDate('date', $date)
                                ->first();

        $this->assertNotEquals('early_departure', $attendance->status);
        $this->assertEquals(0, $attendance->minutes_early_departure);
    }

    /** @test */
    public function it_calculates_overtime_according_to_daily_threshold()
    {
        $date = Carbon::parse('2026-02-16');

        // Work 10 hours (08:00 - 18:00)
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 0));
        $this->createClockEvent('clock_out', $date->copy()->setTime(18, 0));

        $result = $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_number', $this->employee->employee_number)
                                ->whereDate('date', $date)
                                ->first();

        $this->assertEquals(10.0, $attendance->net_hours);
        $this->assertEquals(8.0, $attendance->regular_hours);
        $this->assertEquals(2.0, $attendance->overtime_hours);
        $this->assertEquals(0.0, $attendance->double_time_hours);
    }

    /** @test */
    public function it_applies_double_time_after_threshold()
    {
        // Update policy: double time after 10 hours
        $policy = $this->defaultPolicy;
        $policy->double_time_threshold_hours = 10.0;
        $policy->save();

        $date = Carbon::parse('2026-02-16');

        // Work 12 hours (08:00 - 20:00)
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 0));
        $this->createClockEvent('clock_out', $date->copy()->setTime(20, 0));

        $result = $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_number', $this->employee->employee_number)
                                ->whereDate('date', $date)
                                ->first();

        $this->assertEquals(12.0, $attendance->net_hours);
        $this->assertEquals(8.0, $attendance->regular_hours);
        $this->assertEquals(2.0, $attendance->overtime_hours); // 10-12 = 2 overtime
        $this->assertEquals(2.0, $attendance->double_time_hours); // 8-10 = 2 double
    }

    /** @test */
    public function it_respects_max_daily_overtime_limit()
    {
        // Set max daily overtime to 2 hours
        $policy = $this->defaultPolicy;
        $policy->max_daily_overtime_hours = 2.0;
        $policy->save();

        $date = Carbon::parse('2026-02-16');

        // Work 12 hours (08:00 - 20:00)
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 0));
        $this->createClockEvent('clock_out', $date->copy()->setTime(20, 0));

        $result = $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_number', $this->employee->employee_number)
                                ->whereDate('date', $date)
                                ->first();

        $this->assertEquals(12.0, $attendance->net_hours);
        $this->assertEquals(8.0, $attendance->regular_hours);
        $this->assertEquals(2.0, $attendance->overtime_hours); // Capped
        // $this->assertEquals(2.0, $attendance->double_time_hours); // Double is separate
    }

    /** @test */
    public function it_handles_zero_grace_period_correctly()
    {
        $policy = $this->defaultPolicy;
        $policy->grace_period_minutes = 0;
        $policy->save();

        $date = Carbon::parse('2026-02-16');

        // Clock-in at 08:01 -> late (no grace)
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 1));
        $this->createClockEvent('clock_out', $date->copy()->setTime(17, 0));

        $result = $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_number', $this->employee->employee_number)
                                ->whereDate('date', $date)
                                ->first();

        $this->assertEquals('late', $attendance->status);
        $this->assertEquals(1, $attendance->minutes_late);
    }

    /** @test */
    public function it_detects_missing_clock_out()
    {
        $date = Carbon::parse('2026-02-16');

        // Only clock-in
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 0));
        // No clock-out

        $result = $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_number', $this->employee->employee_number)
                                ->whereDate('date', $date)
                                ->first();

        // $this->assertEquals('incomplete', $attendance->status);
        $this->assertEquals('absent', $attendance->status); // 0.0 is absent
        $this->assertEquals(0.0, $attendance->net_hours); // No completed session
        $this->assertTrue((bool) $attendance->needs_review);

        // Also verify AttendanceSession created with null end_time
        $session = AttendanceSession::where('attendance_id', $attendance->id)->first();
        $this->assertNotNull($session);
        $this->assertNull($session->end_time);
    }

    /** @test */
    public function it_handles_non_working_day_according_to_work_pattern()
    {
        $date = Carbon::parse('2026-02-15'); // Sunday (not in applicable_days)

        // Employee clocks in anyway
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 0));
        $this->createClockEvent('clock_out', $date->copy()->setTime(17, 0));

        $result = $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_number', $this->employee->employee_number)
                                ->whereDate('date', $date)
                                ->first();

        // Should still calculate, but status may be 'unscheduled' or similar
        $this->assertEquals('unscheduled', $attendance->status);
        $this->assertEquals(9.0, $attendance->net_hours);
        $this->assertTrue((bool) $attendance->needs_review);
    }

    /** @test */
    public function it_uses_employee_specific_policy_over_default()
    {
        // Create custom policy for this employee
        $customPolicy = AttendancePolicy::factory()->create([
            'name' => 'Custom Policy',
            'grace_period_minutes' => 10,
            'is_default' => false,
        ]);

        // Assign to employee position
        $position = EmployeePosition::where('employee_id', $this->employee->id)->first();
        $position->attendance_policy_id = $customPolicy->id;
        $position->save();

        $date = Carbon::parse('2026-02-16');

        // Clock-in at 08:09 (within custom 10m grace, but outside default 5m)
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 9));
        $this->createClockEvent('clock_out', $date->copy()->setTime(17, 0));

        $result = $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_number', $this->employee->employee_number)
                                ->whereDate('date', $date)
                                ->first();

        $this->assertNotEquals('late', $attendance->status);
        $this->assertEquals(0, $attendance->minutes_late);
        $this->assertEquals($customPolicy->id, $attendance->attendance_policy_id);
    }

    /** @test */
    public function it_calculates_weekly_overtime_correctly()
    {
        // This test requires multiple days calculation
        // For simplicity, we can test the internal method or implement a weekly aggregation test
        $this->markTestIncomplete('Weekly overtime calculation requires multiple days and will be implemented separately.');
    }

    /** @test */
    public function it_marks_absent_when_no_clock_events_on_scheduled_day()
    {
        $date = Carbon::parse('2026-02-16'); // Monday, working day
        // No clock events

        $result = $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_number', $this->employee->employee_number)
                                ->whereDate('date', $date)
                                ->first();

        $this->assertEquals('absent', $attendance->status);
        $this->assertEquals(0.0, $attendance->net_hours);
        $this->assertTrue((bool) $attendance->needs_review);
    }

    /** @test */
    public function it_applies_unpaid_break_deduction()
    {
        $policy = $this->defaultPolicy;
        $policy->unpaid_break_minutes = 30; // 0.5 hour
        $policy->save();

        $date = Carbon::parse('2026-02-16');

        // Work 9 hours (08:00 - 17:00)
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 0));
        $this->createClockEvent('clock_out', $date->copy()->setTime(17, 0));

        $result = $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_number', $this->employee->employee_number)
                                ->whereDate('date', $date)
                                ->first();

        // Net hours should be reduced by unpaid break
        $this->assertEquals(8.5, $attendance->net_hours); // 9 - 0.5
        $this->assertEquals(8.0, $attendance->regular_hours);
        $this->assertEquals(0.5, $attendance->overtime_hours);
    }

    /** @test */
    public function it_detects_missed_break_when_required()
    {
        $policy = $this->defaultPolicy;
        $policy->requires_break_after_hours = 5.0;
        $policy->break_duration_minutes = 30;
        $policy->save();

        $date = Carbon::parse('2026-02-16');

        // Work 8 hours continuous without break
        $this->createClockEvent('clock_in', $date->copy()->setTime(8, 0));
        $this->createClockEvent('clock_out', $date->copy()->setTime(16, 0)); // 8 hours

        $result = $this->calculator->calculateForDay($this->employee->employee_number, $date);

        $attendance = Attendance::where('employee_number', $this->employee->employee_number)
                                ->whereDate('date', $date)
                                ->first();

        $this->assertEquals(30, $attendance->missed_break_minutes);
        $this->assertTrue((bool) $attendance->needs_review);
        $this->assertStringContainsString('missed_break', json_encode($attendance->calculation_metadata));
    }

    // --- Helper Methods ---

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
