<?php

namespace Loogares\ExtraBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Loogares\LugarBundle\Entity\TipoCategoria;


class DefaultController extends Controller
{
    
    public function indexAction($name)
    {
        return $this->render('LoogaresExtraBundle:Default:index.html.twig', array('name' => $name));
    }

    public function menuAction(){

    	$em = $this->getDoctrine()->getEntityManager();
        $tlr = $em->getRepository("LoogaresLugarBundle:TipoCategoria");    
        $tipoCategoria = $tlr->findAll();
        $ciudad = $this->get('session')->get('ciudad');
        $data = array();

        foreach($tipoCategoria as $key => $value){
            $q = $em->createQuery("SELECT cl, tl.nombre as tipo_nombre, tl.slug as tipo_slug, c.nombre as categoria_nombre, c.slug as categoria_slug, count(c.id) as total
                                   FROM Loogares\LugarBundle\Entity\CategoriaLugar cl

                                   JOIN cl.lugar l 
                                   LEFT JOIN cl.categoria c
                                   LEFT JOIN c.tipo_categoria tl
                                   JOIN l.comuna com
                                   WHERE tl.id = ?1
                                   AND com.ciudad = ?2
                                   GROUP BY c.id
                                   ORDER BY tl.id");
            $q->setParameter(1, $value->getId());
            $q->setParameter(2, $ciudad['id']);
            $buff = $q->getResult();
            $data[$value->getSlug()]['tipo'] = $tipoCategoria[$key];
            $data[$value->getSlug()]['categorias'] = $buff;
        }

    	return $this->render('::menu.html.twig', array('menu' => $data));
    }

    public function ciudadAction() {

        $em = $this->getDoctrine()->getEntityManager();
        $cr = $em->getRepository("LoogaresExtraBundle:Ciudad");

        $tipoCiudades = $cr->getCiudadesActivas();

        $data = $tipoCiudades;
        return $this->render('::ciudad.html.twig', array('ciudades' => $data));
    }

    public function localeAction($slug, $start=null) {
        $em = $this->getDoctrine()->getEntityManager();
        if((!$this->get('session')->get('ciudad') && $start) || !$start ) {            
            $cr = $em->getRepository("LoogaresExtraBundle:Ciudad");
            $ciudad = $cr->findOneBySlug($slug);

            // Seteamos el locale correspondiente a la ciudad en la sesión
            $this->get('session')->setLocale($ciudad->getPais()->getLocale());

            $ciudadArray = array();
            $ciudadArray['id'] = $ciudad->getId();
            $ciudadArray['nombre'] = $ciudad->getNombre();
            $ciudadArray['slug'] = $ciudad->getSlug();

            $this->get('session')->set('ciudad',$ciudadArray);
        }
        
        // Si usuario está loggeado, significa que tuvo actividad
        if($this->get('security.context')->isGranted('ROLE_USER')) {
            $usuario = $this->get('security.context')->getToken()->getUser();
            $usuario->setFechaUltimaActividad(new \DateTime());
            $em->flush();
        }

        if($start) {
            return new Response('');
        }
        // Redirección a vista de login 
        return $this->redirect($this->generateUrl('root'));
    }

    public function homepageAction() {
        $em = $this->getDoctrine()->getEntityManager();
        $rr = $em->getRepository("LoogaresUsuarioBundle:Recomendacion");
        $ur = $em->getRepository("LoogaresUsuarioBundle:Usuario");
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $cr = $em->getRepository("LoogaresLugarBundle:Categoria");
        $ar = $em->getRepository("LoogaresExtraBundle:ActividadReciente");

        // Cantidad de premios regalados (totales)
        $q = $em->createQuery("SELECT count(cu.id)
                               FROM Loogares\ExtraBundle\Entity\ConcursoUsuario cu
                               JOIN cu.usuario u
                               WHERE u.estado != ?1");
        $q->setParameter(1, 3);
        $totalPremios = $q->getSingleScalarResult();

        // Cantidad de recomendaciones escritas
        $totalRecomendaciones = $rr->getTotalRecomendaciones();

        $ciudad = $this->get('session')->get('ciudad');

        // Cuando entramos al home por primera vez, sesión no existe. Por default, dejamos Santiago de Chile
        if(!isset($ciudad)) {
            $cir = $em->getRepository("LoogaresExtraBundle:Ciudad");
            $ciudad = $cir->find(1);
            $ciudadArray = array();
            $ciudadArray['id'] = $ciudad->getId();
            $ciudadArray['nombre'] = $ciudad->getNombre();
            $ciudadArray['slug'] = $ciudad->getSlug();
            $this->get('session')->set('ciudad',$ciudadArray);
            $ciudad = $this->get('session')->get('ciudad');
        }

        // Top Five de tres categorías
        $categorias = array();

        // Categoría Restaurantes
        $restaurantes = $cr->findOneBySlug('restaurantes');
        $restaurantes->top_five = $lr->getTopFivePorCategoria($restaurantes->getId(), $ciudad['id']);
        $categorias[] = $restaurantes;

        // Categoría Cafés
        $cafes = $cr->findOneBySlug('cafes-teterias');
        $cafes->top_five = $lr->getTopFivePorCategoria($cafes->getId(), $ciudad['id']);
        $categorias[] = $cafes;

        // Categoría Bares/Pubs
        $bares = $cr->findOneBySlug('bares-pubs');
        $bares->top_five = $lr->getTopFivePorCategoria($bares->getId(), $ciudad['id']);
        $categorias[] = $bares;

        // Recomendación del día
        $recomendacionDelDia = $rr->getRecomendacionDelDia($ciudad['id']);

        $preview = '';
        if(strlen($recomendacionDelDia->getTexto()) > 160) {
            $preview = substr($recomendacionDelDia->getTexto(),0,160).'...';
        }
        else {
            $preview = $recomendacionDelDia->getTexto();
        }

        // Actividad reciente por ciudad
        $actividad = $ar->getActividadReciente(10, $ciudad['id'], null, null, 0);

        foreach($actividad as $a) {
            $r = $em->getRepository($a->getEntidad());
            $entidad = $r->find($a->getEntidadId());
            if($a->getEntidad() == 'Loogares\UsuarioBundle\Entity\Recomendacion') {
                $preview = '';
                if(strlen($entidad->getTexto()) > 160) {
                    $preview = substr($entidad->getTexto(),0,160).'...';
                }
                else {
                    $preview = $entidad->getTexto();
                }
                $entidad->preview = $preview;
            }

            $a->ent = $entidad;
        }

        // Últimos conectados
        $ultimosConectados = $ur->getUltimosConectados(0.02);

        $home = array();
        $home['totalPremios'] = $totalPremios;        
        $home['totalPremios_format'] = number_format( $totalPremios , 0 , '' , '.' );
        $home['totalRecomendaciones'] = $totalRecomendaciones;
        $home['totalRecomendaciones_format'] = number_format( $totalRecomendaciones , 0 , '' , '.' );
        $home['recDia'] = $recomendacionDelDia;
        $home['previewRecDia'] = $preview;
        $home['ultimosConectados'] = $ultimosConectados;
        $home['categorias'] = $categorias;
        $home['actividad'] = $actividad;

        return $this->render('LoogaresExtraBundle:Default:home.html.twig', array(
            'home' => $home,     
        ));
    }

    public function staticAction($static){
        return $this->render('LoogaresExtraBundle:Static:'.$static.'.html.twig');
    }

}
