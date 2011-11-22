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
        echo ":D";
    }

    // line 6
    public function block_content($context, array $blocks = array())
    {
        // line 7
        echo "<!-- Ficha, top part -->
<div id=\"lugar-ficha\">
    
    <div id=\"lugar-info\">
        <div id=\"lugar-foto\">foto</div>
        <div id=\"lugar-mapa\">mapa</div>
            <h2>";
        // line 13
        echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'lugar'), "nombre", array(), "any", false), "html");
        echo "</h2>
            <div class=\"estrellas\">";
        // line 14
        echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'lugar'), "estrellas", array(), "any", false), "html");
        echo " Estrellas | ";
        echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'lugar'), "totalRecomendaciones", array(), "any", false), "html");
        echo " Recomendaciones</div>
            <div id=\"lugar-direccion\">";
        // line 15
        echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'lugar'), "calle", array(), "any", false), "html");
        echo " ";
        echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'lugar'), "numero", array(), "any", false), "html");
        echo " - Ciudad</div>
            <div id=\"lugar-telefono\">123719827</div>
            <div id=\"lugar-categorias\">Categorias: ";
        // line 17
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($this->getContext($context, 'smt'), "categoria", array(), "any", false), "nombre", array(), "any", false), "html");
        echo "</div>
            <div id=\"lugar-algo\">asdads</div>
            <div id=\"lugar-algo\">asdads</div>
            <div id=\"lugar-algo\">asdads</div>
            <div id=\"primero-recomendar\"></div>
        <div id=\"share-stuff\"></div>
        <div id=\"social-stuff\"></div>
    </div>
</div>
<p> estrellas</p>
<p>";
        // line 27
        echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'lugar'), "totalRecomendaciones", array(), "any", false), "html");
        echo " recomendaciones</p>
<p>";
        // line 28
        echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'lugar'), "sitioWeb", array(), "any", false), "html");
        echo "</p>
<p>";
        // line 29
        echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'lugar'), "facebook", array(), "any", false), "html");
        echo "</p>
<p>";
        // line 30
        echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'lugar'), "twitter", array(), "any", false), "html");
        echo "</p>
<p>";
        // line 31
        echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'lugar'), "mail", array(), "any", false), "html");
        echo "</p>
<p>categorias</p>
<p>sector</p>

<h1>
";
        // line 36
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($this->getContext($context, 'smt'), "categoria", array(), "any", false), "nombre", array(), "any", false), "html");
        echo "
</h1>
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
