<?php

namespace Loogares\BlogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Loogares\BlogBundle\Entity\Concurso
 */
class Concurso
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $titulo
     */
    private $titulo;

    /**
     * @var string $descripcion
     */
    private $descripcion;

    /**
     * @var integer $numero_premios
     */
    private $numero_premios;

    /**
     * @var datetime $fecha_inicio
     */
    private $fecha_inicio;

    /**
     * @var datetime $fecha_termino
     */
    private $fecha_termino;

    /**
     * @var integer $twitter_inicio
     */
    private $twitter_inicio;

    /**
     * @var integer $twitter_final
     */
    private $twitter_final;

    /**
     * @var integer $facebook_inicio
     */
    private $facebook_inicio;

    /**
     * @var integer $facebook_final
     */
    private $facebook_final;

     /**
     * @var integer $visitas_home
     */
    private $visitas_home;

    /**
     * @var integer $visitas_busquedas
     */
    private $visitas_busquedas;

    /**
     * @var integer $visitas_otros_locales
     */
    private $visitas_otros_locales;

    /**
     * @var integer $visitas_post
     */
    private $visitas_post;

    /**
     * @var integer $visitas_ficha
     */
    private $visitas_ficha;

    /**
     * @var Loogares\BlogBundle\Entity\Participante
     */
    private $participantes;

    /**
     * @var Loogares\BlogBundle\Entity\Posts
     */
    private $post;

    /**
     * @var Loogares\BlogBundle\Entity\EstadoConcurso
     */
    private $estado_concurso;

    /**
     * @var Loogares\BlogBundle\Entity\TipoConcurso
     */
    private $tipo_concurso;

    public function __construct()
    {
        $this->participantes = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set titulo
     *
     * @param string $titulo
     */
    public function setTitulo($titulo)
    {
        $this->titulo = $titulo;
    }

    /**
     * Get titulo
     *
     * @return string 
     */
    public function getTitulo()
    {
        return $this->titulo;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;
    }

    /**
     * Get descripcion
     *
     * @return string 
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set numero_premios
     *
     * @param integer $numeroPremios
     */
    public function setNumeroPremios($numeroPremios)
    {
        $this->numero_premios = $numeroPremios;
    }

    /**
     * Get numero_premios
     *
     * @return integer 
     */
    public function getNumeroPremios()
    {
        return $this->numero_premios;
    }

    /**
     * Set fecha_inicio
     *
     * @param datetime $fechaInicio
     */
    public function setFechaInicio($fechaInicio)
    {
        $this->fecha_inicio = $fechaInicio;
    }

    /**
     * Get fecha_inicio
     *
     * @return datetime 
     */
    public function getFechaInicio()
    {
        return $this->fecha_inicio;
    }

    /**
     * Set fecha_termino
     *
     * @param datetime $fechaTermino
     */
    public function setFechaTermino($fechaTermino)
    {
        $this->fecha_termino = $fechaTermino;
    }

    /**
     * Get fecha_termino
     *
     * @return datetime 
     */
    public function getFechaTermino()
    {
        return $this->fecha_termino;
    }

    /**
     * Set twitter_inicio
     *
     * @param integer $twitterInicio
     */
    public function setTwitterInicio($twitterInicio)
    {
        $this->twitter_inicio = $twitterInicio;
    }

    /**
     * Get twitter_inicio
     *
     * @return integer 
     */
    public function getTwitterInicio()
    {
        return $this->twitter_inicio;
    }

    /**
     * Set twitter_final
     *
     * @param integer $twitterFinal
     */
    public function setTwitterFinal($twitterFinal)
    {
        $this->twitter_final = $twitterFinal;
    }

    /**
     * Get twitter_final
     *
     * @return integer 
     */
    public function getTwitterFinal()
    {
        return $this->twitter_final;
    }

    /**
     * Set facebook_inicio
     *
     * @param integer $facebookInicio
     */
    public function setFacebookInicio($facebookInicio)
    {
        $this->facebook_inicio = $facebookInicio;
    }

    /**
     * Get facebook_inicio
     *
     * @return integer 
     */
    public function getFacebookInicio()
    {
        return $this->facebook_inicio;
    }

    /**
     * Set facebook_final
     *
     * @param integer $facebookFinal
     */
    public function setFacebookFinal($facebookFinal)
    {
        $this->facebook_final = $facebookFinal;
    }

    /**
     * Get facebook_final
     *
     * @return integer 
     */
    public function getFacebookFinal()
    {
        return $this->facebook_final;
    }

    /**
     * Add participantes
     *
     * @param Loogares\BlogBundle\Entity\Participante $participantes
     */
    public function addParticipante(\Loogares\BlogBundle\Entity\Participante $participantes)
    {
        $this->participantes[] = $participantes;
    }

    /**
     * Get participantes
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getParticipantes()
    {
        return $this->participantes;
    }

    /**
     * Get participantes activos
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getParticipantesActivos()
    {
        $activos = new \Doctrine\Common\Collections\ArrayCollection();
        foreach($this->participantes as $participante) {
            if($participante->getPendiente() == false) {
                $activos[] = $participante;
            }
        }
        return $activos;
    }

    /**
     * Set post
     *
     * @param Loogares\BlogBundle\Entity\Posts $post
     */
    public function setPost(\Loogares\BlogBundle\Entity\Posts $post)
    {
        $this->post = $post;
    }

    /**
     * Get post
     *
     * @return Loogares\BlogBundle\Entity\Posts 
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * Set estado_concurso
     *
     * @param Loogares\BlogBundle\Entity\EstadoConcurso $estadoConcurso
     */
    public function setEstadoConcurso(\Loogares\BlogBundle\Entity\EstadoConcurso $estadoConcurso)
    {
        $this->estado_concurso = $estadoConcurso;
    }

    /**
     * Get estado_concurso
     *
     * @return Loogares\BlogBundle\Entity\EstadoConcurso 
     */
    public function getEstadoConcurso()
    {
        return $this->estado_concurso;
    }

    /**
     * Set tipo_concurso
     *
     * @param Loogares\BlogBundle\Entity\TipoConcurso $tipoConcurso
     */
    public function setTipoConcurso(\Loogares\BlogBundle\Entity\TipoConcurso $tipoConcurso)
    {
        $this->tipo_concurso = $tipoConcurso;
    }

    /**
     * Get tipo_concurso
     *
     * @return Loogares\BlogBundle\Entity\TipoConcurso 
     */
    public function getTipoConcurso()
    {
        return $this->tipo_concurso;
    }
   

    /**
     * Set visitas_home
     *
     * @param integer $visitasHome
     */
    public function setVisitasHome($visitasHome)
    {
        $this->visitas_home = $visitasHome;
    }

    /**
     * Get visitas_home
     *
     * @return integer 
     */
    public function getVisitasHome()
    {
        return $this->visitas_home;
    }

    /**
     * Set visitas_busquedas
     *
     * @param integer $visitasBusquedas
     */
    public function setVisitasBusquedas($visitasBusquedas)
    {
        $this->visitas_busquedas = $visitasBusquedas;
    }

    /**
     * Get visitas_busquedas
     *
     * @return integer 
     */
    public function getVisitasBusquedas()
    {
        return $this->visitas_busquedas;
    }

     /**
     * Set visitas_otros_locales
     *
     * @param integer $visitasOtrosLocales
     */
    public function setVisitasOtrosLocales($visitasOtrosLocales)
    {
        $this->visitas_otros_locales = $visitasOtrosLocales;
    }

    /**
     * Get visitas_otros_locales
     *
     * @return integer 
     */
    public function getVisitasOtrosLocales()
    {
        return $this->visitas_otros_locales;
    }

    /**
     * Set visitas_post
     *
     * @param integer $visitasPost
     */
    public function setVisitasPost($visitasPost)
    {
        $this->visitas_post = $visitasPost;
    }

    /**
     * Get visitas_post
     *
     * @return integer 
     */
    public function getVisitasPost()
    {
        return $this->visitas_post;
    }

    /**
     * Set visitas_ficha
     *
     * @param integer $visitasFicha
     */
    public function setVisitasFicha($visitasFicha)
    {
        $this->visitas_ficha = $visitasFicha;
    }

    /**
     * Get visitas_ficha
     *
     * @return integer 
     */
    public function getVisitasFicha()
    {
        return $this->visitas_ficha;
    }
    
   
    /**
     * @var Loogares\CampanaBundle\Entity\Campana
     */
    private $campana;


    /**
     * Set campana
     *
     * @param Loogares\CampanaBundle\Entity\Campana $campana
     */
    public function setCampana(\Loogares\CampanaBundle\Entity\Campana $campana)
    {
        $this->campana = $campana;
    }

    /**
     * Get campana
     *
     * @return Loogares\CampanaBundle\Entity\Campana 
     */
    public function getCampana()
    {
        return $this->campana;
    }
    /**
     * @var datetime $fecha_termino_concurso
     */
    private $fecha_termino_concurso;


    /**
     * Set fecha_termino_concurso
     *
     * @param datetime $fechaTerminoConcurso
     */
    public function setFechaTerminoConcurso($fechaTerminoConcurso)
    {
        $this->fecha_termino_concurso = $fechaTerminoConcurso;
    }

    /**
     * Get fecha_termino_concurso
     *
     * @return datetime 
     */
    public function getFechaTerminoConcurso()
    {
        return $this->fecha_termino_concurso;
    }
}