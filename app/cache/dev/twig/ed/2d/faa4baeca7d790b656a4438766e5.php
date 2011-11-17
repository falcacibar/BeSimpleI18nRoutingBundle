<?php

/* LoogaresLugarBundle:Default:lugar.html.twig */
class __TwigTemplate_ed2dfaa4baeca7d790b656a4438766e5 extends Twig_Template
{
    protected function doDisplay(array $context, array $blocks = array())
    {
        $context = array_merge($this->env->getGlobals(), $context);

        // line 1
        echo "<style>
    h1{ font-size: 24px; }
</style>

<!--
header
    Nombre
    Foto
    Estrellas
    Recomendaciones
    Rcomeinda boton
    Direccion, ciudad
    url
    facebook
    categorias
    sector
-->

<h1>Lugar: ";
        // line 19
        echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'lugar'), "nombre", array(), "any", false), "html");
        echo "</h1>
<p>";
        // line 20
        echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'lugar'), "estrellas", array(), "any", false), "html");
        echo " estrellas</p>
<p>";
        // line 21
        echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'lugar'), "totalRecomendaciones", array(), "any", false), "html");
        echo " recomendaciones</p>
<h2>";
        // line 22
        echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'lugar'), "calle", array(), "any", false), "html");
        echo " ";
        echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'lugar'), "numero", array(), "any", false), "html");
        echo " - Ciudad</h2>
<p>";
        // line 23
        echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'lugar'), "sitioWeb", array(), "any", false), "html");
        echo "</p>
<p>";
        // line 24
        echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'lugar'), "facebook", array(), "any", false), "html");
        echo "</p>
<p>";
        // line 25
        echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'lugar'), "twitter", array(), "any", false), "html");
        echo "</p>
<p>";
        // line 26
        echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'lugar'), "mail", array(), "any", false), "html");
        echo "</p>
<p>categorias</p>
<p>sector</p>";
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
