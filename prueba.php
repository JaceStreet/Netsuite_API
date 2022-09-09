<?php
require_once 'class/getInventory.php';
require_once 'class/getPurchaseOrder.php';
require_once 'PHPToolkit/NetSuiteService.php';
require 'vendor/autoload.php';

if(isset($_POST["id"]) && isset($_POST["tipo"])){

    $internal_id = $_POST["id"];

    switch ($_POST["tipo"]){
        case "articulo":
            $inventArticle = getInventory::item($internal_id);
            //echo $inventArticle;
            echo "SE GENERÓ ARCHIVO";
            break;

        case "orden":
            $inventArticle = getPurchaseOrder::item($internal_id);
            //echo $inventArticle;
            echo "SE GENERÓ ARCHIVO";
            break;
    }
    
}
?>