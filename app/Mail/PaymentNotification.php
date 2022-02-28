<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class PaymentNotification extends Mailable
{
    use Queueable, SerializesModels;
    public $sendMailCobranza;
    public $informacionPagos;
    public $pdf;
    public $referencia;
    public $logo;
    public $cuenta;
    public $banco;
    public $clabe;
    public $titular;
    public $acumuladoSaldo;
    public $subject;
    public $fontColor;
    public $titleColor;
    public $backgroundColor;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $ultimoPago, $saldoVencido, $totalPagado, $pathPDF, $subject)
    {
        // Array para almacenar la informacion del estado de cuenta
        $layoutEstadoCuenta = [];
        // Contiene el logo del desarrollo
        $logoDesarrollo = "";
        $cuentaDesarrollo = "";
        $bancoDesarrollo = "";
        $clabeDesarrollo = "";
        $titularDesarrollo = "";
        $pagoMensual = "";
        $color = "";
        $titlesColor = "";
        $background = "";
        $this->sendMailCobranza = $data;
        $this->accountStatus = $data;
        $this->path = $pathPDF;
        $this->subject = $subject;

        foreach ($this->accountStatus['customer_account_status'] as $key => $value) {
            // dd($value);
            $estatusPago = "";
            $diferenciaDias = (int)$value['dias_antes_de_pago'];
            $adeudoCliente = (float)$value['monto_pago'];
            $estatusPago = $value['monto_pago'];
            $pagoMensual = strtotime(Carbon::now()) == strtotime($value['fecha_pago']) ? $value['mes_contrato'] : '';
            // SI EXISTE MONTO A PAGAR Y LA DIFERENCIA DE DIAS ES IGUAL
            // O MAYOR A 90 DIAS, ES ATRASO
            if ( ($diferenciaDias >= 90) && $adeudoCliente > 0 ) {

                array_push($layoutEstadoCuenta, [
                    'numero_pago' => $key,
                    'fecha_pago' => $value['fecha_pago'],
                    'monto_pago' => $estatusPago,
                    'estatus' => 'ATRASO',
                    'dias_siguiente_pago' => $this->value['dias_antes_de_pago'],
                ]);
            } else {

                array_push($layoutEstadoCuenta, [
                    'numero_pago' => $key,
                    'fecha_pago' => $value['fecha_pago'],
                    'monto_pago' => $estatusPago,
                    'dias_siguiente_pago' => $value['dias_antes_de_pago'],
                ]);
            }
            //dd([$value['customer_information'], $value['customer_account_status']]);
        }

        // SE ASIGNA EL LOGOTIPO DEL DESARROLLO EN EL ESTADO DE CUENTA
        switch ($this->accountStatus['customer_information']['desarrollo']) {
            case 'Anuva':
                $color = "#3CC4B4";
                $titlesColor = "#3CC4B4";
                $background = "#3CC4B4";
                $cuentaDesarrollo = "65-50725502-0";
                $bancoDesarrollo = "SANTANDER";
                $clabeDesarrollo = "014320655072550201";
                $titularDesarrollo = "NACIONES UNIDAS CAPITAL SAPI DE CV";
                $logoDesarrollo = "https://mcusercontent.com/3ec4abd994abed22a4c543d03/images/57719a0d-e7ae-4677-bd3b-b33caeba75ea.jpg";
                break;
            case 'Brasilia':
                $color = "#ecf3f3";
                $titlesColor = "#000000";
                $background = "#232323";
                $cuentaDesarrollo = "65-50626311-9";
                $bancoDesarrollo = "SANTANDER";
                $clabeDesarrollo = "014320655062631198";
                $titularDesarrollo = "DESARROLLOS BRASILIA SAPI DE C.V";
                $logoDesarrollo = "";
                //$logoDesarrollo = "https://ci4.googleusercontent.com/proxy/RlvahJM72DNi2U6RnA7CNNEi0vamTkzGhSeJFYoRkokM4Mr8drDVBeEGJ8noWUxgEhA-PktyBNf78nE85yc4ip6vhfBkQFklBzQMSTDLEypFCBEEs8yI85LZyZCekZtg5HHN84DOWI5yU7-TYYOY2KUEx7Oopw=s0-d-e1-ft#https://mcusercontent.com/3ec4abd994abed22a4c543d03/images/54c6f1dc-20a2-b872-3683-4eee4fb37016.png";
                break;
            case 'Aladra':
                $color = "#514E93";
                $titlesColor = "#514E93";
                $background = "#514E93";
                $cuentaDesarrollo = "0267861450201";
                $bancoDesarrollo = "BANBAJIO";
                $clabeDesarrollo = "03032090002079227";
                $titularDesarrollo = "LODI RESIDENCIAL SAPI DE CV.";
                $logoDesarrollo = "https://ci3.googleusercontent.com/proxy/VYE-mz4TjBNduJHjfRFLef4BXIYFg_BjEbs5MLXxfD3y6pLcTh39F1iEBIqEgO9GKPIiu0VTfc3ku8LuJ2zL_7zk17rKke7mKCHP5z23-APA-z0k6ZZ24sPxauI8UtokTQTC3JwJzayDkCNQZKrS14g-92WCiA=s0-d-e1-ft#https://mcusercontent.com/3ec4abd994abed22a4c543d03/images/7f656f2e-f2cb-f790-2b8f-6bc0a157eb56.png";
                break;
        }

        /* DATOS GENERALES */
        // SE OBTIENE LA ULTIMA POSICION DEL ARRAY
        setlocale(LC_ALL,"es");
        $acumuladoPagos = $totalPagado;
        $nombreCliente = $this->accountStatus['customer_information']["cliente"];
        $nombreClienteReferencia = explode(' ', $nombreCliente);
        $vivienda = $this->accountStatus['customer_information']["vivienda"];
        $viviendaReferencia = explode('-', $vivienda);
        $prototipo = $this->accountStatus['customer_information']["prototipo"];
        $torre = $this->accountStatus['customer_information']["torre"];
        $desarrollo = $this->accountStatus['customer_information']["desarrollo"];
        $importePago = $this->accountStatus['customer_information']["monto_pago"];
        $precioVivienda = $this->accountStatus['customer_information']["precio"];
        $fechaProximoPago = $this->accountStatus['customer_information']["fecha_pago"];
        $ultimoPagoPDF = $ultimoPago;
        $acumuladoSaldoVencido = $saldoVencido;
        $referenciaPago = $viviendaReferencia[3].' '.$nombreClienteReferencia[0].''.$nombreClienteReferencia[2];
        $this->referencia = $referenciaPago;
        $this->acumuladoSaldo = $saldoVencido;
        $this->logo = $logoDesarrollo;
        $this->cuenta = $cuentaDesarrollo;
        $this->banco = $bancoDesarrollo;
        $this->clabe = $clabeDesarrollo;
        $this->titular = $titularDesarrollo;
        $this->fontColor = $color;
        $this->titleColor = $titlesColor;
        $this->backgroundColor = $background;
        $diasAntesPago = $this->accountStatus['customer_information']["dias_antes_de_pago"];
        /* FIN DATOS GENERALES */
        // GENERACION DEL PDF
        $pdfEstadoCuenta = '';
        $pdf = App::make('dompdf.wrapper');
        $pdfEstadoCuenta = $pdf->loadView('welcome', compact('referenciaPago', 'acumuladoSaldoVencido', 'ultimoPagoPDF',
        'fechaProximoPago', 'logoDesarrollo', 'layoutEstadoCuenta', 'nombreCliente', 'vivienda', 'prototipo', 'torre',
        'desarrollo', 'importePago', 'precioVivienda', 'acumuladoPagos', 'pagoMensual', 'diasAntesPago'))
        ->save($this->path);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)
        ->view('payment_notifications.payment_notification', [
            'colorFuente' => $this->fontColor,
            'cliente' => $this->accountStatus['customer_information']['cliente'],
            'logo' => $this->logo,
            'cuenta' => $this->cuenta,
            'banco' => $this->banco,
            'clabe' => $this->clabe,
            'titular' => $this->titular,
            'torre' => $this->accountStatus['customer_information']['torre'],
            'desarrollo' => $this->accountStatus['customer_information']['desarrollo'],
            'fecha_pago' => $this->accountStatus['customer_information']['fecha_pago'],
            'monto_pago' => $this->accountStatus['customer_information']['monto_pago'],
            'referencia_pago' => $this->referencia,
            'dias_antes_pago' => $this->accountStatus['customer_information']['dias_antes_de_pago'],
            'acumulado_saldo_vencido' => $this->acumuladoSaldo
        ])->attach($this->path, [
                'as' => 'estado_de_cuenta_'.$this->accountStatus['customer_information']['cliente'].'.pdf',
                'mime' => 'application/pdf',
            ]);
    }
}
