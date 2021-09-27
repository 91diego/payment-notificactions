<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait NeodataTrait
{
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
            $payments = DB::connection($connection)->select('Select * from dbo.fnPortalWebCredito(?) as credito');
            // TODO, send payments to notifications store method
        } catch (\Exception $e) {
            //throw $th;
        }
    }
}
