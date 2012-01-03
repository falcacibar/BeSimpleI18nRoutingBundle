<?php

namespace Loogares\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Loogares\AdminBundle\Entity\TempCategoriaLugar
 */
class TempCategoriaLugar
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var smallint $principal
     */
    private $principal;

    /**
     * @var Loogares\LugarBundle\Entity\Categoria
     */
    private $categoria;

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
     * Set principal
     *
     * @param smallint $principal
     */
    public function setPrincipal($principal)
    {
        $this->principal = $principal;
    }

    /**
     * Get principal
     *
     * @return smallint 
     */
    public function getPrincipal()
    {
        return $this->principal;
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