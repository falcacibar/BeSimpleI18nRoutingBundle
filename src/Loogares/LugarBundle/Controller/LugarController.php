<?php

namespace Loogares\LugarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Loogares\LugarBundle\Entity\Lugar;


class LugarController extends Controller
{

    public function listadoAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $q = $em->createQuery('SELECT u FROM Loogares\LugarBundle\Entity\Lugar u');
        $q->setFirstResult(rand(1, 6000));
        $q->setMaxResults(10);
        $result = $q->getResult();

        $repo = $this->getDoctrine()
                     ->getRepository('LoogaresLugarBundle:Lugar');
        
        return $this->render('LoogaresLugarBundle:Default:listado.html.twig', array('lugares' => $result));
    }    

    public function lugarAction($slug){
                $em = $this->getDoctrine()->getEntityManager();
                $q = $em->createQuery('SELECT u FROM Loogares\LugarBundle\Entity\Lugar u WHERE u.slug = ?1');
                $q->setParameter(1, $slug);
                $lugar = $q->getResult();

                return $this->render('LoogaresLugarBundle:Default:lugar.html.twig', array('lugar' => $lugar[0]));            
    }
    
    public function agregarAction()
    {
        $lugar = new Lugar();
        $lugar->setNombre('Lugar X');
        //$lugar->setUsuario('2');
        $lugar->setSlug('lugar-x');
        $lugar->setDireccion('A esquina B');
        $lugar->setDetalle('asdasd');
        $lugar->setIdComuna('1');
        $lugar->setIdBarrio('2');
        $lugar->setMapx('123');
        $lugar->setMapy('123');
        $lugar->setProfesional('pedro');
        $lugar->setAgnoConstruccion('11');
        $lugar->setMateriales('Ningunoo');
        $lugar->setSitioWeb('www.google.cl');
        $lugar->setFacebook('/facebook');
        $lugar->setTwitter('twitters');
        $lugar->setMail('loogares@loogares.com');
        $lugar->setEstrellas('5');
        $lugar->setPrecio('11');
        $lugar->setPrecioInicial('222');
        $lugar->setTotalRecomendaciones('2');
        $lugar->setFechaUltimaRecomendacion(new \DateTime("now"));
        $lugar->setUtiles('2');
        $lugar->setVisitas('1');
        $lugar->setDescripcion('descripcion');
        $lugar->setPrioridadWeb('1');
        $lugar->setIdTipoLugar('2');
        $lugar->setIdEstado('2');
        $lugar->setTieneDueno('0');

        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($lugar);
        $em->flush();

        #return new Response('dohohoho');
        return $this->render('LoogaresLugarBundle:Default:agregar.html.twig', array('lugar' => $lugar->getNombre()));
    }
}
