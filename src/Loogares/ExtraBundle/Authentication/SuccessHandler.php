<?php

namespace Loogares\ExtraBundle\Authentication;

use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse; 
use Symfony\Component\Routing\Router;
use Loogares\BlogBundle\Entity\Participante;

class SuccessHandler implements AuthenticationSuccessHandlerInterface
{
	/**
     * @var Router $router
     */
    protected $router;

    /**
     * @var EntityManager $em
     */
    protected $em;

    public function __construct(Router $router, $em)
    {
        $this->router = $router;
        $this->em = $em;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
    	// Caso en que el usuario participa en un concurso
    	$session = $request->getSession();
    	if($session->get('post_slug')) {
	        $slug = $session->get('post_slug');
	        $ciudad = $session->get('ciudad');

	        // Eliminamos la variable de post de session
	        $session->remove('post_slug');

            // Utilizamos una variable de sesión temporal para mostrar el popup de compartir en redes sociales
            $session->set('popup_compartir', '1');

	        $url = $this->router->generate('post', array('ciudad' => $ciudad['slug'], 'slug' => $slug));
    	}
    	else {
    		if ($targetPath = $session->get('_security.target_path')) {
                $url = $targetPath;
            }
            else {
            	$url = $request->headers->get('referer');
            }
    	}

    	return new RedirectResponse($url);
    }

}