<?php

/* LoogaresLugarBundle:Default:index.html.twig */
class __TwigTemplate_ae93f85588170ff1bdce7ee8ec9b0877 extends Twig_Template
{
    protected function doDisplay(array $context, array $blocks = array())
    {
        $context = array_merge($this->env->getGlobals(), $context);

        // line 1
        echo "Helloasd ";
        echo twig_escape_filter($this->env, $this->getContext($context, 'name'), "html");
        echo "!

From LugarBundle∫";
    }

    public function getTemplateName()
    {
        return "LoogaresLugarBundle:Default:index.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }
}
