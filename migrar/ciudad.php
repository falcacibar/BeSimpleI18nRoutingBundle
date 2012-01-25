<?php
include('config.php');

$STH = $LBH->query('select * from Ciudad order by id asc');
$STH->setFetchMode(PDO::FETCH_ASSOC);

$data = array();
while($row = $STH->fetch()){
    foreach($row as $key => $value){
        $row[$key] = preg_replace('/"/',"'",$row[$key]);
    }


    preg_match('/(?<=[\w]\s)[0-9s\/n]+/',$row['Direccion'], $numero, PREG_OFFSET_CAPTURE);
    $numero = ($numero[0][0] != '')?$numero[0][0]:'s/n';
    $direccion = preg_replace('/(?<=[\w]\s)[0-9s\/n]+/', '', $row['Direccion']);

    $data[] = array(
        'id' => $row['Id'],
        'pais_id' => 1,
        'nombre' => $row['Nombre'],
        'slug' => $row['slug'],
        'mapa' => '',
        'mostrar_lugar' => ($row['Inactivo'] == 'Y')?0:1
    );
}

$i = 0;
foreach($data as $entry){
    $sql = null;
    foreach($entry as $key => $value){
        $sql .= '"'.$value.'", ';
    }
    $sql =  substr($sql, 0, -2);
    $sql = "INSERT INTO ciudad values(" . $sql . ");";
    //echo $sql;
    if(!$DBH->exec($sql)){
        $i++;
        echo "$sql </br>";
    }
}

echo $i;
$DBH->exec("SET FOREIGN_KEY_CHECKS = 1");


$data = array();

$data[] = array(
        'id' => '1',
        'nombre' => 'Chile',
        'slug' => 'chile',
        'mapa' => '',
        'codigo_area' => '+56',
        'mostrar_lugar' => '1',
        'locale' => 'es_cl'
);

$data[] = array(
        'id' => '1',
        'nombre' => 'Chile',
        'slug' => 'chile',
        'mapa' => '',
        'codigo_area' => '+56',
        'mostrar_lugar' => '1',
        'locale' => 'es_AR'
);

$i = 0;
foreach($data as $entry){
    $sql = null;
    foreach($entry as $key => $value){
        $sql .= '"'.$value.'", ';
    }
    $sql =  substr($sql, 0, -2);
    $sql = "INSERT INTO pais values(" . $sql . ");";
    //echo $sql;
    if(!$DBH->exec($sql)){
        $i++;
        echo "$sql </br>";
    }
}

echo "<br />".$i;
$DBH->exec("SET FOREIGN_KEY_CHECKS = 1");

?>