<?php
include('config.php');

// Acciones 'Ya estuve' asociadas a cada recomendación existente
$STH = $DBH->query('select actividad_reciente.id as lol from `actividad_reciente` 
left join util
on util.id = actividad_reciente.entidad_id
where entidad = "Loogares\\UsuarioBundle\\Entity\\Util" and util.id IS NULL');
$STH->setFetchMode(PDO::FETCH_ASSOC);

$data = array();
while($row = $STH->fetch()){
    foreach($row as $key => $value){
        $row[$key] = preg_replace('/"/',"'",$row[$key]);
    }

    $data[] = $row['lol'];
    echo 'lol';
}

$i = 0;
foreach($data as $entry){
    $sql = "DELETE FROM actividad_reciente where id = $entry";
    //echo $sql;
    $i++;

}

$DBH->exec("SET FOREIGN_KEY_CHECKS = 1");
?>