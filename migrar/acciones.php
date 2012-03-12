<?php
include('config.php');

// Acciones 'Ya estuve' asociadas a cada recomendación existente
$STH = $LBH->query('select * from Recomendacion where Id_Estado != 0 OR Id_Estado != 6 order by id asc');
$STH->setFetchMode(PDO::FETCH_ASSOC);

$data = array();
while($row = $STH->fetch()){
    foreach($row as $key => $value){
        $row[$key] = preg_replace('/"/',"'",$row[$key]);
    }

    $data[] = array(
        'lugar_id' => $row['Id_Lugar'],
        'usuario_id' => $row['Usuario_Id'],
        'accion_id' => 3,
        'fecha' => $row['Fecha']
    );
}

$i = 0;
foreach($data as $entry){
    $sql = '';
    foreach($entry as $key => $value){
        $sql .= '"'.$value.'", ';
    }
    $sql =  substr($sql, 0, -2);

    $sql = "INSERT INTO acciones_usuario(lugar_id, usuario_id, accion_id, fecha) values(" . $sql . ");";
    //echo $sql;
    if(!$DBH->exec($sql)){
        $i++;
        echo "$sql </br>";
    }
}

// Acciones disponibles
$data = array();

$data[] = array(
    'nombre' => 'Quiero ir'
);
$data[] = array(
    'nombre' => 'Quiero volver'
);
$data[] = array(
    'nombre' => 'Ya estuve'
);
$data[] = array(
    'nombre' => 'Favoritos'
);
$data[] = array(
    'nombre' => utf8_decode('Recomendar después')
);

$i = 0;
foreach($data as $entry){
    $sql = '';
    foreach($entry as $key => $value){
        $sql .= '"'.$value.'", ';
    }
    $sql =  substr($sql, 0, -2);

    $sql = "INSERT INTO acciones(nombre) values(" . $sql . ");";
    //echo $sql;
    if(!$DBH->exec($sql)){
        $i++;
        echo "$sql </br>";
    }
}

echo $i;
$DBH->exec("SET FOREIGN_KEY_CHECKS = 1");
?>