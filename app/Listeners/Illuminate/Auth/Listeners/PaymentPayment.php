<?php

namespace App\Listeners\Illuminate\Auth\Listeners;

use Illuminate\Auth\Events\PartnerPayment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PaymentPayment
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
     * @param  PartnerPayment  $event
     * @return void
     */
    public function handle(PartnerPayment $event)
    {
        //
    }
}
