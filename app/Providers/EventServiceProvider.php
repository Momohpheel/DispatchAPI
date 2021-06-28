<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\History;
use Illuminate\Auth\Events\RiderHistory;
use Illuminate\Auth\Events\PartnerHistory;
use Illuminate\Auth\Events\UserPayment;
use Illuminate\Auth\Events\PartnerPayment;
use Illuminate\Auth\Events\GlobalLog;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Auth\Listeners\WalletLogs;
use Illuminate\Auth\Listeners\TransactionLogs;
use Illuminate\Auth\Listeners\OrderLogs;
use Illuminate\Auth\Listeners\RiderOrderLogs;
use Illuminate\Auth\Listeners\PartnerOrderLogs;
use Illuminate\Auth\Listeners\UserPayment as Payment;
use Illuminate\Auth\Listeners\PaymentPayment;
use Illuminate\Auth\Listeners\GlobalHistory;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        History::class => [
            OrderLogs::class,
        ],
        RiderHistory::class => [
            RiderOrderLogs::class,
        ],
        PartnerHistory::class => [
            PartnerOrderLogs::class,
        ],
        UserPayment::class => [
            UserPayment::class,
        ],
        PartnerPayment::class => [
            PaymentPayment::class,
        ],
        GlobalLog::class => [
            GlobalHistory::class,
        ]

    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
