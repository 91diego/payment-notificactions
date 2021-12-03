<table align="center" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="bodyTable">
    <tr>
        <td align="center" valign="top" id="bodyCell">
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="center" valign="top" id="templateHeader" data-template-container>
                        <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" class="templateContainer">
                            <tr>
                                <td valign="top" class="headerContainer"></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td align="center" valign="top" id="templateBody" data-template-container>
                        <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" class="templateContainer">
                            <tr>
                                <td valign="top" class="bodyContainer">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnImageBlock" style="min-width:100%;">
                                        <tbody class="mcnImageBlockOuter">
                                            <tr>
                                                <td valign="top" style="padding:9px" class="mcnImageBlockInner">
                                                    <table align="left" width="100%" border="0" cellpadding="0" cellspacing="0" class="mcnImageContentContainer" style="min-width:100%;">
                                                        <tbody>
                                                            <tr>
                                                                <td class="mcnImageContent" valign="top" style="padding-right: 9px; padding-left: 9px; padding-top: 0; padding-bottom: 0; text-align:center;">
                                                                    <img align="center" alt="" src="https://mcusercontent.com/3ec4abd994abed22a4c543d03/images/57719a0d-e7ae-4677-bd3b-b33caeba75ea.jpg" width="564" style="max-width:4167px; padding-bottom: 0; display: inline !important; vertical-align: bottom;" class="mcnImage">
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnTextBlock" style="min-width:100%;">
                                        <tbody class="mcnTextBlockOuter">
                                            <tr>
                                                <td valign="top" class="mcnTextBlockInner" style="padding-top:9px;">
                                                    <table align="left" border="0" cellpadding="0" cellspacing="0" style="max-width:100%; min-width:100%;" width="100%" class="mcnTextContentContainer">
                                                        <tbody>
                                                            <tr>
                                                                <td valign="top" class="mcnTextContent" style="padding-top:0; padding-right:18px; padding-bottom:9px; padding-left:18px;">
                                                                    <h3 class="null" style="text-align: left;"><span style="font-size:19px"><span style="font-family:source sans pro,helvetica neue,helvetica,arial,sans-serif"><span style="color:#33cccc">Estimado {{ $cliente }}</span></span></span><br>&nbsp;</h3>
                                                                    <div style="text-align: center;"><font face="source sans pro, helvetica neue, helvetica, arial, sans-serif" size="3">Por este medio le recordamos que se acerca la fecha de pago de la mensualidad del Departamento que&nbsp;adquirió&nbsp;en el desarrollo inmobiliario denominado {{ $desarrollo }}.</font></div>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnBoxedTextBlock" style="min-width:100%;">
                                        <tbody class="mcnBoxedTextBlockOuter">
                                            <tr>
                                                <td valign="top" class="mcnBoxedTextBlockInner">
                                                    <table align="left" border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;" class="mcnBoxedTextContentContainer">
                                                        <tbody>
                                                            <tr>
                                                                <td style="padding-top:9px; padding-left:18px; padding-bottom:9px; padding-right:18px;">

                                                                    <table border="0" cellspacing="0" class="mcnTextContentContainer" width="100%" style="min-width: 100% !important;background-color: #404040;">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td valign="top" class="mcnTextContent" style="padding: 18px;color: #F2F2F2;font-family: Helvetica;font-size: 14px;font-weight: normal;text-align: center;">
																					<span style="font-size:16px">
																						<span style="font-family:source sans pro,helvetica neue,helvetica,arial,sans-serif">
																						<strong>
                                                                                            @if ($dias_antes_pago >= 90)
                                                                                                Fecha de Pago:&nbsp; Inmediatamente<br>
                                                                                                Monto a pagar: $&nbsp;{{ number_format($acumulado_saldo_vencido, 2) }}&nbsp;pesos M.N.</strong>
                                                                                            @else
                                                                                                Fecha de Pago:&nbsp;<?php $fechaFormato = strftime("%d de %B de %Y", strtotime( date($fecha_pago) ));
                                                                                                echo $fechaFormato; ?><br>
                                                                                                Monto a pagar: $&nbsp;{{ number_format($monto_pago, 2) }}&nbsp;pesos M.N.</strong>
                                                                                            @endif

																						</span>
																					</span>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnTextBlock" style="min-width:100%;">
                                        <tbody class="mcnTextBlockOuter">
                                            <tr>
                                                <td valign="top" class="mcnTextBlockInner" style="padding-top:9px;">
                                                    <table align="left" border="0" cellpadding="0" cellspacing="0" style="max-width:100%; min-width:100%;" width="100%" class="mcnTextContentContainer">
                                                        <tbody>
                                                            <tr>
                                                                <td valign="top" class="mcnTextContent" style="padding-top:0; padding-right:18px; padding-bottom:9px; padding-left:18px;">
                                                                    <div style="text-align: center;"><span style="font-size:16px"><span style="font-family:source sans pro,helvetica neue,helvetica,arial,sans-serif">Con gusto le proporcionamos los datos Bancarios para efectuar su pago:</span></span>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnBoxedTextBlock" style="min-width:100%;">
                                        <tbody class="mcnBoxedTextBlockOuter">
                                            <tr>
                                                <td valign="top" class="mcnBoxedTextBlockInner">
                                                    <table align="left" border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;" class="mcnBoxedTextContentContainer">
                                                        <tbody>
                                                            <tr>
                                                                <td style="padding-top:9px; padding-left:18px; padding-bottom:9px; padding-right:18px;">
                                                                    <table border="0" cellspacing="0" class="mcnTextContentContainer" width="100%" style="min-width: 100% !important;background-color: #404040;">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td valign="top" class="mcnTextContent" style="padding: 18px;color: #F2F2F2;font-family: Helvetica;font-size: 14px;font-weight: normal;text-align: center;">
                                                                                    <div style="text-align: center;"><strong><span style="font-size:16px"><span style="font-family:source sans pro,helvetica neue,helvetica,arial,sans-serif"><span style="color:#33cccc">Para depósitos con cheque o transferencia en ventanilla:</span><br>
																						Cuenta: 65-50725502-0
																						Banco: SANTANDER
																						<br><br>
																						<span style="color:#33cccc">Para depósito con cheque o&nbsp;transferencia en ventanilla:</span><br>
																						CLABE: 014320655072550201<br>
																						Nombre de Titular: NACIONES UNIDAS CAPITAL SAPI DE CV<br>
																						Referencia: {{ $referencia_pago }}<br>
																					</div>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnTextBlock" style="min-width:100%;">
                                        <tbody class="mcnTextBlockOuter">
                                            <tr>
                                                <td valign="top" class="mcnTextBlockInner" style="padding-top:9px;">
                                                    <table align="left" border="0" cellpadding="0" cellspacing="0" style="max-width:100%; min-width:100%;" width="100%" class="mcnTextContentContainer">
                                                        <tbody>
                                                            <tr>
                                                                <td valign="top" class="mcnTextContent" style="padding-top:0; padding-right:18px; padding-bottom:9px; padding-left:18px;">
                                                                    <div style="text-align: center;"><font face="source sans pro, helvetica neue, helvetica, arial, sans-serif" size="3">Sin más por el momento quedamos a sus órdenes y le reiteramos nuestro agradecimiento por haber confiado en nuestro proyecto</font>
                                                                        <br>
                                                                        <br>
                                                                        <font face="source sans pro, helvetica neue, helvetica, arial, sans-serif" size="3">Enviar comprobante de pago al correo <a href="mailto:bat@idex.cc">bat@idex.cc</a> o por&nbsp;WhatsApp:&nbsp;<a href="https://api.whatsapp.com/send?phone=523318951453">3318951453</a>.</font>
                                                                        <br>
                                                                        <font face="source sans pro, helvetica neue, helvetica, arial, sans-serif" size="3">&nbsp;</font>
                                                                        <br>
                                                                        <font face="source sans pro, helvetica neue, helvetica, arial, sans-serif" size="3">&nbsp;</font>
                                                                        <br>
                                                                        <font face="source sans pro, helvetica neue, helvetica, arial, sans-serif" size="3">Atte: El Departamento de Cobranza</font>
                                                                        <br>
                                                                        <font face="source sans pro, helvetica neue, helvetica, arial, sans-serif" size="3">&nbsp;</font>
                                                                        <br>
                                                                        <font face="source sans pro, helvetica neue, helvetica, arial, sans-serif" size="3">Muchas Gracias&nbsp;</font>
                                                                        <br>
                                                                        <font face="source sans pro, helvetica neue, helvetica, arial, sans-serif" size="3">Saludos</font></div>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnBoxedTextBlock" style="min-width:100%;">
                                        <tbody class="mcnBoxedTextBlockOuter">
                                            <tr>
                                                <td valign="top" class="mcnBoxedTextBlockInner">
                                                    <table align="left" border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;" class="mcnBoxedTextContentContainer">
                                                        <tbody>
                                                            <tr>
                                                                <td style="padding-top:9px; padding-left:18px; padding-bottom:9px; padding-right:18px;">
                                                                    <table border="0" cellspacing="0" class="mcnTextContentContainer" width="100%" style="min-width: 100% !important;background-color: #08C9B9;">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td valign="top" class="mcnTextContent" style="padding: 18px;color: #F2F2F2;font-family: Helvetica;font-size: 14px;font-weight: normal;text-align: center;">
                                                                                    <div style="text-align: center;"><span style="font-size:16px"><span style="font-family:source sans pro,helvetica neue,helvetica,arial,sans-serif"><strong>P</strong></span></span><span style="font-size:18px"><span style="font-family:source sans pro,helvetica neue,helvetica,arial,sans-serif"><strong>ara mayores informes con Beatriz Arellano<br>
																						Email:&nbsp;<a href="mailto:bat@idex.cc">bat@idex.cc</a></strong></span></span>
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnTextBlock" style="min-width:100%;">
                                        <tbody class="mcnTextBlockOuter">
                                            <tr>
                                                <td valign="top" class="mcnTextBlockInner" style="padding-top:9px;">
                                                    <table align="left" border="0" cellpadding="0" cellspacing="0" style="max-width:100%; min-width:100%;" width="100%" class="mcnTextContentContainer">
                                                        <tbody>
                                                            <tr>
                                                                <td valign="top" class="mcnTextContent" style="padding-top:0; padding-right:18px; padding-bottom:9px; padding-left:18px;">
                                                                    <div dir="ltr" style="text-align: center;">
                                                                        <a href="https://www.facebook.com/idexinmobiliaria/" target="_blank"><img data-file-id="12721379" height="50" src="https://mcusercontent.com/3ec4abd994abed22a4c543d03/images/0c92c2e0-4a45-4819-a126-6a84e5b58c34.png" style="border: 0px; width: 50px; height: 50px; margin: 0px;" width="50"></a>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
                                                                        <a href="https://www.instagram.com/idexinmobiliaria/?hl=es-la" target="_blank"><img data-file-id="12721375" height="50" src="https://mcusercontent.com/3ec4abd994abed22a4c543d03/images/f6918e00-2f81-47bb-8a93-f11e152a52bc.png" style="border: 0px  ; width: 50px; height: 50px; margin: 0px;" width="50"></a>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
                                                                        <a href="https://ar.linkedin.com/company/idex-inmobiliaria" target="_blank"><img data-file-id="12721399" height="50" src="https://mcusercontent.com/3ec4abd994abed22a4c543d03/images/4de28f94-e2b0-4909-a1a1-ae75277b6dbf.png" style="border: 0px  ; width: 50px; height: 50px; margin: 0px;" width="50"></a>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
                                                                        <a href="https://www.youtube.com/channel/UCk42RTpPbQrynqCMn5yOInw" target="_blank"><img data-file-id="12721403" height="35" src="https://mcusercontent.com/3ec4abd994abed22a4c543d03/images/eb7294c3-0522-47d3-b8b1-ed84a7e61119.png" style="border: 0px  ; width: 50px; height: 35px; margin: 0px;" width="50"></a>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnImageBlock" style="min-width:100%;">
                                        <tbody class="mcnImageBlockOuter">
                                            <tr>
                                                <td valign="top" style="padding:9px" class="mcnImageBlockInner">
                                                    <table align="left" width="100%" border="0" cellpadding="0" cellspacing="0" class="mcnImageContentContainer" style="min-width:100%;">
                                                        <tbody>
                                                            <tr>
                                                                <td class="mcnImageContent" valign="top" style="padding-right: 9px; padding-left: 9px; padding-top: 0; padding-bottom: 0; text-align:center;">
                                                                    <a href="https://idex.cc/" title="" class="" target="_blank">
                                                                        <img align="center" alt="" src="https://gallery.mailchimp.com/3ec4abd994abed22a4c543d03/images/c2dc79f8-e22e-42e8-8824-6abf5b0213d9.jpg" width="300" style="max-width:600px; padding-bottom: 0; display: inline !important; vertical-align: bottom;" class="mcnRetinaImage">
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnTextBlock" style="min-width:100%;">
                                        <tbody class="mcnTextBlockOuter">
                                            <tr>
                                                <td valign="top" class="mcnTextBlockInner" style="padding-top:9px;">
                                                    <table align="left" border="0" cellpadding="0" cellspacing="0" style="max-width:100%; min-width:100%;" width="100%" class="mcnTextContentContainer">
                                                        <tbody>
                                                            <tr>
                                                                <td valign="top" class="mcnTextContent" style="padding-top:0; padding-right:18px; padding-bottom:9px; padding-left:18px;">
																	<div style="text-align: center;"><span style="font-family:source sans pro,helvetica neue,helvetica,arial,sans-serif">
																		Isabel Prieto #800<br>
																		Guadalajara, Jal<br>
																		Italia Providencia<br>
																		Tel. 36150218</span>
																	</div>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td align="center" valign="top" id="templateFooter" data-template-container>
                        <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" class="templateContainer">
                            <tr>
                                <td valign="top" class="footerContainer">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnFollowBlock" style="min-width:100%;">
                                        <tbody class="mcnFollowBlockOuter">
                                            <tr>
                                                <td align="center" valign="top" style="padding:9px" class="mcnFollowBlockInner">
                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnFollowContentContainer" style="min-width:100%;">
                                                        <tbody>
                                                            <tr>
                                                                <td align="center" style="padding-left:9px;padding-right:9px;">
                                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;" class="mcnFollowContent">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td align="center" valign="top" style="padding-top:9px; padding-right:9px; padding-left:9px;">
                                                                                    <table align="center" border="0" cellpadding="0" cellspacing="0">
                                                                                        <tbody>
                                                                                            <tr>
                                                                                                <td align="center" valign="top">
                                                                                                    <table align="left" border="0" cellpadding="0" cellspacing="0" style="display:inline;">
                                                                                                        <tbody>
                                                                                                            <tr>
                                                                                                                <td valign="top" style="padding-right:10px; padding-bottom:9px;" class="mcnFollowContentItemContainer">
                                                                                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnFollowContentItem">
                                                                                                                        <tbody>
                                                                                                                            <tr>
                                                                                                                                <td align="left" valign="middle" style="padding-top:5px; padding-right:10px; padding-bottom:5px; padding-left:9px;">
                                                                                                                                    <table align="left" border="0" cellpadding="0" cellspacing="0" width="">
                                                                                                                                        <tbody>
                                                                                                                                            <tr>
                                                                                                                                                <td align="center" valign="middle" width="24" class="mcnFollowIconContent">
                                                                                                                                                    <a href="https://www.facebook.com/Brasilia10.Residencial/" target="_blank"><img src="https://cdn-images.mailchimp.com/icons/social-block-v2/outline-light-facebook-48.png" alt="Facebook" style="display:block;" height="24" width="24" class=""></a>
                                                                                                                                                </td>
                                                                                                                                            </tr>
                                                                                                                                        </tbody>
                                                                                                                                    </table>
                                                                                                                                </td>
                                                                                                                            </tr>
                                                                                                                        </tbody>
                                                                                                                    </table>
                                                                                                                </td>
                                                                                                            </tr>
                                                                                                        </tbody>
                                                                                                    </table>
                                                                                                    <table align="left" border="0" cellpadding="0" cellspacing="0" style="display:inline;">
                                                                                                        <tbody>
                                                                                                            <tr>
                                                                                                                <td valign="top" style="padding-right:10px; padding-bottom:9px;" class="mcnFollowContentItemContainer">
                                                                                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnFollowContentItem">
                                                                                                                        <tbody>
                                                                                                                            <tr>
                                                                                                                                <td align="left" valign="middle" style="padding-top:5px; padding-right:10px; padding-bottom:5px; padding-left:9px;">
                                                                                                                                    <table align="left" border="0" cellpadding="0" cellspacing="0" width="">
                                                                                                                                        <tbody>
                                                                                                                                            <tr>

                                                                                                                                                <td align="center" valign="middle" width="24" class="mcnFollowIconContent">
                                                                                                                                                    <a href="https://www.youtube.com/user/idexmx" target="_blank"><img src="https://cdn-images.mailchimp.com/icons/social-block-v2/outline-light-youtube-48.png" alt="YouTube" style="display:block;" height="24" width="24" class=""></a>
                                                                                                                                                </td>

                                                                                                                                            </tr>
                                                                                                                                        </tbody>
                                                                                                                                    </table>
                                                                                                                                </td>
                                                                                                                            </tr>
                                                                                                                        </tbody>
                                                                                                                    </table>
                                                                                                                </td>
                                                                                                            </tr>
                                                                                                        </tbody>
                                                                                                    </table>
                                                                                                    <table align="left" border="0" cellpadding="0" cellspacing="0" style="display:inline;">
                                                                                                        <tbody>
                                                                                                            <tr>
                                                                                                                <td valign="top" style="padding-right:10px; padding-bottom:9px;" class="mcnFollowContentItemContainer">
                                                                                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnFollowContentItem">
                                                                                                                        <tbody>
                                                                                                                            <tr>
                                                                                                                                <td align="left" valign="middle" style="padding-top:5px; padding-right:10px; padding-bottom:5px; padding-left:9px;">
                                                                                                                                    <table align="left" border="0" cellpadding="0" cellspacing="0" width="">
                                                                                                                                        <tbody>
                                                                                                                                            <tr>
                                                                                                                                                <td align="center" valign="middle" width="24" class="mcnFollowIconContent">
                                                                                                                                                    <a href="https://brasilia10.com" target="_blank"><img src="https://cdn-images.mailchimp.com/icons/social-block-v2/outline-light-link-48.png" alt="Website" style="display:block;" height="24" width="24" class=""></a>
                                                                                                                                                </td>
                                                                                                                                            </tr>
                                                                                                                                        </tbody>
                                                                                                                                    </table>
                                                                                                                                </td>
                                                                                                                            </tr>
                                                                                                                        </tbody>
                                                                                                                    </table>
                                                                                                                </td>
                                                                                                            </tr>
                                                                                                        </tbody>
                                                                                                    </table>
                                                                                                    <table align="left" border="0" cellpadding="0" cellspacing="0" style="display:inline;">
                                                                                                        <tbody>
                                                                                                            <tr>
                                                                                                                <td valign="top" style="padding-right:0; padding-bottom:9px;" class="mcnFollowContentItemContainer">
                                                                                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnFollowContentItem">
                                                                                                                        <tbody>
                                                                                                                            <tr>
                                                                                                                                <td align="left" valign="middle" style="padding-top:5px; padding-right:10px; padding-bottom:5px; padding-left:9px;">
                                                                                                                                    <table align="left" border="0" cellpadding="0" cellspacing="0" width="">
                                                                                                                                        <tbody>
                                                                                                                                            <tr>

                                                                                                                                                <td align="center" valign="middle" width="24" class="mcnFollowIconContent">
                                                                                                                                                    <a href="https://www.instagram.com/Brasilia10.Residencial/" target="_blank"><img src="https://cdn-images.mailchimp.com/icons/social-block-v2/outline-light-instagram-48.png" alt="Instagram" style="display:block;" height="24" width="24" class=""></a>
                                                                                                                                                </td>

                                                                                                                                            </tr>
                                                                                                                                        </tbody>
                                                                                                                                    </table>
                                                                                                                                </td>
                                                                                                                            </tr>
                                                                                                                        </tbody>
                                                                                                                    </table>
                                                                                                                </td>
                                                                                                            </tr>
                                                                                                        </tbody>
                                                                                                    </table>
                                                                                                </td>
                                                                                            </tr>
                                                                                        </tbody>
                                                                                    </table>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnDividerBlock" style="min-width:100%;">
                                        <tbody class="mcnDividerBlockOuter">
                                            <tr>
                                                <td class="mcnDividerBlockInner" style="min-width:100%; padding:18px;">
                                                    <table class="mcnDividerContent" border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width: 100%;border-top: 2px solid #FFF7F7;">
                                                        <tbody>
                                                            <tr>
                                                                <td>
                                                                    <span></span>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnTextBlock" style="min-width:100%;">
                                        <tbody class="mcnTextBlockOuter">
                                            <tr>
                                                <td valign="top" class="mcnTextBlockInner" style="padding-top:9px;">
                                                    <table align="left" border="0" cellpadding="0" cellspacing="0" style="max-width:100%; min-width:100%;" width="100%" class="mcnTextContentContainer">
                                                        <tbody>
                                                            <tr>

                                                                <td valign="top" class="mcnTextContent" style="padding-top:0; padding-right:18px; padding-bottom:9px; padding-left:18px;">
                                                                    <em>Copyright © {{ date('Y') }} IDEX, All rights reserved.</em>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
