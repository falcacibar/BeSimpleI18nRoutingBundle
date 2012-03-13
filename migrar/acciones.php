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

    $sql = "INSERT INTO acciones_usuario values(" . ($i+1) . ", " . $sql . ");";
    //echo $sql;
    $i++;
    if(!$DBH->exec($sql)){        
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

    $sql = "INSERT INTO acciones values(" . ($i+1) . ", " . $sql . ");";
    //echo $sql;
    $i++;
    if(!$DBH->exec($sql)){
        echo "$sql </br>";
    }
}

$i = 0;
echo $i;
$DBH->exec("SET FOREIGN_KEY_CHECKS = 1");
?>