<?php

namespace App\Listeners\Illuminate\Auth\Listeners;

use Illuminate\Auth\Events\History;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\History as Log;

class OrderLogs
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
     * @param  History  $event
     * @return void
     */
    public function handle(History $event)
    {

        $history = new Log;


        if (get_class($event) == "App\Models\User"){
            $history->history = $event->name." ordered for a dispatch";
            $history->user_id = $event->id;
        }else {
            $history->history = "Order was completed by ".$event->name;
            $history->partner_id = $event->id;
        }

        $history->save();

        return $history;
    }
}
