<?php

namespace Loogares\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Loogares\AdminBundle\Entity\TempCaracteristicaLugar
 */
class TempCaracteristicaLugar
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