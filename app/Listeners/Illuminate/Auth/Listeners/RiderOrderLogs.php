<?php

namespace App\Listeners\Illuminate\Auth\Listeners;

use Illuminate\Auth\Events\RiderHistory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RiderOrderLogs
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
     * @param  RiderHistory  $event
     * @return void
     */
    public function handle(RiderHistory $event)
    {
        //
    }
}
