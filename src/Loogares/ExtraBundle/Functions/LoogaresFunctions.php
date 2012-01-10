<?php

namespace Loogares\ExtraBundle\Functions;

class LoogaresFunctions
{

    public function paginacion($total, $pp, $paginaActual, $offset,  $alrededor = null, $path){
        return array(
            'totalPaginas' => $totalPaginas = floor($total / $pp),
            'paginaActual' => $paginaActual,
            'mostrarDesde' => $paginaActual - $alrededor,
            'mostrarHasta' => $paginaActual + $alrededor,
            'offset'       => $offset,
            'path' => $path
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

}