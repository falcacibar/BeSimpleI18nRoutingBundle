<?php

namespace Loogares\UsuarioBundle\Security\User;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Loogares\UsuarioBundle\Entity\Usuario;
use Loogares\UsuarioBundle\Controller\UsuarioController;
use Loogares\ExtraBundle\Functions\LoogaresFunctions;
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
    protected $container;

    public function __construct(BaseFacebook $facebook, $em, $validator, $container)
    {
        $this->facebook = $facebook;
        $this->userManager = $em;
        $this->validator = $validator;
        $this->container = $container;

        //$this->container->get('security.context');
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

                // Si el usuario está loggeado y no tiene UID asociado, conectamos cuentas


                // Si el usuario no está loggeado, buscamos por mail y sino registramos

                // Revisamos si un usuario con el mismo email está registrado (para quienes conectan estando registrados)
                if (isset($fbdata['email'])) {
                    $user = $ur->findOneByMail($fbdata['email']);
                }
                
                // Si en este punto el usuario no existe, entonces debemos registrarlo
                if (empty($user)) {
                    //$user = new Usuario();
                    $user = new Usuario();

                    if (isset($fbdata['email'])) {
                        $user->setMail($fbdata['email']);
                    }
                    $estadoUsuario = $em->getRepository("LoogaresExtraBundle:Estado")
                                      ->findOneByNombre('Activo');
                    $user->setEstado($estadoUsuario);
                    $user->setFBData($fbdata);
                    $fn = new LoogaresFunctions();
                    $slug = $fn->generarSlug($user->getNombre().'-'.$user->getApellido());
                    $repetidos = $ur->getUsuarioSlugRepetido($slug);
                    if($repetidos > 0)
                        $slug = $slug.'-'.++$repetidos;                    
                    $user->setSlug($slug);

                    if(isset($fbdata['id'])) {
                        $fbimg = $this->facebook->api(array(
                            'method' => 'fql.query',
                            'query' => "SELECT pic_big FROM user WHERE uid = ".$fbdata['id'],
                            'callback' => ''
                        ));
                    }
                   
                    $user->setImagenFull("default.gif");
                    $user->setFechaRegistro(new \DateTime());
                    $user->setNewsletterActivo(1);
                    $hashConfirmacion = md5($user->getMail().time());
                    $user->setHashConfirmacion($hashConfirmacion);
                    $user->setSalt('');

                    $user->setPassword(sha1(time().$user->getSlug().time()));
                    $user->setSha1password(1);

                    // Seteamos tipo_usuario a ROLE_USER
                    $tipoUsuario = $em->getRepository("LoogaresUsuarioBundle:TipoUsuario")
                                      ->findOneByNombre('ROLE_USER');
                    $user->setTipoUsuario($tipoUsuario);
                    $em->persist($user);
                    $em->flush();

                    // Imagen Facebook
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_POST, 0);
                    curl_setopt($ch, CURLOPT_URL, $fbimg[0]['pic_big']);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                    $result = curl_exec($ch);
                    curl_close($ch);

                    $u = explode('.',$fbimg[0]['pic_big']);
                    $ext = array_pop($u);
                    $fn = time().'.jpg';
                    if(file_put_contents('assets/images/temp/'.$fn, $result)) {                        
                        if(getimagesize('assets/images/temp/'.$fn)) {
                            $imagen = new UploadedFile('assets/images/temp/'.$fn, $fn);                            
                            $user->file = $imagen;
                            $user->setImagenFull('default.gif');
                        }                        
                    }
                }

                $user->setFBData($fbdata);           
            }
            
            $em->flush();

            if(isset($fn)) {
                unlink('assets/images/temp/'.$fn);
            }
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

    public function getFacebook() {
        return $this->facebook;
    }
}