<?php
include('config.php');

$STH = $LBH->query('select * from Usuario order by id asc');
$STH->setFetchMode(PDO::FETCH_ASSOC);

$data = array();
while($row = $STH->fetch()){
    foreach($row as $key => $value){
        $row[$key] = preg_replace('/"/',"'",$row[$key]);
    }

    $tipo_usuario = '2';
    if($row['Mail'] == 'contacto@loogares.com') {
        $tipo_usuario = '1';
    }

    $data[] = array(
        'id' => $row['Id'],
        'comuna_id' => '',
        'tipo_usuario_id' => $tipo_usuario,
        'nombre' => $row['Nombre'],
        'apellido' => $row['Apellido'],
        'password' => $row['Pass'],
        'slug' => $row['slug'],
        'mail' => $row['Mail'],
        'telefono' => $row['Telefono'],
        'sexo' => $row['Sexo'],
        'web' => $row['Link_1'],
        'facebook' => $row['Link_2'],
        'twitter' => $row['Link_3'],
        'imagen_full' => $row['Imagen_full'],
        'fecha_nacimiento' => $row['FechaNacimiento'],
        'fecha_registro' => $row['FechaRegistro'],
        'fecha_ultima_actividad' => $row['UltimaActividad'],
        'confirmado' => $row['Confirmado'],
        'newsletter_activo' => $row['Reportes'],
        'hash_confirmacion' => $row['HashConfirmacion'],
        'cookie' => $row['Cookie'],
        'facebook_uid' => $row['Facebook_UID'],
        'facebook_no_publicar' => $row['Facebook_NoPublicar'],
        'facebook_data' => $row['Facebook_Data'],
        'facebook_ultima_actividad' => $row['FacebookUltimaActividad'],
        'salt' => ''
    );
}

$i = 0;
foreach($data as $entry){
    $sql = null;
    foreach($entry as $key => $value){
        $sql .= '"'.$value.'", ';
    }
    $sql =  substr($sql, 0, -2);
    $sql = "INSERT INTO usuarios values(" . $sql . ");";
    //echo $sql;
    if(!$DBH->exec($sql)){
        $i++;
        echo "$sql </br>";
    }
}

echo $i;
$DBH->exec("SET FOREIGN_KEY_CHECKS = 1");
?>