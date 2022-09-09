<?php
require_once '../PHPToolkit/NetSuiteService.php';
require_once '../class/DownloadExcel.php';
require '../vendor/autoload.php';

$service = new NetSuiteService();

$request = new GetRequest();
$request->baseRef = new RecordRef();
$request->baseRef->internalId = "1509654"; //1509654,1509655
$request->baseRef->type = "transferOrder";
$getResponse = $service->get($request);
$res = $getResponse->readResponse->record;
//echo json_encode($getResponse);


//PARA GENERAR PEDIDOS CABECERA
$dN = '1';
$tranId = $res->tranId;
/*
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

$cabecera = array(
    'DocNum',
    'NumeroReferenciaDocumento',
    'TipoDocumento',
    'SerieDocumento',
    'CorrelativoDocumento',
    'FechaPedidoDespacho',
    'CodigoClienteSolum',
    'CodClientedelClienteSolum',
    'RucClientedelClienteSolum',
    'NombreClientedelClienteSolum',
    'DireccióndeEntrega',
    'UbigeoDirección',
    'DireccióndeEntrega',
    'CondicionesPago',
    'FechaEntrega',
    'HoraInicio',
    'FechaFinEntrega',
    'HoraFin',
    'TipoCanal',
    'LoteFacturacion',
    'CondicionesPago'
);

$datos = array([
    $dN,
    $tranId,
    "",
    "",
    "",
    $fechaPedDesp,
    $CodCliSolum,
    $CodCliCliSolum,
    $RucCliCliSolum,
    $NombreCliCliSolum,
    $DirEntrega,
    $UbigeoDir,
    $DirEntrega,
    "",
    $fechaEntrega,
    "",
    "",
    "",
    "",
    "",
    "-1",
]);

DownloadExcel::createExcel($datos, $cabecera, 'PedidosCabecera');
*/

//PARA GENERAR ORDEN DETALLE
$itemList = $res->itemList->item;
//echo json_encode($itemList);

$data = array();
$cont = 0;
foreach($itemList as $value){
    $info=array(
        $dN,
        $tranId,
        "","","",
        $cont,
        'wo'.$value->item->internalId,
        $value->description,
        $value->quantity,
        '1',
        'L03',
        '-1'
    );
    $cont++;
    array_push($data, $info);
}

$headers = array(
    'DocNum',
    'UnidadesInventarioporCantidad',
    'TipoDocumento',
    'SerieDocumento',
    'CorrelativoDocumento',
    'CorrelativoLinea',
    'CodArticulo',
    'DescripcionArticuloCliente',
    'Cantidad',
    'UnidadesInventarioporCantidad',
    'TratamientoEspecial',
    'CodigoAlmacen',
    'UomEntry'
);
DownloadExcel::createExcel($data, $headers, 'PedidosDetalle');
?>