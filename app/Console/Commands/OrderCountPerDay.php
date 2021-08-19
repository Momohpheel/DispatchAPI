<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Partner;

class OrderCountPerDay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:count';

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
        \Log::info("Cron is working fine!");
        foreach ($partners as $partner){
            if (isset($partner->subscription_id) && $partner->subscription_status == 'paid'){
                if ($partner->subscription_id == 1){
                    $partner->order_count_per_day += 5;
                }else if ($partner->subscription_id == 2){
                    $partner->order_count_per_day += 15;
                }else if ($partner->subscription_id == 3){
                    $partner->order_count_per_day += 25;
                }else {
                    $partner->order_count_per_day = 'unlimited';
                }

                $partner->save();
            }
        }
    }
}
