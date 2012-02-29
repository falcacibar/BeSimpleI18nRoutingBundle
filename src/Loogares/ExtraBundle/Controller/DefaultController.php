<?php

namespace Loogares\ExtraBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
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
        $idCiudad = $ciudad['id'];
        $data = array();

        foreach($tipoCategoria as $key => $value){
            $id = $value->getId();
            $buff = $this->getDoctrine()
            ->getConnection()->fetchAll("SELECT count(categorias.id) as total, categorias.nombre as categoria_nombre, categorias.slug as categoria_slug, tipo_categoria.nombre, tipo_categoria.slug
                                         FROM lugares

                                         JOIN comuna
                                         ON comuna.id = lugares.comuna_id

                                         LEFT JOIN categoria_lugar
                                         ON categoria_lugar.lugar_id = lugares.id

                                         JOIN categorias
                                         ON categorias.id = categoria_lugar.categoria_id

                                         LEFT JOIN tipo_categoria
                                         ON tipo_categoria.id = categorias.tipo_categoria_id

                                         WHERE tipo_categoria.id = $id AND comuna.ciudad_id = $idCiudad

                                         GROUP BY categorias.id
                                         ORDER BY tipo_categoria.id, categorias.nombre asc");
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
        $actividad = $ar->getActividadReciente(5, $ciudad['id'], null, null, 0);

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

    public function actividadAction(Request $request) {
        $fn = $this->get('fn');
        $router = $this->get('router');
        $em = $this->getDoctrine()->getEntityManager();
        $ar = $em->getRepository("LoogaresExtraBundle:ActividadReciente");
        $ciudad = $this->get('session')->get('ciudad');

        $filtro = (!$request->query->get('filtro')) ? 'todo' : $request->query->get('filtro');
        $pagina = (!$request->query->get('pagina')) ? 1 : $request->query->get('pagina');
        $ppag = 20;
        $offset = ($pagina == 1) ? 0 : floor(($pagina - 1) * $ppag);

        // Actividad reciente por ciudad
        $actividad = $ar->getActividadReciente($ppag, $ciudad['id'], null, ($filtro != 'todo') ? $filtro : null, $offset);

        $totalActividad = $ar->getTotalActividad($ciudad['id'], null, ($filtro != 'todo') ? $filtro : null);

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

        $data = array();
        $data['lista'] = $actividad;
        $data['pagina'] = $pagina;
        $data['totalPaginas'] = ($totalActividad > $ppag) ? ceil($totalActividad / $ppag) : 1;
        $data['totalActividad'] = $totalActividad;
        $data['offset'] = $offset;
        $data['filtro'] = $filtro;

        $params = array(
            'filtro' => $filtro,
        );

        $paginacion = $fn->paginacion($totalActividad, $ppag, 'actividad', $params, $router );

        return $this->render('LoogaresExtraBundle:Default:actividad_extendida.html.twig', array(
            'actividad' => $data,
            'paginacion' => $paginacion,
        ));
    }

    public function staticAction($static){
        return $this->render('LoogaresExtraBundle:Static:'.$static.'.html.twig');
    }

}
