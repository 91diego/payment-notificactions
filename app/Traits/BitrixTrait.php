<?php

namespace App\Traits;

use App\Models\LastRecord;
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
            $dealsUrl = Http::get("$this->bitrixSite$this->bitrixToken/crm.lead.list?start=$firstRow&FILTER[STATUS_ID]=$phase&FILTER[>DATE_CREATE]=2020-07-31T23:59:59-05:00");
            $jsonDeals = $dealsUrl->json();
            for ($deal = 0; $deal < ceil($jsonDeals['total'] / $rows); $deal++)
            {
                $deal == 0 ? $firstRow = $firstRow : $firstRow = $firstRow + $rows;
                $deal == (intval((ceil($jsonDeals["total"] / $rows)) - 1)) ? intval($jsonDeals["total"] > intval($firstRow) ? $substractionRows = (intval($jsonDeals["total"] - intval($firstRow))) : $substractionRows = (intval($firstRow) - intval($jsonDeals["total"]))) : $substractionRows = $rows;

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
                        'status' => $status,
                        'bitrix_created_by' => strtoupper($createdBy['fullname']),
                        'bitrix_created_at' => $createdAt,
                        'bitrix_modified_at' => $modifiedAt,
                    ]);
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
