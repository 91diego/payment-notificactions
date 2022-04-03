<?php

namespace App\Repositories;

use App\Mail\PaymentNotification;
use App\Services\ConnectionService;
use App\Traits\NeodataTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

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
    public function index($request)
    {
        DB::beginTransaction();
        try {
            $customerPayments = [];
            $connections = $this->connectionService->index($request);
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
                    'dias_antes_de_pago' => $customerAccount->diferencia_dias
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
                'dias_antes_de_pago' => $customer->diferencia_dias
            ],
            'customer_account_status' => $customerAccountStatus
        ];
        // TODO, account status process
        return $customerInformation;
    }

    /**
     * Send email notification
     */
    public function sendEmailNotification($accountStatus, $mailSubject, $pathPDF)
    {
        // ACUMULA EL TOTAL PAGADO SIEMPRE Y CUANDO EL MONTO PAGO SEA IGUAL A 0
        $totalPayment = 0;
        // UTLTIMO PAGO DEL CLIENTE
        $lastPayment = 0;
        // ACUMALDO DEL SALDO VENCIDO
        $balanceDue = 0;
        // ALMACENA LA FECHA INICIAL
        $fechaBase = '';
        // ALMACENA LA FECHA PARA COMPARAR
        $fechaComparacion = '';

        foreach ($accountStatus['customer_account_status'] as $key => $value) {

            $currentPayment = (int)$value['monto_pago'];
            $fechaBase = strtotime(date($value['fecha_pago']));
            // Get next payment
            if ($key === (count($accountStatus['customer_account_status']) - 1) ) {
                $nextPayment = (int)$value['monto_pago'];
                $fechaComparacion = strtotime(date($value['fecha_pago']));
            } else {
                $nextPayment = (int)$accountStatus['customer_account_status'][$key + 1]['monto_pago'];
                $fechaComparacion = strtotime(date($accountStatus['customer_account_status'][$key + 1]['fecha_pago']));
            }
            // SE OBTIENE EL ULTIMO PAGO
            $lastPayment = ($currentPayment === 0 && ($fechaBase < $fechaComparacion))
                && ($nextPayment != 0 && ($fechaComparacion > $fechaBase)) ?
                    (int)$value['mes_contrato'] : '';

            // ACUMULA EL TOTAL PAGADO SIEMPRE Y CUANDO EL MONTO PAGO SEA IGUAL A 0
            (int)$value['monto_pago'] === 0 ? $totalPayment = $totalPayment + (int)$value['mes_contrato'] : '';

            // ACUMULA LA DIFERENCiA DEL PAGO DEL MES Y EL MONTO PAGO CUANDO ESTE ULTIMO ES MAYOR A 0
            // ES DECIR, EXISTIO UN ATRASO EN EL PAGO Y NO SE LIQUIDO POR COMPLETO DICHO ATRASO
            if ((int)$value['monto_pago'] > 0) {
                $diferencia = (int)$value['mes_contrato'] - (int)$value['monto_pago'];
                $totalPayment = $totalPayment + $diferencia;
            }

            // ACUMULA EL TOTAL DE SALDO VENCIDO
            (int)$value['monto_pago'] > 0 && (int)$value['dias_antes_de_pago'] > 0 ?
                $balanceDue = $balanceDue + (int)$value['monto_pago'] : '';
        }
        //$bccEmails = ['ygomez@idex.cc','soportecrm@idex.cc','bat@idex.cc','dgonzalez@milktech.com','cmata@idex.cc']; // Listado de emails bcc
        $bccEmails = ['ygomez@idex.cc', 'soportecrm@idex.cc', 'diegoaglez91@gmail.com']; // Listado de emails bcc
        try {
            // ESTE EL EMIAL DEL CLIENTE => $accountStatus['customer_information']['email']
            Mail::to("dgonzalez@milktech.io")
           // Mail::to($accountStatus['customer_information']['email']) // esta linea contiene el array con los emails de los clientes
            ->bcc($bccEmails) // las lineas con bcc es el envio con copia oculta
            ->send(new PaymentNotification($accountStatus, $lastPayment, $balanceDue, $totalPayment, $pathPDF, $mailSubject));
        } catch (\Exception $error) {
            return $error->getMessage();
        }
        return [
            "cliente" => $accountStatus['customer_information']['cliente'],
            "desarrollo" => $accountStatus['customer_information']['desarrollo'],
            "email" => $accountStatus['customer_information']['email'],
        ];
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
            $acountLogs = [];
            $status = 'success';
            $code = 200;
            $message = "Notificaciones enviadas";
            $pathPDFBrasilia = storage_path().'/notificaciones_cobranza/brasilia/edo_cuenta.pdf';
            $pathPDFAnuva = storage_path().'/notificaciones_cobranza/anuva/edo_cuenta.pdf';
            $pathPDFAladra = storage_path().'/notificaciones_cobranza/aladra/edo_cuenta.pdf';
            foreach ($request as $value) {
                if ($value['develop_name'] == 'BRASILIA') {
                    foreach ($value['items']['customer_payments'] as $customer) {
                        // 7 days before payment
                        if ($customer->diferencia_dias == -7 && str_contains($customer->concepto, 'ENGANCHE') && $customer->saldo_pendiente > 0) {
                            array_push($acountLogs, $this->sendEmailNotification($this->makeAccountStatus($customer, $value['items']['customer_payments']), "Estimado cliente, le recordamos que su proxima fecha de pago es en 7 dias.", $pathPDFBrasilia));
                        }else {
                            array_push($acountLogs, "No existen usuarios para envio en $customer->diferencia_dias");
                        }
                        // 7 days after payment
                        if ($customer->diferencia_dias == 7 && str_contains($customer->concepto, 'ENGANCHE') && $customer->saldo_pendiente > 0) {
                            array_push($acountLogs, $this->sendEmailNotification($this->makeAccountStatus($customer, $value['items']['customer_payments']), "Estimado cliente, le recordamos que existe un atraso de 7 dias en su pago.", $pathPDFBrasilia));
                        }else {
                            array_push($acountLogs, "No existen usuarios para envio en $customer->diferencia_dias");
                        }
                        // 90 days after payment
                        if ($customer->diferencia_dias == 90 && str_contains($customer->concepto, 'ENGANCHE') && $customer->saldo_pendiente > 0) {
                            array_push($acountLogs, $this->sendEmailNotification($this->makeAccountStatus($customer, $value['items']['customer_payments']), "Estimado cliente, le recordamos que existe un atraso de 90 dias en su pago.", $pathPDFBrasilia));
                        }else {
                            array_push($acountLogs, "No existen usuarios para envio en $customer->diferencia_dias");
                        }
                        // 120 days after payment
                        if ($customer->diferencia_dias == 120 && str_contains($customer->concepto, 'ENGANCHE') && $customer->saldo_pendiente > 0) {
                            array_push($acountLogs, $this->sendEmailNotification($this->makeAccountStatus($customer, $value['items']['customer_payments']), "Estimado cliente, le recordamos que existe un atraso de 120 dias en su pago.", $pathPDFBrasilia));
                        }else {
                            array_push($acountLogs, "No existen usuarios para envio en $customer->diferencia_dias");
                        }
                        // test
                        if ($customer->diferencia_dias == 652 && str_contains($customer->concepto, 'ENGANCHE') && $customer->saldo_pendiente > 0) {
                            array_push($acountLogs, $this->sendEmailNotification($this->makeAccountStatus($customer, $value['items']['customer_payments']), "Esto es un test", $pathPDFBrasilia));
                        }else {
                            array_push($acountLogs, "No existen usuarios para envio en $customer->diferencia_dias");
                        }
                    }
                }

                if ($value['develop_name'] == 'ANUVA') {
                    foreach ($value['items']['customer_payments'] as $customer) {
                        // 7 days before payment
                        if ($customer->diferencia_dias == -7 && str_contains($customer->concepto, 'ENGANCHE') && $customer->saldo_pendiente > 0) {
                            array_push($acountLogs, $this->sendEmailNotification($this->makeAccountStatus($customer, $value['items']['customer_payments']), "Estimado cliente, le recordamos que su proxima fecha de pago es en 7 dias.", $pathPDFAnuva));
                        }else {
                            array_push($acountLogs, "No existen usuarios para envio en $customer->diferencia_dias");
                        }
                        // 7 days after payment
                        if ($customer->diferencia_dias == 7 && str_contains($customer->concepto, 'ENGANCHE') && $customer->saldo_pendiente > 0) {
                            array_push($acountLogs, $this->sendEmailNotification($this->makeAccountStatus($customer, $value['items']['customer_payments']), "Estimado cliente, le recordamos que existe un atraso de 7 dias en su pago.", $pathPDFAnuva));
                        }else {
                            array_push($acountLogs, "No existen usuarios para envio en $customer->diferencia_dias");
                        }
                        // 90 days after payment
                        if ($customer->diferencia_dias == 90 && str_contains($customer->concepto, 'ENGANCHE') && $customer->saldo_pendiente > 0) {
                            array_push($acountLogs, $this->sendEmailNotification($this->makeAccountStatus($customer, $value['items']['customer_payments']), "Estimado cliente, le recordamos que existe un atraso de 90 dias en su pago.", $pathPDFAnuva));
                        }else {
                            array_push($acountLogs, "No existen usuarios para envio en $customer->diferencia_dias");
                        }
                        // 120 days after payment
                        if ($customer->diferencia_dias == 120 && str_contains($customer->concepto, 'ENGANCHE') && $customer->saldo_pendiente > 0) {
                            array_push($acountLogs, $this->sendEmailNotification($this->makeAccountStatus($customer, $value['items']['customer_payments']), "Estimado cliente, le recordamos que existe un atraso de 120 dias en su pago.", $pathPDFAnuva));
                        }else {
                            array_push($acountLogs, "No existen usuarios para envio en $customer->diferencia_dias");
                        }
                        // test
                        if ($customer->diferencia_dias == 798 && str_contains($customer->concepto, 'ENGANCHE') && $customer->saldo_pendiente > 0) {
                            array_push($acountLogs, $this->sendEmailNotification($this->makeAccountStatus($customer, $value['items']['customer_payments']), "Esto es un test", $pathPDFAnuva));
                        }else {
                            array_push($acountLogs, "No existen usuarios para envio en $customer->diferencia_dias");
                        }
                    }
                }

                if ($value['develop_name'] == 'ALADRA') {
                    foreach ($value['items']['customer_payments'] as $customer) {
                        // 7 days before payment
                        if ($customer->diferencia_dias == -7 && str_contains($customer->concepto, 'ENGANCHE') && $customer->saldo_pendiente > 0) {
                            array_push($acountLogs, $this->sendEmailNotification($this->makeAccountStatus($customer, $value['items']['customer_payments']), "Estimado cliente, le recordamos que su proxima fecha de pago es en 7 dias.", $pathPDFAladra));
                        }else {
                            array_push($acountLogs, "No existen usuarios para envio en $customer->diferencia_dias");
                        }
                        // 7 days after payment
                        if ($customer->diferencia_dias == 7 && str_contains($customer->concepto, 'ENGANCHE') && $customer->saldo_pendiente > 0) {
                            array_push($acountLogs, $this->sendEmailNotification($this->makeAccountStatus($customer, $value['items']['customer_payments']), "Estimado cliente, le recordamos que existe un atraso de 7 dias en su pago.", $pathPDFAladra));
                        }else {
                            array_push($acountLogs, "No existen usuarios para envio en $customer->diferencia_dias");
                        }
                        // 90 days after payment
                        if ($customer->diferencia_dias == 90 && str_contains($customer->concepto, 'ENGANCHE') && $customer->saldo_pendiente > 0) {
                            array_push($acountLogs, $this->sendEmailNotification($this->makeAccountStatus($customer, $value['items']['customer_payments']), "Estimado cliente, le recordamos que existe un atraso de 90 dias en su pago.", $pathPDFAladra));
                        }else {
                            array_push($acountLogs, "No existen usuarios para envio en $customer->diferencia_dias");
                        }
                        // 120 days after payment
                        if ($customer->diferencia_dias == 120 && str_contains($customer->concepto, 'ENGANCHE') && $customer->saldo_pendiente > 0) {
                            array_push($acountLogs, $this->sendEmailNotification($this->makeAccountStatus($customer, $value['items']['customer_payments']), "Estimado cliente, le recordamos que existe un atraso de 120 dias en su pago.", $pathPDFAladra));
                        }else {
                            array_push($acountLogs, "No existen usuarios para envio en $customer->diferencia_dias");
                        }
                        // test
                        if ($customer->diferencia_dias == 798 && str_contains($customer->concepto, 'ENGANCHE') && $customer->saldo_pendiente > 0) {
                            array_push($acountLogs, $this->sendEmailNotification($this->makeAccountStatus($customer, $value['items']['customer_payments']), "Esto es un test", $pathPDFAladra));
                        }else {
                            array_push($acountLogs, "No existen usuarios para envio en $customer->diferencia_dias");
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();;
            return $e->getMessage();;
        }
        $acountLog = json_encode($acountLogs);
        $log = date("Y-m-d H:i:s") . ", Log estados de cuenta: $acountLog $message";
        Storage::append('log_envio_estados_de_cuenta.txt', $log);
        return response()->json(['status' => $status, 'code' => $code, 'message' => $message, 'items' => null], $code);
    }
}
