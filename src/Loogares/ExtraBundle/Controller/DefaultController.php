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
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");        

        $tipoCategorias = $lr->getTipoCategoriaPorPrioridad();
        $categorias = $em->getRepository("LoogaresLugarBundle:Categoria")->findAll();             
        
        $data = array();           
        
        $data['tipoCategorias'] = $tipoCategorias;
        $data['categorias']  = $categorias;

        foreach($data['categorias'] as $categoria){
            $lugares = $em->getRepository("LoogaresLugarBundle:Lugar")->getTotalLugaresPorCategoria($categoria->getId()); 

            $categoria->lugares = $lugares['total'];
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

        // Recomendación del día
        $recomendacionDelDia = $rr->getRecomendacionDelDia($ciudad['id']);

        $preview = '';
        if(strlen($recomendacionDelDia->getTexto()) > 300) {
            $preview = substr($recomendacionDelDia->getTexto(),0,300).'...';
        }
        else {
            $preview = $recomendacionDelDia->getTexto();
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

        return $this->render('LoogaresExtraBundle:Default:home.html.twig', array(
            'home' => $home,     
        ));
    }

    public function staticAction($static){
        return $this->render('LoogaresExtraBundle:Static:'.$static.'.html.twig');
    }

}
