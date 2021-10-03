<?php

namespace App\Repositories;

use App\Services\ConnectionService;
use App\Traits\NeodataTrait;
use Illuminate\Support\Facades\DB;

class NotificationRepository
{
    use NeodataTrait;

    /**
     * Constructor
     */
    Public function __construct(ConnectionService $connectionService)
    {
        $this->connectionService = $connectionService;
    }

    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        DB::beginTransaction();
        try {
            $customerPayments = [];
            $connections = $this->connectionService->index();
            foreach ($connections as $value) {
                array_push($customerPayments, ['develop_name' => $value->name, "items" => $this->getNeodataPayments($value->notification_cases, $value->name)]);
            }
            DB::commit();
            return $customerPayments;
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
    }

    /**
     * @param customer $customer, specific customer
     * @param customers $customers, all array customer
     */
    public function makeAccountStatus($customer, $customers)
    {
        $customerInformation = [];
        $customerAccountStatus = [];
        foreach ($customers as $customerAccount) {
            if($customerAccount->id_cliente_neodata == $customer->id_cliente_neodata) {
                array_push($customerAccountStatus, [
                    'fecha_pago' => $customerAccount->fecha_plan,
                    'monto_pago' => $customerAccount->saldo_pendiente,
                    'precio' => $customerAccount->precio_real,
                    'mes_contrato' => $customerAccount->mes_contrato,
                    'dias_ates_de_pago' => $customerAccount->diferencia_dias
                ]);
            }
        }
        $customerInformation = [
            'customer_information' => [
                'id_cliente_neodata' => $customer->id_cliente_neodata,
                'cliente' => $customer->cliente,
                'vivienda' => $customer->vivienda,
                'prototipo' => $customer->prototipo,
                'metros_cuadrados' => $customer->m2,
                'torre' => $customer->torre,
                'desarrollo' => $customer->desarrollo,
                'email' => $customer->email,
                'fecha_pago' => $customer->fecha_plan,
                'monto_pago' => $customer->saldo_pendiente,
                'precio' => $customer->precio_real,
                // 'mes_contrato' => $customer->mes_contrato,
                'dias_ates_de_pago' => $customer->diferencia_dias
            ],
            'customer_account_status' => $customerAccountStatus
        ];
        // TODO, account status process
        return $customerInformation;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param $request
     */
    public function store($request)
    {
        /**
         * Get records with concept PLAN DEL CREDITO -1
         * And fecha de entrega equals to CURRENT DATE
         */
        try {
            foreach ($request as $value) {
                // return $value['items']['customer_payments'][30]->concepto;
                switch ($value['develop_name']) {
                    case 'BRASILIA':
                        foreach ($value['items']['customer_payments'] as $customer) {
                            if ($customer->diferencia_real_dias == 1403 /*&& $customer->saldo_pendiente > 0 && $customer->concepto == 'PLAN DEL CREDITO- 1'*/) {
                                $customerInformation = $this->makeAccountStatus($customer, $value['items']['customer_payments']);
                            }
                            // TODO, send emails
                            dd($customerInformation);
                            // TODO, store notification if mail was sent
                        }
                        break;

                    case 'ANUVA':
                        foreach ($value['items']['customer_payments'] as $customer) {
                            if ($customer->diferencia_real_dias == 1403 /*&& $customer->saldo_pendiente > 0 && $customer->concepto == 'PLAN DEL CREDITO- 1'*/) {
                                $customerInformation = $this->makeAccountStatus($customer, $value['items']['customer_payments']);
                            }
                            // TODO, send emails
                            dd($customerInformation);
                            // TODO, store notification if mail was sent
                        }
                        break;

                    case 'ALADRA':
                        foreach ($value['items']['customer_payments'] as $customer) {
                            if ($customer->diferencia_real_dias == 1403 /*&& $customer->saldo_pendiente > 0 && $customer->concepto == 'PLAN DEL CREDITO- 1'*/) {
                                $customerInformation = $this->makeAccountStatus($customer, $value['items']['customer_payments']);
                            }
                            // TODO, send emails
                            dd($customerInformation);
                            // TODO, store notification if mail was sent
                        }
                        break;
                }
            }
            return 'Store OK!';
        } catch (\Throwable $th) {
            return $th;
        }
    }

    public function sendNotification($payments)
    {
        dd($payments);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     */
    public function show($id)
    {
        // TODO
    }
}
