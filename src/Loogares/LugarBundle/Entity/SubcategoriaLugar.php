<?php

namespace Loogares\LugarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Loogares\LugarBundle\Entity\SubcategoriaLugar
 */
class SubcategoriaLugar
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var Loogares\LugarBundle\Entity\Categoria
     */
    private $categoria;

    /**
     * @var Loogares\LugarBundle\Entity\Lugar
     */
    private $lugar;


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

    /**
     * Set lugar
     *
     * @param Loogares\LugarBundle\Entity\Lugar $lugar
     */
    public function setLugar(\Loogares\LugarBundle\Entity\Lugar $lugar)
    {
        $this->lugar = $lugar;
    }

    /**
     * Get lugar
     *
     * @return Loogares\LugarBundle\Entity\Lugar 
     */
    public function getLugar()
    {
        return $this->lugar;
    }
}