<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use App\Repositories\CrmReportsRepository;
use Illuminate\Support\Facades\Storage;

class UpdateReportLeads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:report-leads';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Task for update leads to get uopdated report leads';
    protected $crmReportsRepository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(CrmReportsRepository $crmReportsRepository)
    {
        parent::__construct();
        $this->crmReportsRepository = $crmReportsRepository;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $message = "Cron Job update report leads executed.";
        try {
            $this->crmReportsRepository->updateLeadsReport(true);
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        $log = "[" . date('Y-m-d H:i:s') . "] Update report leads: " . $message;
        Storage::append("cron_job_log_update_report_leads.txt", $log);
    }
}
