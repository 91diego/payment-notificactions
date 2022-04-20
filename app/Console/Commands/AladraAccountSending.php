<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class AladraAccountSending extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'account-sending:aladra';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send customer account to ALADRA customers';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $message = "ALADRA CRONJOB EXECUTED SUCCESFULLY!!!";
        try {
            $development = [
                "name" => "ALADRA"
            ];
            $developments = $this->notificationService->index($development);
            $this->notificationService->store($developments);
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        $log = "[" . date('Y-m-d H:i:s') . "] LOG ALADRA EMAIL ACCOUNT SENDING: " . $message;
        Storage::append("log_aladra_email_account_sending.txt", $log);
    }
}
