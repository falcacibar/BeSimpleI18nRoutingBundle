<?php

namespace Loogares\ExtraBundle\Functions;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\Loader\FileLoader;

class LoogaresFunctions
{
    function generarTemplateNuevoMail($filename){
        if(!is_dir('assets/media/cache/mail_concursos_nuevo/assets/images/blog')) mkdir('assets/media/cache/mail_concursos_nuevo/assets/images/blog', 0777, true);
        $filename = explode('.', $filename);

        $imagine = new \Imagine\Gd\Imagine();

        $thumbnail = $imagine->create(new \Imagine\Image\Box(212, 122), new \Imagine\Image\Color('000', 100));
        
        $preview = $imagine->open("assets/images/blog/".$filename[0].'.'.$filename[1]);
        $lol = $preview->thumbnail(new \Imagine\Image\Box(204, 115));

        $nuevo = $imagine->open("assets/images/concursos/nuevo.png");
        
        $thumbnail->paste($lol, new \Imagine\Image\Point(8,7));
        $thumbnail->paste($nuevo, new \Imagine\Image\Point(0,0));

        $thumbnail->save("assets/media/cache/mail_concursos_nuevo/assets/images/blog/".$filename[0].".png");

        return "assets/media/cache/assets/mail_concursos_nuevo/images/blog/".$filename[0]."png";
    }

    //Funcion para generar la imagen default del Lugar
    function generarThumbnailTelefono($filename, $filter){
        //Parsing filter data
        $filterSettings = array(
            'phone_lista_thumbnail' => array(
                'width' => 112,
                'height' => 112,
                'offsetx' => 16,
                'offsety' => 8,
                'thumbWidth' => 136,
                'thumbHeight' => 136,
                'tipo' => 'lugares'
            ),
            'phone_ficha_thumbnail' => array(
                'width' => 222,
                'height' => 222,
                'offsetx' => 16,
                'offsety' => 10,
                'thumbWidth' => 248,
                'thumbHeight' => 248,
                'tipo' => 'lugares'
            ),
            'phone_recomendacion_thumbnail' => array(
                'width' => 104,
                'height' => 104,
                'offsetx' => 10,
                'offsety' => 6,
                'thumbWidth' => 120,
                'thumbHeight' => 120,
                'tipo' => 'usuarios'
            )
        );

        $new = explode('.',$filename);
        $new = $new[0].'.png';
        
        $tipo = $filterSettings[$filter]['tipo'];
        if(!file_exists("assets/images/$tipo/$filename") || !$filename) $filename = 'default.png';

        $imagine = new \Imagine\Gd\Imagine();

        $assetsPath = "assets/images";
        $cachePath = "assets/media/cache";
        $filterPath = "$cachePath/$filter/$assetsPath";
        $cachedFile = $filterPath . "/$new";

        //Creamos las carpetas si no estan
        if(!is_dir($filterPath)) mkdir($filterPath, 0777, true);

        if(!file_exists($cachedFile)){
            $offsetx = $filterSettings[$filter]['offsetx'];
            $offsety = $filterSettings[$filter]['offsety'];
            $offset = new \Imagine\Image\Point($offsetx, $offsety);

            $width = $filterSettings[$filter]['width'];
            $height = $filterSettings[$filter]['height'];
            $filterSize = new \Imagine\Image\Box($width, $height);

            //Creamos la caja principal, tiene el tamaño total que debe tener el thumbnail
            $thumbWidth = $filterSettings[$filter]['thumbWidth'];
            $thumbHeight = $filterSettings[$filter]['thumbHeight'];
            $thumbnail = $imagine->create(new \Imagine\Image\Box($thumbWidth, $thumbHeight), new \Imagine\Image\Color('000', 100));


            //Abrrimos la imagen a manipular, y la achicamos
            try{
                $preview = $imagine->open("assets/images/$tipo/$filename");
            }catch(\Exception $e){
                return 'default.png';
            }

            //Sacamos las dimensiones de la imagen original achicada
            $size = explode('x', $preview->getSize());
            $previewWidth = (int)$size[0];
            $previewHeight = (int)$size[1];

            //Caso 1, Matener Height -> Largo es mayor al Alto.
            if($previewWidth > $previewHeight){
                $newWidth = (($width+20) * $previewWidth)/$previewHeight;
                $newHeight = $height+20;

                $pointx = 10;
                $pointy = 10;
            }else if($previewHeight > $previewWidth){
                $newWidth = $width+10;
                $newHeight = (($height+10) * $previewHeight)/$previewWidth;

                $pointx = 0;
                $pointy = 0;
            }else{
                $newWidth = $width;
                $newHeight = $height;

                $pointx = 0;
                $pointy = 0;
            }

            try{
                $lol = $preview->thumbnail(new \Imagine\Image\Box($newWidth, $newHeight))
                        ->crop(new \Imagine\Image\Point($pointx, $pointy), new \Imagine\Image\Box($width, $height));
            }catch(\Exception $e){
                return 'default.png';
            }
            
            $thumbnail->paste($lol, $offset)
                      ->save($cachedFile);
            /*catch(\Imagine\Exception\Exception $e)*/
        }
        return $cachedFile;
    }

    function ip2int($ip){
        //Localhost ipv6 mac fix
        if($ip == '::1') { $ip = "31.201.0.176"; }
        if ($ip == "") {
            return null;
        } else {
            $ips = explode (".", "$ip");
            return ($ips[3] + $ips[2] * 256 + $ips[1] * 256 * 256 + $ips[0] * 256 * 256 * 256);
        }
    }

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
                'izq' => ($paginaActual <= 4)?$paginaActual-1:4,
                'der' => ($totalPaginas - $paginaActual >= 4)?4:($totalPaginas-$paginaActual)
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

    public function cleanInput($string){
        //Estandarizamos caracteres de $string  
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

        return $string;
    }

	public function generarSlug($string){
	    // Dejamos todo en minúsculas
	    $string = strtolower($this->cleanInput($string));

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
		return preg_replace('/^http[s]?:\/\//', '', $ele);
	}

	public function generarHorario($horarioArray){
        $dias = array('Lun','Mar','Mié','Jue','Vie','Sáb','Dom');
        $out = null;
        $horario = array();
        $dia = 0;
        $hh = array();

        if(empty($horarioArray[0]) && empty($horarioArray[1]) && empty($horarioArray[2]) && empty($horarioArray[3]) && empty($horarioArray[4]) && empty($horarioArray[5]) && empty($horarioArray[6])){ return null; }
        for($i=0;$i<7;$i++){
            $hh[$i] = array(
                'Id_Dia' => $i,
                'Aper_M_L' => '',
                'Cierre_M_L' => '',
                'Aper_T_L' => '',
                'Cierre_T_L' => ''
            );
        }

        for($i=sizeOf($horarioArray)-1;$i>=0;$i--){
            $temp = $horarioArray[$i];
                $hh[$temp->getDia()] = array(
                    'Id_Dia' => $temp->getDia(),
                    'Aper_M_L' => $temp->getAperturaAM(),
                    'Cierre_M_L' => $temp->getCierreAM(),
                    'Aper_T_L' => $temp->getAperturaPM(),
                    'Cierre_T_L' => $temp->getCierrePM()
                );   
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

    public function genRandomString($length)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $string = '';
        for ($p = 0; $p < $length; $p++)
        {
            $string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }
        return $string;
    }
}
