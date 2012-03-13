<?php
//include('config.php');

$STH = $LBH->query("SELECT TelefonosLugar.Id, TelefonosLugar.Telefono FROM `TelefonosLugar`
                    LEFT JOIN Lugares ON Lugares.Id=TelefonosLugar.Id_Lugar WHERE Ciudad='2'"
                    );
$STH->setFetchMode(PDO::FETCH_ASSOC);



while($row = $STH->fetch()){
    foreach($row as $key => $value){
        if($key!='Id'){
            
            $sustituir='';         
            $patrones='\(';
            $abrir= ereg_replace($patrones ,$sustituir, $value);
            
            $patrones='\)';
            $no_parentesis= trim(ereg_replace($patrones ,$sustituir, $abrir));
            
            //eliminar el prefijo
            
            $patrones = '^[+56|56]+';
            //si tiene más de 7 carçacteres borramos prefijo
            if (strlen($no_parentesis>7))
                $prefijo= ereg_replace($patrones ,$sustituir, $no_parentesis);
            else
                $prefijo=$no_parentesis;
            $no_espacios=trim($prefijo);
            
            
            
            //eliminar lo que no sea número
            $patrones='[^0-9]';
            $digit_in= ereg_replace($patrones, $sustituir, $no_espacios);
            
            //eliminar ceros del principio
            $patrones='^0';
            $final= ereg_replace($patrones, $sustituir, $digit_in);
            
            //verificamos prefijo de Valpo y contiene más de 7 números
            if ((substr($final,0,2)=='32') && strlen($final)>7){
                $patrones= '^32';
                $sustituir= '32 ';
                $valpo= ereg_replace($patrones, $sustituir, $final);
                
            }elseif ((substr($final,0,1)=='2') && strlen($final)>7){
                //Si es de Valpo y primer carácter igual a 2, lo cambiamos a 32
                //echo $row['Id'];
                $patrones= '^2';
                $sustituir= '32 ';
                
                $valpo= ereg_replace($patrones, $sustituir, $final);
                //quitamos el prefijo de Valpo
                //echo $valpo;

            }elseif ((substr($final,0,1)=='9') && strlen($final)>7){
                $patrones= '^9';
                $sustituir= '9 ';
                $valpo= ereg_replace($patrones, $sustituir, $final);
                
            }elseif ((substr($final,0,1)=='8') && strlen($final)>7){
                $patrones= '^8';
                $sustituir= '8 ';
                $valpo= ereg_replace($patrones, $sustituir, $final);
                
            }elseif ((substr($final,0,1)=='7') && strlen($final)>7){
                $patrones= '^7';
                $sustituir= '7 ';
                $valpo= ereg_replace($patrones, $sustituir, $final);
                
            }elseif ((substr($final,0,1)=='6') && strlen($final)>7){
                $patrones= '^6';
                $sustituir= '6 ';
                $valpo= ereg_replace($patrones, $sustituir, $final);
                
            }else{
                $valpo= $final;
            }

            //quitar prefijo 32 de los call centers
            if (strlen($valpo)==13){
                $valpo=substr($valpo,3);
            }
        }
    }
    
    //update
    /*$update= "UPDATE TelefonosLugar 
            SET Telefono='".$final['telefono']."'
            WHERE Id=".$final['Id'];*/
    
        $update= "UPDATE TelefonosLugar
            SET Telefono='".$valpo."'
            WHERE Id='".$row['Id']."'";
    //echo "$update </br>";
    //if($row['Id'] == 7974){
        $LBH->exec($update);
    //}
    // 
    
    

}       


?>