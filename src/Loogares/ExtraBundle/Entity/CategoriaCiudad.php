<?php

namespace Loogares\ExtraBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Loogares\ExtraBundle\Entity\CategoriaCiudad
 */
class CategoriaCiudad
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var Loogares\ExtraBundle\Entity\Ciudad
     */
    private $ciudad;

    /**
     * @var Loogares\LugarBundle\Entity\Categoria
     */
    private $categoria;


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
     * Set ciudad
     *
     * @param Loogares\ExtraBundle\Entity\Ciudad $ciudad
     */
    public function setCiudad(\Loogares\ExtraBundle\Entity\Ciudad $ciudad)
    {
        $this->ciudad = $ciudad;
    }

    /**
     * Get ciudad
     *
     * @return Loogares\ExtraBundle\Entity\Ciudad 
     */
    public function getCiudad()
    {
        return $this->ciudad;
    }

    /**
     * Set categoria
     *
     * @param Loogares\LugarBundle\Entity\Categoria $categoria
     */
    public function setCategoria(\Loogares\LugarBundle\Entity\Categoria $categoria)
    {
        $this->categoria = $categoria;
    }

    /**
     * Get categoria
     *
     * @return Loogares\LugarBundle\Entity\Categoria 
     */
    public function getCategoria()
    {
        return $this->categoria;
    }
}