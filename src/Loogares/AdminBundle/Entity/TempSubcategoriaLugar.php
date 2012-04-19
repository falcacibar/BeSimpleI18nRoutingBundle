<?php

namespace Loogares\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Loogares\AdminBundle\Entity\TempSubcategoriaLugar
 */
class TempSubcategoriaLugar
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
     * @var Loogares\AdminBundle\Entity\TempLugar
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
     * @param Loogares\AdminBundle\Entity\TempLugar $lugar
     */
    public function setLugar(\Loogares\AdminBundle\Entity\TempLugar $lugar)
    {
        $this->lugar = $lugar;
    }

    /**
     * Get lugar
     *
     * @return Loogares\AdminBundle\Entity\TempLugar 
     */
    public function getLugar()
    {
        return $this->lugar;
    }
}