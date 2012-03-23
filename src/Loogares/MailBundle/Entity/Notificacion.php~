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
     * @var string $nombre
     */
    private $nombre;

    /**
     * @var string $slug
     */
    private $slug;

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
    /**
     * @var string $estado
     */
    private $estado;


    /**
     * Set estado
     *
     * @param string $estado
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;
    }

    /**
     * Get estado
     *
     * @return string 
     */
    public function getEstado()
    {
        return $this->estado;
    }
}