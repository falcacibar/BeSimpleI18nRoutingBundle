<?php

namespace Loogares\MailBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Loogares\MailBundle\Entity\TipoNotificacion
 */
class TipoNotificacion
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
     * @var Loogares\MailBundle\Entity\Notificacion
     */
    private $notificaciones;

    public function __construct()
    {
        $this->notificaciones = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Add notificaciones
     *
     * @param Loogares\MailBundle\Entity\Notificacion $notificaciones
     */
    public function addNotificacion(\Loogares\MailBundle\Entity\Notificacion $notificaciones)
    {
        $this->notificaciones[] = $notificaciones;
    }

    /**
     * Get notificaciones
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getNotificaciones()
    {
        return $this->notificaciones;
    }
}