<?php

$STH = $LBH->query("SELECT Id, Link_1, Link_2, Link_3 FROM Usuario");
$STH->setFetchMode(PDO::FETCH_ASSOC);


while($row = $STH->fetch()){
    $final= array(
        'web' => NULL
        ,'facebook' => NULL
        ,'twitter' => NULL);

    foreach($row as $key => $value){
        if($key != 'Id') {
            //Comprobamos Link_1
            if(strpos($value, "facebook") !== false){
                //echo ' Si';
                $final['facebook']=$value;
                
            }elseif(strpos($value, "twitter") !== false){
                //Si
                $final['twitter']=$value;
                
            }elseif($value!==null){
                $final['web']=$value;
            }
        }
    }

    //insert
    
    $update= "UPDATE Usuario 
            SET Link_1='".$final['web']."', Link_2='".$final['facebook']."', Link_3='".$final['twitter']."'
            WHERE Id=".$row['Id'];
    echo "$update </br>";
    $LBH->exec($update);
    
}
?>