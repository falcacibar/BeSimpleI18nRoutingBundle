<?php
include('config.php');

$data = array();

$data[] = array(
        'nombre' => 'ROLE_ADMIN',
        'descripcion' => 'Administrador'
);

$data[] = array(
        'nombre' => 'ROLE_USER',
        'descripcion' => 'Usuario'
);

$data[] = array(
        'nombre' => 'ROLE_OWNER',
        'descripcion' => 'Dueño de local'
);

$i = 0;
foreach($data as $entry){
    $sql = null;
    foreach($entry as $key => $value){
        $sql .= '"'.$value.'", ';
    }
    $sql =  substr($sql, 0, -2);
    $sql = "INSERT INTO tipo_usuario (nombre, descripcion) values(" . $sql . ");";
    //echo $sql;
    if(!$DBH->exec($sql)){
        $i++;
        echo "$sql </br>";
    }
}

echo $i;
$DBH->exec("SET FOREIGN_KEY_CHECKS = 1");
?>