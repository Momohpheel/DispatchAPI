<?php

namespace App\Listeners\Illuminate\Auth\Events;

use Illuminate\Auth\Events\UserPayment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UserPayment
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
     * @param  UserPayment  $event
     * @return void
     */
    public function handle(UserPayment $event)
    {
        //
    }
}
