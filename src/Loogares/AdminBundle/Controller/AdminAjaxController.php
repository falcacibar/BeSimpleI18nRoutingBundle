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
        $codigo = $this->get('fn')->genRandomString(8);
        $ganador = new Ganador();
        $participante = $pr->find($g);
        $ganador->setParticipante($participante);
        $ganador->setCodigo($codigo);
        $ganador->setCanjeado(false);

        $em->persist($ganador);
        $em->flush();

        // Se envía mail al ganador informando el premio
        $mail = array();
        $mail['usuario'] = $participante->getUsuario();
        $mail['ganador'] = $ganador;
        $mail['concurso'] = $participante->getConcurso();
        $paths = array();

        if(!file_exists('assets/media/cache/medium_concurso/assets/images/blog/'.$participante->getConcurso()->getPost()->getImagen())) {
            $this->get('imagine.controller')->filter('assets/images/blog/'.$participante->getConcurso()->getPost()->getImagen(), "medium_concurso");
        }
        $paths['concurso'] = 'assets/media/cache/medium_concurso/assets/images/blog/'.$participante->getConcurso()->getPost()->getImagen();
        $paths['logo'] = 'assets/images/mails/logo_mails.png';

        if($participante->getConcurso()->getTipoConcurso()->getSlug() == 'click') {
          $mail['asunto'] = $this->get('translator')->trans('extra.modulo_concursos.ganadores.mail.asunto');
          $message = $this->get('fn')->enviarMail($mail['asunto'], $participante->getUsuario()->getMail(), 'noreply@loogares.com', $mail, $paths, 'LoogaresAdminBundle:Mails:mail_ganador.html.twig', $this->get('templating'));

        }
        else if($participante->getConcurso()->getTipoConcurso()->getSlug() == 'recomendacion') {
          $mail['asunto'] = $this->get('translator')->trans('extra.modulo_concursos.ganadores.mail_alianza.asunto', array('%premio%' => $participante->getConcurso()->getPost()->getTitulo()));
          $message = $this->get('fn')->enviarMail($mail['asunto'], $participante->getUsuario()->getMail(), 'noreply@loogares.com', $mail, $paths, 'LoogaresAdminBundle:Mails:mail_ganador_alianza.html.twig', $this->get('templating'));
        }
        $this->get('mailer')->send($message);

      }

      return new Response(json_encode(array('status' => 'ok')));
    }

    public function traducirAction() {
        $jsonrpc = array('result' => null, 'error' => null, 'id' => null);

        try {
            $post = $this->get('request')->request;
            $post->set('entidad', stripslashes($post->get('entidad')));

            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->find(
                        $post->get('entidad'),
                        $post->get('id')
            );

            $entity->{'set'.$this->container->camelize($post->get('campo'))}(
                        $post->get('texto')
            );

            $entity->setTranslatableLocale($post->get('locale'));
            $em->persist($entity);
            $em->flush();

            unset($entity);

            $jsonrpc['result'] = true;
        } catch( \Exception $ex ) {
            $jsonrpc['error']  = $ex->getMessage();
        }

        return new Response(
                        json_encode($jsonrpc),
                        200,
                        array(
                            'Content-Disposition' => 'inline' ,
                            'Content-Type' => 'application/json'
                        )
        );
    }

    public function tablaTraduccionesAction() {
        $post = $this->get('request')->request;
        $post->set('entidad', stripslashes($post->get('entidad')));

        $session = $this->get('session');
        $currentLocale = $session->getLocale();
        $session->setLocale($post->get('locale'));

        $em = $this->getDoctrine()->getEntityManager();
        $meta = $em->getClassMetadata($post->get('entidad'));
        $dbConn = $this->getDoctrine()->getConnection();

        // var_dump($this->container->camelize('weon_feo'), $meta);

        $rs = $dbConn->fetchAll(
            '   SELECT field FROM ext_translations WHERE object_class = ?
                GROUP BY field ORDER BY field ',
            array($post->get('entidad')) ,
            array(\PDO::PARAM_STR)
        );

        $fn  = function($row) { $f = $row['field']; unset($row); return $f; };
        $out = array(
                'name'          => basename($meta->name) ,
                'columns'       => array() ,
                'i18nfields'    => array_map($fn, $rs) ,
                'data'          => array() ,
                'identity'      => null
        );

        unset($rs, $fn);

        foreach($meta->fieldMappings as &$field) {
            if(isset($field['id']) && $field['id']) {
                $out['identity'] = $field['fieldName'];
                break;
            }
        }

        $quote = function($str) {
            return "'".mysql_escape_string($str)."'";
        };

        $sqlfields = array();
        foreach($meta->fieldMappings as &$field) {
            $out['columns'][] = $field['fieldName'];

            if(     $field['type'] === 'string'
                    && in_array($field['fieldName'], $out['i18nfields'], true)
            ) {
                $sqlfields[] =
                    'IFNULL((SELECT 1 ' .
                    'FROM ext_translations x ' .
                    'WHERE x.object_class = '.$quote($post->get('entidad')).
                           ' AND x.field = '.$quote($field['fieldName']) .
                           ' AND x.foreign_key = t.'.$out['identity'] .
                    '), 0) as `tr:'.$field['fieldName'].'`';
            }
        }

        $q = $em->createQuery(
            "   SELECT i
                FROM {$post->get('entidad')} i
                ORDER BY i.".$out['identity']
        );

        $result = $q->getResult();

        $rs = $dbConn->fetchAll(
                '   SELECT '.join(' , ', $sqlfields).'
                    FROM '.$meta->table['name'].' t
                    ORDER BY t.'.$out['identity']
        );

        for($c=sizeof($result),$i=0;$i<$c;$i++) {
            foreach($out['i18nfields'] as &$f) {
                settype($rs[$i]['tr:'.$f], "int");
                settype($rs[$i]['tr:'.$f], "bool");
            }

            foreach($meta->fieldMappings as &$field) {
                $rs[$i][$field['fieldName']] =
                    $result[$i]->{'get'.$this->container->camelize($field['fieldName'])}();
            }
        }

        $out['data'] = &$rs;

        $session->setLocale($currentLocale);
        return new Response(
                        json_encode(array('result' => &$out, 'error' => null, 'id' => null)),
                        200,
                        array(
                            'Content-Disposition' => 'inline' ,
                            'Content-Type' => 'application/json'
                        )
        );

    }
}
