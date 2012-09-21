<?php

namespace Loogares\ExtraBundle\EventListener;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * HttpCommunicationListener
 *
 * Response and Request manipulation.
 *
 */
class HttpCommunicationListener
{
    private $container;
    private $entityManager;

    public  $localeCookie = null;
    public  $ciudadCookie = null;

    public  $periodo = 31536000;


    public function __construct(ContainerInterface $container, \Doctrine\ORM\EntityManager $entityManager)
    {
        $this->container = $container;
        $this->entityManager = $entityManager;
    }

    public function onKernelRequest(GetResponseEvent $event) {
    }

    /**
     * onKernelResponse
     *
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $sp = $this->container->parameters['session.storage.options'];

        if(!is_null($this->localeCookie)) {
            $event->getResponse()->headers->setCookie(new Cookie(
                'loogares.locale' , // nombre
                $this->localeCookie, // valor
                time() + $this->periodo , // periodo (1 año)
                '/', // ruta
                (isset($sp['domain']) ? $sp['domain'] : null) , // dominio
                false , // segura
                true // sólo http
            ));
        }

        if(!is_null($this->ciudadCookie)) {
            $event->getResponse()->headers->setCookie(new Cookie(
                'loogares.ciudad' , // nombre
                $this->ciudadCookie, // valor
                time() + $this->periodo , // periodo (1 año)
                '/', // ruta
                (isset($sp['domain']) ? $sp['domain'] : null) , // dominio
                false , // segura
                true // sólo http
            ));
        }
    }
}
