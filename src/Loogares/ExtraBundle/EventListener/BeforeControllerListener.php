<?php

namespace Loogares\ExtraBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Bundle\TwigBundle\Controller\ExceptionController;
use Symfony\Component\HttpFoundation\Request;


/**
 * This code gets executed everytime Kernel sends a event to a ApiBundle Controller
 * Here tokens are checked and if token is not ok, exception is thrown
 *
 * @throws AccessDeniedHttpException in case token is not valid
 */
class BeforeControllerListener
{
    /**
     * This variable gets kernel container object
     *
     * @var ContainerInterface
     */
    protected $container;
    protected $em;
    protected $times;

    /**
     * This constructor method injects a Container object in order to have access to YML bundle configuration inside the listener
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container, $em, ControllerResolver $resolver)
    {
        $this->container = $container;
        $this->em = $em;
        $this->times = 0;
        $this->resolver = $resolver;
    }

    /**
     * This method handles kernelControllerEvent checking if token is valid
     *
     * @param FilterControllerEvent $event
     * @throws AccessDeniedHttpException in case token is not valid
     */
    public function onKernelController( FilterControllerEvent $event )
    {
        $this->times++;

        // Nos aseguramos de que se ejecute una sola vez por página
        //if($this->times <= 1) {
            $controller = $event->getController();
            /**
             * $controller passed can be either a class or a Closure. This is not usual in Symfony2 but it may happien.
             * If it is a class, it comes in array format, so this works as @stof said
             * @see https://github.com/symfony/symfony/issues/1975
             */
            if(!is_array($controller)) return;

            /**
             * @todo This works because right now all API actions need a token and are available to all devices.
             * A cleaner method will need to be done if we want to restrict actions depending on agent token.
             * This code gets executed every time, even when a Exception occurs, where Symfony2 executes ExceptionController, so on that case, no actions based on tokens needs to be done
             */
            $em = $this->em;
            $controller         = $controller[0];
            $controllerClass    = get_class($controller);
            $controllerAttrs    = $event->getRequest()->attributes;
            $controllerAction   = substr($controllerAttrs->get('_controller'), strlen($controllerClass)+2);

            if(substr($controllerClass, 9, 13) === 'CampanaBundle') {
                $req = $controller->getRequest();
                $lugarRepository = $em->getRepository('LoogaresLugarBundle:Lugar');
                $lugar = $lugarRepository->findOneBySlug($req->get('slug'));
                $usuario = $controller->get('security.context')->getToken()->getUser();

                if($controller->get('security.context')->isGranted('ROLE_ADMIN')){
                    //YOU ARE ALLOWED
                } else if ($lugar->getDueno() == null || $lugar->getDueno()->getUsuario() == null){
                    $request = new Request();
                    $request->attributes->set('_controller', 'LoogaresExtraBundle:Default:homepage');
                    $event->setController($this->resolver->getController($request));
                } else if (!$controller->get('security.context')->isGranted('ROLE_USER') || ($lugar->getDueno()->getUsuario()->getId() != $usuario->getId())){
                    $request = new Request();
                    $request->attributes->set('_controller', 'LoogaresLugarBundle:Lugar:lugar', array('slug' => $lugar->getSlug()));
                    $event->setController($this->resolver->getController($request));
                }
            }


            if(substr($controllerClass, 0, 8) === 'Loogares') {
                $cr = $em->getRepository("LoogaresExtraBundle:Ciudad");

                $cookies = &$event->getRequest()->cookies;

                if( (   ($controller instanceOf \Loogares\LugarBundle\Controller\SearchController)
                        && $controllerAction === 'buscarAction'
                    ) || (
                        ($controller instanceOf \Loogares\ExtraBundle\Controller\DefaultController)
                        && ($controllerAction == 'homepageAction' || $controllerAction === 'localeAction')
                    )
                )
                    $ciudadSlug = $controllerAttrs->get('slug');

                elseif(!isset($ciudadSlug) && $controllerAttrs->has('ciudad'))
                    $ciudadSlug = $controllerAttrs->get('ciudad');
                // Cookie
                elseif($cookies->get('loogares_ciudad'))
                    $ciudadCookie = $cookies->get('loogares_ciudad');
                // Base de Datos
                elseif(
                    //Vemos si no hay ciudad en la peticion, si es usuario  y si tiene ciudad
                    !isset($ciudadSlug) && !isset($ciudadCookie) &&
                    $controller->get('security.context')->isGranted('ROLE_USER') &&
                    $controller->get('security.context')->getToken()->getUser()->getCiudad()
                )
                    $ciudadId = $controller->get('security.context')->getToken()->getUser()->getCiudad()->getId();
                else {
                    $rs = $em->getConnection()->fetchArray(
                               '    SELECT          c.id as `0`
                                    FROM            ciudad c
                                    INNER JOIN      pais p ON c.pais_id = p.id
                                    WHERE           p.codigo_iso2c =  (
                                                       SELECT  country_code
                                                       FROM    ip2loc
                                                       WHERE   INET_ATON(?) BETWEEN range_from AND range_to
                                                       LIMIT 1
                                                   )
                                                   AND (
                                                       c.mostrar_lugar = 3
                                                       OR c.mostrar_lugar = 1
                                    )
                                    ORDER BY c.mostrar_lugar DESC, RAND()
                                    LIMIT 1
                    ', array($_SERVER['REMOTE_ADDR']));

                    if(!count($rs))
                        $ciudadId = array_shift($rs);

                    unset($rs);
                }

                if(isset($ciudadSlug) || isset($ciudadCookie) || isset($ciudadId)) {
                    $ciudadArray = $controller->get('session')->get('ciudad');

                    if(isset($ciudadSlug) || isset($ciudadCookie)) {
                        $iCiudadSlug = (isset($ciudadSlug) ? $ciudadSlug : $ciudadCookie);

                        if(is_null($ciudadArray) || $ciudadArray['slug'] !== $iCiudadSlug)
                            $ciudad = $cr->findOneBySlug($iCiudadSlug);
                    } else if(is_null($ciudadId) || $ciudadArray['id'] != $ciudadId)
                        $ciudad = $cr->findOneById($ciudadId);
                    else if(is_null($ciudadArray)) {
                        // @todo: redireccionar a pagina para elegir pais
                        $ciudad = $cr->findOneBySlug('santiago-de-chile');
                    }

                    if(isset($ciudad)) {
                        $ciudadArray = array(
                                'id'        => $ciudad->getId() ,
                                'nombre'    => $ciudad->getNombre() ,
                                'slug'      => $ciudad->getSlug() ,
                                'pais'      => array(
                                    'id'        => $ciudad->getPais()->getId(),
                                    'nombre'    => $ciudad->getPais()->getNombre(),
                                    'slug'      => $ciudad->getPais()->getSlug(),
                                    'cctld'     => $ciudad->getPais()->getCctld()
                                )
                        );

                        $controller->get('session')->set('ciudad', $ciudadArray);
                        $this->container->get('http.communication_listener')->ciudadCookie = $ciudadArray['slug'];
                        $event->getRequest()->cookies->set('loogares_ciudad', $ciudadArray['slug']);
                    }
                }

                // Localizacion
                'IMPORTANTE: Asignacion dentro del if.'; // Comprobamos si NO hay locale de usuario
                if(! (
                    //Vemos si es usuario, si el locale no es null, y si cumple con la norma
                    $controller->get('security.context')->isGranted('ROLE_USER') &&
                    !is_null($locale = $controller->get('security.context')->getToken()->getUser()->getLocale()) &&
                    preg_match('/[a-z]{2}(_[a-z]{2})?/i', $locale)
                )) {
                    $locale = null;
                }

                'IMPORTANTE: Asignacion dentro del if.';
                if((
                      $cookies->has('loogares_locale') &&
                      (!($localeCookie = $cookies->get('loogares_locale'))) && is_null($locale)
                    ) || (!is_null($locale) && (!isset($localeCookie) || ($locale != $localeCookie)))
                ) {
                    if(is_null($locale)) {
                        if(!isset($ciudad)) {
                            if($ciudadArray) {
                                $ciudad = $em->getRepository("LoogaresExtraBundle:Ciudad")->findOneBySlug($ca['slug']);
                            }
                        }

                        if(isset($ciudad))
                            $locale = $ciudad->getPais()->getLocale();
                    }

                    // Cambiamos el locale por el del usuario.
                    $this->container->get('http.communication_listener')->localeCookie = $locale;
                    $event->getRequest()->cookies->set('loogares_locale', $locale);

                } else $locale = &$localeCookie;

                // locale definido
                if(isset($locale)) {
                    // current session
                    $controller->get('session')->setLocale($locale);

                    // Gedmo
                    $this->container->get('gedmo.listener.translatable')->setTranslatableLocale($locale);

                    // Facebook fos bundle
                    $ffh = $this->container->get('fos_facebook.helper');

                    // Facebook no acepta "en" y usa "es_LA" para latinoamerica.
                    $fblocale = (substr($locale, 0, 3) === 'es_' && $locale !== 'es_ES')
                                ? 'es_LA'
                                : $locale;

                    $ffhReflector = new \ReflectionClass($ffh);
                    $ffhReflectorCultureProp = $ffhReflector->getProperty('culture');
                    $ffhReflectorCultureProp->setAccessible(true);
                    $ffhReflectorCultureProp->setValue($ffh, $fblocale);

                    unset($ffhReflectorCultureProp, $ffhReflector);
                }

                // Reemplazo de slugs.
                if($controller instanceOf \Loogares\LugarBundle\Controller\SearchController) {
                    if($controllerAction === 'buscarAction') {
                        foreach(array(
                                'categoria'     => 'categorias',
                                'subcategoria'  => 'subcategoria'
                            ) as $slug => $tabla
                        ) {
                            if($controllerAttrs->has($slug)) {
                                $s = $em->getConnection()->fetchArray(
                                        '   SELECT slug FROM '.$tabla.'
                                            WHERE id = i18n_trad_id_por_campo(?, ?, ?, ?)
                                         ' , array(
                                                $slug
                                                ,'slug'
                                                , $controller->get('session')->getLocale()
                                                , $controllerAttrs->get($slug)
                                ));

                                if($s !== false) {
                                    $controllerAttrs->set($slug, $s[0]);
                                }


                                unset($s);
                            }
                        }
                    }
                }

                if(!isset($ciudadArray))
                    $ciudadArray = $controller->get('session')->get('ciudad');

                //@todo quitar parche tras tiempo razonable.
                if(isset($ciudadArray) && !isset($ciudadArray['pais']['cctld'])) {
                    $r = $em->getConnection()->fetchArray(
                                        ' SELECT cctld as `0` FROM pais WHERE id = ?'
                                        , array($ciudadArray['pais']['id'])
                    );

                    $ciudadArray['pais']['cctld'] = array_shift($r);
                    unset($r);

                    $controller->get('session')->set('ciudad', $ciudadArray);
                }

                $httpHost = &$_SERVER['HTTP_HOST'];

                if(strstr($httpHost, '.') !== false) {
                    $country_codes = array_map(
                            $f = function($r) { return $r['c']; } ,
                            $em->getConnection()->fetchAll(' SELECT cctld as c FROM pais WHERE mostrar_lugar = 3')
                    );

                    unset($f);

                    if(($prefix = (preg_match('/[a-z]{2}\.(.*?)\.com/i', $httpHost)
                                   && substr($httpHost, 0, 2) !== $ciudadArray['pais']['cctld']))
                        || ( preg_match('/\.[a-z]{2}$/i', $httpHost)
                             && in_array(substr($httpHost, -2), $country_codes)
                    )) {
                        if(isset($ciudadSlug)) {
                            $slug       = $ciudadArray['slug'];
                            $cctld      = $ciudadArray['pais']['cctld'];
                        } else {
                            $cctld = ($prefix) ? substr($httpHost, 0, 2) : substr($httpHost, -2);

                            if(substr($httpHost, -2) === $ciudadArray['pais']['cctld'])
                                    $slug = $ciudadArray['slug'];
                            else {
                                $r = $em->getConnection()->fetchArray(
                                                '   SELECT      c.slug
                                                    FROM        ciudad c
                                                    INNER JOIN  pais p ON c.pais_id = p.id
                                                    WHERE       p.cctld = ?
                                                                AND c.mostrar_lugar = 3
                                                    ORDER BY RAND()
                                                    LIMIT 1'
                                                , array($cctld)
                                )   ;

                                $slug = array_shift($r);
                                unset($r);
                            }
                        }

                        $controllerAttrs->set('_controller', 'LoogaresExtraBundle:Default:redirector');
                        $controllerAttrs->set('url', 'http://'.$cctld.'.localhost.com'.$controller->generateUrl('locale', array('slug' => $slug)));

                        $event->setController(array($controller, 'redirectorAction'));
                    }
                }


                // Si usuario está loggeado, significa que tuvo actividad
                if($controller->get('security.context')->isGranted('ROLE_USER')) {
                    $usuario = $controller->get('security.context')->getToken()->getUser();
                    $usuario->setFechaUltimaActividad(new \DateTime());
                    $em->flush();
                }
            }
        //}
    }
}
?>