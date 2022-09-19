<?php
require_once 'class/getInventory.php';
require_once 'class/getPurchaseOrder.php';
require_once 'class/getDevolucion.php';
require_once 'class/getTransferOrder.php';
require_once 'PHPToolkit/NetSuiteService.php';
require 'vendor/autoload.php';

define("SERVER","20.94.40.28"); //IP del servidor
define("PORT",21); //Puerto
define("USER","wow"); //Usuario
define("PASSWORD","W0VV$@792022"); //Contraseña

$id_ftp=ftp_connect(SERVER,PORT) or die("No se pudo conectar");
ftp_login($id_ftp,USER,PASSWORD);
ftp_pasv($id_ftp,true);

ftp_chdir($id_ftp, "/IN/INVENTORY/TEST");
$dir=ftp_pwd($id_ftp);

if(isset($_POST["id"]) && isset($_POST["tipo"])){

    $internal_id = $_POST["id"];

    switch ($_POST["tipo"]){

        case "articulo":
            $fileFrom = getInventory::item($internal_id);
            echo "sucess";
            /*
            $fileTo = $dir."/".$fileFrom;
            $upload = ftp_put($id_ftp, $fileTo, $fileFrom, FTP_ASCII);
            if (!$upload) {
                echo 'Upload failed!';
            } else {
                echo 'Upload success';
            }
            */
            break;

        case "devolucion":
            [$fileFromCab, $fileFromDet] = getDevolucion::item($internal_id);
            /*
            $fileToCab = $dir."/".$fileFromCab;
            $fileToDet = $dir."/".$fileFromDet;
            $uploadCab = ftp_put($id_ftp, $fileToCab, $fileFromCab, FTP_ASCII);
            $uploadDet = ftp_put($id_ftp, $fileToDet, $fileFromDet, FTP_ASCII);
            if (!$uploadCab || !$uploadDet) {
                echo 'Upload failed!';
            } else {
                echo 'Upload success';
            }
            */
            break;

        case "orden":
            [$fileFromCab, $fileFromDet] = getPurchaseOrder::item($internal_id);
            /*
            $fileToCab = $dir."/".$fileFromCab;
            $fileToDet = $dir."/".$fileFromDet;
            $uploadCab = ftp_put($id_ftp, $fileToCab, $fileFromCab, FTP_ASCII);
            $uploadDet = ftp_put($id_ftp, $fileToDet, $fileFromDet, FTP_ASCII);
            if (!$uploadCab || !$uploadDet) {
                echo 'Upload failed!';
            } else {
                echo 'Upload success';
            }
            */
            break;

        case "despacho":
            [$fileFromCab, $fileFromDet] = getTransferOrder::item($internal_id);
            /*
            $fileToCab = $dir."/".$fileFromCab;
            $fileToDet = $dir."/".$fileFromDet;
            $uploadCab = ftp_put($id_ftp, $fileToCab, $fileFromCab, FTP_ASCII);
            $uploadDet = ftp_put($id_ftp, $fileToDet, $fileFromDet, FTP_ASCII);
            if (!$uploadCab || !$uploadDet) {
                echo 'Upload failed!';
            } else {
                echo 'Upload success';
            }
            */
            break;
    }
    ftp_quit($id_ftp);
}
?>