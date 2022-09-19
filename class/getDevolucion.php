<?php
class getDevolucion {

    public static function item (string $internalId){
        $service = new NetSuiteService();
        $request = new GetRequest();
        $request->baseRef = new RecordRef();
        $request->baseRef->internalId = $internalId;
        $request->baseRef->type = "purchaseOrder";
        $getResponse = $service->get($request);

        if (!$getResponse->readResponse->status->isSuccess) {
            echo "OCURRIÓ UN ERROR";
        }else {
           //PARA GENERAR ORDEN CABECERA
           $res = $getResponse->readResponse->record;
            $dN = '1';
            $tranId = 'PO'.$res->tranId;

            $customfields = $res->customFieldList->customField;

            foreach ($customfields as $field) {
                if(isset($field->value->name)) {
                    switch ($field->value->typeId) {
                        case "630":
                            if ($field->value->internalId == "1" ){
                                $Origen = "N";
                            }else {
                                $Origen = "I";
                            }
                            break;
                    }
                }
            }
            $CodCliSolum = 'P20605977406';
            $CodProvCli = $res->vatRegNum;
            $NomProvCli = $res->billingAddress->addressee;
            $FechaOrdenRecibo = date('Ymd', strtotime(explode("T",$res->dueDate)[0]));
            $fechaEntrega = date('Ymd', strtotime(explode("T",$res->tranDate)[0]));

            $data = array(
                'DocNum'                    => $dN,
                'NumPedido'                 => $tranId,
                'CodigoClienteSolum'        => $CodCliSolum,
                'OrigenOrdenRecibo'         => $Origen,
                'CodProveedorCliente'       => $CodProvCli,
                'NombreProveedorCliente'    => $NomProvCli,
                'FechaOrdenRecibo'          => $FechaOrdenRecibo,
                'Fechaentrega'              => $fechaEntrega,
                'PaisOrigen'                => "",
                'TipoCarga'                 => "",
                'PorcentajeMuestreo'        => "",
            );

            $headers = "";
            $detalle = "";

            foreach ($data as $key => $value) {

                if (mb_strlen($key) > mb_strlen($value)) {
                    $detalle .= str_pad($value, mb_strlen($key));
                    $headers .= $key;
                }elseif (mb_strlen($key) < mb_strlen($value)) {
                    $headers .= str_pad($key, mb_strlen($value));
                    $detalle .= $value;
                }

                $headers .= "\t";
                $detalle .= "\t";
            }

            $texto = $headers."\n".$detalle;

            $today = new DateTime();
            $today->setTimezone(new DateTimeZone('America/Lima'));
            $newToday = $today->format("YmdHis");  
            //Genera archivo txt
            $fh = fopen('SAP_OI_CAB_'.$newToday.'.txt', 'w');
            fwrite($fh, $texto);
            fclose($fh);

            //PARA GENERAR ORDEN DETALLE
            switch ($res->currencyName) {
                case "US Dollar":
                    $currencyName = 'USD';
                    break;
            }
            $itemList = $res->itemList->item;

            $data = array();
            $cont = 0;
            foreach($itemList as $value){
                $info=array(
                    'DocNum'                => $dN,
                    'NumPedido'             => $tranId,
                    'CorrelativoLinea'      => $cont,
                    'CodigoArticulo'        => 'wo'.$value->item->internalId,
                    'Cantidad'              => $value->quantity,
                    'MonedaPrecio'          => $currencyName,
                    'TratamientoEspecial'   => 'N',
                    'CodigoAlmacen'         => 'L01',
                    'UomEntry'              => '-1',
                );
                $cont++;
                array_push($data, $info);
            }

            $headers = "";
            $detalle = "";
            $band = 1;
            foreach ($data as $lista) {
                foreach ($lista as $key => $value) {
                    if (mb_strlen($key) > mb_strlen($value)) {
                        $detalle .= str_pad($value, mb_strlen($key));
                        if ($band == 1) {
                            $headers .= $key;
                            $headers .= "\t";
                        }
                    }elseif (mb_strlen($key) < mb_strlen($value)) {
                        if ($band == 1) {
                            $headers .= str_pad($key, mb_strlen($value));
                            $headers .= "\t";
                        }
                        $detalle .= $value;
                    }
                    $detalle .= "\t";
                }
                $detalle .= "\n";
                $band++;
            }

            $texto = $headers."\n".$detalle;

            $today = new DateTime();
            $today->setTimezone(new DateTimeZone('America/Lima'));
            $newToday = $today->format("YmdHis");  
            //Genera archivo txt
            $fh = fopen('SAP_OI_DET_'.$newToday.'.txt', 'w');
            fwrite($fh, $texto);
            fclose($fh);
        }
    }
}