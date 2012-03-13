<?php
include('config.php');

$STH = $LBH->query('select * from Horario order by id asc');
$STH->setFetchMode(PDO::FETCH_ASSOC);

$data = array();
while($row = $STH->fetch()){
    foreach($row as $key => $value){
        $row[$key] = preg_replace('/"/',"'",$row[$key]);
    }
    
    $data[] = array(
        'id' => $row['Id'],
        'lugar_id' => $row['Id_Lugar'],
        'dia' => $row['Id_Dia'],
        'apertura_am' => $row['Aper_M_L'],
        'cierre_am' => $row['Cierre_M_L'],
        'apertura_pm' => $row['Aper_T_L'],
        'cierre_pm' => $row['Cierre_T_L']
    );
}

$i = 0;
foreach($data as $entry){
    $sql = null;
    foreach($entry as $key => $value){
        $sql .= '"'.$value.'", ';
    }
    $sql =  substr($sql, 0, -2);
    $sql = "INSERT INTO horario values(" . $sql . ");";
    //echo $sql;
    if(!$DBH->exec($sql)){
        $i++;
        echo "$sql </br>";
    }
}

echo $i;
$DBH->exec("SET FOREIGN_KEY_CHECKS = 1");
?>