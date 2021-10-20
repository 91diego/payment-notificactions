<?php

namespace App\Console\Commands;

use App\Models\Lead;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class UpdateLeads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:leads';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Task for update leads from report lead';

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
        $message = "ALL IS OK!!";
        try {
            $leadsRecordsDb = count(Lead::all());
            $dealsUrl = Http::get("$this->bitrixSite$this->bitrixToken/crm.lead.list?FILTER[>DATE_CREATE]=2020-07-31T23:59:59-05:00");
            $jsonDeals = $dealsUrl->json();
            $bitrixLeads = $jsonDeals['total'] + 1;
            $this->addLead($leadsRecordsDb, $bitrixLeads);
            $this->updateLead('LEADS');

        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        $log = "[" . date('Y-m-d H:i:s') . "] Cron Job Update Leads Log: " . $message;
        Storage::append("log_leads_report.txt", $log);
    }
}
