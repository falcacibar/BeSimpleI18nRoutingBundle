<?php
include('config.php');

$data = array();

$data[] = array(
        'nombre' => 'Por revisar'
);

$data[] = array(
        'nombre' => 'Aprobado'
);

$data[] = array(
        'nombre' => 'Eliminado'
);

$data[] = array(
        'nombre' => 'Cerrado'
);

$data[] = array(
        'nombre' => 'Reportado'
);

$data[] = array(
        'nombre' => 'Por confirmar'
);

$data[] = array(
        'nombre' => 'Activo'
);

$data[] = array(
        'nombre' => 'Inactivo'
);

$i = 0;
foreach($data as $entry){
    $sql = null;
    foreach($entry as $key => $value){
        $sql .= '"'.$value.'", ';
    }
    $sql =  substr($sql, 0, -2);
    $sql = "INSERT INTO estado (nombre) values(" . $sql . ");";
    //echo $sql;
    if(!$DBH->exec($sql)){
        $i++;
        echo "$sql </br>";
    }
}

echo $i;
$DBH->exec("SET FOREIGN_KEY_CHECKS = 1");
?>