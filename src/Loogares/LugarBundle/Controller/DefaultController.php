<?php

namespace Loogares\LugarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Loogares\LugarBundle\Entity\ImagenLugar;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;



class DefaultController extends Controller
{
    
    public function indexAction()
    {
        return $this->render('LoogaresLugarBundle:Lugares:ajax.html.twig');
    }

    public function galeriaAction($slug) {
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");

        $lugar = $lr->findOneBySlug($slug);
        $id = $lr->getImagenLugarMasReciente($lugar)->getId();

        return $this->forward('LoogaresLugarBundle:Lugar:fotoGaleria', array('slug' => $slug, 'id' => $id));
    }

    public function fotoGaleriaAction($slug, $id) {
        $em = $this->getDoctrine()->getEntityManager();
        $lr = $em->getRepository("LoogaresLugarBundle:Lugar");
        $ilr = $em->getRepository("LoogaresLugarBundle:ImagenLugar");

        $lugar = $lr->findOneBySlug($slug);
        $imagen = $ilr->find($id);

        $imagen->loggeadoCorrecto = $this->get('security.context')->getToken()->getUser() == $imagen->getUsuario();

        return $this->render('LoogaresLugarBundle:Lugares:foto_galeria.html.twig', array(
            'lugar' => $lugar,
            'imagen' => $imagen,
        ));
    }
    
}
