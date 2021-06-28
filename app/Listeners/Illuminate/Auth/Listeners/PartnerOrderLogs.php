<?php

namespace App\Listeners\Illuminate\Auth\Listeners;

use Illuminate\Auth\Events\PartnerHistory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PartnerOrderLogs
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  PartnerHistory  $event
     * @return void
     */
    public function handle(PartnerHistory $event)
    {
        //
    }
}
