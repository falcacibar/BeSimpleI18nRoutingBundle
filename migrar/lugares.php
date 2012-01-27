<?php
include('config.php');

$STH = $LBH->query('select * from Lugares order by Id asc');
$STH->setFetchMode(PDO::FETCH_ASSOC);

$data = array();
while($row = $STH->fetch()){
    $profesional = null;
    $numero = null;
    $direccion = null;
    $idLugar = $row['Id'];

    foreach($row as $key => $value){
        $row[$key] = preg_replace('/"/',"'",$row[$key]);
    }

    if($row['Arquitecto'] != ''){
        $profesional = $row['Arquitecto'];
    }else if($row['Escultor'] != ''){
        $profesional = $row['Escultor'];
    }else if($row['Paisajista'] != ''){
        $profesional = $row['Paisajista'];
    }else if($row['Artista'] != ''){
        $profesional =  $row['Artista'];
    }else{
        $profesional = "";
    }

    $estado_lugar = '2';
    if($row['Id_Estado'] == '1')
        $estado_lugar = '1';    
    else if($row['Id_Estado'] == '6')
        $estado_lugar = '3';
    else if($row['Id_Estado'] == '7')
        $estado_lugar = '4';

    preg_match('/(?<=[\w]\s)[0-9s\/n]+/',$row['Direccion'], $numero, PREG_OFFSET_CAPTURE);
    $numero = ($numero[0][0] != '')?$numero[0][0]:'s/n';
    $direccion = preg_replace('/(?<=[\w]\s)[0-9s\/n]+/', '', $row['Direccion']);    

    $data[] = array(
        'id' => $idLugar,
        'comuna_id' => $row['Comuna'],
        'sector_id' => $row['Barrio'],
        'tipo_lugar_id' => $row['Id_Tipo'],
        'estado_id' => $estado_lugar,
        'usuario_id' => $row['Usuario_Id'],
        'nombre' => $row['Nombre'],        
        'slug' => $row['Slug'],
        'calle' => $direccion,
        'numero' => $numero,
        'detalle' => ($row['Detalle'] == '')?"":$row['Detalle'],
        'descripcion' => ($row['Descripcion'] == '')?"":"'".$row['Descripcion']."'",
        'dueno_id' => ($row['TieneDueno'] > 0)? $row['TieneDueno']:0,
        'mapx' => $row['Coord_MapX'],
        'mapy' => $row['Coord_MapY'],
        'profesional' => $profesional,  
        'agno_construccion' => ($row['Agno_Construccion'] == '')?"":$row['Agno_Construccion'],
        'materiales' => ($row['Materiales'] == '')?"":$row['Materiales'],
        'sitio_web' => ($row['Sitio_Web'] == '')?"":$row['Sitio_Web'],
        'facebook' => ($row['Facebook'] == '')?"":$row['Facebook'],
        'twitter' => ($row['Twitter'] == '')?"":$row['Twitter'],
        'mail' => ($row['mail'] == '')?"":$row['mail'],
        'telefono1' => '',
        'telefono2' => '',
        'telefono3' => '',
        'estrellas' => ($row['Puntuacion'] > 0)? $row['Puntuacion']:0,
        'visitas' => ($row['Vistas'] > 0)? $row['Vistas']:0,
        'utiles' => ($row['Evaluacion'] > 0)? $row['Evaluacion']:0,
        'fecha_agregado' => $row['Fecha'],
        'fecha_ultima_recomendacion' => $row['FechaUltimaRecomendacion'],
        'total_recomendaciones' => ($row['NumeroRecomendaciones'] > 0)?$row['NumeroRecomendaciones']:0,
        'precio' => ($row['Precio'] > 0)? $row['Precio']:0,
        'precio_inicial' => ($row['PrecioInicial'] > 0)? $row['PrecioInicial']:0,
        'prioridad_web' => ($row['Orden_Sector'] > 0)? $row['Orden_Sector']:0
    );

    $i = 1;
    $telefonos = null;
    $telefonos = $TTH = $LBH->query("select * from TelefonosLugar where Id_Lugar = $idLugar");
    if($telefonos){
        foreach($telefonos as $telefono){
            $data[sizeOf($data)-1]['telefono'.$i] = $telefono['Telefono'];
            $i++;
        }
    }
}

$i = 0;

foreach($data as $entry){
    $sql = null;
    foreach($entry as $key => $value){
        $sql .= '"'.$value.'", ';
    }
    $sql =  substr($sql, 0, -2);
    $sql = "INSERT INTO lugares values(" . $sql . ");";

    if(!$DBH->exec($sql)){
        $i++;
        echo "$sql </br>";
    }
}

echo $i;
$DBH->exec("SET FOREIGN_KEY_CHECKS = 1");
//SET FOREIGN_KEY_CHECKS = 0;
//truncate table lugares;
//SET FOREIGN_KEY_CHECKS = 1
?>