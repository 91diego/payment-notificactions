<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
  </head>
  <body>
    <div class="container-fluid">

        <img src="{{ $logoDesarrollo ?? '' }}" alt="Card image" width="364" style="max-width:4167px; padding-bottom: 0; display: inline !important; vertical-align: bottom;">
        <div class="card bg-white text-black border border-white">
            <div class="card-body">
                <h3> {{ $nombreCliente ?? '' }} </h3>
                <small>Ubicacion:<strong> {{ $vivienda ?? '' }}</strong></small><br>
                <small>Prototipo: <strong> {{ $prototipo }}</strong></small><br>
                <small>Torre: <strong>{{ $torre }}</strong></small><br>
                <small>Desarrollo: <strong>{{ $desarrollo }}</strong></small>
            </div>
        </div>
        <h5>Estado de cuenta.</h5>
        <table class="table table-borderless table-sm">
            <thead>
              <tr>
                <th></th>
                <th></th>
              </tr>
            </thead>
            <tbody>
                <tr>
                    <td>PRECIO DE LA VIVIENDA</td>
                    <td>${{ number_format((int)$precioVivienda, 2) }}</td>
                </tr>
                <tr class="table-primary">
                    <td>PAGO MENSUAL.</td>
                    @if ($diasAntesPago >= 90)
                        <td>$0</td>
                    @else
                        <td>${{ number_format($importePago, 2) }}</td>
                    @endif

                </tr>
              {{--<tr>
                <td>SALDO ANTERIOR.</td>
                <td>${{ number_format($precioVivienda - $acumuladoPagos, 2) }}</td>
              </tr>
              <tr>
                <td>PAGOS Y ABONOS.</td>
                <td>${{ number_format($ultimoPagoPDF, 2) }}</td>
              </tr>
              <tr>
                <td>SALDO ACTUAL AL CORTE</td>
                <td>${{ number_format( ($precioVivienda - $acumuladoPagos) - $ultimoPagoPDF, 2) }}</td>
              </tr>--}}
                <tr class="table-danger">
                    <td>TOTAL SALDO VENCIDO</td>
                    <td>${{ number_format($acumuladoSaldoVencido, 2) }}</td>
                </tr>
                <tr class="table-success">
                    <td>TOTAL PAGADO</td>
                    <td>${{ number_format($acumuladoPagos, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="card bg-gray text-black border border-dark rounded-bottom">
            <div class="card-footer">
                FECHA LIMITE DE PAGO <strong><?php $fechaFormato = strftime("%d de %B de %Y", strtotime( date($fechaProximoPago) ));
                echo $fechaFormato; ?> </strong><br>
                NO. DE CUENTA <strong>65-50725502-0</strong><br>
                REFERENCIA <strong>{{ $referenciaPago }}</strong><br>
                CLABE <strong>014320655072550201</strong><br>
                BANCO <strong>SANTANDER</strong><br>
            </div>
        </div>
        <br>
        <h5>Resumen de pagos.</h5>
        <table class="table table-hover table-sm">
            <thead>
              <tr>
                <th>No. Pago</th>
                <th>Fecha pago</th>
                <th>Monto</th>
                <th>Estatus</th>
              </tr>
            </thead>
            <tbody>
                @foreach($layoutEstadoCuenta as $key => $dato)

                    <!-- EXISTE ATRASO EN LOS PAGOS -->
                    @if($dato['monto_pago'] != 0 && $dato['dias_siguiente_pago'] > 0)
                        <tr class="table-danger">
                            <td>{{ $dato['numero_pago'] }}</td>
                            <td>{{ $dato['fecha_pago'] }}</td>
                            <td>${{ number_format($dato['monto_pago'], 2) }}</td>
                            <td>{{ 'MENSUALIDAD ATRASADA' }}</td>
                        </tr>
                    @endif

                    <!-- PAGOS AL CORRIENTE -->
                    @if($dato['monto_pago'] == 0 && $dato['dias_siguiente_pago'] > 0)
                        <tr class="table-success">
                            <td>{{ $dato['numero_pago'] }}</td>
                            <td>{{ $dato['fecha_pago'] }}</td>
                            <td>${{ number_format($dato['monto_pago'], 2) }}</td>
                            <td>{{ 'MENSUALIDAD PAGADA' }}</td>
                        </tr>
                    @endif

                    <!-- PAGOS PENDIENTES -->
                    @if($dato['monto_pago'] != 0 && $dato['dias_siguiente_pago'] < 0)
                        <tr class="table-info">
                            <td>{{ $dato['numero_pago'] }}</td>
                            <td>{{ $dato['fecha_pago'] }}</td>
                            <td>${{ number_format($dato['monto_pago'], 2) }}</td>
                            <td>{{ 'MENSUALIDAD PENDIENTE' }}</td>
                        </tr>
                    @endif

                @endforeach
            </tbody>
        </table>
    </div>

    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
  </body>
</html>
