<?php
include('config.php');

$STH = $LBH->query('select * from Pedido');
$STH->setFetchMode(PDO::FETCH_ASSOC);

$data = array();

while($row = $STH->fetch()){
    foreach($row as $key => $value){
        $row[$key] = preg_replace('/"/',"'",$row[$key]);
    }

    $data[] = array(
        'id' => $row['Id'],
        'nombre' => $row['Nombre'],
        'link_base' => $row['Link'],
        'imagen_chica' => $row['ImagenChica'],
        'imagen_grande' => $row['ImagenGrande']
    );
}

$i = 0;

foreach($data as $entry){
    $sql = null;
    foreach($entry as $key => $value){
        $sql .= '"'.$value.'", ';
    }
    $sql =  substr($sql, 0, -2);
    $sql = "INSERT INTO servicios_pedido values(" . $sql . ");";
    //echo $sql;
    if(!$DBH->exec($sql)){
        $i++;
        echo "$sql </br>";
    }
}

echo $i;
$DBH->exec("SET FOREIGN_KEY_CHECKS = 1");
?>