<?php
include('config.php');

// Array con estado de tablas migradas
$migratedTables = array();

$STH = $DBH->query('SELECT COUNT(id) total FROM estado');
$STH->setFetchMode(PDO::FETCH_ASSOC);

$count = $STH->fetch();

$migratedTables['estado'] = ($count['total'] > 0) ? 1 : 0;



$STH = $DBH->query('SELECT COUNT(id) total FROM lugares');
$STH->setFetchMode(PDO::FETCH_ASSOC);

$count = $STH->fetch();

$migratedTables['lugares'] = ($count['total'] > 0) ? 1 : 0;



$STH = $DBH->query('SELECT COUNT(id) total FROM categorias');
$STH->setFetchMode(PDO::FETCH_ASSOC);

$count = $STH->fetch();

$migratedTables['categorias'] = ($count['total'] > 0) ? 1 : 0;



$STH = $DBH->query('SELECT COUNT(id) total FROM categoria_lugar');
$STH->setFetchMode(PDO::FETCH_ASSOC);

$count = $STH->fetch();

$migratedTables['categoria_lugar'] = ($count['total'] > 0) ? 1 : 0;



$STH = $DBH->query('SELECT COUNT(id) total FROM tipo_categoria');
$STH->setFetchMode(PDO::FETCH_ASSOC);

$count = $STH->fetch();

$migratedTables['tipo_categoria'] = ($count['total'] > 0) ? 1 : 0;



$STH = $DBH->query('SELECT COUNT(id) total FROM caracteristica');
$STH->setFetchMode(PDO::FETCH_ASSOC);

$count = $STH->fetch();

$migratedTables['caracteristica'] = ($count['total'] > 0) ? 1 : 0;



$STH = $DBH->query('SELECT COUNT(id) total FROM caracteristica_lugar');
$STH->setFetchMode(PDO::FETCH_ASSOC);

$count = $STH->fetch();

$migratedTables['caracteristica_lugar'] = ($count['total'] > 0) ? 1 : 0;



$STH = $DBH->query('SELECT COUNT(id) total FROM caracteristica_categoria');
$STH->setFetchMode(PDO::FETCH_ASSOC);

$count = $STH->fetch();

$migratedTables['caracteristica_categoria'] = ($count['total'] > 0) ? 1 : 0;



$STH = $DBH->query('SELECT COUNT(id) total FROM imagenes_lugar');
$STH->setFetchMode(PDO::FETCH_ASSOC);

$count = $STH->fetch();

$migratedTables['imagenes_lugar'] = ($count['total'] > 0) ? 1 : 0;



$STH = $DBH->query('SELECT COUNT(id) total FROM subcategoria');
$STH->setFetchMode(PDO::FETCH_ASSOC);

$count = $STH->fetch();

$migratedTables['subcategoria'] = ($count['total'] > 0) ? 1 : 0;



$STH = $DBH->query('SELECT COUNT(id) total FROM subcategoria_lugar');
$STH->setFetchMode(PDO::FETCH_ASSOC);

$count = $STH->fetch();

$migratedTables['subcategoria_lugar'] = ($count['total'] > 0) ? 1 : 0;



$STH = $DBH->query('SELECT COUNT(id) total FROM horario');
$STH->setFetchMode(PDO::FETCH_ASSOC);

$count = $STH->fetch();

$migratedTables['horario'] = ($count['total'] > 0) ? 1 : 0;



$STH = $DBH->query('SELECT COUNT(id) total FROM usuarios');
$STH->setFetchMode(PDO::FETCH_ASSOC);

$count = $STH->fetch();

$migratedTables['usuarios'] = ($count['total'] > 0) ? 1 : 0;



$STH = $DBH->query('SELECT COUNT(id) total FROM tipo_usuario');
$STH->setFetchMode(PDO::FETCH_ASSOC);

$count = $STH->fetch();

$migratedTables['tipo_usuario'] = ($count['total'] > 0) ? 1 : 0;



$STH = $DBH->query('SELECT COUNT(id) total FROM recomendacion');
$STH->setFetchMode(PDO::FETCH_ASSOC);

$count = $STH->fetch();

$migratedTables['recomendaciones'] = ($count['total'] > 0) ? 1 : 0;



$STH = $DBH->query('SELECT COUNT(id) total FROM tag');
$STH->setFetchMode(PDO::FETCH_ASSOC);

$count = $STH->fetch();

$migratedTables['tag'] = ($count['total'] > 0) ? 1 : 0;



$STH = $DBH->query('SELECT COUNT(id) total FROM tag_recomendacion');
$STH->setFetchMode(PDO::FETCH_ASSOC);

$count = $STH->fetch();

$migratedTables['tag_recomendacion'] = ($count['total'] > 0) ? 1 : 0;



$STH = $DBH->query('SELECT COUNT(id) total FROM util');
$STH->setFetchMode(PDO::FETCH_ASSOC);

$count = $STH->fetch();

$migratedTables['util'] = ($count['total'] > 0) ? 1 : 0;



$STH = $DBH->query('SELECT COUNT(id) total FROM comuna');
$STH->setFetchMode(PDO::FETCH_ASSOC);

$count = $STH->fetch();

$migratedTables['comuna'] = ($count['total'] > 0) ? 1 : 0;



$STH = $DBH->query('SELECT COUNT(id) total FROM ciudad');
$STH->setFetchMode(PDO::FETCH_ASSOC);

$count = $STH->fetch();

$migratedTables['ciudad'] = ($count['total'] > 0) ? 1 : 0;



$STH = $DBH->query('SELECT COUNT(id) total FROM sector');
$STH->setFetchMode(PDO::FETCH_ASSOC);

$count = $STH->fetch();

$migratedTables['sector'] = ($count['total'] > 0) ? 1 : 0;






























