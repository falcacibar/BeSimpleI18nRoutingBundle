<?php

/* LoogaresLugarBundle:Default:agregar.html.twig */
class __TwigTemplate_711aa3d70b6256d7d57fa204a6601515 extends Twig_Template
{
    protected function doDisplay(array $context, array $blocks = array())
    {
        $context = array_merge($this->env->getGlobals(), $context);

        // line 1
        echo "Agregamos ";
        echo twig_escape_filter($this->env, $this->getContext($context, 'lugar'), "html");
        echo " a nuestra DB!";
    }

    public function getTemplateName()
    {
        return "LoogaresLugarBundle:Default:agregar.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }
}
