<?php

namespace Loogares\BlogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Loogares\BlogBundle\Entity\Posts
 */
class Posts
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var text $titulo
     */
    private $titulo;

    /**
     * @var text $slug
     */
    private $slug;

    /**
     * @var text $contenido
     */
    private $contenido;

    /**
     * @var text $preview
     */
    private $preview;

    /**
     * @var date $fecha
     */
    private $fecha;

    /**
     * @var text $tipo
     */
    private $tipo;

    /**
     * @var text $ganadores
     */
    private $ganadores;

    /**
     * @var Loogares\LugarBundle\Entity\Lugar
     */
    private $lugar;

    /**
     * @var Loogares\UsuarioBundle\Entity\Usuario
     */
    private $usuario;


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
     * @param text $titulo
     */
    public function setTitulo($titulo)
    {
        $this->titulo = $titulo;
    }

    /**
     * Get titulo
     *
     * @return text 
     */
    public function getTitulo()
    {
        return $this->titulo;
    }

    /**
     * Set slug
     *
     * @param text $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * Get slug
     *
     * @return text 
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set contenido
     *
     * @param text $contenido
     */
    public function setContenido($contenido)
    {
        $this->contenido = $contenido;
    }

    /**
     * Get contenido
     *
     * @return text 
     */
    public function getContenido()
    {
        return $this->contenido;
    }

    /**
     * Set preview
     *
     * @param text $preview
     */
    public function setPreview($preview)
    {
        $this->preview = $preview;
    }

    /**
     * Get preview
     *
     * @return text 
     */
    public function getPreview()
    {
        return $this->preview;
    }

    /**
     * Set fecha
     *
     * @param date $fecha
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;
    }

    /**
     * Get fecha
     *
     * @return date 
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * Set tipo
     *
     * @param text $tipo
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;
    }

    /**
     * Get tipo
     *
     * @return text 
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * Set ganadores
     *
     * @param text $ganadores
     */
    public function setGanadores($ganadores)
    {
        $this->ganadores = $ganadores;
    }

    /**
     * Get ganadores
     *
     * @return text 
     */
    public function getGanadores()
    {
        return $this->ganadores;
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

    /**
     * Set usuario
     *
     * @param Loogares\UsuarioBundle\Entity\Usuario $usuario
     */
    public function setUsuario(\Loogares\UsuarioBundle\Entity\Usuario $usuario)
    {
        $this->usuario = $usuario;
    }

    /**
     * Get usuario
     *
     * @return Loogares\UsuarioBundle\Entity\Usuario 
     */
    public function getUsuario()
    {
        return $this->usuario;
    }
    /**
     * @var string $imagen
     */
    private $imagen;

    /**
     * @var text $condiciones
     */
    private $condiciones;


    /**
     * Set imagen
     *
     * @param string $imagen
     */
    public function setImagen($imagen)
    {
        $this->imagen = $imagen;
    }

    /**
     * Get imagen
     *
     * @return string 
     */
    public function getImagen()
    {
        return $this->imagen;
    }

    /**
     * Set condiciones
     *
     * @param text $condiciones
     */
    public function setCondiciones($condiciones)
    {
        $this->condiciones = $condiciones;
    }

    /**
     * Get condiciones
     *
     * @return text 
     */
    public function getCondiciones()
    {
        return $this->condiciones;
    }
    /**
     * @var text $detalles
     */
    private $detalles;


    /**
     * Set detalles
     *
     * @param text $detalles
     */
    public function setDetalles($detalles)
    {
        $this->detalles = $detalles;
    }

    /**
     * Get detalles
     *
     * @return text 
     */
    public function getDetalles()
    {
        return $this->detalles;
    }
    /**
     * @var string $imagen_detalle
     */
    private $imagen_detalle;


    /**
     * Set imagen_detalle
     *
     * @param string $imagenDetalle
     */
    public function setImagenDetalle($imagenDetalle)
    {
        $this->imagen_detalle = $imagenDetalle;
    }

    /**
     * Get imagen_detalle
     *
     * @return string 
     */
    public function getImagenDetalle()
    {
        return $this->imagen_detalle;
    }
    /**
     * @var Loogares\BlogBundle\Entity\TipoPost
     */
    private $tipo_post;


    /**
     * Set tipo_post
     *
     * @param Loogares\BlogBundle\Entity\TipoPost $tipoPost
     */
    public function setTipoPost(\Loogares\BlogBundle\Entity\TipoPost $tipoPost)
    {
        $this->tipo_post = $tipoPost;
    }

    /**
     * Get tipo_post
     *
     * @return Loogares\BlogBundle\Entity\TipoPost 
     */
    public function getTipoPost()
    {
        return $this->tipo_post;
    }
    /**
     * @var smallint $publicado
     */
    private $publicado;


    /**
     * Set publicado
     *
     * @param smallint $publicado
     */
    public function setPublicado($publicado)
    {
        $this->publicado = $publicado;
    }

    /**
     * Get publicado
     *
     * @return smallint 
     */
    public function getPublicado()
    {
        return $this->publicado;
    }
    /**
     * @var string $titulo_home
     */
    private $titulo_home;

    /**
     * @var string $descripcion_home
     */
    private $descripcion_home;

    /**
     * @var string $imagen_home
     */
    private $imagen_home;

    /**
     * @var datetime $fecha_publicacion
     */
    private $fecha_publicacion;

    /**
     * @var datetime $feca_termino
     */
    private $feca_termino;

    /**
     * @var smallint $numero_premios
     */
    private $numero_premios;

    /**
     * @var string $titulo_alianza_estado
     */
    private $titulo_alianza_estado;

    /**
     * @var smallint $destacado_home
     */
    private $destacado_home;

    /**
     * @var Loogares\ExtraBundle\Entity\Ciudad
     */
    private $ciudad;


    /**
     * Set titulo_home
     *
     * @param string $tituloHome
     */
    public function setTituloHome($tituloHome)
    {
        $this->titulo_home = $tituloHome;
    }

    /**
     * Get titulo_home
     *
     * @return string 
     */
    public function getTituloHome()
    {
        return $this->titulo_home;
    }

    /**
     * Set descripcion_home
     *
     * @param string $descripcionHome
     */
    public function setDescripcionHome($descripcionHome)
    {
        $this->descripcion_home = $descripcionHome;
    }

    /**
     * Get descripcion_home
     *
     * @return string 
     */
    public function getDescripcionHome()
    {
        return $this->descripcion_home;
    }

    /**
     * Set imagen_home
     *
     * @param string $imagenHome
     */
    public function setImagenHome($imagenHome)
    {
        $this->imagen_home = $imagenHome;
    }

    /**
     * Get imagen_home
     *
     * @return string 
     */
    public function getImagenHome()
    {
        return $this->imagen_home;
    }

    /**
     * Set fecha_publicacion
     *
     * @param datetime $fechaPublicacion
     */
    public function setFechaPublicacion($fechaPublicacion)
    {
        $this->fecha_publicacion = $fechaPublicacion;
    }

    /**
     * Get fecha_publicacion
     *
     * @return datetime 
     */
    public function getFechaPublicacion()
    {
        return $this->fecha_publicacion;
    }

    /**
     * Set feca_termino
     *
     * @param datetime $fecaTermino
     */
    public function setFecaTermino($fecaTermino)
    {
        $this->feca_termino = $fecaTermino;
    }

    /**
     * Get feca_termino
     *
     * @return datetime 
     */
    public function getFecaTermino()
    {
        return $this->feca_termino;
    }

    /**
     * Set numero_premios
     *
     * @param smallint $numeroPremios
     */
    public function setNumeroPremios($numeroPremios)
    {
        $this->numero_premios = $numeroPremios;
    }

    /**
     * Get numero_premios
     *
     * @return smallint 
     */
    public function getNumeroPremios()
    {
        return $this->numero_premios;
    }

    /**
     * Set titulo_alianza_estado
     *
     * @param string $tituloAlianzaEstado
     */
    public function setTituloAlianzaEstado($tituloAlianzaEstado)
    {
        $this->titulo_alianza_estado = $tituloAlianzaEstado;
    }

    /**
     * Get titulo_alianza_estado
     *
     * @return string 
     */
    public function getTituloAlianzaEstado()
    {
        return $this->titulo_alianza_estado;
    }

    /**
     * Set destacado_home
     *
     * @param smallint $destacadoHome
     */
    public function setDestacadoHome($destacadoHome)
    {
        $this->destacado_home = $destacadoHome;
    }

    /**
     * Get destacado_home
     *
     * @return smallint 
     */
    public function getDestacadoHome()
    {
        return $this->destacado_home;
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
     * @var datetime $fecha_termino
     */
    private $fecha_termino;


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
}