<?php
include('config.php');

$STH = $LBH->query('select * from LugarPedido');
$STH->setFetchMode(PDO::FETCH_ASSOC);

$data = array();

while($row = $STH->fetch()){
    foreach($row as $key => $value){
        $row[$key] = preg_replace('/"/',"'",$row[$key]);
    }

    $data[] = array(
        'lugar_id' => $row['IdLugar'],
        'servicio_pedido_id' => $row['IdPedido'],
        'tipo_pedido_id' => $row['TipoPedido'],
        'prioridad' => $row['Prioridad'],
        'referral' => $row['LinkPedido'],
        'promocion' => '0'
    );
}

$i = 0;

foreach($data as $entry){
    $sql = null;
    foreach($entry as $key => $value){
        $sql .= '"'.$value.'", ';
    }
    $sql =  substr($sql, 0, -2);
    $sql = "INSERT INTO pedidos_lugar(lugar_id, servicio_pedido_id, tipo_pedido_id, prioridad, referral, promocion) values(" . $sql . ");";
    //echo $sql;
    if(!$DBH->exec($sql)){
        $i++;
        echo "$sql </br>";
    }
}

echo $i;
$DBH->exec("SET FOREIGN_KEY_CHECKS = 1");
?>