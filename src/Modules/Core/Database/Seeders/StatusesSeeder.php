<?php

namespace App\Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Class StatusesSeeder
 *
 * This seeder populates the statuses table with a variety of status types.
 * The statuses are categorized into general workflow, HR-specific, finance,
 * sales/orders, support/ticketing, IT/infrastructure, and miscellaneous.
 */

class StatusesSeeder extends Seeder
{
    public function run()
    {
        /*$statuses = [
            // General workflow
            ['name' => 'Draft', 'description' => 'Initial unsubmitted version.', 'editable' => false],
            ['name' => 'Pending', 'description' => 'Waiting for action or approval.', 'editable' => false],
            ['name' => 'Submitted', 'description' => 'Formally sent for consideration.', 'editable' => false],
            ['name' => 'Approved', 'description' => 'Reviewed and authorized.', 'editable' => false],
            ['name' => 'Rejected', 'description' => 'Reviewed and not accepted.', 'editable' => false],
            ['name' => 'On Hold', 'description' => 'Paused temporarily.', 'editable' => false],
            ['name' => 'In Progress', 'description' => 'Currently being worked on.', 'editable' => false],
            ['name' => 'Completed', 'description' => 'Process or task is finished.', 'editable' => false],
            ['name' => 'Canceled', 'description' => 'Terminated before completion.', 'editable' => false],

            // HR-specific
            ['name' => 'Interview Scheduled', 'description' => 'Interview is planned.', 'editable' => false],
            ['name' => 'Hired', 'description' => 'Candidate accepted and onboarded.', 'editable' => false],
            ['name' => 'Rejected - HR', 'description' => 'Not selected by HR team.', 'editable' => false],
            ['name' => 'Terminated', 'description' => 'Employment ended.', 'editable' => false],
            ['name' => 'Resigned', 'description' => 'Employee voluntarily left.', 'editable' => false],
            ['name' => 'Resigned', 'description' => 'Employee voluntarily left.', 'editable' => false],

            // Finance
            ['name' => 'Paid', 'description' => 'Payment completed.', 'editable' => false],
            ['name' => 'Unpaid', 'description' => 'Payment not made.', 'editable' => false],
            ['name' => 'Partially Paid', 'description' => 'Payment partially settled.', 'editable' => false],
            ['name' => 'Overdue', 'description' => 'Payment is past due date.', 'editable' => false],

            // Sales/Orders
            ['name' => 'Processing', 'description' => 'Order being processed.', 'editable' => false],
            ['name' => 'Shipped', 'description' => 'Item dispatched to customer.', 'editable' => false],
            ['name' => 'Delivered', 'description' => 'Item delivered to customer.', 'editable' => false],
            ['name' => 'Returned', 'description' => 'Item returned by customer.', 'editable' => false],
            ['name' => 'Refunded', 'description' => 'Amount refunded to customer.', 'editable' => false],

            // Support/Ticketing
            ['name' => 'Open', 'description' => 'Ticket is active.', 'editable' => false],
            ['name' => 'In Review', 'description' => 'Ticket under investigation.', 'editable' => false],
            ['name' => 'Resolved', 'description' => 'Issue resolved.', 'editable' => false],
            ['name' => 'Closed', 'description' => 'Ticket closed.', 'editable' => false],

            // IT/Infrastructure
            ['name' => 'Under Maintenance', 'description' => 'System is down for maintenance.', 'editable' => false],
            ['name' => 'Operational', 'description' => 'System is up and running.', 'editable' => false],
            ['name' => 'Degraded', 'description' => 'Service is slow or impaired.', 'editable' => false],
            ['name' => 'Down', 'description' => 'System is not accessible.', 'editable' => false],

            // Miscellaneous
            ['name' => 'Scheduled', 'description' => 'Planned for future execution.', 'editable' => false],
            ['name' => 'Error', 'description' => 'Unexpected issue occurred.', 'editable' => false],
            ['name' => 'Escalated', 'description' => 'Issue raised to higher authority.', 'editable' => false],
        ];

        DB::table('statuses')->insert($statuses);*/
    }
}




