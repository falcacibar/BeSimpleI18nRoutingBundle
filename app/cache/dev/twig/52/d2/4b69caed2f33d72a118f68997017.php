<?php

/* ::base.html.twig */
class __TwigTemplate_52d24b69caed2f33d72a118f68997017 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = array();
        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'stylesheets' => array($this, 'block_stylesheets'),
            'content' => array($this, 'block_content'),
            'body' => array($this, 'block_body'),
            'javascripts' => array($this, 'block_javascripts'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $context = array_merge($this->env->getGlobals(), $context);

        // line 1
        echo "<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
        <title>";
        // line 5
        $this->displayBlock('title', $context, $blocks);
        echo "</title>
        ";
        // line 6
        $this->displayBlock('stylesheets', $context, $blocks);
        // line 7
        echo "        ";
        if (isset($context['assetic']['debug']) && $context['assetic']['debug']) {
            // asset "3060b16_0"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_3060b16_0") : $this->env->getExtension('assets')->getAssetUrl("_controller/assets/css/compressed_part_1_base_1.css");
            // line 10
            echo "            <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, $this->getContext($context, 'asset_url'), "html");
            echo "\" type=\"text/css\" media=\"screen\" />
        ";
            // asset "3060b16_1"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_3060b16_1") : $this->env->getExtension('assets')->getAssetUrl("_controller/assets/css/compressed_part_1_lugar_2.css");
            echo "            <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, $this->getContext($context, 'asset_url'), "html");
            echo "\" type=\"text/css\" media=\"screen\" />
        ";
        } else {
            // asset "3060b16"
            $context["asset_url"] = isset($context['assetic']['use_controller']) && $context['assetic']['use_controller'] ? $this->env->getExtension('routing')->getPath("_assetic_3060b16") : $this->env->getExtension('assets')->getAssetUrl("_controller/assets/css/compressed.css");
            echo "            <link rel=\"stylesheet\" href=\"";
            echo twig_escape_filter($this->env, $this->getContext($context, 'asset_url'), "html");
            echo "\" type=\"text/css\" media=\"screen\" />
        ";
        }
        unset($context["asset_url"]);
        // line 12
        echo "        <link rel=\"shortcut icon\" href=\"";
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("favicon.ico"), "html");
        echo "\" />
        <style>
            body{
                background: #333;
                font-family: Helvetica;
                font-size: 14px;
                text-shadow: 0 1px 0 #fff;
            }

            h1{
                padding: 0;
                margin: 0;
            }
            #wrapper{
                background: #f0f0f0;
                width: 940px;
                padding: 0;
                padding: 10px 15px;
                margin: 0 auto;
                -moz-border-radius: 5px;
                border-radius: 5px;
            }

            .recomendacion{
                background: #CCF6FF;
                padding: 5px;
                -moz-border-radius: 5px;
                border-radius: 5px;
                margin: 15px 0;
            }

            ul{
                padding: 0;
            }

            li{
                display: inline;
            }
        </style>
    </head>
    <body>
        <div id=\"wrapper\">
<!--             <div id=\"header\">
                <div id=\"logo\">
                    <h1>Loogares.com</h1>
                </div>
                <div id=\"login-facebook\">No estas conectado a Facebook, Hazlo ya! >> FACEBOOKBTN <strong>Registrate | Entra</strong></div>
                <div id=\"search-bar\">
                    que buscas? :o
                    <input type=\"text\" />
                    <input type=\"text\" />
                </div>
                <ul id=\"main-nav\">
                    <li>Santiago</li>
                    <li>Que Visitar</li>
                    <li>Donde comer</li>
                    <li>Que comprar</li>
                    <li>Como entretenerse</li>
                    <li>Como cuidarse</li>
                    <li>Donde dormir</li>
                    <li>Servicios</li>
                </ul>
            </div> -->
            ";
        // line 75
        $this->displayBlock('content', $context, $blocks);
        // line 76
        echo "            ";
        $this->displayBlock('body', $context, $blocks);
        // line 77
        echo "            ";
        $this->displayBlock('javascripts', $context, $blocks);
        // line 78
        echo "        </div>
    </body>
</html>
";
    }

    // line 5
    public function block_title($context, array $blocks = array())
    {
        echo "Welcome!";
    }

    // line 6
    public function block_stylesheets($context, array $blocks = array())
    {
    }

    // line 75
    public function block_content($context, array $blocks = array())
    {
    }

    // line 76
    public function block_body($context, array $blocks = array())
    {
    }

    // line 77
    public function block_javascripts($context, array $blocks = array())
    {
    }

    public function getTemplateName()
    {
        return "::base.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }
}
