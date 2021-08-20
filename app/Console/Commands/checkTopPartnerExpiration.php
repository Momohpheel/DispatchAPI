<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Partner;

class checkTopPartnerExpiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'toppartner:expiry';

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
        \Log::info("Check TopPartner Subscription Cron is working fine!");
        foreach ($partners as $partner){
            if ($partner->is_top_partner && $partner->top_partner_expiry_date == Carbon::now()->toDateString()){

                $partner->is_top_partner = 0;
                $partner->top_partner_expiry_date = null;

                $partner->save();
            }
        }
    }
}
