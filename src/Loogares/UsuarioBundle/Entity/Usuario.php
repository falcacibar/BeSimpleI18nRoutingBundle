<?php

namespace Loogares\UsuarioBundle\Entity;

use Loogares\ExtraBundle\Functions\LoogaresFunctions;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Mapping as ORM;

/**
 * Loogares\UsuarioBundle\Entity\Usuario
 */
class Usuario implements AdvancedUserInterface, \Serializable 
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $nombre
     */
    private $nombre;

    /**
     * @var string $apellido
     */
    private $apellido;

    /**
     * @var string $password
     */
    private $password;

    /**
     * @var string $slug
     */
    private $slug;

    /**
     * @var string $mail
     */
    private $mail;

    /**
     * @var string $telefono
     */
    private $telefono;

    /**
     * @var string $sexo
     */
    private $sexo;

    /**
     * @var string $web
     */
    private $web;

    /**
     * @var string $facebook
     */
    private $facebook;

    /**
     * @var string $twitter
     */
    private $twitter;

    /**
     * @var string $imagen_full
     */
    private $imagen_full;

    /**
     * Propiedad virtual para referenciar imagenes 
     */
    public $file;

    /**
     * @var datetime $fecha_nacimiento
     */
    private $fecha_nacimiento;

    /**
     * @var boolean $mostrar_edad
     */
    private $mostrar_edad;

    /**
     * @var datetime $fecha_registro
     */
    private $fecha_registro;

    /**
     * @var datetime $fecha_ultima_actividad
     */
    private $fecha_ultima_actividad;

    /**
     * @var boolean $newsletter_activo
     */
    private $newsletter_activo;

    /**
     * @var string $hash_confirmacion
     */
    private $hash_confirmacion;

    /**
     * @var string $cookie
     */
    private $cookie;

    /**
     * @var integer $facebook_uid
     */
    private $facebook_uid;

    /**
     * @var smallint $facebook_no_publicar
     */
    private $facebook_no_publicar;

    /**
     * @var string $facebook_data
     */
    private $facebook_data;

    /**
     * @var datetime $fecha_facebook_ultima_actividad
     */
    private $fecha_facebook_ultima_actividad;

    /**
     * @var string $salt
     */
    private $salt;

    /**
     * @var Loogares\UsuarioBundle\Entity\TipoUsuario
     */
    private $tipo_usuario;

    /**
     * @var Loogares\ExtraBundle\Entity\Estado
     */
    private $estado;

    /**
     * @var Loogares\ExtraBundle\Entity\Comuna
     */
    private $comuna;

    /**
     * @var Loogares\UsuarioBundle\Entity\Recomendacion
     */
    private $recomendaciones;


    public function __construct()
    {
        $this->recomendaciones = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    }

    /**
     * Get nombre
     *
     * @return string 
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set apellido
     *
     * @param string $apellido
     */
    public function setApellido($apellido)
    {
        $this->apellido = $apellido;
    }

    /**
     * Get apellido
     *
     * @return string 
     */
    public function getApellido()
    {
        return $this->apellido;
    }

    /**
     * Set password
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set slug
     *
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set mail
     *
     * @param string $mail
     */
    public function setMail($mail)
    {
        $this->mail = $mail;
    }

    /**
     * Get mail
     *
     * @return string 
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * Set telefono
     *
     * @param string $telefono
     */
    public function setTelefono($telefono)
    {
        $this->telefono = $telefono;
    }

    /**
     * Get telefono
     *
     * @return string 
     */
    public function getTelefono()
    {
        return $this->telefono;
    }

    /**
     * Set sexo
     *
     * @param string $sexo
     */
    public function setSexo($sexo)
    {
        $this->sexo = $sexo;
    }

    /**
     * Get sexo
     *
     * @return string 
     */
    public function getSexo()
    {
        return $this->sexo;
    }

    /**
     * Set web
     *
     * @param string $web
     */
    public function setWeb($web)
    {
        $this->web = $web;
    }

    /**
     * Get web
     *
     * @return string 
     */
    public function getWeb()
    {
        return $this->web;
    }

    /**
     * Set facebook
     *
     * @param string $facebook
     */
    public function setFacebook($facebook)
    {
        $this->facebook = $facebook;
    }

    /**
     * Get facebook
     *
     * @return string 
     */
    public function getFacebook()
    {
        return $this->facebook;
    }

    /**
     * Set twitter
     *
     * @param string $twitter
     */
    public function setTwitter($twitter)
    {
        $this->twitter = $twitter;
    }

    /**
     * Get twitter
     *
     * @return string 
     */
    public function getTwitter()
    {
        return $this->twitter;
    }

    /**
     * Set imagen_full
     *
     * @param string $imagenFull
     */
    public function setImagenFull($imagenFull)
    {
        $this->imagen_full = $imagenFull;
    }

    /**
     * Get imagen_full
     *
     * @return string 
     */
    public function getImagenFull()
    {
        return $this->imagen_full;
    }

    /**
     * Set fecha_nacimiento
     *
     * @param datetime $fechaNacimiento
     */
    public function setFechaNacimiento($fechaNacimiento)
    {
        $this->fecha_nacimiento = $fechaNacimiento;
    }

    /**
     * Get fecha_nacimiento
     *
     * @return datetime 
     */
    public function getFechaNacimiento()
    {
        return $this->fecha_nacimiento;
    }

     /**
     * Set mostrar_edad
     *
     * @param boolean $mostrarEdad
     */
    public function setMostrarEdad($mostrarEdad)
    {
        $this->mostrar_edad = $mostrarEdad;
    }

    /**
     * Get mostrar_edad
     *
     * @return boolean 
     */
    public function getMostrarEdad()
    {
        return $this->mostrar_edad;
    }

    /**
     * Set fecha_registro
     *
     * @param datetime $fechaRegistro
     */
    public function setFechaRegistro($fechaRegistro)
    {
        $this->fecha_registro = $fechaRegistro;
    }

    /**
     * Get fecha_registro
     *
     * @return datetime 
     */
    public function getFechaRegistro()
    {
        return $this->fecha_registro;
    }

    /**
     * Set fecha_ultima_actividad
     *
     * @param datetime $fechaUltimaActividad
     */
    public function setFechaUltimaActividad($fechaUltimaActividad)
    {
        $this->fecha_ultima_actividad = $fechaUltimaActividad;
    }

    /**
     * Get fecha_ultima_actividad
     *
     * @return datetime 
     */
    public function getFechaUltimaActividad()
    {
        return $this->fecha_ultima_actividad;
    }

    /**
     * Set newsletter_activo
     *
     * @param boolean $newsletterActivo
     */
    public function setNewsletterActivo($newsletterActivo)
    {
        $this->newsletter_activo = $newsletterActivo;
    }

    /**
     * Get newsletter_activo
     *
     * @return boolean 
     */
    public function getNewsletterActivo()
    {
        return $this->newsletter_activo;
    }

    /**
     * Set hash_confirmacion
     *
     * @param string $hashConfirmacion
     */
    public function setHashConfirmacion($hashConfirmacion)
    {
        $this->hash_confirmacion = $hashConfirmacion;
    }

    /**
     * Get hash_confirmacion
     *
     * @return string 
     */
    public function getHashConfirmacion()
    {
        return $this->hash_confirmacion;
    }

    /**
     * Set cookie
     *
     * @param string $cookie
     */
    public function setCookie($cookie)
    {
        $this->cookie = $cookie;
    }

    /**
     * Get cookie
     *
     * @return string 
     */
    public function getCookie()
    {
        return $this->cookie;
    }

    /**
     * Set facebook_uid
     *
     * @param integer $facebookUid
     */
    public function setFacebookUid($facebookUid)
    {
        $this->facebook_uid = $facebookUid;
    }

    /**
     * Get facebook_uid
     *
     * @return integer 
     */
    public function getFacebookUid()
    {
        return $this->facebook_uid;
    }

    /**
     * Set facebook_no_publicar
     *
     * @param smallint $facebookNoPublicar
     */
    public function setFacebookNoPublicar($facebookNoPublicar)
    {
        $this->facebook_no_publicar = $facebookNoPublicar;
    }

    /**
     * Get facebook_no_publicar
     *
     * @return smallint 
     */
    public function getFacebookNoPublicar()
    {
        return $this->facebook_no_publicar;
    }

    /**
     * Set facebook_data
     *
     * @param string $facebookData
     */
    public function setFacebookData($facebookData)
    {
        $this->facebook_data = $facebookData;
    }

    /**
     * Get facebook_data
     *
     * @return string 
     */
    public function getFacebookData()
    {
        return $this->facebook_data;
    }

    /**
     * Set fecha_facebook_ultima_actividad
     *
     * @param datetime $fechaFacebookUltimaActividad
     */
    public function setFechaFacebookUltimaActividad($fechaFacebookUltimaActividad)
    {
        $this->fecha_facebook_ultima_actividad = $fechaFacebookUltimaActividad;
    }

    /**
     * Get fecha_facebook_ultima_actividad
     *
     * @return datetime 
     */
    public function getFechaFacebookUltimaActividad()
    {
        return $this->fecha_facebook_ultima_actividad;
    }

    /**
     * Set salt
     *
     * @param string $salt
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
    }

    /**
     * Get salt
     *
     * @return string 
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set tipo_usuario
     *
     * @param Loogares\UsuarioBundle\Entity\TipoUsuario $tipoUsuario
     */
    public function setTipoUsuario(\Loogares\UsuarioBundle\Entity\TipoUsuario $tipoUsuario)
    {
        $this->tipo_usuario = $tipoUsuario;
    }

    /**
     * Get tipo_usuario
     *
     * @return Loogares\UsuarioBundle\Entity\TipoUsuario 
     */
    public function getTipoUsuario()
    {
        return $this->tipo_usuario;
    }

    /**
     * Set estado
     *
     * @param Loogares\ExtraBundle\Entity\Estado $estado
     */
    public function setEstado(\Loogares\ExtraBundle\Entity\Estado $estado)
    {
        $this->estado = $estado;
    }

    /**
     * Get estado
     *
     * @return Loogares\ExtraBundle\Entity\Estado 
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * Set comuna
     *
     * @param Loogares\ExtraBundle\Entity\Comuna $comuna
     */
    public function setComuna(\Loogares\ExtraBundle\Entity\Comuna $comuna)
    {
        $this->comuna = $comuna;
    }

    /**
     * Get comuna
     *
     * @return Loogares\ExtraBundle\Entity\Comuna 
     */
    public function getComuna()
    {
        return $this->comuna;
    }
    
    /**
     * Add recomendaciones
     *
     * @param Loogares\UsuarioBundle\Entity\Recomendacion $recomendaciones
     */
    public function addRecomendacion(\Loogares\UsuarioBundle\Entity\Recomendacion $recomendaciones)
    {
        $this->recomendaciones[] = $recomendaciones;
    }

    /**
     * Get recomendaciones
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getRecomendaciones()
    {
        return $this->recomendaciones;
    }

    /**
    * Implementación de AdvancedUserInterface interface
    */

    public function eraseCredentials(){
        
    }

    public function getRoles(){
        return array($this->tipo_usuario->getNombre());
    }

    public function getUsername(){
        return $this->getMail();
    }
    public function equals(UserInterface $user){
        if($this->getId()){
            if ($user->getId() != $this->id) {
                return false;
            }
        }
        else {
            if($user->getMail() != $this->mail) {
                return false;
            }
        }       
       
        return true;
    }

    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        if($this->estado->getNombre() == 'Activo')
            return true;

        return false;
    }

    /**
    * Implementando la Serializable interface
    */
    public function serialize()
    {
       return serialize($this->mail);
    }
    
    public function unserialize($data)
    {
        $this->mail = unserialize($data);
    }

    /**
    * Funciones que permiten manejar de mejor forma la imagen de usuario
    */
    public function getAbsolutePath()
    {
        return null === $this->imagen_full ? null : $this->getUploadRootDir().'/'.$this->imagen_full;
    }

    public function getWebPath()
    {
        return null === $this->imagen_full ? null : $this->getUploadDir().'/'.$this->imagen_full;
    }

    protected function getUploadRootDir()
    {
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        return 'assets/images/usuarios';
    }



    /**
     * @ORM\preUpdate
     */
    public function preUpload()
    {
        if ($this->file !== null) {
            // Se genera nombre de la imagen (nombre-apellido-id)
            $fn = new LoogaresFunctions();
            $filename = $fn->generarSlug($this->nombre.'-'.$this->apellido.'-'.$this->id);
            $this->setImagenFull($filename.'.jpg');//.$this->file->guessExtension());
        }
    }

    /**
     * @ORM\postUpdate
     */
    public function upload()
    {
        if ($this->file  === null) {
            return;
        }
        $this->file->move($this->getUploadRootDir(), $this->imagen_full);
        unset($this->file);

        // Eliminamos thumbnails
        if(file_exists(__DIR__.'/../../../../web/media/cache/medium_usuario/'.$this->getUploadDir().'/'.$this->getImagenFull()))
            unlink(__DIR__.'/../../../../web/media/cache/medium_usuario/'.$this->getUploadDir().'/'.$this->getImagenFull());
            
        if(file_exists(__DIR__.'/../../../../web/media/cache/small_usuario/'.$this->getUploadDir().'/'.$this->getImagenFull()))
            unlink(__DIR__.'/../../../../web/media/cache/small_usuario/'.$this->getUploadDir().'/'.$this->getImagenFull());
    }

    /**
     * @ORM\postRemove
     */
    public function removeUpload()
    {
        if ($file = $this->getAbsolutePath()) {
            unlink($file);
        }
    }

}