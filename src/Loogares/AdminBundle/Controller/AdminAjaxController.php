<?php

namespace Loogares\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


class AdminAjaxController extends Controller{
    public function sugerirLugarAction(){
      $em = $this->getDoctrine()->getEntityManager();
      $d = $_GET['term'];
      if(preg_match('/^\d+/', $d)){
        $q = $em->createQuery('SELECT DISTINCT u.nombre, u.calle, u.id, c.nombre as comuna, u.numero FROM Loogares\LugarBundle\Entity\Lugar u left join u.comuna c where u.id = ?1');
        $q->setParameter(1, $d);
      }else{
        $q = $em->createQuery('SELECT DISTINCT u.nombre, u.calle, u.id, c.nombre as comuna, u.numero FROM Loogares\LugarBundle\Entity\Lugar u left join u.comuna c where u.nombre LIKE ?1');
        $q->setParameter(1, "%".$d."%");
      }
                             
      $lugares = '';

      $q->setMaxResults(7);
      $lugaresResult = $q->getResult();

      foreach($lugaresResult as $key => $value){
        $lugares[] = $value['nombre'] . " (".$value['id'].")" . " | ".$value['calle'] . " " . $value['numero'] . " - " . $value['comuna'];
      }

      return new Response(json_encode($lugares));
    }
}
