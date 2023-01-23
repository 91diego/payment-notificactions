<?php

namespace App\Repositories;

use Exception;
use Carbon\Carbon;
use App\Models\Lead;
use App\Models\User;
use App\Models\Deal;
use App\Models\Profile;
use App\Traits\BitrixTrait;
use App\Traits\ResponseTrait;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class LeadRepository
{
    use BitrixTrait, ResponseTrait;

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

    public function getLead($request)
    {
        try {
            $lead = $this->getLeadByIdB24($request->id);
            $validateLeadOnDb = Lead::where('prospecto_bitrix_id', $request->id)->get();
            if (count($validateLeadOnDb) == 0) {
                Lead::create($lead);
                $message = "creado correctamente";
            }else {
                // update
                Lead::where('prospecto_bitrix_id', $lead['prospecto_bitrix_id'])
                    ->update($lead);
                $message = "actualizado correctamente";
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
        return $message;
    }


    /**
     * Retrieve information from specific deal by id
     * @param $request
     */
    public function getDealById($request)
    {
        try {
            $customerInformation = $this->getDealInformation($request['id']);
            $customerContact = $this->dealContact($customerInformation);
            return ['deal' => $customerInformation, 'contact' => $customerContact];
        } catch (\Exception $e) {
            return $e;
        }
    }

    /**
     * Get bitrix deal information
     * @param id $id
     */
    public function getDealInformation($id)
    {
        try {
            $dealUrl = Http::get("$this->bitrixSite$this->bitrixToken/crm.deal.get?ID=$id");
            // $dealUrl = Http::get(env('BITRIX_SITE') . "/rest/1/" . env('BITRIX_TOKEN') . "/crm.deal.get?ID=" . $id);
            $jsonDeal = $dealUrl->json();
            return $jsonDeal['result'];
        } catch (\Exception $e) {
            return $e;
        }
    }

    /**
     * Get contact information from customer
     * @param $request, CONTACT ID
     */
    public function dealContact($request)
    {
        try {
            $customerContactInformation = "$this->bitrixSite$this->bitrixToken/crm.contact.get?ID=" . $request['CONTACT_ID'];
            $response = file_get_contents($customerContactInformation);
            $response = json_decode($response, true);
            return $response['result'];
        } catch (\Exception $e) {
            return $e;
        }
    }

    /**
     * Store deal by id when is created on BITRIX24
     * @param $request
     */
    public function storeDealById($request)
    {
        $code = 201;
        $status = 'success';
        $items = null;
        $message = '';
        DB::beginTransaction();
        try {
            if(isset($request['user']) && count($request['user']) < 1) {
                $code = 400;
                $status = 'error';
                $message = '¡El usuario no ha sido encontrado!';
                return $this->apiResponse($code, $status, $message, $items);
            }
            $realStateDevelopment = '';
            $towerAcronym = '';
            $deliveryDate = '';
            $customerBirthdate = '';
            $customerEmail = '';
            $customerName = '';
            $customerSecondName = '';
            $customerLastName = '';
            $customerEmail = '';
            $customerPhone = '';
            $developmentName = '';

            switch ($request['deal']['UF_CRM_5D12A1A9D28ED']) {
                case '283':
                    $developmentName = 'ANUVA';
                    break;

                case '541':
                    $developmentName = 'ALADRA';
                    break;

                case '151':
                    $developmentName = 'BRASILIA';
                    break;

                case '886':
                    $developmentName = 'BOSQUE CAPITAL';
                    break;

                case '966':
                    $developmentName = 'DEIMARE';
                    break;

                default:
                    $developmentName = 'DESARROLLO NO CONFIGURADO';
                    break;
            }

            $details = "$this->bitrixSite$this->bitrixToken/crm.deal.get?ID=" . $request['deal']['ID'];
            $response = file_get_contents($details);
            $response = json_decode($response, true);
            $realStateDevelopment = $developmentName;
            $towerAcronym = empty($response['result']['UF_CRM_1573063908']) ?
                explode("-", $response['result']["UF_CRM_1573064054413"]) : explode("-", $response['result']["UF_CRM_1573063908"]);
            empty($response['result']['UF_CRM_1586290304']) ?
                $deliveryDate = '' : $deliveryDate = explode('T', $response['result']['UF_CRM_1586290304']);

            if($request['contact']['ID'] == $request['deal']['CONTACT_ID']) {
                isset($request['contact']['BIRTHDATE']) && !empty($request['contact']['BIRTHDATE'])
                    ? $customerBirthdate = explode('T', $request['contact']['BIRTHDATE']) : Carbon::now();
                $customerEmail = isset($request['contact']['EMAIL'][0]['VALUE']) ? strtolower($request['contact']['EMAIL'][0]['VALUE']) : 'SIN EMAIL';
                $customerPhone = isset($request['contact']['PHONE'][0]['VALUE']) ? $request['contact']['PHONE'][0]['VALUE'] : 'SIN TELEFONO';
                $customerName = isset($request['contact']['NAME']) ? ucwords($request['contact']['NAME']) : '';
                $customerSecondName = isset($request['contact']['SECOND_NAME']) ? ucwords($request['contact']['SECOND_NAME']) : '';
                $customerLastName = isset($request['contact']['LAST_NAME']) ? ucwords($request['contact']['LAST_NAME']) : '';
            }

            if( !(Deal::where('deal_id', '=', $request['deal']['ID'])->exists()) ) {
                $deal = new Deal();
                $deal->deal_id = $request['deal']['ID'];
                $deal->lead_id = $request['deal']['LEAD_ID'];
                $deal->real_state_development = $realStateDevelopment;
                $deal->tower_acronym = count($towerAcronym) > 1 ? trim($towerAcronym[0]) : 'S/A';
                $deal->tower = count($towerAcronym) > 1 ? trim($towerAcronym[1]) : 'S/A';
                $deal->floor = count($towerAcronym) > 1 ? trim($towerAcronym[2]) : 'S/A';
                $deal->department = count($towerAcronym) > 1 ? trim($towerAcronym[3]) : 'S/A';
                $deal->delivery_date = is_array($deliveryDate) ? $deliveryDate[0] : $deliveryDate;
                $deal->status = $this->getStages($request['deal']['STAGE_ID']);
                $deal->status_number = $this->b24StatusPosition($this->getStages($request['deal']['STAGE_ID']));
                $deal->payment_method = $this->getPaymentMethodByDealId($request['deal']['ID']);
                if( !(User::where('email', '=', $customerEmail)->exists()) ) {
                    $password = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!#$%&/()=[]*-.,:;"), 0, 10);
                    $user = new User();
                    $user->name = $customerName;
                    $user->email = $customerEmail;
                    $user->password = bcrypt($password);
                    $user->save();
                    $user->assignRole('cliente');

                    $profile = new Profile();
                    $profile->name = $customerName . $customerSecondName;
                    $profile->last_name = $customerLastName;
                    $profile->birthdate = Carbon::now(); // is_array($customerBirthdate) ? $customerBirthdate[0] : $customerBirthdate;
                    $profile->phone = $customerPhone;
                    $profile->email = $customerEmail;
                    $profile->user_id = $user->id;
                    $profile->save();

                    $deal->user_id = $user->id;
                    $code = 200;
                    $status = 'success';
                    $message = '¡El usuario ha sido registrado exitosamente!';
                    $items = $user;
                    $userAccess = [
                        'name' => "$profile->name $profile->last_name",
                        'email' => $user->email,
                        'password' => $password,
                    ];
                    $userEmail = '';
                    $user->email == 'SIN EMAIL' ? $userEmail = 'ti-sistemas@idex.cc' : $userEmail = $user->email;
                    Mail::to($userEmail)->send(new WelcomeMail($userAccess));
                    //Mail::to('dgonzalez@milktech.io')->send(new WelcomeMail($userAccess));
                } else {
                    $userProfile = User::where('email', '=', $customerEmail)->with('profile')->get();
                    $deal->user_id = $userProfile[0]->id;
                    $code = 201;
                    $status = 'success';
                    $message = '¡El usuario ya existe! La negociación ha sido asociada al perfil del usuario.';
                }
                $log = date("Y-m-d H:i:s") . "Deal $deal->user_id: Mensaje: $message, Estatus: $status";
                Storage::append("registro_usuarios_portal_web_$realStateDevelopment.txt", $log);
                $deal->save();
            } else {
                $code = 400;
                $status = 'warning';
                $message = '¡La negociacion actualmente esta asociada a otro perfil de usuario!';
            }
            $log = date("Y-m-d H:i:s") . "Mensaje: $message, Estatus: $status";
            Storage::append("registro_usuarios_$realStateDevelopment.txt", $log);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $code = 400;
            $status = 'error';
            $message = '¡Ha ocurrido un error! ' . $e;
        }
        return $this->apiResponse($code, $status, $message, $items);
    }

}
