<?php

namespace Loogares\BlogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

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
     * @var string $titulo
     */
    private $titulo;

    /**
     * @var string $slug
     */
    private $slug;

    /**
     * @var string $titulo_home
     */
    private $titulo_home;

    /**
     * @var string $descripcion_home
     */
    private $descripcion_home;

    /**
     * @var string $imagen
     */
    private $imagen;

    /**
     * @var string $imagen_detalle
     */
    private $imagen_detalle;

    /**
     * @var string $imagen_home
     */
    private $imagen_home;

    /**
     * @var text $contenido
     */
    private $contenido;

    /**
     * @var string $preview
     */
    private $preview;

    /**
     * @var date $fecha
     */
    private $fecha;

    /**
     * @var datetime $fecha_publicacion
     */
    private $fecha_publicacion;

    /**
     * @var datetime $fecha_termino
     */
    private $fecha_termino;

    /**
     * @var text $condiciones
     */
    private $condiciones;

    /**
     * @var smallint $numero_premios
     */
    private $numero_premios;

    /**
     * @var text $ganadores
     */
    private $ganadores;

    /**
     * @var text $detalles
     */
    private $detalles;

    /**
     * @var smallint $destacado_home
     */
    private $destacado_home;

    /**
     * @var smallint $posicion_home
     */
    private $posicion_home;

    /**
     * @var Loogares\LugarBundle\Entity\Lugar
     */
    private $lugar;

    /**
     * @var Loogares\ExtraBundle\Entity\Ciudad
     */
    private $ciudad;

    /**
     * @var Loogares\UsuarioBundle\Entity\Usuario
     */
    private $usuario;

    /**
     * @var Loogares\BlogBundle\Entity\Categoria
     */
    private $blog_categoria;

    /**
     * @var Loogares\BlogBundle\Entity\EstadoConcurso
     */
    private $blog_estado_concurso;

    /**
     * @var Loogares\BlogBundle\Entity\Estado
     */
    private $blog_estado;

    /**
     * Propiedad virtual para referenciar primera imagen 
     */
    public $vimagen;

    /**
     * Propiedad virtual para referenciar segunda imagen 
     */
    public $vimagen_home;

    /**
     * Propiedad virtual para referenciar tercera imagen
     */
    public $vimagen_detalle;

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
     * Set slug
     *
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }

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
     * @param string $preview
     */
    public function setPreview($preview)
    {
        $this->preview = $preview;
    }

    /**
     * Get preview
     *
     * @return string 
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
     * Set posicion_home
     *
     * @param smallint $posicionHome
     */
    public function setPosicionHome($posicionHome)
    {
        $this->posicion_home = $posicionHome;
    }

    /**
     * Get posicion_home
     *
     * @return smallint 
     */
    public function getPosicionHome()
    {
        return $this->posicion_home;
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
     * Set blog_categoria
     *
     * @param Loogares\BlogBundle\Entity\Categoria $blogCategoria
     */
    public function setBlogCategoria(\Loogares\BlogBundle\Entity\Categoria $blogCategoria)
    {
        $this->blog_categoria = $blogCategoria;
    }

    /**
     * Get blog_categoria
     *
     * @return Loogares\BlogBundle\Entity\Categoria 
     */
    public function getBlogCategoria()
    {
        return $this->blog_categoria;
    }

    /**
     * Set blog_estado_concurso
     *
     * @param Loogares\BlogBundle\Entity\EstadoConcurso $blogEstadoConcurso
     */
    public function setBlogEstadoConcurso(\Loogares\BlogBundle\Entity\EstadoConcurso $blogEstadoConcurso)
    {
        $this->blog_estado_concurso = $blogEstadoConcurso;
    }

    /**
     * Get blog_estado_concurso
     *
     * @return Loogares\BlogBundle\Entity\EstadoConcurso 
     */
    public function getBlogEstadoConcurso()
    {
        return $this->blog_estado_concurso;
    }

    /**
     * Set blog_estado
     *
     * @param Loogares\BlogBundle\Entity\Estado $blogEstado
     */
    public function setBlogEstado(\Loogares\BlogBundle\Entity\Estado $blogEstado)
    {
        $this->blog_estado = $blogEstado;
    }

    /**
     * Get blog_estado
     *
     * @return Loogares\BlogBundle\Entity\Estado 
     */
    public function getBlogEstado()
    {
        return $this->blog_estado;
    }

        /**
    * Funciones que permiten manejar de mejor forma la imagen de usuario
    */
    public function getAbsolutePath()
    {
        return null === $this->imagen ? null : $this->getUploadRootDir().'/'.$this->imagen;
    }

    public function getWebPath()
    {
        return null === $this->imagen ? null : $this->getUploadDir().'/'.$this->imagen;
    }

    protected function getUploadRootDir()
    {
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        return 'assets/images/blog';
    }

    /**
     * @ORM\preUpdate
     */
    public function preUpload()
    {
        if ($this->vimagen !== null) {
            $fn = new LoogaresFunctions();
            $filename = 'imagen.jpg';
            $this->setImagen($filename.'.jpg');
        }
    }

    /**
     * @ORM\postUpdate
     */
    public function upload()
    {
        echo 'as';
        if ($this->vimage  === null) {
            return;
        }
        try {
            $this->vimagen->move($this->getUploadRootDir(), $this->imagen);
        }
        catch(FileException $e) {
            rename($this->vimagen->getPathname(),$this->getAbsolutePath());
        }
        unset($this->vimagen);
    }

    /**
     * @ORM\postRemove
     */
    public function removeUpload()
    {
        if ($firstImg = $this->getAbsolutePath()) {
            unlink($firstImg);
        }
    }
}