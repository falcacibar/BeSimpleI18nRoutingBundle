<?php

namespace Loogares\LugarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Loogares\LugarBundle\Entity\CaracteristicaCategoria
 */
class CaracteristicaCategoria
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var Loogares\LugarBundle\Entity\Caracteristica
     */
    private $caracteristica;

    /**
     * @var Loogares\LugarBundle\Entity\Categoria
     */
    private $lugar;


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
     * Set caracteristica
     *
     * @param Loogares\LugarBundle\Entity\Caracteristica $caracteristica
     */
    public function setCaracteristica(\Loogares\LugarBundle\Entity\Caracteristica $caracteristica)
    {
        $this->caracteristica = $caracteristica;
    }

    /**
     * Get caracteristica
     *
     * @return Loogares\LugarBundle\Entity\Caracteristica 
     */
    public function getCaracteristica()
    {
        return $this->caracteristica;
    }

    /**
     * Set lugar
     *
     * @param Loogares\LugarBundle\Entity\Categoria $lugar
     */
    public function setLugar(\Loogares\LugarBundle\Entity\Categoria $lugar)
    {
        $this->lugar = $lugar;
    }

    /**
     * Get lugar
     *
     * @return Loogares\LugarBundle\Entity\Categoria 
     */
    public function getLugar()
    {
        return $this->lugar;
    }
}