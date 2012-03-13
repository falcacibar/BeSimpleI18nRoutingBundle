<?php
include('config.php');

$STH = $LBH->query('select * from ActividadReciente order by id asc');
$STH->setFetchMode(PDO::FETCH_ASSOC);


$data = array();
while($row = $STH->fetch()){
    foreach($row as $key => $value){
        $row[$key] = preg_replace('/"/',"'",$row[$key]);
    }

    $entidad = $row['Tabla'];
    $tipo_actividad_reciente = 1;
    $query = '';
    if($entidad == 'Recomendacion') {
        $entidad = "Loogares\\UsuarioBundle\\Entity\\Recomendacion";
    }

    else if($entidad == 'ImagenesLugar') {
        $entidad = "Loogares\\LugarBundle\\Entity\\ImagenLugar";
    }

    else if($entidad == 'Lugares') {
        $entidad = "Loogares\\LugarBundle\\Entity\\Lugar";
    }

    else if($entidad == 'RecomendacionEditada') {
        $entidad = "Loogares\\UsuarioBundle\\Entity\\Recomendacion";
        $tipo_actividad_reciente = 2;
    }

    else if($entidad == 'EvaluacionRecomendacion') {
        $entidad = "Loogares\\UsuarioBundle\\Entity\\Util";
    }

    if($entidad != 'ComentarioBlog'){
        echo $row['Id'];
        $data[] = array(
            'id' => $row['Id'],
            'usuario_id' => $row['Usuario_Id'],
            'ciudad_id' => $row['Id_Ciudad'],
            'estado_id' => 2,
            'tipo_actividad_reciente_id' => $tipo_actividad_reciente,
            'entidad' => $entidad,
            'entidad_id' => $row['Id_Tabla'],
            'fecha' => $row['Fecha']
        );
    }    
}

$i = 0;
foreach($data as $entry){
    $sql = null;
    foreach($entry as $key => $value){
        $sql .= ":" . $key .", ";
    }
    $sql =  substr($sql, 0, -2);
    $sql = "INSERT INTO actividad_reciente values($sql);";
    //echo $sql.' <br>';
    $stmt = $DBH->prepare($sql);
    if(!$stmt->execute($entry)){
        $i++;
        echo "$sql </br>";
    }
}

echo $i;
$DBH->exec("SET FOREIGN_KEY_CHECKS = 1");
?>