<?php
include('config.php');

$data = array();

$data[] = array(
        'id' => '1',
        'nombre' => 'ROLE_ADMIN'
);

$data[] = array(
        'id' => '2',
        'nombre' => 'ROLE_USER'
);

$i = 0;
foreach($data as $entry){
    $sql = null;
    foreach($entry as $key => $value){
        $sql .= '"'.$value.'", ';
    }
    $sql =  substr($sql, 0, -2);
    $sql = "INSERT INTO tipo_usuario values(" . $sql . ");";
    //echo $sql;
    if(!$DBH->exec($sql)){
        $i++;
        echo "$sql </br>";
    }
}

echo $i;
$DBH->exec("SET FOREIGN_KEY_CHECKS = 1");
?>