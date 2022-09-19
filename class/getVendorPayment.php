<?php

//Pago factura proveedor
Class getVendorPayment {

    public static function item (string $internalId){
        //GET Info Payment
        $service = new NetSuiteService();
        $request = new GetRequest();
        $request->baseRef = new RecordRef();
        $request->baseRef->internalId = $internalId; //"1508123"// 1509342 //1509042 //1509242 //1508123 ///1509449 / 1509450 / 1509553 
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

        //Datos para el nombre del archivo
        date_default_timezone_set('America/Bogota');
        $año = substr(date('Y', time()),-2);
        $mes = date ('m',time());
        $day = date ('d',time());
        $horaymin = date ('hi',time());
        $nombrearchivo = $año.$mes.$day.$horaymin;

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

                //GET Info Payment
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
                $SN_mail = ($vendor->email);//email proveedor
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
                };
                $SN_RUC = ($vendor->vatRegNumber);//RUC proveedor
                $SN_RS = str_pad(($vendor->companyName),60," ",STR_PAD_RIGHT);//Razon social proveedor
                $mail = str_pad($SN_mail,50," ",STR_PAD_RIGHT);

                //Campos de Invoice
                $lpinvoice = sizeof($invoice->customFieldList->customField);
                for ($iiii = 0, $llll = $lpinvoice; $iiii < $llll ; $iiii++){
                    $arrrr = ($invoice->customFieldList->customField)[$iiii];
                    $idddd = $arrrr->scriptId;
                    if ($idddd == 'custbody_lmry_serie_doc_cxp'){
                        $Serie_invoice = ($arrrr->value);
                    };

                    if ($idddd == 'custbody_lmry_num_preimpreso'){
                        $Correlativo_invoice = str_pad($arrrr->value,15,"0",STR_PAD_LEFT);
                    };
                };
                $dateinvoice = $invoice->tranDate;
                $duedateinvoice = $invoice->dueDate;

                //Construccion txt
                if ($SN_tp == 6) {
                    $Tipo_orden = '01';//Pago Proveedores es Código 01(Tabla 01)
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
                if ($SN_tp == 6) {
                    $formapago = '4';//Abono en cuenta CCI (Tabla 02)
                };
                if ($currency == 'US Dollar'){
                    $CCIVendor = $SN_CCIU;
                }elseif($currency == 'Sol'){
                    $CCIVendor = $SN_CCIS;
                };
                $invoicedate = date("Ymd", strtotime($dateinvoice));
                $invoiceduedate = date("Ymd", strtotime($duedateinvoice));
                $nroInvoice = $Serie_invoice."-".$Correlativo_invoice;
                $moduloRaiz = rand(50, 99);
                $digControl = "XX";
                if ($SN_tp == 6) {
                    $Subtp_pago = ' ';//Sub tipo de pago (Tabla 04)
                }else {
                    $Subtp_pago = '@';//Sub tipo de pago (Tabla 04)
                };
                if ($type_apply == 'Factura de compra') {
                    $Signo = '+';//Signo el sistema
                }else {
                    $Signo = '-';//Signo el sistema
                };
 
                $concatenado=$Tipo_orden.$ref1y2.$moneda.$CCI.$paymentdate.$SN_RUC.$SN_RS.$formapago.$CCIVendor.$invoicedate.$invoiceduedate.$nroInvoice.$importe_neto.$moduloRaiz.$digControl.$Subtp_pago.$Signo.$mail;
                $fi = fopen("F".$nombrearchivo.".txt","a");

                fputs($fi,$concatenado);
                fputs($fi,"\n");
            }
        }
        fclose($fi);
        return $nombrearchivo;
    }
}