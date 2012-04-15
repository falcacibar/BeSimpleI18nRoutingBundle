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
     * This method handles kernelControllerEvent checking if token is valid
     *
     * @param FilterControllerEvent $event
     * @throws AccessDeniedHttpException in case token is not valid
     */
    public function onKernelController(FilterControllerEvent $event)
    {
  
    }
}
?>