<?php

namespace App\Modules\Core\Listeners;

use App\Modules\Core\Events\DataTableFormEvent;
use App\Modules\Core\Models\Status;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Modules\Production\Models\ProductionProcessLog;
use  App\Modules\Production\Events\ProductionProcessLogEvent;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

 class DatatableFormListener
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
     public function handle(DataTableFormEvent $event) {
        
     }







}
