<?php
$host = '127.0.0.1';
try {
  $DBH = new PDO("mysql:host=$host;dbname=loogares_new;",'root', 'root'); 
  $DBH->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );  
}  
catch(PDOException $e) {  
    echo $e->getMessage();
    file_put_contents('PDOErrors.txt', $e->getMessage(), FILE_APPEND);  
}

$hoy = new Datetime();
$hoy = $hoy->format('Y-m-d');

$STH = $DBH->query("select * from blog_posts where fecha_publicacion = '$hoy 00:00:00'");
$STH->setFetchMode(PDO::FETCH_ASSOC);

while($row = $STH->fetch()){
	$id = $row['id'];
    $sql = "UPDATE blog_posts SET blog_estado_id = 2 WHERE blog_posts.id = $id";
    $DBH->exec($sql);
}

$STH = $DBH->query("select * from blog_posts where fecha_termino = '$hoy 00:00:00'");
$STH->setFetchMode(PDO::FETCH_ASSOC);
while($row = $STH->fetch()){
	$id = $row['id'];
    $sql = "UPDATE blog_posts SET blog_estado_concurso_id = 3, destacado_home = 0 WHERE blog_posts.id = $id";
    $DBH->exec($sql);
}

?>