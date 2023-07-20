<?php
global $config;
$config = include('config.php');

global $ID_CLUB;
$ID_CLUB = $_SESSION['ID_CLUB'];

################# BDD ##################

function db_conectar(){
    global $db;
    global $config;

    $db_server=$config->db->host;
    $db_user=$config->db->user;
    $db_pass=$config->db->pass;
    $db_bdd =$config->db->bdd;
    
    $db = new mysqli($db_server, $db_user, $db_pass, $db_bdd);
    if($db->connect_errno > 0){
        die("Unable to connect to database mysqli(Server: $db_server, BDD: $db_bdd) -> [" . $db->connect_error . ']');
    }
    
    return $db;
}
function db_desconectar(){
    global $db;
    $db->close();
}

function seleccionar($sql){
    global $db;

    $db->escape_string($sql);
    $result = $db->query($sql);
    if(!$result and isset($_GET['debug'])){
        echo 'There was an error running the query [' . $db->error . ']';
    }
    return $result;
}

function seleccionar_una($sql){
    global $db;
    //if (developer()) echo "SQL: $sql";
    $data = seleccionar($sql, $db);
    if ($data){
        $row = $data->fetch_assoc();
        $data->free();
    }
    return $row;
}

function hay_resultados($sql){
    global $db;
    $result=seleccionar($sql, $db);
    $cuantos = $result->num_rows;
    return ($cuantos>0);
}

function num_resultados($sql){
    $result=seleccionar($sql);
    return $result->num_rows;
}

function ejecutar($sql, $Ejecutar=1, $Mostrar=0){
    global $db;
    $db->escape_string($sql);
    if (isset($_GET['debug'])) echo $sql."<hr>";
    
    if(!$result = $db->query($sql)){
        return false;
    }
    if ($Mostrar) echo '<p>'.$db->affected_rows.' registros afectados.</p>';
    return true;
}

function action_log($texto, $ID_GRUPO=''){
    if ($ID_GRUPO=='') $ID_GRUPO = $_SESSION['USUARIO']['ID_GRUPO'];
    if (isset($_SESSION['USUARIO']['NOMBRE'])) $usuario = " - USER: ".$_SESSION['USUARIO']['NOMBRE'];
    $usuario = "IP: ".$_SERVER['REMOTE_ADDR'].$usuario;		
    $sql = "INSERT INTO LOG VALUES('', '$usuario', '".date('YmdHis')."', '".htmlspecialchars($texto, ENT_QUOTES)."')";
    $R = ejecutar($sql);
}


function txt_normalizar($string){
    $string = trim($string);
 
    //action_log("NORMALIZAR (0): $string");
    
    $string = str_replace(
        array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä', 'Ã'),
        array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A', 'A'),
        $string
    );
 
    $string = str_replace(
        array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
        array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
        $string
    );
 
    $string = str_replace(
        array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
        array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
        $string
    );
 
    $string = str_replace(
        array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
        array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
        $string
    );
 
    $string = str_replace(
        array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
        array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
        $string
    );
    
    $string = str_replace(
        array('ç', 'Ç'),
        array('c', 'C',),
        $string
    );
//     
//    $string = str_replace(
//        array('ñ', 'Ñ', 'ç', 'Ç'),
//        array('n', 'N', 'c', 'C',),
//        $string
//    );
    
    $string = str_replace(
        array("'", ' ', ' / ', '/', ','),
        array("", '_', '_', '', ''),
        $string
    );

    $string = str_replace("&amp;#039;", "", $string);
    $string = str_replace("&amp;", "", $string);
    $string = str_replace("#039;", "", $string);
    $string = str_replace("&quot;", "", $string);
    
    //echo $string;

    $string = strtolower($string);
    $string = ucfirst($string);
    
    //action_log("NORMALIZAR (1): $string");

    return $string;
}




################# SESION ##################
class sesion{

    function buscar_login_memorizado(){
        if (!$this->empezada()){
            if ((isset($_COOKIE['USUARIO'])) && (isset($_COOKIE['PASS']))){
                $this->conectar($_COOKIE['USUARIO'], $_COOKIE['PASS']);
            }
        }
    }

    // true sesion_empezada() Comprueba que la sesión esta empezada
    function empezada(){
        if (isset($_SESSION['USUARIO'])){
            return true;
        }else{
            return false;
        }
    }
    // true empezar_sesion() Guarda la sesion id en la vble global $_SESSION y envía lee el idioma predetrminado guardado en la cookie
    function conectar($mail, $pass_try){
        global $ID_CLUB;

        $query = "SELECT * FROM USUARIOS WHERE EMAIL = '$mail' and PASS = '".md5($pass_try) ."'";
        $row_user = seleccionar_una($query);
        if ($row_user!=""){
            extract($row_user);

            // Cargamos los datos del usuario
            $_SESSION = array();
            $_SESSION['USUARIO'] = $row_user;
           
            $ID_CLUB = $row_user['ID_CLUB'];
            $_SESSION['ID_CLUB'] = $ID_CLUB;
            return true;

        }else{
            //aviso("El nombre de usuario no existe.");
            action_log("INICIO INCORRECTO - Usr: $mail, Pass: $pass_try");
            return false;
        }
    }

    function desconectar(){
        session_destroy();
        $_SESSION = array();
        setcookie('USUARIO','',time()+60*60*24*50);
        setcookie('PASS','',time()+60*60*24*50);
    }
}

?>
