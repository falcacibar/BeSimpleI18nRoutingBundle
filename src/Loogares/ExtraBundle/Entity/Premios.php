<?php

namespace Loogares\ExtraBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Loogares\ExtraBundle\Entity\Premios
 */
class Premios
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
     * @var string $cantidad
     */
    private $cantidad;


    /**
     * Set id
     *
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * Set cantidad
     *
     * @param string $cantidad
     */
    public function setCantidad($cantidad)
    {
        $this->cantidad = $cantidad;
    }

    /**
     * Get cantidad
     *
     * @return string 
     */
    public function getCantidad()
    {
        return $this->cantidad;
    }
}