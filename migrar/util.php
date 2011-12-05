<?php
include('config.php');

$STH = $LBH->query('select * from EvaluacionRecomendacion order by id asc');
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
        'recomendacion_id' => $row['Id_Recomendacion'],
        'usuario_id' => $row['Usuario_Id'],
        'fecha' => $row['Fecha']
    );
}

$i = 0;
foreach($data as $entry){
    $sql = null;
    foreach($entry as $key => $value){
        $sql .= '"'.$value.'", ';
    }
    $sql =  substr($sql, 0, -2);
    $sql = "INSERT INTO util values(" . $sql . ");";
    //echo $sql;
    if(!$DBH->exec($sql)){
        $i++;
        echo "$sql </br>";
    }
}

echo $i;
$DBH->exec("SET FOREIGN_KEY_CHECKS = 1");
?>