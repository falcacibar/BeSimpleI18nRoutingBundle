<?php

/* LoogaresUsuarioBundle:Default:index.html.twig */
class __TwigTemplate_0ffd541abb9b93699bc9261bff06de13 extends Twig_Template
{
    protected function doDisplay(array $context, array $blocks = array())
    {
        $context = array_merge($this->env->getGlobals(), $context);

        // line 1
        echo "Hello ";
        echo twig_escape_filter($this->env, $this->getContext($context, 'name'), "html");
        echo "!
";
    }

    public function getTemplateName()
    {
        return "LoogaresUsuarioBundle:Default:index.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }
}
