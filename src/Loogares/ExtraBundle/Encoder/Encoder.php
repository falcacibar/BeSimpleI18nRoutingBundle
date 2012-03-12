<?php
namespace Loogares\ExtraBundle\Encoder;

use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;


class Encoder extends BasePasswordEncoder {
    
    protected $em;
    
    public function __construct($em)
    {
        $this->em = $em;
    }

    public function encodePassword($raw, $salt){
        $mail = $salt;
        $ur = $this->em->getRepository("LoogaresUsuarioBundle:Usuario");
        $usuario = $ur->findOneByMail($mail);
        
        if($usuario->getSha1password() == 0 && $usuario->getPassword() == md5($raw)){      
            $usuario->setSha1password(1);
            $usuario->setPassword(sha1($raw));
            $this->em->persist($usuario);
            $this->em->flush();
            return true;
        }

        return $this->comparePasswords($usuario->getPassword(), sha1($raw));
    }
	
    public function isPasswordValid($encoded, $raw, $salt){
        return $this->encodePassword($raw, $salt);
    }
}