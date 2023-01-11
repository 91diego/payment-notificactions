<?php

namespace App\Repositories;

use Exception;
use App\Traits\BitrixTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DealRepository
{

    /**
     * return deal stage
     */
    public function getStageByDealId($id)
    {
        // UF_CRM_1560978472415 -> Esquema de compra
        $deal = $this->getDealInformation($id);
        return $this->getStages($deal['STAGE_ID']);
    }

    /**
     * Get bitrix deal information
     * @param id $id
     */
    public function getDealInformation($id)
    {
        try {
            $dealUrl = Http::get("https://intranet.idex.cc/rest/1/evcwp69f5yg7gkwc/crm.deal.get?ID=$id");
            // $dealUrl = Http::get(env('BITRIX_SITE') . "/rest/1/" . env('BITRIX_TOKEN') . "/crm.deal.get?ID=" . $id);
            $jsonDeal = $dealUrl->json();
            return $jsonDeal['result'];
        } catch (\Exception $e) {
            return $e;
        }
    }

    /**
     * Get deal stages names
     * @param stageId $id
     */
    public function getStages($id)
    {
        $fields = Http::get("https://intranet.idex.cc/rest/1/evcwp69f5yg7gkwc/crm.status.list");
        $jsonFields = $fields->json();
        for ($i = 0; $i < count($jsonFields['result']); $i++) {
            if ($jsonFields['result'][$i]['STATUS_ID'] == $id) {
                return $this->mapStages($jsonFields['result'][$i]['NAME']);
            }
        }
        return 'Sin etapa';
    }

    /**
     * Map payment method to cash or credit method
     */
    public function mapStages($stageId)
    {
        $stages = [
            'Vivienda en Proceso', 'Actualización de documentos',
            'Crédito Autorizado', 'Avaluo',
            'Proceso de Escrituracion', 'Escriturada',
            'Liberación para Entrega', 'Programada para Entrega'
        ];

        foreach ($stages as $value) {
            if ($stageId == $value) {
                return $value;
            }
        }
        return 'Etapa no configurada';
    }

    public function b24StatusPosition($stage)
    {
        $statusNumber = '';
        switch ($stage) {
            case 'Vivienda en Proceso':
                $statusNumber = 1;
                break;

            case 'Actualización de documentos':
                $statusNumber = 2;
                break;

            case 'Crédito Autorizado':
                $statusNumber = 3;
                break;

            case 'Avaluo':
                $statusNumber = 4;
                break;

            case 'Proceso de Escrituracion':
                $statusNumber = 5;
                break;

            case 'Escriturada':
                $statusNumber = 6;
                break;

            case 'Liberación para Entrega':
                $statusNumber = 7;
                break;

            case 'Programada para Entrega':
                $statusNumber = 8;
                break;
            default:
                $statusNumber = 0;
                break;
        }
        return $statusNumber;
    }


    public function setConnection($request)
    {
        switch ($request) {
            case 'PORTAL_WEB':
                $connection = 'mysql_portal_web';
                break;
        }
        return $connection;
    }

    /**
     * setStage call external api to set status
     * @param  \Illuminate\Http\Request  $request
     */
    public function getDealStage($request)
    {
        $id = $request->id;
        try {
            $message = 'No se ha modificado el deal ya que no existe una etapa asignada.';
            $dealStage= $this->getStageByDealId($id);
            if ($dealStage != 'Sin etapa') {
                $statusNumber = $this->b24StatusPosition($dealStage);
                $message = "La etapa del deal $id ha sido cambiada a $dealStage.";
                $connection = $this->setConnection('PORTAL_WEB');
                // $res = DB::connection($connection)->update("UPDATE deals SET status = '$dealStage', status_number = $statusNumber where deal_id = ?", [$id]);
                // $res = DB::connection($connection)->update('UPDATE deals SET status = ?, status_number = ? where deal_id = ?', ["$dealStage" , $statusNumber , $id]);
                $res = DB::connection($connection)->update(DB::raw('UPDATE deals SET status = "' . $dealStage . '", status_number = ' . $statusNumber . ' where deal_id = ' . $id));
                if (!$res)
                {
                    $message = "La etapa del deal $id no ha sido modificada";
                }
            }
            $log = date("Y-m-d H:i:s") . ", $message";
            Storage::append('cambio_etapa_deal_portal_web.txt', $log);
        } catch (Exception $e) {
            $message = "¡Ha ocurrido un error! " . $e->getMessage();
        }
        return $message;
    }
}
