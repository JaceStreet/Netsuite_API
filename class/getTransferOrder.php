<?php
class getTransferOrder {

    public static function item (string $internalId){
        $service = new NetSuiteService();
        $request = new GetRequest();
        $request->baseRef = new RecordRef();
        $request->baseRef->internalId = $internalId;
        $request->baseRef->type = "transferOrder";
        $getResponse = $service->get($request);
        $res = $getResponse->readResponse->record;
        
        if (!$getResponse->readResponse->status->isSuccess) {
            echo "OCURRIÓ UN ERROR";
        }else {
            //PARA GENERAR PEDIDOS CABECERA
            $dN = '1';
            if (isset($res->class->name)) {
                $clase = explode(" ",$res->class->name)[1];
            }else {
                $clase = "";
            }

            $tranId = $res->tranId;

            $fechaPedDesp = date('Ymd', strtotime(explode("T",$res->tranDate)[0]));
            $CodCliSolum = "C20605977406";
            $CodCliCliSolum = "20605977406";
            $NombreCliCliSolum = $res->transferLocation->name;
            $fechaEntrega = date('Ymd', strtotime(explode("T",$res->shipDate)[0]));

            $customfields = $res->customFieldList->customField;
            foreach ($customfields as $field) {
                //echo json_encode($field);
                switch ($field->scriptId) {
                    case "custbody24":
                        $RucCliCliSolum = $field->value;
                        break;
                    case "custbody18":
                        $DirEntrega = $field->value;
                        break;
                    case "custbody23":
                        $UbigeoDir = $field->value;
                        break;
                    default:
                    $UbigeoDir = "";
                }
            }

            $data = array(
                'DocNum'                        => $dN,
                'NumeroReferenciaDocumento'     => $tranId,
                'TipoDocumento'                 => "",
                'SerieDocumento'                => "",
                'CorrelativoDocumento'          => "",
                'FechaPedidoDespacho'           => $fechaPedDesp,
                'CodigoClienteSolum'            => $CodCliSolum,
                'CodClientedelClienteSolum'     => $CodCliCliSolum,
                'RucClientedelClienteSolum'     => $RucCliCliSolum,
                'NombreClientedelClienteSolum'  => $NombreCliCliSolum,
                'DireccióndeEntrega'            => $DirEntrega,
                'UbigeoDirección'               => $UbigeoDir,
                'DireccióndeEntrega2'           => $DirEntrega,
                'CondicionesPago'               => "",
                'FechaEntrega'                  => $fechaEntrega,
                'HoraInicio'                    => "",
                'FechaFinEntrega'               => "",
                'HoraFin'                       => "",
                'TipoCanal'                     => $clase,
                'LoteFacturacion'               => "",
                'CondicionesPago2'              => "-1",
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

                $detalle .= "\t";
                $headers .= "\t";
            }

            $texto = $headers."\n".$detalle;

            $today = new DateTime();
            $today->setTimezone(new DateTimeZone('America/Lima'));
            $newToday = $today->format("YmdHis");
            $filename_cab = 'SAP_OD_CAB_'.$newToday.'.txt';
            //Genera archivo txt
            $fh = fopen($filename_cab, 'w');
            fwrite($fh, $texto);
            fclose($fh);


            //PARA GENERAR ORDEN DETALLE
            $itemList = $res->itemList->item;
            //echo json_encode($itemList);

            $data = array();
            $cont = 0;
            foreach($itemList as $value){
                $info=array(
                    'DocNum'                        => $dN,
                    'NumeroReferenciaDocumento'     => $tranId,
                    'TipoDocumento'                 => "",
                    'SerieDocumento'                => "",
                    'CorrelativoDocumento'          => "",
                    'CorrelativoLinea'              => $cont,
                    'CodArticulo'                   => 'wo'.$value->item->internalId,
                    'DescripcionArticuloCliente'    => explode(" ",$value->item->name)[1],                    'Cantidad'                      => $value->quantity,
                    'UnidadesInventarioporCantidad' => '1',
                    'MonedadelPrecio'               => "",
                    'PrecioporUndMedInventario'     => "",
                    'CodigoImpuesto'                => "",
                    'CodigoAlmacen'                 => 'L03',
                    'UomEntry'                      => '-1'
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
                        if ($band == 1) $headers .= $key;

                    }elseif (mb_strlen($key) < mb_strlen($value)) {
                        if ($band == 1) $headers .= str_pad($key, mb_strlen($value));
                        $detalle .= $value;
                    }
                    $detalle .= "\t";
                    $headers .= "\t";
                }
                $detalle .= "\n";
                $band++;
            }

            $texto = $headers."\n".$detalle;

            $today = new DateTime();
            $today->setTimezone(new DateTimeZone('America/Lima'));
            $newToday = $today->format("YmdHis");
            $filename_det = 'SAP_OD_DET_'.$newToday.'.txt';
            //Genera archivo txt
            $fh = fopen($filename_det, 'w');
            fwrite($fh, $texto);
            fclose($fh);
            
            return [$filename_cab, $filename_det];
        }
    }
}