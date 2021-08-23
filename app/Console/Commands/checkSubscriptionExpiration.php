<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Partner;
use Carbon\Carbon;

class checkSubscriptionExpiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $partners = Partner::all();
        \Log::info("CheckSubscription Cron is working fine!");
        foreach ($partners as $partner){
            if (isset($partner->subscription_expiry_date) && $partner->subscription_expiry_date == Carbon::now()){

                $partner->order_count_per_day = 0;
                $partner->subscription_expiry_date = null;
                $partner->subscription_date = null;
                $partner->subscription_status = 'not paid';

                $partner->save();
            }
        }
    }
}
