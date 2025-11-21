<?php

namespace App\Modules\Hr\Listeners;

use App\Modules\System\Events\DataTableFormEvent;
use App\Modules\System\Listeners\DatatableFormListener;

use  App\Modules\Hr\Http\Controllers\PayrollRunController;
use App\Modules\Hr\Models\PayrollRun;


class PayrollRunEventListener extends DatatableFormListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(DataTableFormEvent $event): void
    {
        $this->handleModelEvent($event);

    }

    protected function handleModelEvent($event) {

        if (!str_contains($event->model, "PayrollRun")
        ) {
            return;
        }

        if ($event->eventName == "AfterCreate" || $event->eventName == "AfterUpdate"
            || $event->eventName == "created" || $event->eventName == "updated"
        )  {
            $this->handleStatusChange($event);
        }
    }



    protected function handleStatusChange($event)
    {

        if (isset($event->newRecord) &&  isset($event->newRecord["status"])) {
            switch ($event->newRecord["status"]) {
                case "approved":
                    //PayrollRun $payrollRun, Request $request)
                    $payrollRunController = new PayrollRunController();
                    $payrollRunController->approve(PayrollRun::findOrFail($event->newRecord["id"]), request());
            }

        }


    }




}
