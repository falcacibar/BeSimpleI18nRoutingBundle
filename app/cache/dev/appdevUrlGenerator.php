<?php

use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Exception\RouteNotFoundException;


/**
 * appdevUrlGenerator
 *
 * This class has been auto-generated
 * by the Symfony Routing Component.
 */
class appdevUrlGenerator extends Symfony\Component\Routing\Generator\UrlGenerator
{
    static private $declaredRouteNames = array(
       '_assetic_3060b16' => true,
       '_assetic_3060b16_0' => true,
       '_assetic_3060b16_1' => true,
       '_wdt' => true,
       '_profiler_search' => true,
       '_profiler_purge' => true,
       '_profiler_import' => true,
       '_profiler_export' => true,
       '_profiler_search_results' => true,
       '_profiler' => true,
       '_configurator_home' => true,
       '_configurator_step' => true,
       '_configurator_final' => true,
       'LoogaresExtraBundle_homepage' => true,
       'LoogaresUsuarioBundle_homepage' => true,
       'LoogaresLugarBundle_homepage' => true,
       'LoogaresLugarBundle_listado' => true,
       'LoogaresLugarBundle_lugar' => true,
    );

    /**
     * Constructor.
     */
    public function __construct(RequestContext $context)
    {
        $this->context = $context;
    }

    public function generate($name, $parameters = array(), $absolute = false)
    {
        if (!isset(self::$declaredRouteNames[$name])) {
            throw new RouteNotFoundException(sprintf('Route "%s" does not exist.', $name));
        }

        $escapedName = str_replace('.', '__', $name);

        list($variables, $defaults, $requirements, $tokens) = $this->{'get'.$escapedName.'RouteInfo'}();

        return $this->doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $absolute);
    }

    private function get_assetic_3060b16RouteInfo()
    {
        return array(array (), array (  '_controller' => 'assetic.controller:render',  'name' => '3060b16',  'pos' => NULL,  '_format' => 'css',), array (), array (  0 =>   array (    0 => 'text',    1 => '/assets/css/compressed.css',  ),));
    }

    private function get_assetic_3060b16_0RouteInfo()
    {
        return array(array (), array (  '_controller' => 'assetic.controller:render',  'name' => '3060b16',  'pos' => 0,  '_format' => 'css',), array (), array (  0 =>   array (    0 => 'text',    1 => '/assets/css/compressed_part_1_base_1.css',  ),));
    }

    private function get_assetic_3060b16_1RouteInfo()
    {
        return array(array (), array (  '_controller' => 'assetic.controller:render',  'name' => '3060b16',  'pos' => 1,  '_format' => 'css',), array (), array (  0 =>   array (    0 => 'text',    1 => '/assets/css/compressed_part_1_lugar_2.css',  ),));
    }

    private function get_wdtRouteInfo()
    {
        return array(array (  0 => 'token',), array (  '_controller' => 'Symfony\\Bundle\\WebProfilerBundle\\Controller\\ProfilerController::toolbarAction',), array (), array (  0 =>   array (    0 => 'variable',    1 => '/',    2 => '[^/]+?',    3 => 'token',  ),  1 =>   array (    0 => 'text',    1 => '/_wdt',  ),));
    }

    private function get_profiler_searchRouteInfo()
    {
        return array(array (), array (  '_controller' => 'Symfony\\Bundle\\WebProfilerBundle\\Controller\\ProfilerController::searchAction',), array (), array (  0 =>   array (    0 => 'text',    1 => '/_profiler/search',  ),));
    }

    private function get_profiler_purgeRouteInfo()
    {
        return array(array (), array (  '_controller' => 'Symfony\\Bundle\\WebProfilerBundle\\Controller\\ProfilerController::purgeAction',), array (), array (  0 =>   array (    0 => 'text',    1 => '/_profiler/purge',  ),));
    }

    private function get_profiler_importRouteInfo()
    {
        return array(array (), array (  '_controller' => 'Symfony\\Bundle\\WebProfilerBundle\\Controller\\ProfilerController::importAction',), array (), array (  0 =>   array (    0 => 'text',    1 => '/_profiler/import',  ),));
    }

    private function get_profiler_exportRouteInfo()
    {
        return array(array (  0 => 'token',), array (  '_controller' => 'Symfony\\Bundle\\WebProfilerBundle\\Controller\\ProfilerController::exportAction',), array (), array (  0 =>   array (    0 => 'text',    1 => '.txt',  ),  1 =>   array (    0 => 'variable',    1 => '/',    2 => '[^/\\.]+?',    3 => 'token',  ),  2 =>   array (    0 => 'text',    1 => '/_profiler/export',  ),));
    }

    private function get_profiler_search_resultsRouteInfo()
    {
        return array(array (  0 => 'token',), array (  '_controller' => 'Symfony\\Bundle\\WebProfilerBundle\\Controller\\ProfilerController::searchResultsAction',), array (), array (  0 =>   array (    0 => 'text',    1 => '/search/results',  ),  1 =>   array (    0 => 'variable',    1 => '/',    2 => '[^/]+?',    3 => 'token',  ),  2 =>   array (    0 => 'text',    1 => '/_profiler',  ),));
    }

    private function get_profilerRouteInfo()
    {
        return array(array (  0 => 'token',), array (  '_controller' => 'Symfony\\Bundle\\WebProfilerBundle\\Controller\\ProfilerController::panelAction',), array (), array (  0 =>   array (    0 => 'variable',    1 => '/',    2 => '[^/]+?',    3 => 'token',  ),  1 =>   array (    0 => 'text',    1 => '/_profiler',  ),));
    }

    private function get_configurator_homeRouteInfo()
    {
        return array(array (), array (  '_controller' => 'Sensio\\Bundle\\DistributionBundle\\Controller\\ConfiguratorController::checkAction',), array (), array (  0 =>   array (    0 => 'text',    1 => '/_configurator/',  ),));
    }

    private function get_configurator_stepRouteInfo()
    {
        return array(array (  0 => 'index',), array (  '_controller' => 'Sensio\\Bundle\\DistributionBundle\\Controller\\ConfiguratorController::stepAction',), array (), array (  0 =>   array (    0 => 'variable',    1 => '/',    2 => '[^/]+?',    3 => 'index',  ),  1 =>   array (    0 => 'text',    1 => '/_configurator/step',  ),));
    }

    private function get_configurator_finalRouteInfo()
    {
        return array(array (), array (  '_controller' => 'Sensio\\Bundle\\DistributionBundle\\Controller\\ConfiguratorController::finalAction',), array (), array (  0 =>   array (    0 => 'text',    1 => '/_configurator/final',  ),));
    }

    private function getLoogaresExtraBundle_homepageRouteInfo()
    {
        return array(array (  0 => 'name',), array (  '_controller' => 'Loogares\\ExtraBundle\\Controller\\DefaultController::indexAction',), array (), array (  0 =>   array (    0 => 'variable',    1 => '/',    2 => '[^/]+?',    3 => 'name',  ),  1 =>   array (    0 => 'text',    1 => '/hello',  ),));
    }

    private function getLoogaresUsuarioBundle_homepageRouteInfo()
    {
        return array(array (  0 => 'name',), array (  '_controller' => 'Loogares\\UsuarioBundle\\Controller\\DefaultController::indexAction',), array (), array (  0 =>   array (    0 => 'variable',    1 => '/',    2 => '[^/]+?',    3 => 'name',  ),  1 =>   array (    0 => 'text',    1 => '/hello',  ),));
    }

    private function getLoogaresLugarBundle_homepageRouteInfo()
    {
        return array(array (), array (  '_controller' => 'Loogares\\LugarBundle\\Controller\\LugarController::agregarAction',), array (), array (  0 =>   array (    0 => 'text',    1 => '/agregar',  ),));
    }

    private function getLoogaresLugarBundle_listadoRouteInfo()
    {
        return array(array (), array (  '_controller' => 'Loogares\\LugarBundle\\Controller\\LugarController::listadoAction',), array (), array (  0 =>   array (    0 => 'text',    1 => '/listado',  ),));
    }

    private function getLoogaresLugarBundle_lugarRouteInfo()
    {
        return array(array (  0 => 'slug',), array (  '_controller' => 'Loogares\\LugarBundle\\Controller\\LugarController::lugarAction',), array (), array (  0 =>   array (    0 => 'variable',    1 => '/',    2 => '[^/]+?',    3 => 'slug',  ),  1 =>   array (    0 => 'text',    1 => '/lugar',  ),));
    }
}
