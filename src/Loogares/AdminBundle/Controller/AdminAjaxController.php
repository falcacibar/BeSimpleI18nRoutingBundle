<?php

namespace Loogares\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Loogares\BlogBundle\Entity\Ganador;



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

    public function sugerirUsuarioAction(){
      $em = $this->getDoctrine()->getEntityManager();
      $d = $_GET['term'];
      if(preg_match('/^\d+/', $d)){
        $q = $em->createQuery('SELECT DISTINCT u.nombre, u.apellido, u.slug, u.id FROM Loogares\UsuarioBundle\Entity\Usuario u where u.id = ?1');
        $q->setParameter(1, $d);
      }else{
        $q = $em->createQuery('SELECT DISTINCT u.nombre, u.apellido, u.slug, u.id FROM Loogares\UsuarioBundle\Entity\Usuario u where u.slug LIKE ?1');
        $q->setParameter(1, "%".$d."%");
      }
                             
      $usuarios = '';

      $q->setMaxResults(7);
      $usuariosResult = $q->getResult();

      foreach($usuariosResult as $key => $value){
        $usuarios[] = $value['nombre'] . " " . $value['apellido'] . " (".$value['id'].")" . " | " . $value['slug'];
      }

      return new Response(json_encode($usuarios));
    }

    public function borrarPromocionAction($ciudad, $slug, $id) {
      $em = $this->getDoctrine()->getEntityManager();
      $plr = $em->getRepository("LoogaresLugarBundle:PedidoLugar");

      $pedido = $plr->find($id);
      $promocion = $pedido->getPromocion();

      if($promocion != null) {
        // Borramos la promocion
        $pedido->setPromocion(null);
        $pedido->setTienePromocion(false);
        $em->remove($promocion);                
        $em->flush();
      }
      
      return $this->render('LoogaresAdminBundle:Admin:promocion.html.twig', array(
          'pedido' => $pedido,
          'slug' => $slug,
          'ciudad' => $ciudad
      ));
    }

    public function asignarGanadoresAction(Request $request) {
      $em = $this->getDoctrine()->getEntityManager();
      $pr = $em->getRepository("LoogaresBlogBundle:Participante");
      foreach($request->request->get('ganadores') as $g) {
        $ganador = new Ganador();
        $participante = $pr->find($g);
        $ganador->setParticipante($participante);
        $ganador->setCodigo(md5($participante->getConcurso()->getId().$participante->getUsuario()->getId()));
        $ganador->setCanjeado(false);

        $em->persist($ganador);
        $em->flush();

        // Se envía mail al ganador informando el premio
        $mail = array();
        $mail['asunto'] = $this->get('translator')->trans('extra.modulo_concursos.ganadores.mail.asunto');
        $mail['usuario'] = $participante->getUsuario();
        $mail['ganador'] = $ganador;
        $mail['concurso'] = $participante->getConcurso();
        $paths = array();

        if(!file_exists('assets/media/cache/medium_concurso/assets/images/blog/'.$participante->getConcurso()->getPost()->getImagen())) {                   
            $this->get('imagine.controller')->filter('assets/images/blog/'.$participante->getConcurso()->getPost()->getImagen(), "medium_concurso");            
        }  
        $paths['concurso'] = 'assets/media/cache/medium_concurso/assets/images/blog/'.$participante->getConcurso()->getPost()->getImagen();      
        $paths['logo'] = 'assets/images/mails/logo_mails.png';

        $message = $this->get('fn')->enviarMail($mail['asunto'], $participante->getUsuario()->getMail(), 'noreply@loogares.com', $mail, $paths, 'LoogaresAdminBundle:Mails:mail_ganador.html.twig', $this->get('templating'));
        $this->get('mailer')->send($message);
      }

      return new Response(json_encode(array('status' => 'ok')));
    }
}
