<?php
 
namespace Loogares\ExtraBundle\EventListener;
 
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Bundle\TwigBundle\Controller\ExceptionController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
            $controller = $controller[0];
            $controllerPath = explode('\\', get_class($controller));
            $em = $this->em;

            if($controllerPath[1] == 'CampanaBundle'){
                $req = $controller->getRequest();
                $lugarRepository = $em->getRepository('LoogaresLugarBundle:Lugar');
                $lugar = $lugarRepository->findOneBySlug($req->get('slug'));
                $usuario = $controller->get('security.context')->getToken()->getUser();

                if($usuario->getTipoUsuario()->getId() == 1){
                }else if($lugar->getDueno() == null || $lugar->getDueno()->getUsuario() == null){
                    $request = new Request();
                    $request->attributes->set('_controller', 'LoogaresExtraBundle:Default:homepage');
                    $event->setController($this->resolver->getController($request));
                }else if($lugar->getDueno()->getUsuario()->getId() != $usuario->getId()){
                    $request = new Request();
                    $request->attributes->set('_controller', 'LoogaresLugarBundle:Lugar:lugar', array('slug' => $lugar->getSlug()));
                    $event->setController($this->resolver->getController($request));
                }
            }

            if($controllerPath[0] == 'Loogares') {
                // Si ciudad no está en la sesión, seteamos Santiago de Chile por default
                if(!$controller->get('session')->get('ciudad')) {            
                    $cr = $em->getRepository("LoogaresExtraBundle:Ciudad");
                    $ciudad = $cr->findOneBySlug('santiago-de-chile');

                    // Seteamos el locale correspondiente a la ciudad en la sesión
                    $controller->get('session')->setLocale($ciudad->getPais()->getLocale());

                    $ciudadArray = array();
                    $ciudadArray['id'] = $ciudad->getId();
                    $ciudadArray['nombre'] = $ciudad->getNombre();
                    $ciudadArray['slug'] = $ciudad->getSlug();

                    $ciudadArray['pais']['id'] = $ciudad->getPais()->getId();
                    $ciudadArray['pais']['nombre'] = $ciudad->getPais()->getNombre();
                    $ciudadArray['pais']['slug'] = $ciudad->getPais()->getSlug();

                    $controller->get('session')->set('ciudad',$ciudadArray);
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