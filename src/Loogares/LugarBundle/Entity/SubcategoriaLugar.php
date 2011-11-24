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
     * @var Loogares\LugarBundle\Entity\SubCategoria
     */
    private $subcategoria;

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
     * Set subcategoria
     *
     * @param Loogares\LugarBundle\Entity\SubCategoria $subcategoria
     */
    public function setSubcategoria(\Loogares\LugarBundle\Entity\SubCategoria $subcategoria)
    {
        $this->subcategoria = $subcategoria;
    }

    /**
     * Get subcategoria
     *
     * @return Loogares\LugarBundle\Entity\SubCategoria 
     */
    public function getSubcategoria()
    {
        return $this->subcategoria;
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