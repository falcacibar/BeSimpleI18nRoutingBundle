<?php

/* LoogaresExtraBundle:Default:index.html.twig */
class __TwigTemplate_b03be73f4477af695b66c46e68a27224 extends Twig_Template
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
        return "LoogaresExtraBundle:Default:index.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }
}
