<?php

/* LoogaresLugarBundle:Default:listado.html.twig */
class __TwigTemplate_bc9a2dea1d3d425e7246b6f0e519f391 extends Twig_Template
{
    protected function doDisplay(array $context, array $blocks = array())
    {
        $context = array_merge($this->env->getGlobals(), $context);

        // line 1
        echo ":D
<ul>
";
        // line 3
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable($this->getContext($context, 'lugares'));
        foreach ($context['_seq'] as $context['_key'] => $context['lugar']) {
            // line 4
            echo "<li><a href=\"lugar/";
            echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'lugar'), "slug", array(), "any", false), "html");
            echo "\">";
            echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, 'lugar'), "nombre", array(), "any", false), "html");
            echo "</a></li>
";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['lugar'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
        // line 6
        echo "</ul>";
    }

    public function getTemplateName()
    {
        return "LoogaresLugarBundle:Default:listado.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }
}
