<?php
require_once '../PHPToolkit/NetSuiteService.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

//GET Info Payment
$service = new NetSuiteService();
$request = new GetRequest();
$request->baseRef = new RecordRef();
$request->baseRef->internalId = "1510883"; // 1508937 /859051
$request->baseRef->type = "vendorPayment";
$getVendorPayment = $service->get($request);
$paymentvendor=$getVendorPayment->readResponse->record;

//campos de Vendor en payment
$vendor_id = ($paymentvendor->entity->internalId);//ID proveedor
$vendor_name = ($paymentvendor->entity->name);//codigo proveedor

//GET Info Vendor
$request1 = new GetRequest();   
$request1->baseRef = new RecordRef();
$request1->baseRef->internalId = $vendor_id;
$request1->baseRef->type = "employee";
$getVendor = $service->get($request1);
$vendor= $getVendor->readResponse->record;

//navegacion en los documentos pagados
$lppayment = sizeof($paymentvendor->applyList->apply);
$test = $paymentvendor->applyList->apply;

//Datos para el nombre del archivo
date_default_timezone_set('America/Bogota');
$año = substr(date('Y', time()),-2);
$mes = date ('m',time());
$day = date ('d',time());
$horaymin = date ('Hi',time());
$nombrearchivo = $año.$mes.$day.$horaymin;

for ($i = 0, $l = $lppayment; $i<$l ; $i++){
    $ar = ($test)[$i];
    $id = $ar->apply;
    
    if ($id == true){
        $invoice_id = ($ar->doc);//ID Factura
        $date_apply = ($ar->applyDate);//fecha vencimiento factura
        $amount_apply = ($ar->amount);//monto pagado
        $type_apply = ($ar->type);
        $amount = number_format((float)round($amount_apply, PHP_ROUND_HALF_DOWN),2,'','');
        $importe_neto = str_pad($amount,11,"0",STR_PAD_LEFT);

        //GET Info Invoice
        $request2 = new GetRequest();   
        $request2->baseRef = new RecordRef();
        $request2->baseRef->internalId = $invoice_id;
        $request2->baseRef->type = "journalEntry";
        $getInvoice = $service->get($request2);
        $invoice= $getInvoice->readResponse->record;
        $reftext = ($ar->type).($ar->refNum);
        $refpayment = str_pad(($reftext),20," ",STR_PAD_RIGHT); //referencia de pago

        //GET Info Payment
        $currency = ($paymentvendor->currencyName);//moneda del pago
        $account_id = ($paymentvendor->account->internalId);//ID cuenta de cargo
        $account_name = ($paymentvendor->account->name);//nombre de la cuenta de cargo
        $fecha_payment = ($paymentvendor->tranDate);//fecha de pago
        $memo = str_pad(($paymentvendor->memo),31," ",STR_PAD_RIGHT);//memo de pago (Ref1&2)

        //GET Info CuentaCargo 
        $request3 = new GetRequest();   
        $request3->baseRef = new RecordRef();
        $request3->baseRef->internalId = $account_id;
        $request3->baseRef->type = "account";
        $getAccount = $service->get($request3);
        $account= $getAccount->readResponse->record->customFieldList;

        //GET CCI Cuenta de Cargo
        $lpaccount = sizeof($account->customField);
        for ($ii = 0, $ll = $lpaccount; $ii < $ll ; $ii++){
            $arr = ($account->customField)[$ii];
            $idd = $arr->scriptId;
            if ($idd == 'custrecord_lmry_bank_account'){
                $CCI = ($arr->value);
            };
        };

        //Campos de Proveedor (Socio de Negocio)
        $lpvendor = sizeof($vendor->customFieldList->customField);//largo de arreglo de campos personalizados
        for ($iii = 0, $lll = $lpvendor; $iii < $lll ; $iii++){
            $arrr = ($vendor->customFieldList->customField)[$iii];
            $iddd = $arrr->scriptId;
            if ($iddd == 'custentity_lmry_sunat_tipo_doc_cod'){
                $SN_tp = ($arrr->value);
            };
            if ($iddd == 'custentitywow_cci_sol'){
                $SN_CCIS = ($arrr->value);
            };
            if ($iddd == 'custentitywow_cci_usd'){
                $SN_CCIU = ($arrr->value);
            };
            if ($iddd == 'custentity_lmry_sv_taxpayer_number'){
                $SN_RUC=str_pad(($arrr->value),11," ",STR_PAD_RIGHT);
            };
        };
        
        $SN_RS = str_pad(($vendor->entityId),60," ",STR_PAD_RIGHT);//Nombre del empleado

        //Construccion txt
        if ($SN_tp == 1) {
            $Tipo_orden = '13';//Pagos Varios es Código 13(Tabla 01)
        }else{
            $Tipo_orden = 'XX';
        };
        $ref1y2 = substr($memo,0,31);//$Serie_invoice."-".$Correlativo_invoice;
        if ($currency == 'US Dollar'){
            $moneda = '01';
        }elseif($currency == 'Sol'){
            $moneda = '00';
        };
        $CCI;
        $paymentdate = date("Ymd", strtotime($fecha_payment));
        $SN_RUC;
        $SN_RS;
        $formapago = '4';//Abono en cuenta CCI (Tabla 02)
        if ($currency == 'US Dollar'){
            $CCIVendor = $SN_CCIU;
        }elseif($currency == 'Sol'){
            $CCIVendor = $SN_CCIS;
        };
        $moduloRaiz = rand(50, 99);
        $digControl = "XX";
        $Signo = '+';//Signo el sistema
    
        //TXT
        $concatenado=$Tipo_orden.$ref1y2.$moneda.$CCI.$paymentdate.$SN_RUC.$SN_RS.$formapago.$CCIVendor.$importe_neto.$moduloRaiz.$digControl.$Signo.$refpayment;
        $fi = fopen("V".$nombrearchivo.".txt","a")
        or die("problemas al crear archivo");

        fputs($fi,$concatenado);
        fputs($fi,"\n");

        echo json_encode($concatenado);
        
        /*echo json_encode($Tipo_orden);
        echo json_encode($ref1y2);
        echo json_encode($moneda);
        echo json_encode($CCI);
        echo json_encode($paymentdate);
        echo json_encode($SN_RUC);
        echo json_encode($SN_RS);
        echo json_encode($formapago);
        echo json_encode($CCIVendor);
        echo json_encode($importe_neto);
        echo json_encode($moduloRaiz);
        echo json_encode($digControl);
        echo json_encode($Signo);
        echo json_encode($refpayment);
        echo json_decode("\n");*/
    };    
};
?>
