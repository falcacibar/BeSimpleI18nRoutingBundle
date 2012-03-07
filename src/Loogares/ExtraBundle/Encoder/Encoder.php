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
        $user=$ur->findOneByMail($mail);
        
        if($user->getSha1password() == null && $user->getPassword() == md5($raw)){      
            $user->setSha1password(1);
            $user->setPassword(sha1($raw));
            $this->em->persist($user);
            $this->em->flush();
            return true;
        }

        return $this->comparePasswords($user->getPassword(), sha1($raw));
    }
	
    public function isPasswordValid($encoded, $raw, $salt){
        return $this->encodePassword($raw, $salt);
    }
}