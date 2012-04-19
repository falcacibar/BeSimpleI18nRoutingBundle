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
     * @var boolean $newsletter
     */
    private $newsletter;

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
     * Set newsletter
     *
     * @param boolean $newsletter
     */
    public function setNewsletter($newsletter)
    {
        $this->newsletter = $newsletter;
    }

    /**
     * Get newsletter
     *
     * @return boolean 
     */
    public function getNewsletter()
    {
        return $this->newsletter;
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