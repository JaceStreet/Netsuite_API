<?php
require_once 'class/getVendorPayment.php';
require_once 'PHPToolkit/NetSuiteService.php';
require 'vendor/autoload.php';

if(isset($_POST["id"]) && isset($_POST["tipo"])){

    $internal_id = $_POST["id"];

    switch ($_POST["tipo"]){

        case "pagoFactura":
            //echo "accede";
            $fileFrom = getVendorPayment::item($internal_id);
            echo "Se generó archivo";
            break;

    }
    ftp_quit($id_ftp);
}
?>