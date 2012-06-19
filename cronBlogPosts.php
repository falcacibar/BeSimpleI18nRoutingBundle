<?php
$host = '127.0.0.1';
try {
  $DBH = new PDO("mysql:host=$host;dbname=loogares_symfony;",'symfony', 'L0og4r3s'); 
  $DBH->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );  
}  
catch(PDOException $e) {  
    echo $e->getMessage();
    file_put_contents('PDOErrors.txt', $e->getMessage(), FILE_APPEND);  
}

$hoy = new Datetime();
$hoy = $hoy->format('Y-m-d');
$ayer = date("Y-m-d", strtotime("yesterday"));

$STH = $DBH->query("select * from blog_posts where fecha_publicacion = '$hoy 00:00:00'");
$STH->setFetchMode(PDO::FETCH_ASSOC);

while($row = $STH->fetch()){
	$id = $row['id'];
    $sql = "UPDATE blog_posts SET blog_estado_id = 2 WHERE blog_posts.id = $id";
    $DBH->exec($sql);
}

$STH = $DBH->query("select * from concursos where fecha_termino >= '$ayer 00:00:01' and fecha_termino <= '$hoy 00:00:00'");
$STH->setFetchMode(PDO::FETCH_ASSOC);
while($row = $STH->fetch()){
	$id = $row['id'];
    $sql = "UPDATE concursos SET estado_concurso_id = 3 WHERE concursos.id = $id";
    $DBH->exec($sql);
}

?>
