<?php
include('config.php');

$STH = $LBH->query('select * from ImagenesLugar order by id asc');
$STH->setFetchMode(PDO::FETCH_ASSOC);

$data = array();
while($row = $STH->fetch()){
    foreach($row as $key => $value){
        $row[$key] = preg_replace('/"/',"'",$row[$key]);
    }

    $imagen = $row['full'];
    if($imagen == '')
        $imagen = $row['large'];

    $estado_imagen = '2';
    if($row['Id_Estado'] == '1')
        $estado_imagen = '1';
    else if($row['Id_Estado'] == '6')
        $estado_imagen = '3';

    $data[] = array(
        'id' => $row['Id'],
        'usuario_id' => $row['Usuario_Id'],
        'lugar_id' => $row['Id_Lugar'],
        'estado_id' => $estado_imagen,
        'es_enlace' => $row['Es_Enlace'],
        'titulo_enlace' => $row['Titulo_Enlace'],        
        'fecha_creacion' => $row['Fecha_Creacion'],
        'fecha_modificacion' => $row['Fecha_Modificacion'],
        'imagen_full' => $imagen
    );
}

$i = 0;
foreach($data as $entry){
    $sql = null;
    foreach($entry as $key => $value){
        $sql .= '"'.$value.'", ';
    }
    $sql =  substr($sql, 0, -2);
    $sql = "INSERT INTO imagenes_lugar values(" . $sql . ");";
    //echo $sql;
    if($entry['lugar_id'] != '') {
        if(!$DBH->exec($sql)){
            $i++;
            echo "$sql </br>";
        }
    }
}

echo $i;
$DBH->exec("SET FOREIGN_KEY_CHECKS = 1");
?>