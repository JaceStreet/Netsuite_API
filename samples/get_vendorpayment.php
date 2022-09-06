<?php
require_once '../PHPToolkit/NetSuiteService.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

//GET Info Payment
$service = new NetSuiteService();
$request = new GetRequest();
$request->baseRef = new RecordRef();
$request->baseRef->internalId = "1508123";
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
$request1->baseRef->type = "vendor";
$getVendor = $service->get($request1);
$vendor= $getVendor->readResponse->record;

//navegacion en los documentos pagados
$lppayment = sizeof($paymentvendor->applyList->apply);
$test = $paymentvendor->applyList->apply;
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
        $request2->baseRef->type = "vendorBill";
        $getInvoice = $service->get($request2);
        $invoice= $getInvoice->readResponse->record;

$currency = ($paymentvendor->currencyName);//moneda del pago
$account_id = ($paymentvendor->account->internalId);//ID cuenta de cargo
$account_name = ($paymentvendor->account->name);//nombre de la cuenta de cargo
$fecha_payment = ($paymentvendor->tranDate);//fecha de pago
$memo = str_pad(($paymentvendor->memo),31," ",STR_PAD_LEFT);//memo de pago (Ref02)

//GET Info CuentaCargo
$request3 = new GetRequest();   
$request3->baseRef = new RecordRef();
$request3->baseRef->internalId = $account_id;
$request3->baseRef->type = "account";
$getAccount = $service->get($request3);
$account= $getAccount->readResponse->record->customFieldList;

/*
$invoice_id = ($apply->doc);//ID Factura
$date_apply = ($apply->applyDate);//fecha vencimiento factura
$amount_apply = ($apply->amount);//monto pagado
$type_apply = ($apply->type);
$amount = number_format((float)round($amount_apply, PHP_ROUND_HALF_DOWN),2,'','');
$importe_neto = str_pad($amount,11,"0",STR_PAD_LEFT);
*/

/*se movio al for de apply
//GET Info Invoice
        $request2 = new GetRequest();   
        $request2->baseRef = new RecordRef();
        $request2->baseRef->internalId = $invoice_id;
        $request2->baseRef->type = "vendorBill";
        $getInvoice = $service->get($request2);
        $invoice= $getInvoice->readResponse->record;
*/

//$test =$getAccount->readResponse->record->customFieldList->customField[16];
$lpaccount = sizeof($account->customField);
for ($i = 0, $l = $lpaccount; $i<$l ; $i++){
    $ar = ($account->customField)[$i];
    $id = $ar->scriptId;
    //echo json_encode($idd)."\n";
    if ($id == 'custrecord_lmry_bank_account'){
        $CCI = ($ar->value);
        //echo json_encode ($CCI);
    };
};
//$CCI_AR= ($account->customField)[15];
//$CCI = $CCI_AR->value;

//Campos de Proveedor (Socio de Negocio)
$SN_mail = ($vendor->email);//email proveedor
$lpvendor = sizeof($vendor->customFieldList->customField);//largo de arreglo de campos personalizados
for ($i = 0, $l = $lpvendor; $i<$l ; $i++){
    $ar = ($vendor->customFieldList->customField)[$i];
    $id = $ar->scriptId;
    if ($id == 'custentity_lmry_sunat_tipo_doc_cod'){
        $SN_tp = ($ar->value);
    };
};
//$SN_tipo_doc = ($vendor->customFieldList->customField)[$ari];//tabla sunat_tipo_doc_cod
//$SN_tp = ($SN_tipo_doc->value); //valor 6 - Juridica //
$SN_RUC = ($vendor->vatRegNumber);//RUC proveedor
$SN_RS = str_pad(($vendor->companyName),60," ",STR_PAD_RIGHT);//Razon social proveedor
$SN_CCISOL = ($vendor->customFieldList->customField)[5];
$SN_CCIUSD = ($vendor->customFieldList->customField)[11];
if ($SN_CCISOL->scriptId = 'custentitywow_cci_sol'){
    $SN_CCIS = $SN_CCISOL->value;
};
if ($SN_CCIUSD->scriptId = 'custentitywow_cci_usd'){
    $SN_CCIU = $SN_CCIUSD->value;
};
$mail = str_pad($SN_mail,50," ",STR_PAD_RIGHT);

//Campos de factura aplicada
$Invoice_Serie = ($invoice->customFieldList->customField)[2];//tabla serie_doc_cxp
$Serie_invoice = ($Invoice_Serie->value);//Serie Factura
$Invoice_correlativo = ($invoice->customFieldList->customField)[20];//tabla correlativo_doc_cxp
$Correlativo_invoice = str_pad($Invoice_correlativo->value,15,"0",STR_PAD_LEFT);//Corelativo Factura
$dateinvoice = $invoice->tranDate;
$duedateinvoice = $invoice->dueDate;


if ($SN_tp = 6) {
    $Tipo_orden = '01';//Pago Proveedores es Código 01(Tabla 01)
}else{
    $Tipo_orden = 'XX';
};
$ref1y2 = substr($memo,0,31);//$Serie_invoice."-".$Correlativo_invoice;
if ($currency = 'US Dollar'){
    $moneda = '01';
}elseif($currency = 'Sol'){
    $moneda = '00';
};
$CCI;
$paymentdate = date("Ymd", strtotime($fecha_payment));
$SN_RUC;
$SN_RS;
if ($SN_tp = 6) {
    $formapago = '4';//Abono en cuenta CCI (Tabla 02)
};
if ($currency = 'US Dollar'){
    $CCIVendor = $SN_CCIU;
}elseif($currency = 'Sol'){
    $CCIVendor = $SN_CCIS;
};
$invoicedate = date("Ymd", strtotime($dateinvoice));
$invoiceduedate = date("Ymd", strtotime($duedateinvoice));
$nroInvoice = $Serie_invoice."-".$Correlativo_invoice;
$moduloRaiz = rand(50, 99);
$digControl = "XX";
if ($SN_tp = 6) {
    $Subtp_pago = ' ';//Sub tipo de pago (Tabla 04)
}else {
    $Subtp_pago = '@';//Sub tipo de pago (Tabla 04)
};
if ($type_apply = 'Factura de compra') {
    $Signo = '+';//Signo el sistema
}else {
    $Signo = '-';//Signo el sistema
};
$mail;


echo json_encode($Tipo_orden);
echo json_encode($ref1y2);
echo json_encode($moneda);
echo json_encode($CCI);
echo json_encode($paymentdate);
echo json_encode($SN_RUC);
echo json_encode($SN_RS);
echo json_encode($formapago);
echo json_encode($CCIVendor);
echo json_encode($invoicedate);
echo json_encode($invoiceduedate);
echo json_encode($nroInvoice);
echo json_encode($importe_neto);
echo json_encode($moduloRaiz);
echo json_encode($digControl);
echo json_encode($Subtp_pago);
echo json_encode($Signo);
echo json_encode($mail);

//echo json_encode($test);
//echo json_encode($SN_tipo_doc);
//echo json_encode($invoice);
//echo json_encode($SN_CCIS);
//echo json_encode($CCI);
//custentity_lmry_pa_person_code

//echo json_encode($paymenvendor->applyList->apply)[0]->refNum;

/*
$request1 = new GetRequest();   
$request1->baseRef = new RecordRef();
$request1->baseRef->internalId = $vendor_id;
$request1->baseRef->type = "vendor";
$getResponse1 = $service->get($request1);
*/
//$vendorPayment = $getResponse->readResponse->record;
//$vendor = $getResponse1->readResponse->record;

//echo json_encode($vendorPayment);



//$ref1 = $vendorPayment->memo;
//$person_type = $vendor->companyName;

?>
