<?php

namespace App\Listeners\Illuminate\Auth\Listeners;

use Illuminate\Auth\Events\GlobalLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class GlobalHistory
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
     * @param  GlobalLog  $event
     * @return void
     */
    public function handle(GlobalLog $event)
    {
        //
    }
}
