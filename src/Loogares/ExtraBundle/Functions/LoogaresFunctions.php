<?php

namespace Loogares\ExtraBundle\Functions;
class LoogaresFunctions
{

    public function mostrarPrecio($lugar){
        //Donde Dormir 6 | Donde Comer 3
        //Nightclub 31
        $mostrarPrecioCategoria = array('nightclub' => 31);
        $mostrarPrecioTipoCategoria = array('dondeDormir' => 6, 'dondeComer' => 3);
        $mostrarPrecio = false;

        //Comprobamos si mostramos el precio
        foreach($lugar->getCategoriaLugar() as $categoriaLugar){
          $idCategoria = $categoriaLugar->getCategoria()->getId();
          $idTipoCategoria = $categoriaLugar->getCategoria()->getTipoCategoria()->getId();

          foreach($mostrarPrecioCategoria as $key => $value){
            if($idCategoria == $value){
              $mostrarPrecio = $key;
            }
          }
          foreach($mostrarPrecioTipoCategoria as $key => $value){
            if($idTipoCategoria == $value){
              $mostrarPrecio = $key;
            }
          }
        }

        return $mostrarPrecio;
    }

    public function paginacion($total, $porPagina, $path, $params = array(), $router, $options = null){
        $buffer = '';
        $paginaActual = (!isset($_GET['pagina']))?1:$_GET['pagina'];
        
        $offset = $porPagina * ($paginaActual - 1);

        $mostrandoDe = $offset + 1;
        $mostrandoHasta = ($offset + $porPagina >= $total)?$total:($offset + $porPagina);
        $totalPaginas = ceil($total / $porPagina);


        //Opciones por defecto
        if($options == null){
            $options = array(
                'izq' => ($paginaActual == 1)?0:(($totalPaginas - $paginaActual == 0)?$totalPaginas-1:$totalPaginas - $paginaActual),
                'der' => ($totalPaginas <= 4)?$totalPaginas-$paginaActual:4
            );
        }

        $params = array_merge($params, $_GET);

        for($i=$paginaActual-$options['izq'];$i <= $paginaActual+$options['der']; $i++){
            
            if($i == $paginaActual-$options['izq']){ //Primera iteracion, flecha a la primera pagina
                $params['pagina'] = 1;
                $buffer .= '<li><a href="'.$router->generate($path, $params).'">&#8676;</a></li>'; //Flecha a primera Pagina
                if($paginaActual == 1){ //Estamos en la primera, solo mostramos la flecha
                    $buffer .= '<li><a href="">&larr;</a></li>';
                }else{ //Bindeamos la flecha a la pagina previa
                    $params['pagina'] = $paginaActual-1;
                    $buffer .= '<li><a href="'.$router->generate($path, $params).'">&larr;</a></li>';
                }
            }

            if($i == $paginaActual){
                $class = "class='" . ($paginaActual == $i)?"class='actual'":"" . "' ";
                $buffer .= "<li class='pagina-actual'><a $class>$i</a></li>";
            }else if($i <= 0 || $i > $totalPaginas){
                $buffer .= "<li>--</li>";
            }else{
                $params['pagina'] = $i;
                $buffer .= "<li><a href='".$router->generate($path, $params)."'>$i</a></li>";
            }

            if($i == $paginaActual+$options['der']){
                if($paginaActual != $totalPaginas){
                    $params['pagina'] = $paginaActual+1;
                    $buffer .= '<li><a href="'.$router->generate($path, $params).'">&rarr;</a></li>';
                }else{
                    $buffer .= '<li><a href="">&rarr;</a></li>';
                }
                $params['pagina'] = $totalPaginas;
                $buffer .= '<li><a href="'.$router->generate($path, $params).'">&#8677;</a></li>';
            }
        }

        return array(
            'paginacion'     => $buffer
           ,'totalPaginas'   => $totalPaginas
           ,'mostrandoDe'    => $mostrandoDe
           ,'mostrandoHasta' => $mostrandoHasta
           ,'total'          => $total
        );
    }

	public function generarSlug($string)
	{
		// Estandarizamos caracteres de $string	 
	    $string = trim($string);
	 
	    $string = str_replace(
	        array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
	        array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
	        $string
	    );
	 
	    $string = str_replace(
	        array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
	        array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
	        $string
	    );
	 
	    $string = str_replace(
	        array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
	        array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
	        $string
	    );
	 
	    $string = str_replace(
	        array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
	        array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
	        $string
	    );
	 
	    $string = str_replace(
	        array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
	        array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
	        $string
	    );
	 
	    $string = str_replace(
	        array('ñ', 'Ñ', 'ç', 'Ç'),
	        array('n', 'N', 'c', 'C',),
	        $string
	    );
	 
	    // Esta parte se encarga de eliminar cualquier caracter extraño
	    $string = str_replace(
	        array("\\", "¨", "º", "~",".",
	             "#", "@", "|", "!", "\"",
	             "·", "$", "%", "&", "/",
	             "(", ")", "?", "'", "¡",
	             "¿", "[", "^", "`", "]",
	             "+", "}", "{", "¨", "´",
	             ">", "<", ";", ",", ":"),
	        '',
	        $string
	    );

	    // Dejamos todo en minúsculas
	    $string = strtolower($string);

	    // Reemplazamos espacios por guiones
        $string = str_replace(" ","-",$string);

        // Finalmente removemos el exceso de guiones
        $string = $this->removerExcesoGuiones($string);
	 
	    return $string;
	}

	public function removerExcesoGuiones($string) {
		return preg_replace('/-(-+)/', '-', $string);
	}

	public function stripHTTP($ele){
		return preg_replace('/^http:\/\//', '', $ele);
	}

	public function generarHorario($horarioArray){
        $dias = array('Lun','Mar','Mié','Jue','Vie','Sáb','Dom');
        $out = null;
        $horario = array();
        $dia = 0;
        $hh = array();

        if(empty($horarioArray[0]) && empty($horarioArray[1]) && empty($horarioArray[2]) && empty($horarioArray[3]) && empty($horarioArray[4]) && empty($horarioArray[5]) && empty($horarioArray[6])){ return null; }
        for($i=0;$i<7;$i++){
            $temp = $horarioArray[$i];
            if(!empty($temp)){
                $hh[$i] = array(
                    'Id_Dia' => $i,
                    'Aper_M_L' => $temp->getAperturaAM(),
                    'Cierre_M_L' => $temp->getCierreAM(),
                    'Aper_T_L' => $temp->getAperturaPM(),
                    'Cierre_T_L' => $temp->getCierrePM()
                );
            } else {
                $hh[$i] = array(
                    'Id_Dia' => $i,
                    'Aper_M_L' => '',
                    'Cierre_M_L' => '',
                    'Aper_T_L' => '',
                    'Cierre_T_L' => ''
                );
            }
        }

        if(count($hh)){
            $inicial = $hh[0];
            $final = $hh[0];
            $dia++;
            while($dia<7){
                if( $hh[$dia]['Aper_M_L'] != $inicial['Aper_M_L'] || $hh[$dia]['Cierre_M_L'] != $inicial['Cierre_M_L'] ||
                    $hh[$dia]['Aper_T_L'] != $inicial['Aper_T_L'] || $hh[$dia]['Cierre_T_L'] != $inicial['Cierre_T_L']) {
                    $out = $dias[$inicial['Id_Dia']];
                    if($final['Id_Dia'] != $inicial['Id_Dia']){
                        $out.= '-' . $dias[$final['Id_Dia']];
                    }
                    if(!empty($inicial['Aper_M_L'])){
                        $out.= ': ' . $inicial['Aper_M_L'] . ' - ' . $inicial['Cierre_M_L'];
                        if(!empty($inicial['Cierre_T_L'])){
                            $out.= ' / ' . $inicial['Aper_T_L'] . ' - ' . $inicial['Cierre_T_L'];
                        }
                    } else {
                        $out.= ': Cerrado' ;
                    }
                    $horario[] = $out;
                    $out=null;
                    $inicial = $hh[$dia];
                    $final = $hh[$dia];
                } else {
                    $final = $hh[$dia];
                }
                $dia++;
            }
            $out.= $dias[$inicial['Id_Dia']];
            if($final['Id_Dia'] != $inicial['Id_Dia']){
                $out.= '-' . $dias[$final['Id_Dia']];
            }
            if(!empty($inicial['Aper_M_L'])){
                $out.= ': ' . $inicial['Aper_M_L'] . ' - ' . $inicial['Cierre_M_L'];
                if(!empty($inicial['Aper_T_L'])){
                    $out.= ' / ' . $inicial['Aper_T_L'] . ' - ' . $inicial['Cierre_T_L'];
                }
            } else {
                $out.= ': Cerrado';
            }
            $horario[] = $out;
            $out=null;
        }

        return $horario;

    }

    public function enviarMail($subject, $to, $from, $mail, $paths, $template, $templating) {
        $message = \Swift_Message::newInstance()
                    ->setSubject($subject)
                    ->setFrom($from)
                    ->setTo($to);

        $assets = array();
        foreach ($paths as $key => $value) {
            $assets[$key] = $message->embed(\Swift_Image::fromPath($value));
        }

        $message->setBody($templating->render($template, array('mail' => $mail, 'assets' => $assets)), 'text/html');
        return $message;
    }

}