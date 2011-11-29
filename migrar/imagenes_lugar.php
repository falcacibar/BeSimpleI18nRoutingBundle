<?php
include('config.php');

$STH = $LBH->query('select * from ImagenesLugar order by id asc');
$STH->setFetchMode(PDO::FETCH_ASSOC);

$data = array();
while($row = $STH->fetch()){
    foreach($row as $key => $value){
        $row[$key] = preg_replace('/"/',"'",$row[$key]);
    }

    $data[] = array(
        'id' => $row['Id'],
        'usuario_id' => $row['Usuario_Id'],
        'lugar_id' => $row['Id_Lugar'],
        'estado_imagen_id' => $row['Id_Estado'],
        'titulo_enlace' => $row[''],
        'es_enlace' => $row['Id_Caracteristica'],
        'fecha_creacion' => $row['Fecha_Creacion'],
        'fecha_modificacion' => $row['Fecha_Modificacion'],
        'imagen_full' => $row['full']
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
    if(!$DBH->exec($sql)){
        $i++;
        echo "$sql </br>";
    }
}

echo $i;
$DBH->exec("SET FOREIGN_KEY_CHECKS = 1");
?>