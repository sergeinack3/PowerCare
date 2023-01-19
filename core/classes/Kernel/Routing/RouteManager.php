<?php

/**
 * @package Mediboard\Core\Kernel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Kernel\Routing;

use Exception;
use Ox\Components\OASGenerator\Generator;
use Ox\Components\OASGenerator\Specifications;
use Ox\Core\CClassMap;
use Ox\Core\CController;
use Ox\Core\CMbException;
use Ox\Core\Kernel\Exception\ControllerException;
use Ox\Core\Kernel\Exception\RouteException;
use Ox\Core\Sessions\CSessionManager;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class CRouteManager
 */
class RouteManager
{
    /** @var array */
    public const ALLOWED_METHODS = [
        'GET',
        'POST',
        'PUT',
        'DELETE',
        'PATCH',
        'HEAD',
    ];

    /** @var array */
    public const ALLOWED_OPTIONS = [
        'openapi',
        'accept',
        'description',
        'parameters',
        'responses',
        'body',
        'dirname',
    ];

    /** @var array */
    public const ALLOWED_DEFAULTS = [
        'public',
        'permission',
        '_controller',
        '_route',
        'object_class',
    ];


    /** @var array */
    public const ALLOWED_OPTIONS_BODY = [
        'required',
        'content-type',
    ];

    /** @var array */
    public const ALLOWED_PERMISSIONS = [
        'read',
        'edit',
        'admin',
        'none',
    ];

    public const ALLOWED_PATH_PREFIX = [
        'api/',
        'gui/',
    ];

    public const DEFAULT_RESPONSE = 'default';

    public const ROUTES_DIR = 'config/routes';

    public const ROUTES_FILE_DIR = '/modules/*/config/routes/*.yml';

    /** @var RouteCollection */
    private $route_collection;

    /** @var array */
    private $routes_name = [];

    /** @var array */
    private $routes_prefix = [];

    /** @var array */
    private $routes_path_methods = [];

    /** @var array */
    private $modules_controller_unicity = [];

    /** @var string $all_routes_path */
    private $all_routes_path;

    /** @var string $root */
    private $root;

    /**
     * RouteManager constructor.
     */
    public function __construct()
    {
        $this->root             = dirname(__DIR__, 4);
        $this->all_routes_path  = $this->root . DIRECTORY_SEPARATOR . 'includes'
            . DIRECTORY_SEPARATOR . 'all_routes.yml';
        $this->route_collection = new RouteCollection();
    }

    /**
     * @return string
     */
    public function getRoot(): string
    {
        return $this->root;
    }

    /**
     * @return array|RouteCollection|null
     */
    public function getRouteCollection()
    {
        return $this->route_collection;
    }

    /**
     * @return string path to all_routes.yml file
     */
    public function getAllRoutesPath(): string
    {
        return $this->all_routes_path;
    }

    /**
     * @return $this
     * @throws RouteException
     * @throws Exception
     */
    public function loadAllRoutes(bool $glob = true): RouteManager
    {
        if ($glob) {
            foreach ($this->getRessources() as $_file) {
                $pathinfo    = pathinfo($_file);
                $fileLocator = new FileLocator($pathinfo['dirname']);
                $loader      = new YamlFileLoader($fileLocator);
                try {
                    $new_collection = $loader->load($pathinfo['basename']);
                    $new_collection->addOptions(['dirname' => $pathinfo['dirname']]);
                    $this->route_collection->addCollection($new_collection);
                } catch (Exception $e) {
                    throw new RouteException($e->getMessage() . ' in ' . $_file);
                }
            }
        } else {
            if (!file_exists($this->getAllRoutesPath())) {
                throw new Exception('File ' . $this->getAllRoutesPath() . ' is not exists.');
            }

            $pathinfo    = pathinfo($this->getAllRoutesPath());
            $fileLocator = new FileLocator($pathinfo['dirname']);
            $loader      = new YamlFileLoader($fileLocator);
            $collection  = $loader->load($pathinfo['basename']);
            $this->route_collection->addCollection($collection);
        }

        return $this;
    }


    /**
     * @param string               $prefix
     * @param RouteCollection|null $collection
     *
     * @return RouteCollection
     */
    public function filterRoutesCollectionByPrefix(string $prefix, RouteCollection $collection = null): RouteCollection
    {
        $collection_filtered = new RouteCollection();
        $collection          = $collection ?? $this->route_collection;
        $prefix              = $prefix[0] === '/' ? $prefix : '/' . $prefix;

        /**@var Link $_route */
        foreach ($collection as $_name => $_route) {
            if (strpos($_route->getPath(), $prefix) !== 0) {
                continue;
            }
            $collection_filtered->add($_name, $_route);
        }

        return $collection_filtered;
    }


    /**
     * @param string $route_name
     *
     * @return Link
     * @throws RouteException
     */
    public function getRouteByName(string $route_name): Route
    {
        $route = $this->route_collection->get($route_name);
        if ($route === null) {
            throw new RouteException('[%s] Invalid route name', $route_name);
        }

        return $route;
    }

    /**
     * @param string $route_path
     *
     * @return array|RouteCollection
     */
    public function getRoutesByPath(string $route_path)
    {
        /**@var Link $_route */
        $routes = [];
        foreach ($this->route_collection as $_name => $_route) {
            if ($_route->getPath() === $route_path) {
                $routes[$_name] = $_route;
            }
        }

        return $routes;
    }

    /**
     * @return string
     * @throws RouteException
     */
    public function buildAllRoutes(): string
    {
        $time_start = microtime(true);

        $file = $this->all_routes_path;
        if (file_exists($file) && is_file($file)) {
            unlink($file);
        }

        // Init
        $content             = null;
        $this->routes_name   = [];
        $this->routes_prefix = [];


        // Check route collection validity
        foreach ($this->route_collection as $route_name => &$route) {
            $this->checkRoute($route_name, $route);
        }

        // Create all_routes.yml
        $yml     = new Yaml();
        $content = [];
        foreach ($this->route_collection as $route_name => $route) {
            $content[] = $yml->dump($this->convertRouteToArray($route_name, $route));
        }

        // Store
        file_put_contents($file, implode(PHP_EOL, $content));

        $time         = round(microtime(true) - $time_start, 3);
        $count_routes = $this->route_collection->count();

        return "Generated routing file in {$file} containing {$count_routes} routes during {$time} sec";
    }

    /**
     * @return array|string routes yml files
     */
    public function getRessources()
    {
        return glob($this->root . self::ROUTES_FILE_DIR, defined('GLOB_BRACE') ? GLOB_BRACE : 0) ?: [];
    }


    /**
     * @param Link $route
     * @param bool $check_controller
     *
     * @return bool
     * @throws RouteException
     * @throws ControllerException
     */
    public function checkRoute(string $route_name, Route $route, bool $check_controller = true): bool
    {
        //// init
        $route_dir = $route->getOption('dirname') ?? "";
        $route_dir = str_replace(self::ROUTES_DIR, '', $route_dir);

        if (strpos(PHP_OS, 'WIN') !== false) {
            // window compat
            $route_dir = str_replace('/', '\\', $route_dir);
        }

        //// Name
        if (in_array($route_name, $this->routes_name, true)) {
            throw new RouteException('[%s] Duplicate route name', $route_name);
        }
        $this->routes_name[] = $route_name;

        //// Prefix
        $routes_segments = explode('_', $route_name);
        if (count($routes_segments) <= 1) {
            throw new RouteException('[%s] Invalid route name, missing prefix', $route_name);
        }
        $_prefix = $routes_segments[0];

        if (!isset($this->routes_prefix[$route_dir])) {
            // first route in collection/file
            if (in_array($_prefix, array_values($this->routes_prefix), true)) {
                throw new RouteException('[%s] Duplicate route prefix %s', $route_name, $_prefix);
            }
            $this->routes_prefix[$route_dir] = $_prefix;
        } else {
            $previous_prefix = $this->routes_prefix[$route_dir];
            if ($previous_prefix !== $_prefix) {
                throw new RouteException(
                    '[%s] Invalid prefix name %s not equals %s',
                    $route_name,
                    $previous_prefix,
                    $_prefix
                );
            }
        }

        //// Methods
        $http_methods = $route->getMethods();
        if (empty($http_methods)) {
            throw new RouteException('[%s] Empty methods', $route_name);
        }
        foreach ($http_methods as $http_method) {
            if (!in_array($http_method, self::ALLOWED_METHODS, true)) {
                $allowed_methods = implode(', ', self::ALLOWED_METHODS);
                throw new RouteException(
                    '[%s] Invalid method %s, expected one of: %s',
                    $route_name,
                    $http_method,
                    $allowed_methods
                );
            }

            // duplicity route path + http method
            $path_http_method = $route->getPath() . '_' . $http_method;
            if (in_array($path_http_method, $this->routes_path_methods, true)) {
                throw new RouteException(
                    '[%s] Duplicate route method %s for path %s ',
                    $route_name,
                    $http_method,
                    $route->getPath()
                );
            }
            $this->routes_path_methods[] = $path_http_method;
        }

        if ($check_controller) {
            //// Controller
            $default_controller = $route->getDefault('_controller');
            if ($default_controller === null || strpos($default_controller, '::') === false) {
                throw new RouteException('[%s] Invalid default controller %s', $route_name, $default_controller);
            }
            [$controller, $method] = explode('::', $route->getDefault('_controller'));
            if (!class_exists($controller)) {
                throw new RouteException('[%s] Invalid controller class %s', $route_name, $controller);
            }

            try {
                /** @var CController $instance */
                $instance = new $controller();
            } catch (Exception $e) {
                throw new RouteException('[%s] Invalid controller instance %s', $route_name, $controller);
            }

            if (!is_subclass_of($instance, CController::class)) {
                throw new RouteException('[%s] Invalid controller subclass %s', $route_name, $controller);
            }

            if (!$route || strpos($instance->getReflectionClass()->getFileName(), $route_dir) !== 0) {
                throw new RouteException(
                    '[%s] Controller path does not match route directory %s',
                    $route_name,
                    $controller
                );
            }

            if (!method_exists($instance, $method)) {
                throw new RouteException('[%s] Invalid controller method %s::%s', $route_name, $controller, $method);
            }

            // Module controller unicity (access_log)
            $key_unicity = implode(
                '-',
                [
                    $instance->getModuleName(),
                    CClassMap::getSN($controller),
                    $method,
                ]
            );
            if (
                array_key_exists($key_unicity, $this->modules_controller_unicity)
                && $this->modules_controller_unicity[$key_unicity] !== $controller
            ) {
                throw new RouteException(
                    '[%s] Invalid controller unicity constraint %s',
                    $route_name,
                    $key_unicity
                );
            }
            $this->modules_controller_unicity[$key_unicity] = $controller;
            unset($key_unicity);

            // Api security
            if (strpos($route->getPath(), 'api') === 1) {
                $reflection_method = $instance->getReflectionMethod($method);
                $doc_comment       = $reflection_method->getDocComment();
                $pattern           = $route->getDefault('public') === true ? '/@api public/i' : '/@api\s(?!public)/i';
                if ($doc_comment === false || preg_match($pattern, $doc_comment) !== 1) {
                    $doc_expected = $route->getDefault('public') === true ? '@api public' : '@api';
                    throw new RouteException(
                        '[%s] Invalid controller doc comments %s::%s, does not match %s',
                        $route_name,
                        $controller,
                        $method,
                        $doc_expected
                    );
                }
            }
        }

        //// Path
        $path       = $route->getPath();
        $valid_path = false;
        foreach (self::ALLOWED_PATH_PREFIX as $prefix) {
            if (strpos($path, $prefix) === 1) {
                $valid_path = true;
                break;
            }
        }
        if (!$valid_path) {
            throw new RouteException(
                '[%s] Path must start by %s',
                $route_name,
                implode(',', self::ALLOWED_PATH_PREFIX)
            );
        }


        //// Defaults
        $defaults = $route->getDefaults();
        if (!array_key_exists('permission', $defaults)) {
            throw new RouteException('[%s] Missing mandatory defaults key %s', $route_name, 'permission');
        }

        $route_is_public = false;

        foreach ($defaults as $default_name => $default_value) {
            // available default
            if (!in_array($default_name, self::ALLOWED_DEFAULTS, true)) {
                $allowed = implode(', ', self::ALLOWED_DEFAULTS);
                throw new RouteException(
                    '[%s] Invalid default %s, expected one of: %s',
                    $route_name,
                    $default_name,
                    $allowed
                );
            }

            // security (= authentication)
            if ($default_name === 'public') {
                if ($default_value !== true) {
                    throw new RouteException(
                        '[%s] Invalid defaults public %s, expected true or no property',
                        $route_name,
                        gettype($default_value)
                    );
                }

                $route_is_public = true;
            }

            // permission
            if ($default_name === 'permission') {
                if (!is_string($default_value)) {
                    throw new RouteException(
                        '[%s] Invalid defaults permission %s, expected string',
                        $route_name,
                        gettype($default_value)
                    );
                }

                if ($route_is_public && $default_value !== 'none') {
                    throw new RouteException(
                        '[%s] Invalid permission %s, public route expects \'none\'',
                        $route_name,
                        $default_value
                    );
                } else {
                    $allowed_permission = static::ALLOWED_PERMISSIONS;
                    if (!in_array($default_value, $allowed_permission, true)) {
                        throw new RouteException(
                            '[%s] Invalid allowed option permission %s, expected one of: %s',
                            $route_name,
                            $default_value,
                            implode(', ', $allowed_permission)
                        );
                    }
                }
            }
        }

        //// Options
        foreach ($route->getOptions() as $option_name => $option_value) {
            // ignore compiler_class
            if ($option_name === 'compiler_class') {
                continue;
            }

            // available options
            if (!in_array($option_name, self::ALLOWED_OPTIONS, true)) {
                $allowed_options = implode(', ', self::ALLOWED_OPTIONS);
                throw new RouteException(
                    '[%s] Invalid option %s, expected one of: %s',
                    $route_name,
                    $option_name,
                    $allowed_options
                );
            }

            // description
            if (($option_name === 'description') && !is_string($option_value)) {
                throw new RouteException(
                    '[%s] Invalid option desciption %s, expected string',
                    $route_name,
                    gettype($option_value)
                );
            }

            // openapi
            if (($option_name === 'openapi') && !is_bool($option_value)) {
                throw new RouteException(
                    '[%s] Invalid option openapi %s, expected bool',
                    $route_name,
                    gettype($option_value)
                );
            }

            // content negociation
            if (($option_name === 'accept') && !is_array($option_value)) {
                throw new RouteException(
                    '[%s] Invalid option accept %s, expected array',
                    $route_name,
                    gettype($option_value)
                );
            }

            // body
            if ($option_name === 'body') {
                if (!is_array($option_value)) {
                    throw new RouteException(
                        '[%s] Invalid option body %s, expected array',
                        $route_name,
                        gettype($option_value)
                    );
                }
                foreach ($option_value as $body_option => $body_option_value) {
                    if (!in_array($body_option, self::ALLOWED_OPTIONS_BODY, true)) {
                        $allowed_body_option = implode(', ', self::ALLOWED_OPTIONS_BODY);
                        throw new RouteException(
                            '[%s] Invalid allowed option body %s, expected one of: %s',
                            $route_name,
                            $body_option,
                            $allowed_body_option
                        );
                    }

                    if ($body_option === 'required' && !is_bool($body_option_value)) {
                        throw new RouteException(
                            '[%s] Invalid option body required %s, expected bool',
                            $route_name,
                            gettype($body_option_value)
                        );
                    }

                    // defalut application/json
                    if ($body_option === 'content-type' && !is_array($body_option_value)) {
                        throw new RouteException(
                            '[%s] Invalid body option content-type %s, expected array',
                            $route_name,
                            gettype($body_option_value)
                        );
                    }
                }
            }

            // responses
            if ($option_name === 'responses') {
                if (!is_array($option_value)) {
                    throw new RouteException(
                        '[%s] Invalid responses option %s, expected array',
                        $route_name,
                        gettype($option_value)
                    );
                }
                foreach ($option_value as $_response_code => $_response_description) {
                    if (!is_int($_response_code) && $_response_code !== static::DEFAULT_RESPONSE) {
                        throw new RouteException(
                            '[%s] Invalid response code option %s, expected int',
                            $route_name,
                            gettype($_response_code)
                        );
                    }
                    if (!is_string($_response_description)) {
                        throw new RouteException(
                            '[%s] Invalid response description %s, expected string',
                            $route_name,
                            gettype($_response_description)
                        );
                    }
                }
            }
        }

        return true;
    }

    /**
     * @param array $arguments
     *
     * @return Link
     * @throws CMbException
     */
    public function createRouteFromRequest($arguments): Route
    {
        $path         = rtrim($arguments['path'] ?? "", '/');
        $requirements = [];
        foreach ($arguments['req_names'] as $_key => $_name) {
            if ($_name) {
                $path .= "/{{$_name}}";

                if (!isset($arguments['req_types'][$_key]) || !$arguments['req_types'][$_key]) {
                    throw new CMbException('Type is mandatory for requirement %s', $_name);
                }

                $requirements[$_name] = stripslashes($arguments['req_types'][$_key]);
            }
        }

        $route        = new Route($path);
        $route->_name = $arguments['route_name'];
        $route->setDefault('_controller', stripslashes($arguments['controller']));
        $route->setMethods(array_keys($arguments['methods']));
        $route->setRequirements($requirements);

        $route->setOption('openapi', (bool)$arguments['openapi']);

        if ($arguments['accept']) {
            $route->setOption('accept', array_keys($arguments['accept']));
        }

        if ($arguments['description']) {
            $route->setOption('description', $arguments['description']);
        }

        if ($arguments['param_names']) {
            $params = [];
            foreach ($arguments['param_names'] as $_key => $_name) {
                if ($_name) {
                    $params[$_name] = (isset($arguments['param_types'][$_key]) && $arguments['param_types'][$_key])
                        ? stripslashes($arguments['param_types'][$_key]) : '';
                }
            }

            if ($params) {
                $route->setOption('parameters', $params);
            }
        }

        if ($arguments['response_names']) {
            $responses = [];
            foreach ($arguments['response_names'] as $_key => $_name) {
                if ($_name) {
                    $responses[$_name]
                        = (isset($arguments['response_descs'][$_key]) && $arguments['response_descs'][$_key])
                        ? $arguments['response_descs'][$_key] : null;
                }
            }

            if ($responses) {
                $route->setOption('responses', $responses);
            }
        }

        $body = [
            'required' => (bool)$arguments['body_required'],
        ];

        if ($arguments['content_type']) {
            $body['content-type'] = array_keys($arguments['content_type']);
        }

        $route->setOption('body', $body);
        $route->setDefault('permission', $arguments['permission']);

        return $route;
    }

    /**
     * @param Link $route
     *
     * @return array[]
     */
    public function convertRouteToArray(string $route_name, Route $route): array
    {
        $data = [
            'path'     => $route->getPath(),
            'methods'  => $route->getMethods(),
            'defaults' => $route->getDefaults(), // (contains _controller public permission)
        ];

        $requirements = $route->getRequirements();
        if (!empty($requirements)) {
            $data['requirements'] = $requirements;
        }

        $condition = $route->getCondition();
        if ($condition) {
            $data['condition'] = $condition;
        }

        return [$route_name => $data];
    }

    public function convertRoutesApiToOAS()
    {
        $route_collection = $this->filterRoutesCollectionByPrefix('api');

        // Infos
        $specifications = new Specifications();
        $specifications->setTitle("OX APIs documentation")
            ->setVersion("1.0.1")
            ->setDescription(
                "Visualize and interact with our APIs resources.<br>Making it easy for back end implementation and client side consumption<br>Generated\n with <b>Mediboard</b> OpenApi Specifications and <b>Swagger UI</b> open source project."
            )
            ->setContact("Support", "dev@openxtrem.com")
            ->setLicense("License GPL", "https://openxtrem.com/licenses/gpl.html");

        // Tags
        $specifications->addTag('system', 'Administration system')
            ->addTag('admin', ' Permissions management')
            ->addTag('etablissement', 'Groups management')
            ->addTag('outils', 'Developpement tools');

        // Security
        try {
            $session_name = CSessionManager::forgeSessionName(basename($this->root));
        } catch (Exception $exception) {
            $session_name = 'PHPSESSID';
        }

        $specifications->addSecurity(
            'Basic',
            'http',
            'Basic authentication is a simple authentication scheme built into the HTTP protocol.',
            ['scheme' => 'basic']
        )
            ->addSecurity(
                'Token',
                'apiKey',
                'Token authentication is an HTTP authentication scheme that involves security tokens.',
                ['in' => 'header', 'name' => 'X-OXAPI-KEY']
            )
            ->addSecurity(
                'Session',
                'apiKey',
                'Cookie authentication uses HTTP cookies to authenticate client requests and maintain session information.',
                ['in' => 'cookie', 'name' => $session_name]
            )
            ->addSecurity(
                'OAuth',
                'oauth2',
                'OAuth 2.0 is an authorization protocol that gives an API client limited access to user data on a web server.',
                [
                    'flows' => [
                        'clientCredentials' => [
                            'tokenUrl' => '/mediboard/api/oauth2/token',
                            'scopes'   => [
                                'read' => 'Read scope',
                            ],
                        ],
                    ],
                ]
            );

        // Server
        $config_file = $this->root . '/includes/config.php';
        if (file_exists($config_file)) {
            require_once $config_file;
            $url         = $dPconfig['external_url'] ?? 'http://localhost/mediboard/';
            $description = $dPconfig['instance_role'] ?? 'Qualif';
            $specifications->addServer($url, $description);
        }

        $generator = new Generator($specifications, $route_collection);

        return $generator->generate();
    }

    /**
     * @return mixed
     * @throws RouteException
     */
    public function getOAS()
    {
        $oas_path = $this->root . '/includes/documentation.yml';
        if (!file_exists($oas_path)) {
            throw new RouteException('Documentation is missing');
        }


        try {
            return Yaml::parseFile($oas_path);
        } catch (ParseException $e) {
            throw new RouteException('Parse error : ' . $e->getMessage());
        }
    }
}
