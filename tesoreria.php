<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="tesoreria.css">
    <title>Gestion Tesoreria</title>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js" type="text/javascript"></script>

    <script>
        function submit(){
            //var intId = document.getElementById('intId').value
            var intId = $("#intId").val()
            var registro = $("#registros").val()
            $.post("BE_tesoreria.php",{"id":intId, "tipo":registro},function(res){
                alert(res)
                //console.log(res);
		    });
        }
    </script>

</head>
<body>
     <h1>Intranet Netsuite - Tesorería</h1>
     <div id="Content">
        <select class="form-select" id="registros">
            <option selected>Seleccione registro</option>
            <option value="pagoFactura">Pago factura proveedor</option>
        </select>
        <input type="text" id="intId" placeholder="Ingrese ID">
     </div>
    <div>
        <button class="btn" onclick="submit()">Submit</button>
    </div>
</body> 
</html>

