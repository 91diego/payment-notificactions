<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use App\Repositories\NotificationRepository;
use Illuminate\Support\Facades\Storage;

class AccountSendingAnuva extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:anuva';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send customer account to ANUVA customers';
    protected $notificationRepository;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(NotificationRepository $notificationRepository)
    {
        parent::__construct();
        $this->notificationRepository = $notificationRepository;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $message = "CRONJOB EXECUTED !!!";
        try {
            $developments = $this->notificationRepository->index("ANUVA");
            $this->notificationRepository->store($developments);
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        $log = "[" . date('Y-m-d H:i:s') . "] LOG ANUVA EMAIL ACCOUNT SENDING: " . $message;
        Storage::append("log_cron_job_ANUVA.txt", $log);
    }
}
