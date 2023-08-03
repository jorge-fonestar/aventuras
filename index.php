<?php 
header('Content-Type: text/html; charset=utf-8');
header("Cache-Control: post-check=0, pre-check=0",false);
session_cache_limiter("must-revalidate"); 
session_start();
date_default_timezone_set("Europe/Madrid");


// Código para mostrar TODOS los errores:
if (isset($_REQUEST['debug'])){
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);
    echo "<h4 style='width:200px;margin:auto;'>DEBUG MODE:ON</h4>";
}




extract($_REQUEST);

include 'funciones.php';

db_conectar();
$sesion = new sesion;
//$ACCESO = strtoupper($ACCESO);

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
$debug = false;
if ($debug) echo "ACCESO: $ACCESO<br>";

if (isset($ACCESO) and $ACCESO!=''){

    $Paso = base64_decode($ACCESO);
    if ($Paso=='0'){
        $INTRO = "Has terminado esta aventura. <br>Pulsa el botón de Aceptar para regresar al menú de inicio.";

    }else{

        // Cargamos los datos del paso
        if ($debug) echo "(Paso INI: $Paso)<br>";
        $SQL="SELECT * FROM PASOS WHERE ID = '$Paso'";
        $row = seleccionar_una($SQL); 
        extract($row); 

        if ($respuesta!=''){
            
            // Validamos para saber si cargamos PASO_OK o PASO_KO
            $arrRESPUESTAS = array_map('txt_limpio',explode(';', $RESPUESTA));
            $arrPASOS_OK = array_map('txt_limpio',explode(';', $PASO_OK));

            
            $respuesta = txt_limpio($respuesta);
            $PosFound = array_search($respuesta, $arrRESPUESTAS);
            
            if ($debug){
                print_r($arrRESPUESTAS);
                echo "<br>respuesta: $respuesta<br>";
                echo "<br>PosFound: $PosFound<br>";
            }

            if ($PosFound>-1){
                $Paso = $arrPASOS_OK[$PosFound];
                if ($debug) echo "Respuesta '$respuesta' = OK; Pos=$PosFound<br>";
            }else{
                $Paso = $PASO_KO;
                if ($debug) echo "Respuesta '$respuesta' = KO<br>";
            }
            
        }

        //Pintar PASO
        if ($debug) echo "(Paso $Paso)<br>";
        $URL = base64_encode($Paso);
        $SQL="SELECT * FROM PASOS WHERE ID = '$Paso'";
        $row = seleccionar_una($SQL); 
        extract($row);
        $INTRO = utf8_encode("$INTRO");
        $INTRO = str_replace("\r", "<br>", "$INTRO");
    }

    echo $INTRO;
    ?>
    <form action='<?php echo $config->baseurl."/".$URL;?>' method='post'>
        <?php if ($RESPUESTA!=''){ ?>
            <input type='text' id='respuesta' name='respuesta'>
        <?php }else{ ?>
            <input type='hidden' id='respuesta' name='respuesta' value='NEXT-PASO'>
        <?php } ?>
        <button type='submit' id='aceptar'>Aceptar</button>
    </form>
    <?php


}else{

    // MENU PRINCIPAL
    if ($CATEGORIA==''){
        ?>
        <h1>Elige una categoría</h1>
        <?
        $SQLWEB="SELECT DISTINCT(CATEGORIA) FROM HISTORIAS WHERE PUBLIC = 1 ORDER BY ORDEN";
        $dataQueryWeb = seleccionar($SQLWEB);  
        while ($rowweb = $dataQueryWeb->fetch_assoc()) {
            extract($rowweb);
            $CATEGORIA = utf8_encode("$CATEGORIA");
            echo "<form action='$config->baseurl/$URL' method='post'>
                <input class='aventura' type='submit' name='CATEGORIA' value='$CATEGORIA'/>
            </form>";
        }

    }else{

        ?>
        <h1>Elige una aventura</h1>
        <?
        $SQLWEB="SELECT * FROM HISTORIAS WHERE CATEGORIA = '$CATEGORIA' AND PUBLIC = 1 ORDER BY ORDEN";
        $dataQueryWeb = seleccionar($SQLWEB);  
        while ($rowweb = $dataQueryWeb->fetch_assoc()) {
            extract($rowweb);
            $URL = base64_encode($PASO_INI);
            $TITULO = utf8_encode("$TITULO");
            $LORE = utf8_encode("$LORE");
            if ($COLOR_BG=='') $COLOR_BG = '#000';
            if ($COLOR_TEXT=='') $COLOR_TEXT = '#FFF';
            
            echo "<a href='/aventuras/$URL' style='text-decoration: none; color: $COLOR_TEXT !important'>
                    <div class='aventura' style='background-color: $COLOR_BG !important'>
                        <h2>$TITULO</h2>
                        $LORE    
                    </div>
                </a>";
        }
    }


    
}

include "pag_pie.php";
db_desconectar();



function txt_limpio($txt){
    $txt = trim($txt);
    $txt = strtoupper($txt);

    return $txt;
}

?>