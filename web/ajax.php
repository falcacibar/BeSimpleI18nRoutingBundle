<?php
session_start();

$_accion = null;
if(isset($_POST['accion'])) $_accion = $_POST['accion'];
if(isset($_GET['accion'])) $_accion = $_GET['accion'];
if(!isset($_accion)) exit;


if(isset($_SESSION[CLAVE.'usuario'])){
    $LOGGED = $db->get_row("SELECT * FROM Usuario WHERE MD5(Id)='{$_SESSION[CLAVE.'usuario']}'");
}

if(file_exists('./ajax/'.$_accion.'.php')){
    require_once './ajax/'.$_accion.'.php';
} else {
    //header ('Content-type: text/html; charset=latin-1');
    //echo $_accion;
    switch($_accion){
        case 'ocultar_num_actividades_amigos_facebook':
            //$_SESSION[CLAVE.'ocultar_num_actividades_amigos_facebook'] = time();
            if(isset($LOGGED->Id))
                $db->update('Usuario',array('FacebookUltimaActividad'=>'NOW()'),"Id={$LOGGED->Id}");
            break;
        break;
        case 'nofblogged':
            echo '<li class="al-cen fl-der lh-25">No estás conectado a Facebook. ¡Hazlo ya! »</li>';
            exit;
        break;
        case 'fblogged':
            require_once 'facebook.php';
            require_once './pag/facebook/init.php';
            if(isset($session)){
                require_once './pag/facebook/header-opciones.php';
                require_once 'fichaLugar.php';
                require_once 'fichaRecomendacion.php';
                require_once 'fichaUsuario.php';
                require_once 'fichaImagenLugar.php';
                ob_start();
                require_once './pag/facebook/header-amigos.php';
                echo utf8_encode(ob_get_clean());
            };
        default: break;
    }
}