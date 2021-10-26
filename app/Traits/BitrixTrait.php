<?php

namespace App\Traits;

use App\Models\DealSell;
use App\Models\Lead;
use DateTime;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

trait BitrixTrait
{
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
     * Get difference between dates y assign status
     */
    public function setDateStatus(string $date)
    {
        if ($date == 'SIN FECHA DE VISITA') {
            return $date;
        }
        $dateVisit = new DateTime($date);
        $now = now();
        $diff = $dateVisit->diff($now);
        $status = $diff->days >= 15 ? 'VENCIDO' : 'SEGUIMIENTO';
        return $status;
    }

    /**
     * @param int id, ID Lead o Deal
     * @return array data Datos del comentario
     */
    public function getCommentsAndActivities($id, $responsableId)
    {
        // GUARDA ACTIVIDADES Y COMENTARIOS
        $data = [];
        // CONCATENA LOS COMENTARIOS
        $comments = " ";
        // CONCATENA LAS ACTIVIDADES
        $activites = " ";
        $commentsUrl = "$this->bitrixSite$this->bitrixToken/crm.timeline.comment.list?FILTER[ENTITY_ID]=$id&FILTER[ENTITY_TYPE]=DEAL&ORDER[CREATED]=DESC";
        $activityUrl = "$this->bitrixSite$this->bitrixToken/crm.activity.list?FILTER[OWNER_ID]=$id&FILTER[OWNER_TYPE_ID]=2&FILTER[RESPONSIBLE_ID]=$responsableId&ORDER[CREATED]=DESC";
        // OBTIENE LA RESPUESTA DE LA API REST BITRIX
        $responseActivityAPI = file_get_contents($activityUrl);
        $responseCommentsAPI = file_get_contents($commentsUrl);
        // CAMPOS DE LA RESPUESTA
        $jsonActivityResponse = json_decode($responseActivityAPI, true);
        $jsonCommentsResponse = json_decode($responseCommentsAPI, true);

        // VERIFICAMOS QUE EL ARREGLO DE LA RESPUESTA TENGA DATOS
        if(!empty($jsonActivityResponse["result"])) {

            // ITERAMOS EL ARREGLO E INSERTAMOS LOS DATOS QUE NECESITAMOS
            for ($i = 0; $i < $jsonActivityResponse["total"] ; $i++) {

                // SI VAN MAS DE 3 REGISTROS, ROMPEMOS EL CICLO
                    if($i > 2) {
                    break;
                }

                // INSERTAMOS EN EL ARRAY LAS ACTIVIDADES QUE AUN NO SON CERRADAS
                if($jsonActivityResponse["result"][$i]["COMPLETED"] == "N") {

                    $activites = $activites."Actividad: ".$jsonActivityResponse["result"][$i]["SUBJECT"]." Fecha creacion: "
                    .$jsonActivityResponse["result"][$i]["CREATED"]." Fecha de modificación: ".$jsonActivityResponse["result"][$i]["LAST_UPDATED"].
                    " Completado: ".$jsonActivityResponse["result"][$i]["COMPLETED"];
                }
            }
        }else {

            $activites = "La negociación no tiene actividades";
        }

        // VERIFICAMOS QUE EL ARREGLO DE LA RESPUESTA TENGA DATOS
        if(!empty($jsonCommentsResponse["result"])) {

            // ITERAMOS EL ARREGLO E INSERTAMOS LOS DATOS QUE NECESITAMOS
            for ($i = 0; $i < $jsonCommentsResponse["total"] ; $i++) {

                // SI VAN MAS DE 3 REGISTROS, ROMPEMOS EL CICLO
                if($i > 2) {
                    break;
                }

                // VALIDAMOS QUE EL COMENTARIO PERTENZCA AL RESPONSABLE DE LA NEGOCIACION
                if($jsonCommentsResponse["result"][$i]["AUTHOR_ID"] == $responsableId) {

                    $comments = $comments."Comentario: ".$jsonCommentsResponse["result"][$i]["COMMENT"]." Fecha comentario: "
                    .$jsonCommentsResponse["result"][$i]["CREATED"];
                }
            }
        }else {

            $comments = "La negociacion no tiene comentarios";
        }
        array_push($data, [
            "activity" => $activites,
            "comment" => $comments
        ]);
        return $data;
    }

    /**
     * Obtiene el origen del lead o deal
     * @param string sourceName, nombre del origen
     */
    public function getOrigin($sourceName)
    {
        $originUrl = "$this->bitrixSite$this->bitrixToken/crm.status.list?FILTER[ENTITY_ID]=SOURCE";
        // OBTIENE LA RESPUESTA DE LA API REST BITRIX
        $responseAPI = file_get_contents($originUrl);
        // CAMPOS DE LA RESPUESTA
        $jsonResponse = json_decode($responseAPI, true);

        foreach ($jsonResponse["result"] as $origin => $value) {

            if($jsonResponse["result"][$origin]["STATUS_ID"] == $sourceName) {

                return $jsonResponse["result"][$origin]["NAME"];
            }
        }
    }

    /**
     * @param int id ID del responsale del lead o deal
     * @return array Datos del resposable
     */
    public function getResponsable($id)
    {
        $fullName = 'SIN RESPONSABLE';
        $responsableUrl = "$this->bitrixSite$this->bitrixToken/user.get?ID=$id";
        // $this->bitrixSite.'/rest/1/'.$this->bitrixToken.'/crm.deal.fields';
        // OBTIENE LA RESPUESTA DE LA API REST BITRIX
        $responseAPI = file_get_contents($responsableUrl);
        // CAMPOS DE LA RESPUESTA
        $jsonResponse = json_decode($responseAPI, true);
        if(count($jsonResponse['result']) > 0) {
            $fullName = $jsonResponse['result'][0]['NAME'] . " " .$jsonResponse['result'][0]['LAST_NAME'];
        }
        return [
            'fullname' => $fullName
        ];
    }

    /**
     * @param int id ID del contacto
     * @return array Datos del contacto
     */
    public function getContact($id)
    {
        if(!isset($id)){
            return [
                "fullname" => "Sin contacto registrado",
                "phone" => "Sin numero registrado",
                "email" => "Sin correo registrado"
            ];
        }
        $contactUrl = "$this->bitrixSite$this->bitrixToken/crm.contact.get?ID=$id";

        // OBTIENE LA RESPUESTA DE LA API REST BITRIX
        $responseAPI = file_get_contents($contactUrl);
        // CAMPOS DE LA RESPUESTA
        $jsonResponse = json_decode($responseAPI, true);

        return [
            'fullname' => $jsonResponse['result']['NAME']." ".$jsonResponse['result']['LAST_NAME'],
            'phone' => isset($jsonResponse['result']['PHONE'][0]['VALUE']) ? $jsonResponse['result']['PHONE'][0]['VALUE'] : 'Sin numero registrado',
            'email' => isset($jsonResponse['result']['EMAIL'][0]['VALUE']) ? $jsonResponse['result']['EMAIL'][0]['VALUE'] : 'Sin correo registrado'
        ];
    }

    /**
     * Obtiene el motivo de descalificacion
     * @param int id
     * @param string type default deal
     * @return string salesChannel
     */
    public function getDisqualificationReason($id, $type = 'deal')
    {
        $fieldsDeals = "$this->bitrixSite$this->bitrixToken/crm.$type.fields";

        // OBTIENE LA RESPUESTA DE LA API REST BITRIX
        $responseAPI = file_get_contents($fieldsDeals);
        // CAMPOS DE LA RESPUESTA
        $fields = json_decode($responseAPI, true);
        // NUMERO DE CAMPOS EN LA POSICION DEL ARRAY
        $disqualificationReason = $type == 'deal' ? $fields['result']['UF_CRM_5D03F07FD7E99']['items'] : $fields['result']['UF_CRM_1560365005396']['items'] ;

        for ($i = 0; $i < count($disqualificationReason); $i++) {

            if ($disqualificationReason[$i]['ID'] == $id) {
                return isset($disqualificationReason[$i]['VALUE']) || !empty($disqualificationReason[$i]['VALUE']) ? $disqualificationReason[$i]['VALUE']
                : 'Sin motivo de descalificacion';
            }
        }
    }

    /**
     * Obtiene el motivo de compra
     * @param int id
     * @param string type default deal
     * @return string purchase reason
     */
    public function getPurchaseReason($id, $type = 'deal')
    {
        $fieldsDeals = "$this->bitrixSite$this->bitrixToken/crm.$type.fields";

        // OBTIENE LA RESPUESTA DE LA API REST BITRIX
        $responseAPI = file_get_contents($fieldsDeals);
        // CAMPOS DE LA RESPUESTA
        $fields = json_decode($responseAPI, true);
        // NUMERO DE CAMPOS EN LA POSICION DEL ARRAY
        $purchaseReason = $type == 'deal' ? $fields['result']['UF_CRM_5CF9D773AAF07']['items'] : $fields['result']['UF_CRM_1559757849830']['items'] ;

        for ($i = 0; $i < count($purchaseReason); $i++) {

            if ($purchaseReason[$i]['ID'] == $id) {
                return isset($purchaseReason[$i]['VALUE']) || !empty($purchaseReason[$i]['VALUE']) ? $purchaseReason[$i]['VALUE'] : 'Sin motivo de compra';
            }
        }
    }

    /**
     * Obtiene el canal de ventas
     * @param int id
     * @param string type default deal
     * @return string sales channel
     */
    public function getSalesChannel($id, $type = 'deal')
    {
        $fieldsDeals = "$this->bitrixSite$this->bitrixToken/crm.$type.fields";

        // OBTIENE LA RESPUESTA DE LA API REST BITRIX
        $responseAPI = file_get_contents($fieldsDeals);
        // CAMPOS DE LA RESPUESTA
        $fields = json_decode($responseAPI, true);
        // NUMERO DE CAMPOS EN LA POSICION DEL ARRAY
        $development = $type == 'deal' ? $fields['result']['UF_CRM_5D03F07FB6F84']['items'] : $fields['result']['UF_CRM_1560363526781']['items'] ;

        for ($i = 0; $i < count($development); $i++) {

            if ($development[$i]['ID'] == $id) {
                return isset($development[$i]['VALUE']) || !empty($development[$i]['VALUE']) ? $development[$i]['VALUE'] : 'Sin canal de ventas';
            }
        }
    }

    /**
     * Obtiene el nombre del desarrollo del CRM
     * @param int id
     * @return string development
     */
    public function getInterestDevelopment($id)
    {
        $fieldsDeals = "$this->bitrixSite$this->bitrixToken/crm.deal.fields";
        // OBTIENE LA RESPUESTA DE LA API REST BITRIX
        $responseAPI = file_get_contents($fieldsDeals);
        // CAMPOS DE LA RESPUESTA
        $fields = json_decode($responseAPI, true);
        // NUMERO DE CAMPOS EN LA POSICION DEL ARRAY
        $development = $fields['result']['UF_CRM_1598033555703']['items'];
        for ($i = 0; $i < count($development); $i++) {
            if ($development[$i]['ID'] == $id) {
                return isset($development[$i]['VALUE']) || !empty($development[$i]['VALUE']) ? $development[$i]['VALUE'] : 'Sin desarrollo de interes';
            }
        }
        return 'Sin desarrollo de interes';
    }

    /**
     * Obtiene el nombre del desarrollo del CRM
     * @param int id
     * @param string type default deal
     * @return string development
     */
    public function getPlaceName($id, $type = 'deal')
    {
        $fieldsDeals = "$this->bitrixSite$this->bitrixToken/crm.$type.fields";

        // OBTIENE LA RESPUESTA DE LA API REST BITRIX
        $responseAPI = file_get_contents($fieldsDeals);
        // CAMPOS DE LA RESPUESTA
        $fields = json_decode($responseAPI, true);
        // NUMERO DE CAMPOS EN LA POSICION DEL ARRAY
        $development = $type == 'deal' ? $fields['result']['UF_CRM_5D12A1A9D28ED']['items'] : $fields['result']['UF_CRM_1561502098252']['items'] ;

        for ($i = 0; $i < count($development); $i++) {

            if ($development[$i]['ID'] == $id) {
                return isset($development[$i]['VALUE']) ? $development[$i]['VALUE'] : 'Sin desarrollo asignado';
            }
        }
    }

    /**
     * Catalogo de etapas de acuerdo al CRM, los nombres pueden cambiar pero el ID o STATUS ID
     * siempre será el mismo.
     * NEW -> PRECALIFICADO
     * 5 -> CITA PROGRAMADA
     * 1 -> VISITA
     * 2 -> EN PROCESO DE APARTADO
     * 6 -> APARTADO
     * FINAL_INVOICE -> EN PROCESO DE CONTRATO
     * WON -> CONTRATADO
     * LOSE -> SEGUIMIENTO A FUTURO
     * 7 -> BD
     * 3 -> DESPERFILADO
     * 4 -> CANCELACION DE APARTADO
     * @return array stages
    */
    public function stages()
    {
        // Catalogo de fases
        $stages = [];
        // Muestra el catalogo de etapas
        $stageCatalogUrl = "$this->bitrixSite$this->bitrixToken/crm.dealcategory.stage.list";
        $stageCatalogResult = file_get_contents($stageCatalogUrl);
        $jsonCatalog = json_decode($stageCatalogResult, true);

        // Llenamos el array catalog
        foreach ($jsonCatalog["result"] as $catalog => $value) {

            $jsonCatalog["result"][$catalog]["STATUS_ID"] == "NEW" || $jsonCatalog["result"][$catalog]["STATUS_ID"] == "5"
            || $jsonCatalog["result"][$catalog]["STATUS_ID"] == "1" ?
            array_push($stages, ["id" => $jsonCatalog["result"][$catalog]["STATUS_ID"], "stage" => $jsonCatalog["result"][$catalog]["NAME"]])
            : '';
        }
        return $stages;
    }

    /**
     * Get deal stages names
     * @param stageId $id
     */
    public function getStages($id)
    {
        $fields = Http::get("$this->bitrixSite$this->bitrixToken/crm.dealcategory.stage.list");
        $jsonFields = $fields->json();
        for ($i = 0; $i < count($jsonFields['result']); $i++) {
            if ($jsonFields['result'][$i]['STATUS_ID'] == $id) {
                return isset($jsonFields['result'][$i]['NAME']) || !empty($jsonFields['result'][$i]['NAME']) ? $jsonFields['result'][$i]['NAME'] : 'Sin etapa';
            }
        }
    }

    /**
     * Get deal visit tyep
     * @param visitId $id
     */
    public function getVisitType($id)
    {
        $type = "";
        $fields = Http::get("$this->bitrixSite$this->bitrixToken/crm.deal.fields");
        $jsonFields = $fields->json();
        // NUMERO DE CAMPOS EN LA POSICION DEL ARRAY
        $visitType = $jsonFields['result']['UF_CRM_1580847925']['items'];
        for ($i = 0; $i < count($visitType); $i++) {
            if ($visitType[$i]['ID'] == $id) {
                $type = isset($visitType[$i]['VALUE']) || !empty($visitType[$i]['VALUE']) ? $visitType[$i]['VALUE'] : 'Sin tipo de visita';
            }
        }
        return $type;
    }


    /**
     * Stages from leads
     */
    public function leadsStages($stageId)
    {
        switch ($stageId) {
            case 'IN_PROCESS':
                $status = 'PROSPECTO ASIGNADO';
                break;
            case '3':
                $status = 'PROSPECTO EN SEGUIMIENTO';
                break;
            case '4':
                $status = 'PENDIENTES';
                break;
            case '5':
                $status = 'DUPLICADOS';
                break;
            case 'JUNK':
                $status = 'NO CALIFICA';
                break;
            case 'CONVERTED':
                $status = 'CALIFICADO';
                break;
        }
        return $status;
    }

    /**
     * Get and insert deals on tables
     * @param category $category
     * DEAL-SELL, CATEGORY_ID 0
     * DEAL-NEGOTATION, CATEGORY_ID 1
     */
    public function getDeals($category)
    {
        $status = 'success';
        $code = 200;
        $message = "Reporte generado exitosamente deals $category";
        // Numero de registros mostrados por peticion
        $rows = 50;
        // Primer registro para mostrar en la peticion
        $firstRow = 0;
        DB::beginTransaction();
        try {
            $dealsUrl = Http::get("$this->bitrixSite$this->bitrixToken/crm.deal.list?start=$firstRow&FILTER[CATEGORY_ID]=$category");
            $jsonDeals = $dealsUrl->json();
            for ($deal = 0; $deal < ceil($jsonDeals['total'] / $rows); $deal++)
            {
                $deal == 0 ? $firstRow = $firstRow : $firstRow = $firstRow + $rows;
                $deal == (intval((ceil($jsonDeals["total"] / $rows)) - 1)) ? intval($jsonDeals["total"] > intval($firstRow) ? $substractionRows = (intval($jsonDeals["total"] - intval($firstRow))) : $substractionRows = (intval($firstRow) - intval($jsonDeals["total"]))) : $substractionRows = $rows;

                set_time_limit(100000000);
                for ($pushDeal = 0; $pushDeal < $substractionRows; $pushDeal++)
                {
                    // OBTENEMOS LOS DATOS POR CADA ID DEL LISTADO DE $jsonDeals
                    $dealUrl = Http::get("$this->bitrixSite$this->bitrixToken/crm.deal.get?ID=" . $jsonDeals["result"][$pushDeal]['ID']);
                    $jsonDeal = $dealUrl->json();

                    $id = $jsonDeal['result']['ID'];
                    $leadId = $jsonDeal['result']['LEAD_ID'];
                    $negotiationSellId = !empty($jsonDeal['result']['UF_CRM_1572991763556']) ? $jsonDeal['result']['UF_CRM_1572991763556'] : $jsonDeal['result']['UF_CRM_1579545131'];
                    $origin = $jsonDeal['result']['SOURCE_ID'];
                    $stage = $this->getStages($jsonDeal['result']['STAGE_ID']);
                    $type = $jsonDeal['result']['TYPE_ID'];
                    $manager = !empty($jsonDeal['result']['UF_CRM_5E2F60854D7AC']) ? $jsonDeal['result']['UF_CRM_5E2F60854D7AC'] : $jsonDeal['result']['UF_CRM_1580155762'];
                    $responsable = $this->getResponsable($jsonDeal['result']['ASSIGNED_BY_ID']);
                    $salesChannel = $this->getSalesChannel($jsonDeal['result']['UF_CRM_5D03F07FB6F84']);
                    $development = $this->getPlaceName($jsonDeal['result']['UF_CRM_5D12A1A9D28ED']);
                    $interestDevelopment = $this->getInterestDevelopment($jsonDeal['result']['UF_CRM_1598033555703']);
                    $reasonCancelationSection = $jsonDeal['result']['UF_CRM_1560811855979'];
                    $purchaseReason = $this->getPurchaseReason($jsonDeal['result']['UF_CRM_5CF9D773AAF07']);
                    $productName = !empty($jsonDeal['result']['UF_CRM_1573064054413']) ? $jsonDeal['result']['UF_CRM_1573064054413'] : $jsonDeal['result']['UF_CRM_1573063908'];
                    $productPrice = $jsonDeal['result']['UF_CRM_1573066384206'];
                    $disqualificationReason = $this->getDisqualificationReason($jsonDeal['result']['UF_CRM_1560365005396']);
                    $commentsDisqualification = $jsonDeal['result']['UF_CRM_1573858596']; // UF_CRM_5D03F07FD7E99
                    $deliveryDateAt = $jsonDeal['result']['UF_CRM_1586290304'];
                    $newDeliveryDateAt = $jsonDeal['result']['UF_CRM_1586290215'];
                    $visitedAt = $jsonDeal['result']['UF_CRM_1562191387578'];
                    $separatedAt = $jsonDeal['result']['UF_CRM_1562355481964'];
                    $soldAt = $jsonDeal['result']['UF_CRM_1562191592191'];
                    $visitType =  $this->getVisitType($jsonDeal['result']['UF_CRM_1580847925']);
                    $createdAt = $jsonDeal['result']['DATE_CREATE'];
                    $modifiedAt = $jsonDeal['result']['DATE_MODIFY'];

                    DealSell::updateOrCreate([
                        'negociacion_bitrix_id' => $id,
                    ],
                    [
                        'prospecto_bitrix_id' => $leadId,
                        'negociacion_venta_bitrix_id' => $negotiationSellId,
                        'etapa' => $stage,
                        'tipo' => $type,
                        'gerente' => $manager,
                        'responsable' => strtoupper($responsable['fullname']),
                        'origen' => $origin,
                        'motivo_compra' => $purchaseReason,
                        'canal_venta' => strtoupper($salesChannel),
                        'producto' => $productName,
                        'precio' => $productPrice,
                        'motivo_descalificacion' => $disqualificationReason,
                        'motivo_cancelacion_apartado' => $reasonCancelationSection,
                        'desarrollo' => strtoupper($development),
                        'desarrollo_interes' => strtoupper($interestDevelopment),
                        'tipo_visita' => $visitType,
                        'negociacion_descalificado_comentarios' => $commentsDisqualification,
                        'hora_exacta_visita' => $visitedAt,
                        'apartado_el' => $separatedAt,
                        'vendido_el' => $soldAt,
                        'compromiso_entrega_el' => $deliveryDateAt,
                        'compromiso_entrega_reproyectado_el' => $newDeliveryDateAt,
                        'bitrix_created_el' => $createdAt,
                        'bitrix_modificado_el' => $modifiedAt,
                    ]);

                    /*$x = [
                        'id' => $jsonDeal['result']['ID'],
                        'leadId' => $jsonDeal['result']['LEAD_ID'],
                        'negotiationSellId' => !empty($jsonDeal['result']['UF_CRM_1572991763556']) ? $jsonDeal['result']['UF_CRM_1572991763556'] : $jsonDeal['result']['UF_CRM_1579545131'],
                        'origin' => $jsonDeal['result']['SOURCE_ID'],
                        'stage' => $this->getStages($jsonDeal['result']['STAGE_ID']),
                        'type' => $jsonDeal['result']['TYPE_ID'],
                        'manager' => !empty($jsonDeal['result']['UF_CRM_5E2F60854D7AC']) ? $jsonDeal['result']['UF_CRM_5E2F60854D7AC'] : $jsonDeal['result']['UF_CRM_1580155762'],
                        'responsable' => $this->getResponsable($jsonDeal['result']['ASSIGNED_BY_ID']),
                        'salesChannel' => $this->getSalesChannel($jsonDeal['result']['UF_CRM_5D03F07FB6F84']),
                        'development' => $this->getPlaceName($jsonDeal['result']['UF_CRM_5D12A1A9D28ED']),
                        'interestDevelopment' => $this->getInterestDevelopment($jsonDeal['result']['UF_CRM_1598033555703']),
                        'reasonCancelationSection' => $jsonDeal['result']['UF_CRM_1560811855979'],
                        'purchaseReason' => $this->getPurchaseReason($jsonDeal['result']['UF_CRM_5CF9D773AAF07']),
                        'productName' => !empty($jsonDeal['result']['UF_CRM_1573064054413']) ? $jsonDeal['result']['UF_CRM_1573064054413'] : $jsonDeal['result']['UF_CRM_1573063908'],
                        'productPrice' => $jsonDeal['result']['UF_CRM_1573066384206'],
                        'disqualificationReason' => $this->getDisqualificationReason($jsonDeal['result']['UF_CRM_1560365005396']),
                        'commentsDisqualification' => $jsonDeal['result']['UF_CRM_1573858596'], // UF_CRM_5D03F07FD7E99
                        'deliveryDateAt' => $jsonDeal['result']['UF_CRM_1586290304'],
                        'newDeliveryDateAt' => $jsonDeal['result']['UF_CRM_1586290215'],
                        'visitedAt' => $jsonDeal['result']['UF_CRM_1562191387578'],
                        'separatedAt' => $jsonDeal['result']['UF_CRM_1562355481964'],
                        'soldAt' => $jsonDeal['result']['UF_CRM_1562191592191'],
                        'visitType' => $this->getVisitType($jsonDeal['result']['UF_CRM_1580847925']),
                        'createdAt' => $jsonDeal['result']['DATE_CREATE'],
                    ];
                    dd($x);*/
                }
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $status = 'error';
            $code = 400;
            $message = $e;
        }
        return response()->json(['status' => $status, 'code' => $code, 'message' => $message, 'items' => null], $code);
    }

    /**
     * Update leads for deal report
     * @param category $category, 0 DEAL-SELL, 1 DEAL-NEGOTIATION
     */
    public function updateDeal($category)
    {
        // Numero de registros mostrados por peticion
        $rows = 50;
        // Primer registro para mostrar en la peticion
        $firstRow = 0;
        DB::beginTransaction();
        try {
            // Get last lead saved on db
            $dealByModifiedDate = $category == 0 ? DealSell::orderBy('bitrix_created_el', 'DESC')->get() : DealSell::orderBy('bitrix_created_el', 'DESC')->get();
            $dealsUrl = Http::get("$this->bitrixSite$this->bitrixToken/crm.deal.list?start=$firstRow&FILTER[>DATE_MODIFY]=" . $dealByModifiedDate[0]['bitrix_modificado_el'] . "&ORDER[DATE_MODIFY]=DESC&FILTER[CATEGORY_ID]=$category");
            $jsonDeals = $dealsUrl->json();
            if(count($jsonDeals['result']) > 0)
            {
                for ($deal = 0; $deal < ceil(50 / $rows); $deal++)
                {
                    $deal == 0 ? $firstRow = $firstRow : $firstRow = $firstRow + $rows;
                    $deal == (intval((ceil($jsonDeals['total'] / $rows)) - 1)) ? intval($jsonDeals['total'] > intval($firstRow) ? $substractionRows = (intval($jsonDeals["total"] - intval($firstRow))) : $substractionRows = (intval($firstRow) - intval($jsonDeals["total"]))) : $substractionRows = $rows;
                    set_time_limit(100000000);
                    for ($pushDeal = 0; $pushDeal < $substractionRows; $pushDeal++)
                    {
                        // OBTENEMOS LOS DATOS POR CADA ID DEL LISTADO DE $jsonDeals
                        $dealUrl = Http::get("$this->bitrixSite$this->bitrixToken/crm.deal.get?ID=" . $jsonDeals['result'][$pushDeal]['ID']);
                        $jsonDeal = $dealUrl->json();
                        $id = $jsonDeal['result']['ID'];
                        $leadId = $jsonDeal['result']['LEAD_ID'];
                        $negotiationSellId = !empty($jsonDeal['result']['UF_CRM_1572991763556']) ? $jsonDeal['result']['UF_CRM_1572991763556'] : $jsonDeal['result']['UF_CRM_1579545131'];
                        $origin = $jsonDeal['result']['SOURCE_ID'];
                        $stage = $this->getStages($jsonDeal['result']['STAGE_ID']);
                        $type = $jsonDeal['result']['TYPE_ID'];
                        $manager = !empty($jsonDeal['result']['UF_CRM_5E2F60854D7AC']) ? $jsonDeal['result']['UF_CRM_5E2F60854D7AC'] : $jsonDeal['result']['UF_CRM_1580155762'];
                        $responsable = $this->getResponsable($jsonDeal['result']['ASSIGNED_BY_ID']);
                        $salesChannel = $this->getSalesChannel($jsonDeal['result']['UF_CRM_5D03F07FB6F84']);
                        $development = $this->getPlaceName($jsonDeal['result']['UF_CRM_5D12A1A9D28ED']);
                        $interestDevelopment = $this->getInterestDevelopment($jsonDeal['result']['UF_CRM_1598033555703']);
                        $reasonCancelationSection = $jsonDeal['result']['UF_CRM_1560811855979'];
                        $purchaseReason = $this->getPurchaseReason($jsonDeal['result']['UF_CRM_5CF9D773AAF07']);
                        $productName = !empty($jsonDeal['result']['UF_CRM_1573064054413']) ? $jsonDeal['result']['UF_CRM_1573064054413'] : $jsonDeal['result']['UF_CRM_1573063908'];
                        $productPrice = $jsonDeal['result']['UF_CRM_1573066384206'];
                        $disqualificationReason = $this->getDisqualificationReason($jsonDeal['result']['UF_CRM_1560365005396']);
                        $commentsDisqualification = $jsonDeal['result']['UF_CRM_1573858596']; // UF_CRM_5D03F07FD7E99
                        $deliveryDateAt = $jsonDeal['result']['UF_CRM_1586290304'];
                        $newDeliveryDateAt = $jsonDeal['result']['UF_CRM_1586290215'];
                        $visitedAt = $jsonDeal['result']['UF_CRM_1562191387578'];
                        $separatedAt = $jsonDeal['result']['UF_CRM_1562355481964'];
                        $soldAt = $jsonDeal['result']['UF_CRM_1562191592191'];
                        $visitType =  $this->getVisitType($jsonDeal['result']['UF_CRM_1580847925']);
                        $createdAt = $jsonDeal['result']['DATE_CREATE'];
                        $modifiedAt = $jsonDeal['result']['DATE_MODIFY'];

                        $dealDb = $category == 0 ?
                            DealSell::where('negociacion_bitrix_id', $id)->where('bitrix_modificado_el', '<>', $modifiedAt)->exists() :
                            DealSell::where('negociacion_bitrix_id', $id)->where('bitrix_modificado_el', '<>', $modifiedAt)->exists();
                        if($dealDb)
                        {
                            $table = $category == 0 ? 'deal_sells' : 'deal_negotiations';
                            DB::table($table)
                                ->where('negociacion_bitrix_id', $id)
                                ->update([
                                    'negociacion_bitrix_id' => $id,
                                    'prospecto_bitrix_id' => $leadId,
                                    'negociacion_venta_bitrix_id' => $negotiationSellId,
                                    'etapa' => $stage,
                                    'tipo' => $type,
                                    'gerente' => $manager,
                                    'responsable' => strtoupper($responsable['fullname']),
                                    'origen' => $origin,
                                    'motivo_compra' => $purchaseReason,
                                    'canal_venta' => strtoupper($salesChannel),
                                    'producto' => $productName,
                                    'precio' => $productPrice,
                                    'motivo_descalificacion' => $disqualificationReason,
                                    'motivo_cancelacion_apartado' => $reasonCancelationSection,
                                    'desarrollo' => strtoupper($development),
                                    'desarrollo_interes' => strtoupper($interestDevelopment),
                                    'tipo_visita' => $visitType,
                                    'negociacion_descalificado_comentarios' => $commentsDisqualification,
                                    'hora_exacta_visita' => $visitedAt,
                                    'apartado_el' => $separatedAt,
                                    'vendido_el' => $soldAt,
                                    'compromiso_entrega_el' => $deliveryDateAt,
                                    'compromiso_entrega_reproyectado_el' => $newDeliveryDateAt,
                                    'bitrix_created_el' => $createdAt,
                                    'bitrix_modificado_el' => $modifiedAt,
                                ]);
                        }
                    }
                }
            }
            $items = $category == 0 ? DealSell::all() : DealSell::all();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $items = $e->getMessage();
        }
        return response()->json($items);
    }

    /**
     * Get and insert or update.
     * If phase parameter has a value, insert leads on database
     * else, update leads for the database.
     * @param phase $phase
     * PROSPECTO ASIGNADO (STATUS_ID) -> IN_PROCESS
     * PROSPECTO EN SEGUIMIENTO (STATUS_ID) -> 3
     * DUPLICADOS (STATUS_ID) -> 5
     * PENDIENTE (STATUS_ID) -> 4
     * NO CALIFICA (STATUS_ID) -> JUNK
     * CALIFICADO (STATUS_ID) -> CONVERTED
     * PHASE == 0, ALL RECORDS WILL BE RETRIEVE
     */
    public function getLeads($phase)
    {
        $status = 'success';
        $code = 200;
        $message = 'Reporte exitoso';
        // Numero de registros mostrados por peticion
        $rows = 50;
        // Primer registro para mostrar en la peticion
        $firstRow = 0;
        DB::beginTransaction();
        try {
            $leadsUrl =  Http::get("$this->bitrixSite$this->bitrixToken/crm.lead.list?FILTER[STATUS_ID]=$phase&FILTER[>DATE_CREATE]=2019-06-30T23:59:59-05:00");
            if ($phase == 0)
            {
                $leadsUrl =  Http::get("$this->bitrixSite$this->bitrixToken/crm.lead.list?FILTER[>DATE_CREATE]=2020-07-31T23:59:59-05:00");
            }
            $jsonLeads = $leadsUrl->json();
            set_time_limit(9000000000);
            for ($lead = 0; $lead < ceil($jsonLeads['total'] / $rows); $lead++)
            {
                $lead == 0 ? $firstRow = $firstRow : $firstRow = $firstRow + $rows;
                // Get number of records per page
                $lead == (intval((ceil($jsonLeads['total'] / $rows)) - 1)) ?
                    intval($jsonLeads['total'] > intval($firstRow) ?
                        $substractionRows = (intval($jsonLeads['total'] - intval($firstRow))) :
                            $substractionRows = (intval($firstRow) - intval($jsonLeads['total']))) :
                                $substractionRows = $rows;
                $leadsUrl =  Http::get("$this->bitrixSite$this->bitrixToken/crm.lead.list?start=$firstRow&FILTER[STATUS_ID]=$phase&FILTER[>DATE_CREATE]=2019-06-30T23:59:59-05:00");
                if ($phase == 0)
                {
                    $leadsUrl =  Http::get("$this->bitrixSite$this->bitrixToken/crm.lead.list?start=$firstRow&FILTER[>DATE_CREATE]=2019-06-30T23:59:59-05:00");
                }
                $jsonLeads = $leadsUrl->json();
                for ($pushDeal = 0; $pushDeal < $substractionRows; $pushDeal++)
                {
                    // OBTENEMOS LOS DATOS POR CADA ID DEL LISTADO DE $jsonDeals
                    $leadUrl = Http::get("$this->bitrixSite$this->bitrixToken/crm.lead.get?ID=" . $jsonLeads['result'][$pushDeal]['ID']);
                    $jsonLead = $leadUrl->json();

                    $id = $jsonLead['result']['ID'];
                    $leadName = $jsonLead['result']['NAME'] . " " . $jsonLead['result']['SECOND_NAME'] . " " . $jsonLead['result']['LAST_NAME'];
                    $origin = $this->getOrigin($jsonLead['result']['SOURCE_ID']);
                    $contact = $this->getContact($jsonLead['result']['CONTACT_ID']);
                    $responsable = $this->getResponsable($jsonLead['result']['ASSIGNED_BY_ID']);
                    $development = $this->getPlaceName($jsonLead['result']['UF_CRM_1561502098252'], 'lead');
                    $salesChannel = $this->getSalesChannel($jsonLead['result']['UF_CRM_1560363526781'], 'lead');
                    $purchaseReason = $this->getPurchaseReason($jsonLead['result']['UF_CRM_1559757849830'], 'lead');
                    $disqualificationReason = $this->getDisqualificationReason($jsonLead['result']['UF_CRM_1560365005396'], 'lead');
                    $status = $this->leadsStages($jsonLead['result']['STATUS_ID']);
                    $modifiedAt = $jsonLead['result']['DATE_MODIFY'];
                    $createdAt = $jsonLead['result']['DATE_CREATE'];
                    $createdBy = $this->getResponsable($jsonLead["result"]['CREATED_BY_ID']);

                    if($contact['phone'] == 'Sin numero registrado') {
                        $phone = isset($jsonLead['result']['PHONE'][0]['VALUE']) || !empty($jsonLead['result']['PHONE'][0]['VALUE']) ?
                        $jsonLead['result']['PHONE'][0]['VALUE'] : 'Sin numero registrado';
                    }

                    if($contact['email'] == 'Sin correo registrado') {
                        $email = isset($jsonLead['result']['EMAIL'][0]['VALUE']) || !empty($jsonLead['result']['EMAIL'][0]['VALUE']) ?
                        $jsonLead['result']['EMAIL'][0]['VALUE'] : 'Sin correo registrado';
                    }
                    Lead::updateOrCreate([
                        'bitrix_id'   => $id,
                    ],
                    [
                        'nombre' => strtoupper($leadName),
                        'telefono' => $contact['phone'] == 'Sin numero registrado' ? $phone : $contact['phone'],
                        'email' => $contact['email'] == 'Sin correo registrado' ? $email : $contact['email'],
                        'origen' => $origin,
                        'responsable' => strtoupper($responsable['fullname']),
                        'desarrollo' => strtoupper($development),
                        'canal_ventas' => strtoupper($salesChannel),
                        'motivo_compra' => strtoupper($purchaseReason),
                        'motivo_descalificacion' => $disqualificationReason,
                        'estatus' => $status,
                        'bitrix_creado_por' => strtoupper($createdBy['fullname']),
                        'bitrix_creado_el' => $createdAt,
                        'bitrix_modificado_el' => $modifiedAt,
                    ]);
                    echo "INSERTADO; PAGINA $lead, REGISTRO $pushDeal, ID: $id<br>";
                }
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $status = 'error';
            $code = 400;
            $message = $e->getMessage();
        }
        return response()->json(['status' => $status, 'code' => $code, 'message' => $message, 'items' => null], $code);
    }

    /**
     * Update leads for leads report
     * @param report $report, LEADS, DEAL-SELL, DEAL-NEGOTIATION
     */
    public function updateLead($report)
    {
        // Numero de registros mostrados por peticion
        $rows = 50;
        // Primer registro para mostrar en la peticion
        $firstRow = 0;
        DB::beginTransaction();
        try {
            // Get last lead saved on db
            $leadByModifiedDate = Lead::orderBy('bitrix_created_at', 'DESC')->get();
            $dealsUrl = Http::get("$this->bitrixSite$this->bitrixToken/crm.lead.list?start=$firstRow&FILTER[>DATE_MODIFY]=" . $leadByModifiedDate[0]['bitrix_modified_at'] . "&ORDER[DATE_MODIFY]=DESC");
            $jsonDeals = $dealsUrl->json();
            if(count($jsonDeals['result']) > 0)
            {
                for ($deal = 0; $deal < ceil(50 / $rows); $deal++)
                {
                    $deal == 0 ? $firstRow = $firstRow : $firstRow = $firstRow + $rows;
                    $deal == (intval((ceil($jsonDeals['total'] / $rows)) - 1)) ? intval($jsonDeals['total'] > intval($firstRow) ? $substractionRows = (intval($jsonDeals["total"] - intval($firstRow))) : $substractionRows = (intval($firstRow) - intval($jsonDeals["total"]))) : $substractionRows = $rows;
                    set_time_limit(100000000);
                    for ($pushDeal = 0; $pushDeal < $substractionRows; $pushDeal++)
                    {
                        // OBTENEMOS LOS DATOS POR CADA ID DEL LISTADO DE $jsonDeals
                        $dealUrl = Http::get("$this->bitrixSite$this->bitrixToken/crm.lead.get?ID=" . $jsonDeals["result"][$pushDeal]['ID']);
                        $jsonDeal = $dealUrl->json();
                        $id = $jsonDeal['result']['ID'];
                        $leadName = $jsonDeal['result']['NAME'] . " " . $jsonDeal['result']['SECOND_NAME'] . " " . $jsonDeal['result']['LAST_NAME'];
                        $origin = $jsonDeal['result']['SOURCE_ID'];
                        $contact = $this->getContact($jsonDeal['result']['CONTACT_ID']);
                        $responsable = $this->getResponsable($jsonDeal['result']['ASSIGNED_BY_ID']);
                        $development = $this->getPlaceName($jsonDeal['result']['UF_CRM_1561502098252'], 'lead');
                        $salesChannel = $this->getSalesChannel($jsonDeal['result']['UF_CRM_1560363526781'], 'lead');
                        $purchaseReason = $this->getPurchaseReason($jsonDeal['result']['UF_CRM_1559757849830'], 'lead');
                        $disqualificationReason = $this->getDisqualificationReason($jsonDeal['result']['UF_CRM_1560365005396'], 'lead');
                        $status = $this->leadsStages($jsonDeal['result']['STATUS_ID']);
                        $modifiedAt = $jsonDeal['result']['DATE_MODIFY'];
                        $createdAt = $jsonDeal['result']['DATE_CREATE'];
                        $createdBy = $this->getResponsable($jsonDeal["result"]['CREATED_BY_ID']);

                        if($contact['phone'] == 'Sin numero registrado') {
                            $phone = isset($jsonDeal['result']['PHONE'][0]['VALUE']) || !empty($jsonDeal['result']['PHONE'][0]['VALUE']) ?
                            $jsonDeal['result']['PHONE'][0]['VALUE'] : 'Sin numero registrado';
                        }

                        if($contact['email'] == 'Sin correo registrado') {
                            $email = isset($jsonDeal['result']['EMAIL'][0]['VALUE']) || !empty($jsonDeal['result']['EMAIL'][0]['VALUE']) ?
                            $jsonDeal['result']['EMAIL'][0]['VALUE'] : 'Sin correo registrado';
                        }
                        $leadDb = Lead::where('bitrix_id', $id)->where('bitrix_modified_at', '<>', $modifiedAt)->exists();
                        if($leadDb)
                        {
                            DB::table('leads')
                                ->where('bitrix_id', $id)
                                ->update([
                                    'name' => strtoupper($leadName),
                                    'phone' => $contact['phone'] == 'Sin numero registrado' ? $phone : $contact['phone'],
                                    'email' => $contact['email'] == 'Sin correo registrado' ? $email : $contact['email'],
                                    'origin' => $origin,
                                    'responsable' => strtoupper($responsable['fullname']),
                                    'development' => strtoupper($development),
                                    'sales_channel' => strtoupper($salesChannel),
                                    'status' => $status,
                                    'purchase_reason' => strtoupper($purchaseReason),
                                    'disqualification_reason' => $disqualificationReason,
                                    'bitrix_created_by' => strtoupper($createdBy['fullname']),
                                    'bitrix_created_at' => $createdAt,
                                    'bitrix_modified_at' => $modifiedAt,
                                ]);
                        }
                    }
                }
            }
            $items = Lead::all();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $items = $e->getMessage();
        }
        return response()->json($items);
    }

    /**
     * Add new lead on leads report
     */
    public function addLead($dbLeads, $bitrixLeads)
    {
        $status = 'success';
        $code = 200;
        $message = 'Reporte actualizado con exitosamente';
        // Numero de registros mostrados por peticion
        $rows = 50;
        // Primer registro para mostrar en la peticion
        $firstRow = 0;
        DB::beginTransaction();
        try {
            if ($dbLeads < $bitrixLeads)
            {
                // Helps to know how many records will be insert on database,
                // is the difference between bitrix total and database total.
                $numberOfRecords = $bitrixLeads - $dbLeads;
                $bitrixDeals = Http::get("$this->bitrixSite$this->bitrixToken/crm.lead.list?start=$firstRow&ORDER[ID]=DESC");
                $result = $bitrixDeals ->json();

                if ($numberOfRecords > 50) {
                    for ($deal = 0; $deal < ceil($numberOfRecords / $rows); $deal++)
                    {
                        $deal == 0 ? $firstRow = $firstRow : $firstRow = $firstRow + $rows;
                        $deal == (intval((ceil($numberOfRecords / $rows)) - 1)) ? intval($numberOfRecords > intval($firstRow) ? $substractionRows = (intval($numberOfRecords - intval($firstRow))) : $substractionRows = (intval($firstRow) - intval($numberOfRecords))) : $substractionRows = $rows;
                        set_time_limit(100000000);
                        for ($pushDeal = 0; $pushDeal < $substractionRows; $pushDeal++)
                        {
                            // OBTENEMOS LOS DATOS POR CADA ID DEL LISTADO DE $result
                            $dealUrl = Http::get("$this->bitrixSite$this->bitrixToken/crm.lead.get?ID=" . $result['result'][$pushDeal]['ID']);
                            $jsonDeal = $dealUrl->json();

                            $id = $jsonDeal['result']['ID'];
                            $leadName = $jsonDeal['result']['NAME'] . " " . $jsonDeal['result']['SECOND_NAME'] . " " . $jsonDeal['result']['LAST_NAME'];
                            $origin = $jsonDeal['result']['SOURCE_ID'];
                            $contact = $this->getContact($jsonDeal['result']['CONTACT_ID']);
                            $responsable = $this->getResponsable($jsonDeal['result']['ASSIGNED_BY_ID']);
                            $development = $this->getPlaceName($jsonDeal['result']['UF_CRM_1561502098252'], 'lead');
                            $salesChannel = $this->getSalesChannel($jsonDeal['result']['UF_CRM_1560363526781'], 'lead');
                            $purchaseReason = $this->getPurchaseReason($jsonDeal['result']['UF_CRM_1559757849830'], 'lead');
                            $disqualificationReason = $this->getDisqualificationReason($jsonDeal['result']['UF_CRM_1560365005396'], 'lead');
                            $bitrixStatus = $this->leadsStages($jsonDeal['result']['STATUS_ID']);
                            $modifiedAt = $jsonDeal['result']['DATE_MODIFY'];
                            $createdAt = $jsonDeal['result']['DATE_CREATE'];
                            $createdBy = $this->getResponsable($jsonDeal["result"]['CREATED_BY_ID']);

                            if($contact['phone'] == 'Sin numero registrado') {
                                $phone = isset($jsonDeal['result']['PHONE'][0]['VALUE']) || !empty($jsonDeal['result']['PHONE'][0]['VALUE']) ?
                                $jsonDeal['result']['PHONE'][0]['VALUE'] : 'Sin numero registrado';
                            }

                            if($contact['email'] == 'Sin correo registrado') {
                                $email = isset($jsonDeal['result']['EMAIL'][0]['VALUE']) || !empty($jsonDeal['result']['EMAIL'][0]['VALUE']) ?
                                $jsonDeal['result']['EMAIL'][0]['VALUE'] : 'Sin correo registrado';
                            }
                            Lead::create([
                                'bitrix_id'   => $id,
                                'name' => strtoupper($leadName),
                                'phone' => $contact['phone'] == 'Sin numero registrado' ? $phone : $contact['phone'],
                                'email' => $contact['email'] == 'Sin correo registrado' ? $email : $contact['email'],
                                'origin' => $origin,
                                'responsable' => strtoupper($responsable['fullname']),
                                'development' => strtoupper($development),
                                'sales_channel' => strtoupper($salesChannel),
                                'purchase_reason' => strtoupper($purchaseReason),
                                'disqualification_reason' => $disqualificationReason,
                                'status' => $bitrixStatus,
                                'bitrix_created_by' => strtoupper($createdBy['fullname']),
                                'bitrix_created_at' => $createdAt,
                                'bitrix_modified_at' => $modifiedAt,
                            ]);
                        }
                    }
                }else {
                    for ($deal = 0; $deal <= $numberOfRecords; $deal++)
                    {
                        // OBTENEMOS LOS DATOS POR CADA ID DEL LISTADO DE $result
                        $dealUrl = Http::get("$this->bitrixSite$this->bitrixToken/crm.lead.get?ID=" . $result['result'][$deal]['ID']);
                        $jsonDeal = $dealUrl->json();

                        $id = $jsonDeal['result']['ID'];
                        $leadName = $jsonDeal['result']['NAME'] . " " . $jsonDeal['result']['SECOND_NAME'] . " " . $jsonDeal['result']['LAST_NAME'];
                        $origin = $jsonDeal['result']['SOURCE_ID'];
                        $contact = $this->getContact($jsonDeal['result']['CONTACT_ID']);
                        $responsable = $this->getResponsable($jsonDeal['result']['ASSIGNED_BY_ID']);
                        $development = $this->getPlaceName($jsonDeal['result']['UF_CRM_1561502098252'], 'lead');
                        $salesChannel = $this->getSalesChannel($jsonDeal['result']['UF_CRM_1560363526781'], 'lead');
                        $purchaseReason = $this->getPurchaseReason($jsonDeal['result']['UF_CRM_1559757849830'], 'lead');
                        $disqualificationReason = $this->getDisqualificationReason($jsonDeal['result']['UF_CRM_1560365005396'], 'lead');
                        $bitrixStatus = $this->leadsStages($jsonDeal['result']['STATUS_ID']);
                        $modifiedAt = $jsonDeal['result']['DATE_MODIFY'];
                        $createdAt = $jsonDeal['result']['DATE_CREATE'];
                        $createdBy = $this->getResponsable($jsonDeal["result"]['CREATED_BY_ID']);

                        if($contact['phone'] == 'Sin numero registrado') {
                            $phone = isset($jsonDeal['result']['PHONE'][0]['VALUE']) || !empty($jsonDeal['result']['PHONE'][0]['VALUE']) ?
                            $jsonDeal['result']['PHONE'][0]['VALUE'] : 'Sin numero registrado';
                        }

                        if($contact['email'] == 'Sin correo registrado') {
                            $email = isset($jsonDeal['result']['EMAIL'][0]['VALUE']) || !empty($jsonDeal['result']['EMAIL'][0]['VALUE']) ?
                            $jsonDeal['result']['EMAIL'][0]['VALUE'] : 'Sin correo registrado';
                        }
                        Lead::create([
                            'bitrix_id'   => $id,
                            'name' => strtoupper($leadName),
                            'phone' => $contact['phone'] == 'Sin numero registrado' ? $phone : $contact['phone'],
                            'email' => $contact['email'] == 'Sin correo registrado' ? $email : $contact['email'],
                            'origin' => $origin,
                            'responsable' => strtoupper($responsable['fullname']),
                            'development' => strtoupper($development),
                            'sales_channel' => strtoupper($salesChannel),
                            'purchase_reason' => strtoupper($purchaseReason),
                            'disqualification_reason' => $disqualificationReason,
                            'status' => $bitrixStatus,
                            'bitrix_created_by' => strtoupper($createdBy['fullname']),
                            'bitrix_created_at' => $createdAt,
                            'bitrix_modified_at' => $modifiedAt,
                        ]);
                    }
                }
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $status = 'error';
            $code = 400;
            $message = $e->getMessage();
        }
        return response()->json(['status' => $status, 'code' => $code, 'message' => $message, 'items' => null], $code);
    }

}
