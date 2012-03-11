<?php
include('config.php');


        $update= "SELECT TelefonosLugar.id, TelefonosLugar.Telefono 
                FROM TelefonosLugar
                LEFT JOIN Lugares 
                ON Lugares.Id=TelefonosLugar.id_Lugar 
                WHERE Lugares.Id IS NULL";
    echo "$update </br>";

        $LBH->exec($update);

        ?>