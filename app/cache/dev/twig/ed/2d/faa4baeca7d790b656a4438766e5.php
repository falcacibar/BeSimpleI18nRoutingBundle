<?php

/* LoogaresLugarBundle:Default:lugar.html.twig */
class __TwigTemplate_ed2dfaa4baeca7d790b656a4438766e5 extends Twig_Template
{
    protected $parent;

    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = array();
        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'content' => array($this, 'block_content'),
        );
    }

    public function getParent(array $context)
    {
        $parent = "::base.html.twig";
        if ($parent instanceof Twig_Template) {
            $name = $parent->getTemplateName();
            $this->parent[$name] = $parent;
            $parent = $name;
        } elseif (!isset($this->parent[$parent])) {
            $this->parent[$parent] = $this->env->loadTemplate($parent);
        }

        return $this->parent[$parent];
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $context = array_merge($this->env->getGlobals(), $context);

        $this->getParent($context)->display($context, array_merge($this->blocks, $blocks));
    }

    // line 4
    public function block_title($context, array $blocks = array())
    {
        echo "Loogares.com | ";
        echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'lugar'), "nombre", array(), "any", false), "html");
    }

    // line 6
    public function block_content($context, array $blocks = array())
    {
        // line 7
        echo "<!-- Ficha, top part -->
<h1>
</h1>
<div id=\"lugar-ficha\">
    <div id=\"lugar-info\">
        <div id=\"lugar-foto\">foto</div>
        <div id=\"lugar-mapa\">mapa</div>
            <h2>";
        // line 14
        echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'lugar'), "nombre", array(), "any", false), "html");
        echo " - ID: ";
        echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'lugar'), "id", array(), "any", false), "html");
        echo " </h2>
            <div class=\"estrellas\">";
        // line 15
        echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'lugar'), "estrellas", array(), "any", false), "html");
        echo " Estrellas | ";
        echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'lugar'), "totalRecomendaciones", array(), "any", false), "html");
        echo " Recomendaciones</div>
            <div id=\"lugar-direccion\">";
        // line 16
        echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'lugar'), "calle", array(), "any", false), "html");
        echo " ";
        echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'lugar'), "numero", array(), "any", false), "html");
        echo " - Ciudad</div>
            <div id=\"lugar-sector\"></div>
            <div id=\"lugar-telefono\">Telefono: ---</div>
            <div id=\"lugar-horarios\">
                ";
        // line 20
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute($this->getContext($context, 'lugar'), "horarios", array(), "any", false));
        foreach ($context['_seq'] as $context['_key'] => $context['horario']) {
            // line 21
            echo "                    AM: ";
            echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'horario'), "aperturaAm", array(), "any", false), "html");
            echo " - ";
            echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'horario'), "cierreAm", array(), "any", false), "html");
            echo "<br/>
                    PM: ";
            // line 22
            echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'horario'), "aperturaPm", array(), "any", false), "html");
            echo " - ";
            echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'horario'), "cierrePm", array(), "any", false), "html");
            echo "<br/>
                ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['horario'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
        // line 24
        echo "            </div>
            <div id=\"lugar-categorias\">
                Categorias: ";
        // line 26
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute($this->getContext($context, 'lugar'), "categorias", array(), "any", false));
        foreach ($context['_seq'] as $context['_key'] => $context['categoria']) {
            echo " ";
            echo twig_escape_filter($this->env, $this->getContext($context, 'categoria'), "html");
            echo " ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['categoria'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
        // line 27
        echo "            </div>
            <div id=\"lugar-url\">";
        // line 28
        echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'lugar'), "sitioWeb", array(), "any", false), "html");
        echo "</div>
            <div id=\"lugar-facebook\">";
        // line 29
        echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'lugar'), "facebook", array(), "any", false), "html");
        echo "</div>
            <div id=\"lugar-twitter\">";
        // line 30
        echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'lugar'), "twitter", array(), "any", false), "html");
        echo "</div>

            <div id=\"primero-recomendar\">Primero Recomendar: ";
        // line 32
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($this->getAttribute($this->getContext($context, 'lugar'), "primero", array(), "any", false), "usuario", array(), "any", false), "usuario", array(), "any", false), "html");
        echo "</div>

            <div id=\"recomendaciones\">
                <h2>Recomendaciones</h2>
                <p>Mostrando Recomendaciones de ";
        // line 36
        echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'lugar'), "mostrandoComentariosDe", array(), "any", false), "html");
        echo " a ";
        echo twig_escape_filter($this->env, ($this->getAttribute($this->getContext($context, 'lugar'), "mostrandoComentariosDe", array(), "any", false) + 9), "html");
        echo " || ";
        echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'lugar'), "totalRecomendaciones", array(), "any", false), "html");
        echo " recomendaciones totales</p>
                <p style=\"display:inline;\">Ir a Pagina</p>
                <ul style=\"display:inline;\">
                ";
        // line 39
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable(range(1, $this->getAttribute($this->getContext($context, 'lugar'), "totalPaginas", array(), "any", false)));
        foreach ($context['_seq'] as $context['_key'] => $context['i']) {
            // line 40
            echo "                    ";
            if ((($this->getContext($context, 'i') == 1) && ($this->getAttribute($this->getContext($context, 'lugar'), "paginaActual", array(), "any", false) != 1))) {
                // line 41
                echo "                        <li><a href=\"";
                echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_lugar", array("slug" => $this->getAttribute($this->getContext($context, 'lugar'), "slug", array(), "any", false), "pagina" => ($this->getAttribute($this->getContext($context, 'lugar'), "paginaActual", array(), "any", false) - 1), "orden" => $this->getAttribute($this->getContext($context, 'lugar'), "orden", array(), "any", false))), "html");
                echo "\">«</a></li>
                    ";
            }
            // line 43
            echo "
                    ";
            // line 44
            if (($this->getAttribute($this->getContext($context, 'lugar'), "paginaActual", array(), "any", false) == $this->getContext($context, 'i'))) {
                // line 45
                echo "                        <li><strong>";
                echo twig_escape_filter($this->env, $this->getContext($context, 'i'), "html");
                echo "</strong></li>
                    ";
            } else {
                // line 47
                echo "                        <li><a href=\"";
                echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_lugar", array("slug" => $this->getAttribute($this->getContext($context, 'lugar'), "slug", array(), "any", false), "pagina" => $this->getContext($context, 'i'), "orden" => $this->getAttribute($this->getContext($context, 'lugar'), "orden", array(), "any", false))), "html");
                echo "\">";
                echo twig_escape_filter($this->env, $this->getContext($context, 'i'), "html");
                echo "</a></li>
                    ";
            }
            // line 49
            echo "
                    ";
            // line 50
            if ((($this->getContext($context, 'i') == $this->getAttribute($this->getContext($context, 'lugar'), "totalPaginas", array(), "any", false)) && ($this->getAttribute($this->getContext($context, 'lugar'), "paginaActual", array(), "any", false) != $this->getAttribute($this->getContext($context, 'lugar'), "totalPaginas", array(), "any", false)))) {
                // line 51
                echo "                        <li><a href=\"";
                echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_lugar", array("slug" => $this->getAttribute($this->getContext($context, 'lugar'), "slug", array(), "any", false), "pagina" => ($this->getAttribute($this->getContext($context, 'lugar'), "paginaActual", array(), "any", false) + 1), "orden" => $this->getAttribute($this->getContext($context, 'lugar'), "orden", array(), "any", false))), "html");
                echo "\">»</a></li>
                    ";
            }
            // line 53
            echo "                ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['i'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
        // line 54
        echo "                </ul>
                <br/><br/>
                <ul style=\"display: inline\">
                    <li>
                        ";
        // line 58
        if (($this->getAttribute($this->getContext($context, 'lugar'), "orden", array(), "any", false) == "mejor-evaluadas")) {
            // line 59
            echo "                            <strong>Mejores Evaluaciones</strong>
                        ";
        } else {
            // line 61
            echo "                            <a href=\"";
            echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_lugar", array("slug" => $this->getAttribute($this->getContext($context, 'lugar'), "slug", array(), "any", false), "pagina" => "1", "orden" => "mejor-evaluadas")), "html");
            echo "\">Mejores Evaluaciones</a>
                        ";
        }
        // line 63
        echo "                    </li>
                    <li>|</li>
                    <li>
                        ";
        // line 66
        if (($this->getAttribute($this->getContext($context, 'lugar'), "orden", array(), "any", false) == "ultimas")) {
            // line 67
            echo "                            <strong>Ultimas Recomendaciones</strong>
                        ";
        } else {
            // line 69
            echo "                            <a href=\"";
            echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_lugar", array("slug" => $this->getAttribute($this->getContext($context, 'lugar'), "slug", array(), "any", false), "pagina" => "1", "orden" => "ultimas")), "html");
            echo "\">Ultimas Recomendaciones</a>
                        ";
        }
        // line 71
        echo "                    </li>
                    <li>|</li>
                    <li>
                        ";
        // line 74
        if (($this->getAttribute($this->getContext($context, 'lugar'), "orden", array(), "any", false) == "mas-utiles")) {
            // line 75
            echo "                            <strong>Mas Utiles</strong>
                        ";
        } else {
            // line 77
            echo "                            <a href=\"";
            echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_lugar", array("slug" => $this->getAttribute($this->getContext($context, 'lugar'), "slug", array(), "any", false), "pagina" => "1", "orden" => "mas-utiles")), "html");
            echo "\">Mas Utiles</a>
                        ";
        }
        // line 79
        echo "                    </li>
                </ul>
                <p>
                    Resultados por Paginas:
                    <select>
                        <option>10</option>
                        <option>15</option>
                        <option>20</option>
                    </select>
                ";
        // line 88
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute($this->getContext($context, 'lugar'), "recomendaciones", array(), "any", false));
        foreach ($context['_seq'] as $context['_key'] => $context['recomendacion']) {
            // line 89
            echo "                <div class=\"recomendacion\">
                    <p><strong>Nombre:</strong> ";
            // line 90
            echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'recomendacion'), "nombre", array(), "any", false), "html");
            echo "</p>
                    <p><strong>Recomendacion: </strong> ";
            // line 91
            echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'recomendacion'), "texto", array(), "any", false), "html");
            echo "
                    <p><strong>Fecha:</strong> ";
            // line 92
            echo twig_escape_filter($this->env, twig_date_format_filter($this->getAttribute($this->getContext($context, 'recomendacion'), "fechaCreacion", array(), "any", false), "F jS \\a\\t g:ia"), "html");
            echo "</p>
                    <p><strong>Estrellas:</strong> ";
            // line 93
            echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'recomendacion'), "estrellas", array(), "any", false), "html");
            echo "</p>
                    <p><strong>Util:</strong> ";
            // line 94
            echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'recomendacion'), "util", array(), "any", false), "html");
            echo "</p>
                </div>
                ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['recomendacion'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
        // line 97
        echo "            </div>
    </div>
</div>

";
    }

    public function getTemplateName()
    {
        return "LoogaresLugarBundle:Default:lugar.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }
}
