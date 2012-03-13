<?php

namespace Loogares\UsuarioBundle\Security\User;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Loogares\UsuarioBundle\Entity\Usuario;
use \BaseFacebook;
use \FacebookApiException;

class FacebookProvider implements UserProviderInterface
{
    /**
     * @var \Facebook
     */
    protected $facebook;
    protected $userManager;
    protected $validator;

    public function __construct(BaseFacebook $facebook, $em, $validator)
    {
        $this->facebook = $facebook;
        $this->userManager = $em;
        $this->validator = $validator;        
    }

    public function supportsClass($class)
    {
        return $class === 'Loogares\UsuarioBundle\Entity\Usuario';
    }

    public function findUserByFbId($fbId)
    {
        return $this->userManager->getRepository('LoogaresUsuarioBundle:Usuario')->findOneBy(array('facebook_uid' => $fbId));
    }

    public function loadUserByUsername($username)
    {
        $em = $this->userManager;
        $ur = $em->getRepository('LoogaresUsuarioBundle:Usuario');

        // Buscamos por UID para ver si existe en nuestra DB
        $user = $this->findUserByFbId($username);

        try {
            $fbdata = $this->facebook->api('/me');
        } catch (FacebookApiException $e) {
            $fbdata = null;

        }

        if (!empty($fbdata)) {
            if (empty($user)) {
                // Revisamos si un usuario con el mismo email está registrado (para quienes conectan estando registrados)
                if (isset($fbdata['email'])) {
                    $user = $ur->findOneByMail($fbdata['email']);
                }
                
                // Si en este punto el usuario no existe, entonces debemos registrarlo
                if (empty($user)) {
                    $user = new Usuario();
                    $user->setPassword(sha1('probandounpassword'));
                    $user->setSha1Password(1);

                    if (isset($fbdata['email'])) {
                        $user->setMail($fbdata['email']);
                    }
                    $estadoUsuario = $em->getRepository("LoogaresExtraBundle:Estado")
                                      ->findOneByNombre('Activo');
                    $user->setEstado($estadoUsuario);
                    $user->setFBData($fbdata);
                    $em->persist($user);
                }

                $user->setFBData($fbdata);               
            }           

            /*if (count($this->validator->validate($user, 'Facebook'))) {
                // TODO: the user was found obviously, but doesnt match our expectations, do something smart
                throw new UsernameNotFoundException('The facebook user could not be stored');
            }*/
            
            $em->flush();
        }
        else {

        }

        if (empty($user)) {
            throw new UsernameNotFoundException('The user is not authenticated on facebook');
        }

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user)) || !$user->getFacebookUid()) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getFacebookUid());
    }
}