<?php 

if (isset($LoadCombate)){
    //header('Content-type: application/json');
    $SQLWEB="SELECT * FROM REGISTROS WHERE ID_COMBATE = '$LoadCombate'";
    $dataQueryWeb = seleccionar($SQLWEB);  
    while ($rowweb = $dataQueryWeb->fetch_assoc()) {
        extract($rowweb);
        echo "<div class='".strtolower($COLOR)."'>$MINUTO - $TECNICA en $SITUACION</div>";
    }
}

?>