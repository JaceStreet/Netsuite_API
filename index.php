<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="index.css">
    <title>Gestion de Netsuite</title>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js" type="text/javascript"></script>

    <script>
        function submit(){
            //var intId = document.getElementById('intId').value
            var intId = $("#intId").val();
            var registro = $("#registros").val();
            $.post("prueba.php",{"id":intId, "tipo":registro},function(res){
                alert(res)
                //console.log(res);
		    });
        }
    </script>

</head>
<body>
     <h1>Reportes Netsuite</h1>
     <div id="Content">
        <select class="form-select" id="registros">
            <option selected>Seleccione registro</option>
            <option value="articulo">Articulos</option>
            <option value="devolucion">Devoluciones</option>
            <option value="orden">Oden de Recibo</option>
            <option value="despacho">Despachos</option>
        </select>
        <input type="text" id="intId" placeholder="Ingrese ID">
     </div>
    <div>
        <button class="btn" onclick="submit()">GET</button>
    </div>
</body> 
</html>

