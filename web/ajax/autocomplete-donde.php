<?php
if(!isset($_GET['term'])) exit;
$_term = utf8_decode(urldecode($_GET['term']));
require_once '../config.php';

$f = "{ \"id\" : \"_id_\", \"label\" : \"_nombre_\", \"zona\" : \"_zona_\" , \"value\" : \"_nombre_\"} ";
$f1 = array('_id_','_nombre_','_zona_');

$res = array();

/* ciudad, comuna o sector */
$ciudades = $db->get_results("SELECT * FROM Ciudad WHERE Nombre LIKE '%$_term%'");
if(count($ciudades)){
    foreach($ciudades as $s){
        $res[] = str_replace($f1,array($s->Id,utf8_encode($s->Nombre),'Ciudad'),$f);
    }
}

$sectores = $db->get_results("SELECT * FROM Barrio WHERE Nombre LIKE '%$_term%'");
if(count($sectores)){
    foreach($sectores as $s){
        $res[] = str_replace($f1,array($s->Id,utf8_encode($s->Nombre),'Sector'),$f);
    }
}

$comunas = $db->get_results("SELECT * FROM Comuna WHERE Nombre LIKE '%$_term%'");
if(count($comunas)){
    foreach($comunas as $s){
        $res[] = str_replace($f1,array($s->Id,utf8_encode($s->Nombre),'Comuna'),$f);
    }
}

header ('Content-type: text/html; charset=latin-1');
if(count($res)) echo '['.implode(',',$res).']';