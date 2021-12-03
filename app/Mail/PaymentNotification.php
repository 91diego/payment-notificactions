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
    public $acumuladoSaldo;
    public $subject;

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
        $pagoMensual = "";
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

        //dd($this->accountStatus['customer_account_status']['fecha_pago']);

        // SE ASIGNA EL LOGOTIPO DEL DESARROLLO EN EL ESTADO DE CUENTA
        if ($this->accountStatus['customer_information']['desarrollo'] === "Anuva") {
            $logoDesarrollo = asset('images/Anuva.jpg');
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
            'cliente' => $this->accountStatus['customer_information']['cliente'],
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
