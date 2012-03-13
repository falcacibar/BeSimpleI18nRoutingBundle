<?php
include('config.php');

require_once('usuarios_web.php');

$STH = $LBH->query('select * from Usuario order by id asc');
$STH->setFetchMode(PDO::FETCH_ASSOC);

$data = array();
while($row = $STH->fetch()){
    foreach($row as $key => $value){
        $row[$key] = preg_replace('/"/',"'",$row[$key]);
    }

    $tipo_usuario = '2';
    if($row['Id'] == '1') {
        $tipo_usuario = '1';
    }

    $estado_usuario = '7';
    if($row['Id_Estado'] == '1')
        $estado_usuario = '6';
    else if($row['Id_Estado'] == '6')
        $estado_usuario = '8';

    $imagen = $row['Imagen_full'];
    if($imagen == '')
        $imagen = $row['Imagen_96'];
    if($imagen == '')
        $imagen = 'default.gif';

    $data[] = array(
        'id' => $row['Id'],
        'tipo_usuario_id' => $tipo_usuario,
        'estado_id' => $estado_usuario,
        'pais_id' => null,
        'ciudad_id' => null,
        'comuna_id' => null,
        'nombre' => $row['Nombre'],
        'apellido' => $row['Apellido'],
        'password' => $row['Pass'],
        'sha1password' => 0,
        'slug' => $row['slug'],
        'mail' => $row['Mail'],
        'telefono' => $row['Telefono'],
        'sexo' => $row['Sexo'],
        'web' => $row['Link_1'],
        'facebook' => $row['Link_2'],
        'twitter' => $row['Link_3'],
        'imagen_full' => $imagen,
        'fecha_nacimiento' => $row['FechaNacimiento'],
        'mostrar_edad' => '1',
        'fecha_registro' => $row['FechaRegistro'],
        'fecha_ultima_actividad' => $row['UltimaActividad'],
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

/* Actualizamos Sectores con id = 0 */
$query_pais = "UPDATE usuarios SET pais_id = NULL WHERE pais_id = 0";
$query_ciudad = "UPDATE usuarios SET ciudad_id = NULL WHERE ciudad_id = 0";
$query_comuna = "UPDATE usuarios SET comuna_id = NULL WHERE comuna_id = 0";

if(!$DBH->exec($query_pais) || !$DBH->exec($query_ciudad) || !$DBH->exec($query_comuna)) {
    echo "sector_id not updated" . ' <br>';
}

echo $i;
$DBH->exec("SET FOREIGN_KEY_CHECKS = 1");
?>