<?php

namespace App\Traits;

use DateTime;
use Illuminate\Support\Facades\DB;

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
        $responsableUrl = "$this->bitrixSite$this->bitrixToken/user.get?ID=$id";
        // $this->bitrixSite.'/rest/1/'.$this->bitrixToken.'/crm.deal.fields';

        // OBTIENE LA RESPUESTA DE LA API REST BITRIX
        $responseAPI = file_get_contents($responsableUrl);
        // CAMPOS DE LA RESPUESTA
        $jsonResponse = json_decode($responseAPI, true);
        return [
            "fullname" => $jsonResponse["result"][0]["NAME"]." ".$jsonResponse["result"][0]["LAST_NAME"]
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
        // $this->bitrixSite.'/rest/1/'.$this->bitrixToken.'/crm.deal.fields';

        // OBTIENE LA RESPUESTA DE LA API REST BITRIX
        $responseAPI = file_get_contents($contactUrl);
        // CAMPOS DE LA RESPUESTA
        $jsonResponse = json_decode($responseAPI, true);

        return [
            "fullname" => $jsonResponse["result"]["NAME"]." ".$jsonResponse["result"]["LAST_NAME"],
            "phone" => isset($jsonResponse["result"]["PHONE"][0]["VALUE"]) ? $jsonResponse["result"]["PHONE"][0]["VALUE"] : "Sin numero registrado",
            "email" => isset($jsonResponse["result"]["EMAIL"][0]["VALUE"]) ? $jsonResponse["result"]["EMAIL"][0]["VALUE"] : "Sin correo registrado"
        ];
    }

    /**
     * Obtiene el nombre del desarrollo del CRM
     * @param  int  $id
     * @return string placeName
     */
    public function getPlaceName($id){

        $fieldsDeals = "$this->bitrixSite$this->bitrixToken/crm.deal.fields";
        // $this->bitrixSite.'/rest/1/'.$this->bitrixToken.'/crm.deal.fields';

        // OBTIENE LA RESPUESTA DE LA API REST BITRIX
        $responseAPI = file_get_contents($fieldsDeals);
        // CAMPOS DE LA RESPUESTA
        $fields = json_decode($responseAPI, true);
        // NUMERO DE CAMPOS EN LA POSICION DEL ARRAY
        for ($i = 0; $i < count($fields['result']['UF_CRM_5D12A1A9D28ED']['items']); $i++) {

            if ($fields['result']['UF_CRM_5D12A1A9D28ED']['items'][$i]["ID"] == $id) {
                return isset($fields['result']['UF_CRM_5D12A1A9D28ED']['items'][$i]["VALUE"]) ?
                $fields['result']['UF_CRM_5D12A1A9D28ED']['items'][$i]["VALUE"]: 'Sin desarrollo asignado';
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

}
