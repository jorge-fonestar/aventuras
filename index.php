<?php 

// Código para mostrar TODOS los errores:
if (isset($_REQUEST['debug'])){
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);
    echo "<h4 style='width:200px;margin:auto;'>DEBUG MODE:ON</h4>";
}

header("Cache-Control: post-check=0, pre-check=0",false);
session_cache_limiter("must-revalidate"); 
session_start();
date_default_timezone_set("Europe/Madrid");


extract($_REQUEST);

include 'funciones.php';

db_conectar();
$sesion = new sesion;
$ACCESO = strtoupper($ACCESO);

if ($ACCESO=='AJAX'){
    include "ajax.php";
    db_desconectar();
    exit;
}

if ($ACCESO=='SALIR') {
    $sesion->desconectar();
    db_desconectar();
    header('Location: '.$config->url);
    exit;
}


include "pag_cabecera.php";

### COMPROBACION DE SESION INICIADA ###

if (isset($ACCESO)){
    echo "Dentro!";
}else{
    ?>
    Formulario para elegir aventura
    <?
}

include "pag_pie.php";
db_desconectar();
?>