<?php
 
namespace Loogares\ExtraBundle\EventListener;
 
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Bundle\TwigBundle\Controller\ExceptionController;

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
 
    /**
     * This constructor method injects a Container object in order to have access to YML bundle configuration inside the listener
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container, $em)
    {
        $this->container = $container;
        $this->em = $em;
    }
 
    /**
     * This method handles kernelControllerEvent checking if token is valid
     *
     * @param FilterControllerEvent $event
     * @throws AccessDeniedHttpException in case token is not valid
     */
    public function onKernelController(FilterControllerEvent $event)
    {
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
        if($controllerPath[0] == 'Loogares') {
            $em = $this->em;

            echo 'lolol';
        }                         
    }
}
?>