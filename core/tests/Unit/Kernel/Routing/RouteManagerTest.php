<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Kernel\Routing;

use Ox\Core\Kernel\Exception\RouteException;
use Ox\Core\Kernel\Routing\RouteManager;
use Ox\Tests\OxUnitTestCase;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * TUs for the generation of the all_routes file and the manipulation of routes
 */
class RouteManagerTest extends OxUnitTestCase
{
    public function setUp(): void
    {
        $manager = new RouteManager();
        if (!file_exists($manager->getAllRoutesPath())) {
            $manager->loadAllRoutes()->buildAllRoutes();
        }
    }

    /**
     *
     */
    public function testConstruct()
    {
        $manager = new RouteManager();
        $this->assertInstanceOf(RouteManager::class, $manager);
        $this->assertFileExists($manager->getRoot());
        $this->assertInstanceOf(RouteCollection::class, $manager->getRouteCollection());
    }

    /**
     * @throws RouteException
     */
    public function testloadAllRoutes()
    {
        $manager = new RouteManager();
        $routes  = $manager->loadAllRoutes(false)->getRouteCollection();
        $this->assertInstanceOf(RouteCollection::class, $routes);
        $this->assertNotEmpty($routes);
    }

    /**
     * @throws RouteException
     * @group schedules
     */
    public function testloadAllRoutesGlob()
    {
        $manager = new RouteManager();
        $routes  = $manager->loadAllRoutes(true)->getRouteCollection();
        $this->assertInstanceOf(RouteCollection::class, $routes);
        $this->assertNotEmpty($routes);
    }

    /**
     * @param RouteManager $manager
     *
     */
    public function testFilterRoutes()
    {
        $manager = new RouteManager();
        $manager->loadAllRoutes(false);
        $count_all = count($manager->getRouteCollection());
        $count_gui = count($manager->filterRoutesCollectionByPrefix('gui'));
        $count_api = count($manager->filterRoutesCollectionByPrefix('api'));
        $this->assertEquals($count_all, $count_gui + $count_api);
    }

    /**
     * @throws RouteException
     */
    public function testBuildAllRoutes()
    {
        $manager = new RouteManager();
        $manager->loadAllRoutes(true);
        $msg = $manager->buildAllRoutes();
        $this->assertStringStartsWith('Generated routing file in', $msg);

        return $manager;
    }

    /**
     *
     */
    public function testGetRessources()
    {
        $manager    = new RouteManager();
        $ressources = $manager->getRessources();
        $this->assertNotEmpty($ressources);
    }

    public function testConvertRouteToArray()
    {
        $manager = new RouteManager();
        $route   = new Route('/api/lorem/ipsum', ['permission' => 'read', [], ['toto' => 'tata']]);
        $array   = $manager->convertRouteToArray('lorem_ipsum', $route);
        $this->assertArrayHasKey('defaults', $array['lorem_ipsum']);
        $this->assertArrayNotHasKey('options', $array['lorem_ipsum']);
    }

    /**
     * @param string $message the message excetpion
     * @param Route  $route   the route to check
     *
     * @throws \ReflectionException
     * @dataProvider routes
     */
    public function testCheckRoute($message, Route $route)
    {
        $manager         = new RouteManager();
        $reflexion_class = new ReflectionClass($manager);

        $property_routes_name = $reflexion_class->getProperty('routes_name');
        $property_routes_name->setAccessible(true);

        $property_routes_prefix = $reflexion_class->getProperty('routes_prefix');
        $property_routes_prefix->setAccessible(true);

        // specific case
        switch ($route->_name) {
            case 'duplicate_name':
                $property_routes_name->setValue($manager, ['duplicate_name']);
                break;
            case 'duplicate_prefix':
                $property_routes_prefix->setValue($manager, ['dirname' => 'duplicate']);
                break;
            default:
                // reset previous pass
                $property_routes_name->setValue($manager, []);
                $property_routes_prefix->setValue($manager, []);
                break;
        }

        $method = new ReflectionMethod(RouteManager::class, 'checkRoute');
        $method->setAccessible(true);
        try {
            $retour = $method->invoke($manager, $route->_name, $route);
            $this->assertTrue($retour);
        } catch (RouteException $exception) {
            // var_dump($exception->getMessage());
            $this->assertStringStartsWith($message, $exception->getMessage());
        }
    }


    /**
     * @return array
     */
    public function routes()
    {
        $path   = '/api/test/unit';
        $routes = [];


        $route        = new Route($path);
        $route->_name = "duplicate_name";
        $routes[]     = [
            "[$route->_name] Duplicate route name",
            $route,
        ];


        $route        = new Route($path);
        $route->_name = "php";
        $routes[]     = [
            "[$route->_name] Invalid route name, missing prefix",
            $route,
        ];


        $route        = new Route($path);
        $route->_name = "duplicate_prefix";
        $route->_dir  = "dirname_2";
        $routes[]     = [
            "[$route->_name] Duplicate route prefix",
            $route,
        ];


        $route        = new Route($path);
        $route->_name = "php_unit";
        $route->_dir  = "dirname";
        $routes[]     = [
            "[$route->_name] Empty methods",
            $route,
        ];


        $route        = new Route($path);
        $route->_name = "php_unit";
        $route->_dir  = "dirname";
        $route->setMethods('TOTO');
        $routes[] = [
            "[$route->_name] Invalid method TOTO",
            $route,
        ];

        return $routes;
    }

    public function testGetRouteByName()
    {
        $manager = new RouteManager();
        $manager->loadAllRoutes(false);
        $route = $manager->getRouteByName('admin_identicate');
        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals('/api/identicate', $route->getPath());
    }

    public function testGetRouteByPath()
    {
        $manager = new RouteManager();
        $manager->loadAllRoutes(false);
        $routes = $manager->getRoutesByPath('/api/identicate');
        $this->assertInstanceOf(Route::class, $routes['admin_identicate']);
    }

    public function testGetAllRoutesPath()
    {
        $manager = new RouteManager();
        $path    = $manager->getAllRoutesPath();
        $this->assertFileExists($path);
    }

    public function testCreateRouteFromRequest()
    {
        $manager = new RouteManager();
        $args    = [
            'path'           => null,
            'req_names'      => [],
            'route_name'     => 'lorem_ipsum',
            'controller'     => CComposerTest::class,
            'methods'        => [],
            'openapi'        => [],
            'accept'         => [],
            'description'    => [],
            'param_names'    => [],
            'content_type'   => [],
            'body_required'  => [],
            'response_names' => [],
            'security'       => null,
            'permission'     => null,
            'bulk'           => null,
        ];
        $route   = $manager->createRouteFromRequest($args);
        $this->assertInstanceOf(Route::class, $route);
    }
}
