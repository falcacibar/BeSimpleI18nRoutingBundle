<?php

namespace Loogares\MailBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Loogares\MailBundle\Entity\Notificacion
 */
class Notificacion
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var boolean $activa
     */
    private $activa;

    /**
     * @var Loogares\MailBundle\Entity\TipoNotificacion
     */
    private $tipo_notificacion;

    /**
     * @var Loogares\UsuarioBundle\Entity\Usuario
     */
    private $usuario;


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
     * Set activa
     *
     * @param boolean $activa
     */
    public function setActiva($activa)
    {
        $this->activa = $activa;
    }

    /**
     * Get activa
     *
     * @return boolean 
     */
    public function getActiva()
    {
        return $this->activa;
    }

    /**
     * Set tipo_notificacion
     *
     * @param Loogares\MailBundle\Entity\TipoNotificacion $tipoNotificacion
     */
    public function setTipoNotificacion(\Loogares\MailBundle\Entity\TipoNotificacion $tipoNotificacion)
    {
        $this->tipo_notificacion = $tipoNotificacion;
    }

    /**
     * Get tipo_notificacion
     *
     * @return Loogares\MailBundle\Entity\TipoNotificacion 
     */
    public function getTipoNotificacion()
    {
        return $this->tipo_notificacion;
    }

    /**
     * Set usuario
     *
     * @param Loogares\UsuarioBundle\Entity\Usuario $usuario
     */
    public function setUsuario(\Loogares\UsuarioBundle\Entity\Usuario $usuario)
    {
        $this->usuario = $usuario;
    }

    /**
     * Get usuario
     *
     * @return Loogares\UsuarioBundle\Entity\Usuario 
     */
    public function getUsuario()
    {
        return $this->usuario;
    }
}