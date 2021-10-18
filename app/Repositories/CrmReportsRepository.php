<?php
namespace App\Repositories;

use App\Models\Lead;
use App\Traits\BitrixTrait;
use Exception;
use Illuminate\Support\Facades\Http;

class CrmReportsRepository
{

    use BitrixTrait;
    private $bitrixSite;
    private $bitrixToken;

    /**
     * Constructor of BitrixTrait
     * Initialize bitrix api credentials from .ENV
     */
    public function __construct()
    {
        $this->bitrixSite = env('BITRIX_SITE', 'https://intranet.idex.cc/rest/1/');
        $this->bitrixToken = env('BITRIX_TOKEN', 'evcwp69f5yg7gkwc');
    }

    /**
     * Generate leads report
     * @param phase $phase
     * PROSPECTO ASIGNADO (STATUS_ID) -> IN_PROCESS
     * PROSPECTO EN SEGUIMIENTO (STATUS_ID) -> 3
     * DUPLICADOS (STATUS_ID) -> 5
     * PENDIENTE (STATUS_ID) -> 4
     * NO CALIFICA (STATUS_ID) -> JUNK
     * CALIFICADO (STATUS_ID) -> CONVERTED
     */
    public function createLeadsReport($phase)
    {
        return $this->getLeads($phase);
    }

    /**
     * Update leads report
     */
    public function updateLeadsReport()
    {
        $leadsRecordsDb = count(Lead::all());
        // $dealsUrl = Http::get("$this->bitrixSite$this->bitrixToken/crm.lead.list?start=0&FILTER[STATUS_ID]=3");
        $dealsUrl = Http::get("$this->bitrixSite$this->bitrixToken/crm.lead.list");
        $jsonDeals = $dealsUrl->json();
        $bitrixLeads = $jsonDeals['total'] + 1;
        $this->addLead($leadsRecordsDb, $bitrixLeads);
        return $this->updateLead('LEADS');
    }

    /**
     * Generate comercial report
     */
    public function comercialReport()
    {
        // Arreglo con los datos especificos
        $deals = [];
        $stagesCatalog = [];
        array_push($stagesCatalog, $this->stages());
        // Numero de registros mostrados por peticion
        $rows = 50;
        // Muestra el catalogo de etapas
        $stageCatalogUrl = "$this->bitrixSite$this->bitrixToken/crm.dealcategory.stage.list";
        $stageCatalogResult = file_get_contents($stageCatalogUrl);
        $jsonCatalog = json_decode($stageCatalogResult, true);

        // ITERACION DE LAS ETAPAS QUE INTERESAN PARA LOS COMENTARIOS
        for ($stage = 0;$stage < count($stagesCatalog[0]);$stage++)
        {
            // Primer registro para mostrar en la peticion
            $firstRow = 0;
            $dealsUrl = "$this->bitrixSite$this->bitrixToken/crm.deal.list?start=" . $firstRow . "&FILTER[STAGE_ID]=" . $stagesCatalog[0][$stage]["id"];
            $dealsResult = file_get_contents($dealsUrl);
            $jsonDeals = json_decode($dealsResult, true);
            switch ($stagesCatalog[0][$stage]["id"])
            {
                case 'NEW':
                    for ($deal = 0;$deal < ceil($jsonDeals["total"] / $rows);$deal++)
                    {

                        $deal == 0 ? $firstRow = $firstRow : $firstRow = $firstRow + $rows;
                        $deal == (intval((ceil($jsonDeals["total"] / $rows)) - 1)) ? intval($jsonDeals["total"] > intval($firstRow) ? $substractionRows = (intval($jsonDeals["total"] - intval($firstRow))) : $substractionRows = (intval($firstRow) - intval($jsonDeals["total"]))) : $substractionRows = $rows;
                        try
                        {
                            set_time_limit(1000000);
                            for ($pushDeal = 0;$pushDeal < $substractionRows; $pushDeal++)
                            {

                                // OBTENEMOS LOS DATOS POR CADA ID DEL LISTADO DE $jsonDeals
                                $dealUrl = "$this->bitrixSite$this->bitrixToken/crm.deal.get?ID=" . $jsonDeals["result"][$pushDeal]['ID'];
                                $dealResult = file_get_contents($dealUrl);
                                $jsonDeal = json_decode($dealResult, true);
                                // FECHA Y HORA EXACTA DE VISITA
                                $dateVisit = empty($jsonDeal["result"]['UF_CRM_1562191387578']) ? "SIN FECHA DE VISITA" : $jsonDeal["result"]['UF_CRM_1562191387578'];
                                // ESTATUS FECHA DE VISITA
                                $status = $this->setDateStatus($dateVisit);
                                // NOMBRE DEL DESARROLLO
                                $placeName = $this->getPlaceName($jsonDeal["result"]['UF_CRM_5D12A1A9D28ED']);
                                // NOMBRE DEL RESPONSABLE
                                $responsable = $this->getResponsable($jsonDeal["result"]['ASSIGNED_BY_ID']);
                                // DATOS DEL CONTACTO
                                $contact = $this->getContact($jsonDeal["result"]['CONTACT_ID']);
                                // NOMBRE DEL ORIGEN
                                $origin = $this->getOrigin($jsonDeal["result"]["SOURCE_ID"]);
                                // COMENTARIOS DE LA NEGOCIACION
                                $comments = $this->getCommentsAndActivities($jsonDeal["result"]["ID"], $jsonDeal["result"]['ASSIGNED_BY_ID']);

                                array_push($deals, ["ID" => $jsonDeals["result"][$pushDeal]['ID'], "LEAD_ID" => $jsonDeals["result"][$pushDeal]['LEAD_ID'], "TITLE" => $jsonDeals["result"][$pushDeal]['TITLE'], "CONTACT_NAME" => $contact["fullname"], "CONTACT_PHONE" => $contact["phone"], "CONTACT_EMAIL" => $contact["email"], "STAGE" => $stagesCatalog[0][$stage]["stage"], "RESPONSABLE" => $responsable["fullname"], "ORIGIN" => $origin, "NAME_DEVELOP" => $placeName, "DATE_VISIT" => $dateVisit, "STATUS_VISIT" => $status, "DATE_CREATE" => $jsonDeals["result"][$pushDeal]['DATE_CREATE'], "DATE_MODIFY" => $jsonDeals["result"][$pushDeal]['DATE_MODIFY'], "COMMENT" => $comments[0]["comment"], "ACTIVITY" => $comments[0]["activity"]]);
                            }
                        }
                        catch(Exception $e)
                        {
                            dd($e);
                        }
                    }
                break;

                case '5':
                    for ($deal = 0;$deal < ceil($jsonDeals["total"] / $rows);$deal++)
                    {

                        $deal == 0 ? $firstRow = $firstRow : $firstRow = $firstRow + $rows;
                        $deal == (intval((ceil($jsonDeals["total"] / $rows)) - 1)) ? intval($jsonDeals["total"] > intval($firstRow) ? $substractionRows = (intval($jsonDeals["total"] - intval($firstRow))) : $substractionRows = (intval($firstRow) - intval($jsonDeals["total"]))) : $substractionRows = $rows;
                        try
                        {
                            set_time_limit(1000000);
                            for ($pushDeal = 0;$pushDeal < $substractionRows;$pushDeal++)
                            {
                                $dealUrl = "$this->bitrixSite$this->bitrixToken/crm.deal.get?ID=" . $jsonDeals["result"][$pushDeal]['ID'];
                                $dealResult = file_get_contents($dealUrl);
                                $jsonDeal = json_decode($dealResult, true);
                                // FECHA Y HORA EXACTA DE VISITA
                                $dateVisit = empty($jsonDeal["result"]['UF_CRM_1562191387578']) ? "SIN FECHA DE VISITA" : $jsonDeal["result"]['UF_CRM_1562191387578'];
                                // ESTATUS FECHA DE VISITA
                                $status = $this->setDateStatus($dateVisit);
                                // NOMBRE DEL DESARROLLO
                                $placeName = $this->getPlaceName($jsonDeal["result"]['UF_CRM_5D12A1A9D28ED']);
                                // NOMBRE DEL RESPONSABLE
                                $responsable = $this->getResponsable($jsonDeal["result"]['ASSIGNED_BY_ID']);
                                // DATOS DEL CONTACTO
                                $contact = $this->getContact($jsonDeal["result"]['CONTACT_ID']);
                                // NOMBRE DEL ORIGEN
                                $origin = $this->getOrigin($jsonDeal["result"]["SOURCE_ID"]);
                                // COMENTARIOS DE LA NEGOCIACION
                                $comments = $this->getCommentsAndActivities($jsonDeal["result"]["ID"], $jsonDeal["result"]['ASSIGNED_BY_ID']);

                                array_push($deals, ["ID" => $jsonDeals["result"][$pushDeal]['ID'], "LEAD_ID" => $jsonDeals["result"][$pushDeal]['LEAD_ID'], "TITLE" => $jsonDeals["result"][$pushDeal]['TITLE'], "CONTACT_NAME" => $contact["fullname"], "CONTACT_PHONE" => $contact["phone"], "CONTACT_EMAIL" => $contact["email"], "STAGE" => $stagesCatalog[0][$stage]["stage"], "RESPONSABLE" => $responsable["fullname"], "ORIGIN" => $origin, "NAME_DEVELOP" => $placeName, "DATE_VISIT" => $dateVisit, "STATUS_VISIT" => $status, "DATE_CREATE" => $jsonDeals["result"][$pushDeal]['DATE_CREATE'], "DATE_MODIFY" => $jsonDeals["result"][$pushDeal]['DATE_MODIFY'], "COMMENT" => $comments[0]["comment"], "ACTIVITY" => $comments[0]["activity"]]);
                            }
                        }
                        catch(Exception $e)
                        {
                            dd($e);
                        }
                    }
                break;

                case '1':
                    for ($deal = 0;$deal < ceil($jsonDeals["total"] / $rows);$deal++)
                    {

                        $jsonDeals = json_decode($dealsResult, true);
                        $deal == (intval((ceil($jsonDeals["total"] / $rows)) - 1)) ? intval($jsonDeals["total"] > intval($firstRow) ? $substractionRows = (intval($jsonDeals["total"] - intval($firstRow))) : $substractionRows = (intval($firstRow) - intval($jsonDeals["total"]))) : $substractionRows = $rows;
                        try
                        {
                            set_time_limit(1000000);
                            for ($pushDeal = 0;$pushDeal < $substractionRows;$pushDeal++)
                            {
                                $dealUrl = "$this->bitrixSite$this->bitrixToken/crm.deal.get?ID=" . $jsonDeals["result"][$pushDeal]['ID'];
                                $dealResult = file_get_contents($dealUrl);
                                $jsonDeal = json_decode($dealResult, true);
                                // FECHA Y HORA EXACTA DE VISITA
                                $dateVisit = empty($jsonDeal["result"]['UF_CRM_1562191387578']) ? "SIN FECHA DE VISITA" : $jsonDeal["result"]['UF_CRM_1562191387578'];
                                // ESTATUS FECHA DE VISITA
                                $status = $this->setDateStatus($dateVisit);
                                // NOMBRE DEL DESARROLLO
                                $placeName = $this->getPlaceName($jsonDeal["result"]['UF_CRM_5D12A1A9D28ED']);
                                // NOMBRE DEL RESPONSABLE
                                $responsable = $this->getResponsable($jsonDeal["result"]['ASSIGNED_BY_ID']);
                                // DATOS DEL CONTACTO
                                $contact = $this->getContact($jsonDeal["result"]['CONTACT_ID']);
                                // NOMBRE DEL ORIGEN
                                $origin = $this->getOrigin($jsonDeal["result"]["SOURCE_ID"]);
                                // COMENTARIOS DE LA NEGOCIACION
                                $comments = $this->getCommentsAndActivities($jsonDeal["result"]["ID"], $jsonDeal["result"]['ASSIGNED_BY_ID']);
                                array_push($deals, ["ID" => $jsonDeals["result"][$pushDeal]['ID'], "LEAD_ID" => $jsonDeals["result"][$pushDeal]['LEAD_ID'], "TITLE" => $jsonDeals["result"][$pushDeal]['TITLE'], "CONTACT_NAME" => $contact["fullname"], "CONTACT_PHONE" => $contact["phone"], "CONTACT_EMAIL" => $contact["email"], "STAGE" => $stagesCatalog[0][$stage]["stage"], "RESPONSABLE" => $responsable["fullname"], "ORIGIN" => $origin, "NAME_DEVELOP" => $placeName, "DATE_VISIT" => $dateVisit, "STATUS_VISIT" => $status, "DATE_CREATE" => $jsonDeals["result"][$pushDeal]['DATE_CREATE'], "DATE_MODIFY" => $jsonDeals["result"][$pushDeal]['DATE_MODIFY'], "COMMENT" => $comments[0]["comment"], "ACTIVITY" => $comments[0]["activity"]]);
                            }
                        }
                        catch(Exception $e)
                        {
                            dd($e);
                        }
                    }
                break;
            }
        }
        return $deals;
        // return response()->json(['status' => 'success', 'code' => 200, 'message' => 'Reporte exitoso.', 'items' => $deals], 200);
    }
}
