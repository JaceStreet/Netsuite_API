<form action="crear.php" method="post">
    <textarea name="contenido"></textarea>
</form>
<?php
$contenido = $_POST['contenido']; 
$archivo = fopen('archivo.txt','a');  
fputs($archivo,$contenido); 
fclose($archivo);   
?>