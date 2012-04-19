<?php

namespace Loogares\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Loogares\AdminBundle\Entity\TempHorario
 */
class TempHorario
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var smallint $dia
     */
    private $dia;

    /**
     * @var string $apertura_am
     */
    private $apertura_am;

    /**
     * @var string $cierre_am
     */
    private $cierre_am;

    /**
     * @var string $apertura_pm
     */
    private $apertura_pm;

    /**
     * @var string $cierre_pm
     */
    private $cierre_pm;

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
     * Set dia
     *
     * @param smallint $dia
     */
    public function setDia($dia)
    {
        $this->dia = $dia;
    }

    /**
     * Get dia
     *
     * @return smallint 
     */
    public function getDia()
    {
        return $this->dia;
    }

    /**
     * Set apertura_am
     *
     * @param string $aperturaAm
     */
    public function setAperturaAm($aperturaAm)
    {
        $this->apertura_am = $aperturaAm;
    }

    /**
     * Get apertura_am
     *
     * @return string 
     */
    public function getAperturaAm()
    {
        return $this->apertura_am;
    }

    /**
     * Set cierre_am
     *
     * @param string $cierreAm
     */
    public function setCierreAm($cierreAm)
    {
        $this->cierre_am = $cierreAm;
    }

    /**
     * Get cierre_am
     *
     * @return string 
     */
    public function getCierreAm()
    {
        return $this->cierre_am;
    }

    /**
     * Set apertura_pm
     *
     * @param string $aperturaPm
     */
    public function setAperturaPm($aperturaPm)
    {
        $this->apertura_pm = $aperturaPm;
    }

    /**
     * Get apertura_pm
     *
     * @return string 
     */
    public function getAperturaPm()
    {
        return $this->apertura_pm;
    }

    /**
     * Set cierre_pm
     *
     * @param string $cierrePm
     */
    public function setCierrePm($cierrePm)
    {
        $this->cierre_pm = $cierrePm;
    }

    /**
     * Get cierre_pm
     *
     * @return string 
     */
    public function getCierrePm()
    {
        return $this->cierre_pm;
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