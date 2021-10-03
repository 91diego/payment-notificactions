<?php

namespace App\Traits;

use App\Services\NotificationService;
use App\Repositories\NotificationRepository;
use Illuminate\Support\Facades\DB;

trait NeodataTrait
{

    protected $notificationService;
    /**
     * Constructor
     */
    Public function __construct(NotificationRepository $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * @param request $request
     */
    public function setConnection($request)
    {
        switch ($request) {

            case 'BRASILIA':
                $connection = 'sqlsrv_brasilia';
                break;

            case 'ANUVA':
                $connection = 'sqlsrv_anuva';
                break;

            case 'ALADRA':
                $connection = 'sqlsrv_aladra';
                break;
        }
        return $connection;
    }

    /**
     * Get neodata payments
     * @param notificationCases $notificationCases
     */
    public function getNeodataPayments($notificationCases, $database) {
        try {
            // Get Connection
            $connection = $this->setConnection($database);
            $customerPayments = DB::connection($connection)->select('Select * from dbo.fnNotificaciones()');
            return ['notification_cases' => $notificationCases, 'customer_payments' => $customerPayments];
        } catch (\Error $err) {
            return $err;
        }
    }
}
