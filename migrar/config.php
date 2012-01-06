<?php
$host = '127.0.0.1';
try {  
  # MySQL with PDO_MYSQL  
  $DBH = new PDO("mysql:host=$host;dbname=loogares_new", 'root', 'root'); 
  $DBH->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );  
}  
catch(PDOException $e) {  
    echo $e->getMessage();
    file_put_contents('PDOErrors.txt', $e->getMessage(), FILE_APPEND);  
}

$DBH->exec("SET FOREIGN_KEY_CHECKS = 0");

try {  
  # MySQL with PDO_MYSQL  
  $LBH = new PDO("mysql:host=$host;dbname=loogares_old", 'root', 'root');  
}  
catch(PDOException $e) {  
    echo $e->getMessage();  
}
 
?>