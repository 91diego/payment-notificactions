<?php

namespace App\Traits;

use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

trait NeodataTrait
{

    protected $notificationService;
    /**
     * Constructor
     */
    Public function __construct(NotificationService $notificationService)
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
     * @param type $type, tipo de correo
     */
    public function getNeodataPayments($type, $database) {
        try {
            // Get Connection
            $connection = $this->setConnection($database);
            // dd([$type, $database, $connection]);
            // $payments = DB::connection($connection)->select('Select * from dbo.fnPortalWebCredito(?) as credito');
            return $this->notificationService->store($connection);
            // TODO, send payments to notifications store method
        } catch (\Exception $e) {
            return $e;
        }
    }
}
