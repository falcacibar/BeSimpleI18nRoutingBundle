<?php
import_request_variables('g','_');

if(/*isset($_nombre) && */isset($_calle) && isset($_numero) && isset($_comuna) && isset($_ciudad) 
        && !empty($_calle) && !empty($_numero) && !empty($_comuna) && !empty($_ciudad) ){
    $_nombre = enquotes(urldecode($_nombre));
    $_calle = enquotes(urldecode($_calle));
    $_numero = enquotes(urldecode($_numero));
    
    //echo "SELECT * FROM Lugares WHERE Nombre LIKE '%$_nombre%' AND Direccion LIKE '%$_calle $_numero' AND Comuna='$_comuna' AND Ciudad='$_ciudad' AND Id!='$_idlugar'";
    
    $res = $db->get_results("SELECT * FROM Lugares WHERE Nombre LIKE '%$_nombre%' AND Direccion LIKE '%$_calle $_numero' AND Comuna='$_comuna' AND Ciudad='$_ciudad' AND Id!='$_idlugar'");
    
    if(!count($res))
        $res = $db->get_results("SELECT * FROM Lugares WHERE (Direccion LIKE '%$_calle $_numero' AND Comuna='$_comuna' AND Ciudad='$_ciudad' AND Id!='$_idlugar') ORDER BY Nombre ASC");
/*    if(!count($res))
        $res = $db->get_results("SELECT * FROM Lugares WHERE (Nombre LIKE '%$_nombre%' OR LOCATE('$_nombre',Nombre) ) ORDER BY Nombre ASC LIMIT 10");*/
    if(count($res)){
        foreach($res as $r){ ?>
<b><a href="<?php echo WEBROOT.BASELINK.'ficha/'.$r->Id.'/'.urlencode2($r->Nombre) ?>" target="_blank"><?php echo utf8_encode($r->Nombre) ?></a></b> (<?php echo utf8_encode($r->Direccion) ?>)<br /> <?php
        }
    }
}

function enquotes($string){
    if(@get_magic_quotes_gpc())
        return $string;
    else
        return addslashes($string);
}