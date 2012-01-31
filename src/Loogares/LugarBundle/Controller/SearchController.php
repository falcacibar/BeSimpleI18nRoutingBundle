<?php

namespace Loogares\LugarBundle\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SearchController extends Controller
{
  public function buscarAction(){
    $em = $this->getDoctrine()->getEntityManager();
    $buscar = explode(' ', $_GET['buscar']);
    $results = array();
    $where = null;
    $st = null;

    function power_set($in,$minLength = 1) {
     $count = count($in);
     $members = pow(2,$count);
     $return = array();
     for ($i = 0; $i < $members; $i++) {
        $b = sprintf("%0".$count."b",$i);
        $out = array();
        for ($j = 0; $j < $count; $j++) {
           if ($b{$j} == '1') $out[] = $in[$j];
        }
        if (count($out) >= $minLength) {
           $return[] = $out;
        }
     }

     return $return;
  }

  function permutations($array){
   $list = array();
   for($i=0;$i<=10000;$i++){
    shuffle($array);
    $tmp = implode('-',$array);
    if(isset($list[$tmp])){
     $list[$tmp]++;
    }else{
     $list[$tmp] = 1;
    }
   }
   ksort($list);
   $list = array_keys($list);
   return $list;
  } 

  $ps = power_set($buscar);

  foreach($ps as $value){
    $buffer[] = permutations($value);
  }
  sort($buffer);
  $buffer = array_reverse($buffer);

  foreach($buffer as $perm){
    foreach($perm as $key => $value){
      echo "<p style='color:red'>Buscando Por: $value</p>";
      if(preg_match('/-/', $value)){
        $q = $em->createQuery("SELECT u 
                               FROM Loogares\LugarBundle\Entity\Lugar u
                               WHERE u.slug LIKE '%$value%' $where"); 
      }else{
        $singleTerms[] = $value;
      }

      $result = $q->getResult();

      if(isset($result[0])){ 
        foreach($result as $r){
          echo utf8_decode('Encontramos: '.$r->getNombre()."</br>"); 
          $results[] = $r;
          $where .= " AND u.id != ".$r->getId();
          
        }
      }
    }
  }

  foreach($singleTerms as $key=>$value){
    $st .= " tag.tag like '%$value%' OR";
  }
  $st = preg_replace('/OR$/', '', $st);
  echo $st;

    $a = $this->getDoctrine()->getConnection()
         ->fetchAll("select DISTINCT tag.tag as nombre, count(tag.tag) as freq, lugares.visitas, lugares.slug, lugares.id, max(recomendacion.fecha_creacion) as ultimarec from tag_recomendacion

                        join recomendacion
                        on tag_recomendacion.recomendacion_id = recomendacion.id

                        left join tag 
                        on tag_recomendacion.tag_id = tag.id
                        
                        left join lugares
                        on recomendacion.lugar_id = lugares.id

                        WHERE $st

                        group by lugares.id, tag.tag
                        order by freq desc, lugares.visitas, ultimarec desc

        ");
    $results[] = $a;

    echo '<pre>';
    print_r($a);
    echo '</pre>';

    echo sizeOf($results);
    //print_r($buffer);
    return $this->render('LoogaresLugarBundle:Search:search.html.twig', array('lugares' => $results));
  }
}
