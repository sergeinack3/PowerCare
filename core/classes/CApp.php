<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use Exception;
use Ox\Core\Auth\Badges\LogAuthBadge;
use Ox\Core\Auth\Badges\WeakPasswordBadge;
use Ox\Core\Autoload\CAutoloadAlias;
use Ox\Core\Elastic\ElasticClient;
use Ox\Core\Handlers\Facades\HandlerManager;
use Ox\Core\Kernel\Exception\AppException;
use Ox\Core\Kernel\Exception\PublicEnvironmentException;
use Ox\Core\Kernel\Exception\UnavailableApplicationException;
use Ox\Core\Kernel\Routing\RequestHelperTrait;
use Ox\Core\Logger\LoggerLevels;
use Ox\Core\Logger\Wrapper\ApplicationLoggerWrapper;
use Ox\Core\Module\CModule;
use Ox\Core\Mutex\CMbMutex;
use Ox\Core\Profiler\BlackfireHelper;
use Ox\Core\Redis\CRedisClient;
use Ox\Core\ResourceLoaders\CHTMLResourceLoader;
use Ox\Core\Sessions\CSessionHandler;
use Ox\Core\Sessions\CSessionManager;
use Ox\Core\Version\Builder;
use Ox\Core\Version\Version;
use Ox\Mediboard\Admin\CPermModule;
use Ox\Mediboard\Admin\CPermObject;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\AccessLog\AccessLogManager;
use Ox\Mediboard\System\CConfiguration;
use Ox\Mediboard\System\CExchangeHTTPClient;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\Controllers\Legacy\CMainController;
use Ox\Mediboard\System\CSourceHTTP;
use Ox\Mediboard\System\CSourceSMTP;
use Ox\Mediboard\System\CUserAuthentication;
use Ox\Tests\TestsException;
use phpmailerException;
use ReflectionException;
use ReflectionMethod;
use SplPriorityQueue;
use Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Throwable;

/**
 * The actual application class
 * Responsibilities:
 *  - application kill
 *  - class management
 *  - file inclusion
 *  - memory and performance
 */
class CApp
{
    use RequestHelperTrait;

    // Application register shutdown
    const APP_PRIORITY = 'app';

    // Framework register shutdown
    const AUTOLOAD_PRIORITY = 'autoload';
    const EVENT_PRIORITY    = 'event';
    const MUTEX_PRIORITY    = 'mutex';
    const SESSION_PRIORITY  = 'session';
    const PEACE_PRIORITY    = 'peace';
    const ERROR_PRIORITY    = 'error';
    const CRON_PRIORITY     = 'cron';

    const FRAMEWORK_PRIORITIES = [
        self::AUTOLOAD_PRIORITY => 50,
        self::EVENT_PRIORITY    => 40,
        self::MUTEX_PRIORITY    => 30,
        self::SESSION_PRIORITY  => 20,
        self::PEACE_PRIORITY    => 10,
        self::ERROR_PRIORITY    => 0,
        self::CRON_PRIORITY     => -1,
    ];

    const MSG_OFFLINE_MAINTENANCE = "The system is disabled for maintenance.";
    const MSG_OFFLINE_DATABASE    = "The database is not accessible.";

    public const PHPUNIT_RIP_CODE = 46;

    static $inPeace    = false;
    static $encoding   = "utf-8";
    static $classPaths = [];
    static $is_robot   = false;

    /** @var callable[] A array of callbacks to be called at the end of the query */
    static $callbacks   = [];
    static $performance = [
        // Performance
        "genere"          => null,
        "memoire"         => null,
        "size"            => null,
        "objets"          => 0,
        "ip"              => null,

        // Errors
        "error"           => 0,
        "warning"         => 0,
        "notice"          => 0,

        // Cache
        "cachableCount"   => null,
        "cachableCounts"  => null,

        // Objects
        "objectCounts"    => null,

        // Function cache
        "functionCache"   => null,

        // Autoload
        "autoloadCount"   => 0,
        "autoload"        => [],

        // Data source information
        "dataSource"      => null,
        "dataSourceTime"  => null,
        "dataSourceCount" => null,
        "nosqlTime"       => 0,
        "nosqlCount"      => 0,

        // transport tiers
        "transportTiers"  => [],
    ];

    /** @var ApplicationLoggerWrapper */
    static $logger = null;
    /**
     * @var array Cloner & dumper for extracting datas in dev toolbar
     */
    static $extractors = [];

    /*
     * The order of the keys is important (only the first keys
     * are displayed in the short view of the Firebug console).
     */
    /**
     * @var Chronometer Main application chronometer
     */
    static $chrono;
    /** @var bool Is application in readonly mode ? */
    static $readonly;
    /** @var int Useful to log extra bandwidth use such as FTP transfers and so on */
    static $extra_bandwidth = 0;
    /** @var SplPriorityQueue A queue of callbacks to be called on shutdown */
    static private $shutdown_callbacks = [];
    /** @var string Current request unique identifier */
    private static $requestUID = null;
    /** @var Version */
    private static $version;
    /** @var bool */
    private static $turn_off_fetch = false;
    /** @var int */
    private static $rip_error_code = 0;
    /** @var self */
    private static $instance;
    /** @var bool */
    private static $elastic_log;
    /**
     * @var string
     */
    private $root_dir;
    /**
     * @var array
     */
    private $config = [];
    /** @var bool */
    private $is_started = false;
    /** @var bool */
    private $is_stoped = false;
    /** @var bool Is the current Request from a public context|environment? */
    private $is_public = false;

    /** @var bool */
    private static $in_rip = false;

    /**
     * CApp constructor
     */
    private function __construct()
    {
        $this->root_dir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR;
    }

    /**
     * @return bool
     */
    public static function getTurnOffFetch()
    {
        return self::$turn_off_fetch;
    }

    /**
     * Will trigger an error for logging purpose whenever the application dies unexpectedly
     *
     * @return void
     */
    static function checkPeace()
    {
        if (!self::$inPeace) {
            if (!headers_sent()) {
                header("HTTP/1.1 500 Application died unexpectedly");
            }

            trigger_error("Application died unexpectedly", E_USER_ERROR);
        }
    }

    /**
     * Make application die properly
     *
     * @param bool $reset_msg Reset the message stack
     *
     * @return void
     */
    public static function rip($reset_msg = true)
    {
        // If rip has already been called and a callback call CApp::rip we don't want to make an infite loop
        if (static::$in_rip === true) {
            throw new Exception(CAppUI::tr('CApp-Error-Rip-as-already-been-called'));
        }

        static::$in_rip = true;

        // If the client doesn't support cookies, we destroy its session
        // Sometimes, the cookie is empty while the client support cookies (url auth in IE)
        /*if (empty($_COOKIE)) {
          CSessionHandler::end();
        }*/

        // Empty the message stack from remaining messages
        CAppUI::getMsg($reset_msg);

        CView::disableSlave();

        if (static::$performance['genere'] === null && self::$chrono) {
            if (self::$chrono->step > 0) {
                self::$chrono->stop();
            }

            CApp::preparePerformance();
        }

        // Prepare json datas for devtools extension
        if (CDevtools::isActive()) {
            CDevtools::makeTmpFile();
        }

        // Access log
        (AccessLogManager::createFromGlobals())->log();

        // Long request log
        include __DIR__ . "/../../includes/long_request_log.php";

        if (CApp::isSessionRestricted()) {
            CSessionHandler::end(true);
        } else {
            // Explicit close of the session before object destruction
            CSessionHandler::writeClose();
        }

        // Call the callback function, after giving back hand to the user
        if (count(CApp::$callbacks)) {
            if (!headers_sent()) {
                $size = ob_get_length();

                if ($size > 0) {
                    header("Connection: close");
                    header("Content-Length: $size");

                    // Strange behaviour, will not work unless both are called !
                    ob_end_flush();
                    flush();
                }
            }

            static::triggerCallbacksFunc();
        }

        self::$inPeace = true;
        die(self::$rip_error_code);
    }

    /**
     * Prepare performance data to be displayed
     *
     * @return void
     */
    static function preparePerformance()
    {
        arsort(CStoredObject::$cachableCounts);
        arsort(CStoredObject::$objectCounts);
        arsort(self::$performance["autoload"]);

        self::$performance["genere"]         = round(self::$chrono->total, 3);
        self::$performance["memoire"]        = CHTMLResourceLoader::getOutputMemory();
        self::$performance["objets"]         = array_sum(CStoredObject::$objectCounts);
        self::$performance["cachableCount"]  = array_sum(CStoredObject::$cachableCounts);
        self::$performance["cachableCounts"] = CStoredObject::$cachableCounts;
        self::$performance["objectCounts"]   = CStoredObject::$objectCounts;
        self::$performance["ip"]             = $_SERVER["SERVER_ADDR"] ?? null;

        self::$performance["size"] = CHTMLResourceLoader::getOutputLength();

        self::$performance["cache"] = [
            "totals" => Cache::getTotals(),
            "total"  => Cache::getTotal(),
        ];

        self::$performance["enslaved"] = CView::$enslaved;

        $time  = 0;
        $count = 0;

        // Data sources performance
        foreach (CSQLDataSource::$dataSources as $dsn => $ds) {
            if (!$ds) {
                continue;
            }

            $chrono      = $ds->chrono;
            $chronoFetch = $ds->chronoFetch;

            $time  += $chrono->total + $chronoFetch->total;
            $count += $chrono->nbSteps;

            self::$performance["dataSources"][$dsn] = [
                "latency"    => $ds->latency,
                "ct"         => $ds->connection_time,
                "count"      => $chrono->nbSteps,
                "time"       => $chrono->total,
                "countFetch" => $chronoFetch->nbSteps,
                "timeFetch"  => $chronoFetch->total,
            ];
        }

        self::$performance["dataSourceTime"]  = $time;
        self::$performance["dataSourceCount"] = $count;

        $redis_chrono = CRedisClient::$chrono;
        if ($redis_chrono) {
            self::$performance["nosqlTime"]  = (float)$redis_chrono->total;
            self::$performance["nosqlCount"] = $redis_chrono->nbSteps;
        }

        $elastic_chrono = ElasticClient::getChrono();
        if ($elastic_chrono) {
            self::$performance["nosqlTime"]  += (float)$elastic_chrono->total;
            self::$performance["nosqlCount"] += $elastic_chrono->nbSteps;
        }

        // Transport tiers
        self::$performance['transportTiers']['total']   = [
            'count' => 0,
            'time'  => 0,
        ];
        self::$performance['transportTiers']['sources'] = [];

        foreach (CExchangeSource::$call_traces as $exchange_source => $chronometer) {
            self::$performance['transportTiers']['total']['count'] += $chronometer->nbSteps;
            self::$performance['transportTiers']['total']['time']  += $chronometer->total;

            $_short_name = CClassMap::getSN($exchange_source);

            self::$performance['transportTiers']['sources'][$_short_name] = [
                'count' => $chronometer->nbSteps,
                'time'  => $chronometer->total,
            ];
        }
    }

    /**
     * Check whether we access MB in a restricted mode
     * Useful for restricted tokens and MbHost connection
     *
     * @return bool
     */
    static function isSessionRestricted()
    {
        return CAppUI::$token_restricted || (CAppUI::$auth_info && CAppUI::$auth_info->restricted);
    }

    /**
     * Callbacks function (doProbably)
     * @return void
     */
    static function triggerCallbacksFunc()
    {
        foreach (CApp::$callbacks as $_callback) {
            try {
                call_user_func($_callback);
            } catch (Exception $e) {
                trigger_error($e->getMessage(), E_USER_WARNING);
            }
        }
    }

    /**
     * Apply a ratio multiplicator to current memory limit
     *
     * @param float $ratio Ratio to apply
     *
     * @return int Previous memory limit
     */
    static function memoryRatio($ratio)
    {
        $limit = CMbString::fromDecaSI(ini_get("memory_limit")) * $ratio;
        $limit = CMbString::toDecaSI($limit);

        return ini_set("memory_limit", $limit);
    }

    /**
     * Set time limit in seconds
     *
     * @param integer $seconds The time limit in seconds
     *
     * @return string
     */
    static function setTimeLimit($seconds)
    {
        return self::setMaxPhpConfig("max_execution_time", $seconds);
    }

    /**
     * Set a php configuration limit with a minimal value
     * if the value is < actual, the old value is used
     *
     * @param string     $config the php parameter
     * @param string|int $limit  the limit required
     *
     * @return string
     */
    static function setMaxPhpConfig($config, $limit)
    {
        $actual = CMbString::fromDecaBinary(ini_get($config));
        $new    = CMbString::fromDecaBinary($limit);

        //new value is superior => change the config
        if ($new > $actual) {
            return ini_set($config, $limit);
        }

        return ini_get($config);
    }

    /**
     * Set memory limit in megabytes
     *
     * @param string $megabytes The memory limit, suffixed with K, M, G
     *
     * @return string
     */
    static function setMemoryLimit($megabytes)
    {
        return self::setMaxPhpConfig("memory_limit", $megabytes);
    }

    /**
     * Redirect to empty the POST data,
     * so that it is not posted back when refreshing the page.
     * Use it instead of CApp::rip() directly
     *
     * @param bool $redirect Try to redirect if true
     *
     * @return void
     */
    static function emptyPostData($redirect = true)
    {
        if ($redirect && !empty($_POST) && !headers_sent()) {
            CAppUI::redirect(/*CValue::read($_SERVER, "QUERY_STRING")*/
                null,
                303
            );
        }
        self::rip(false);
    }

    /**
     * Outputs JSON data after removing the Output Buffer, with a custom mime type
     *
     * @param mixed      $data     The data to output
     * @param string     $mimeType [optional] The mime type of the data, application/json by default
     * @param bool|false $prettify [optional] Use JSON_PRETTY_PRINT
     *
     * @return void
     */
    static function json($data, $mimeType = "application/json", $prettify = false)
    {
        $json = CMbArray::toJSON($data, true, $prettify ? JSON_PRETTY_PRINT : null);

        ob_clean();
        header("Content-Type: $mimeType");
        echo $json;

        self::rip();
    }

    /**
     * Fetch an HTML content of a module view, as a HTTP GET call would do
     * Very useful to assemble multiple views
     *
     * @param string $module    The module name or the file path
     * @param string $file      [optional] The file of the module, or null
     * @param array  $arguments [optional] The GET arguments
     *
     * @return string The fetched content
     */
    static function fetch($module, $file = null, $arguments = [])
    {
        if (self::$turn_off_fetch) {
            return null;
        }

        $values = CView::reset();

        $saved_GET = $_GET;

        foreach ($arguments as $_key => $_value) {
            $_GET[$_key] = $_value;
        }

        ob_start();
        if (isset($file)) {
            $file_path = __DIR__ . "/../../modules/$module/$file.php";
            if (is_file($file_path)) {
                include $file_path;
            } else {
                $mod = CModule::getActive($module);
                if ($controller = $mod->matchLegacyController($file)) {
                    $controller->$file();
                }
            }
        } else {
            include $module;
        }

        $output = ob_get_clean();

        CView::restore($values);

        $_GET = $saved_GET;

        return $output;
    }

    /**
     * Get the base application URL
     *
     * @return string The URL
     */
    static function getBaseUrl()
    {
        // Todo: Handle CLI for testing cases
        if (PHP_SAPI === 'cli') {
            return 'http://localhost/mediboard';
        }

        $scheme = "http" . (isset($_SERVER["HTTPS"]) ? "s" : "");
        $host   = $_SERVER["SERVER_NAME"];
        $port   = ($_SERVER["SERVER_PORT"] == 80) ? "" : ":{$_SERVER['SERVER_PORT']}";
        $path   = dirname($_SERVER["SCRIPT_NAME"]);

        return $scheme . "://" . $host . $port . $path;
    }

    /**
     * Get root directory
     *
     * @param string $path The directory to get absolute path of
     *
     * @return string
     */
    static function getAbsoluteDirectory($path = null)
    {
        $dir = __DIR__;

        // For Windows
        if (DIRECTORY_SEPARATOR === '\\') {
            $dir = str_replace('\\', '/', __DIR__);
        }

        // Do not use realpath which resolves symbolic links
        return CMbPath::canonicalize("$dir/../../$path");
    }

    /**
     * Return all storable CMbObject classes which module is installed
     *
     * @param array $classes    [optional] Restrain to given classes
     * @param bool  $short_name Return short names or full names
     *
     * @return array Class names
     */
    static function getInstalledClasses($classes = [], $short_name = false)
    {
        $instances = [];

        if (empty($classes)) {
            $classes = self::getMbClasses($instances, $short_name);
        }

        foreach ($classes as $key => $class) {
            if (isset($instances[$class])) {
                $object = $instances[$class];
            } else {
                $object = self::getClassInstance($class);
            }

            // Installed module ? Storable class ?
            if (!$object || $object->_ref_module === null || !$object->_spec->table) {
                unset($classes[$key]);
                continue;
            }
        }

        return $classes;
    }

    /**
     * Return all CStoredObject child classes
     *
     * @param array $instances  If not null, retrieve an array of all object instances
     * @param bool  $short_name Return short names or full names
     *
     * @return array Class names
     */
    static function getMbClasses(&$instances = null, $short_name = false)
    {
        $classes = self::getChildClasses(CStoredObject::class, false, $short_name);

        foreach ($classes as $key => $class) {
            // In case we removed a class and it's still in the cache
            if (!class_exists($class, true)) {
                unset($classes[$key]);
                continue;
            }

            $object = self::getClassInstance($class);

            // Instanciated class?
            if (!$object || !$object->_class) {
                unset($classes[$key]);
                continue;
            }

            $instances[$class] = $object;
        }

        return $classes;
    }

    /**
     * Return all child classes of a given class having given properties
     *
     * @param string $parent        [optional] Parent class
     * @param bool   $active_module [optional] If true, filter on active modules
     * @param bool   $short_names   [optional] If true, return short_names instead of namespaced names
     *
     * @return array Class names
     *
     * @throws Exception
     *
     * @todo Default parent class should probably be CModelObject
     */
    static function getChildClasses(
        $parent = CMbObject::class,
        $active_module = false,
        $short_names = false,
        $only_instantiable = false
    ) {
        $shortname = CClassMap::getSN($parent);
        $key       = 'CApp.getChildClasses-' . implode('-', [$shortname, $active_module, $short_names]);

        $cache = Cache::getCache(Cache::INNER_OUTER)->withCompressor();
        if (($value = $cache->get($key)) !== null) {
            return $value;
        }

        $start = microtime(true);
        $class_map = CClassMap::getInstance();
        $children  = $class_map->getClassChildren($parent, false, $only_instantiable);

        if ($active_module) {
            // Filter on active module
            $cmbo_children = $class_map->getClassChildren(CMbObject::class);
            if ($parent != CMbObject::class && !in_array($parent, $cmbo_children)) {
                throw new Exception("Use active_module only with parent instanceof CMbObject.");
            }

            foreach ($children as $key => &$_child_name) {
                $object = new $_child_name;
                if (!isset($object->_ref_module)) {
                    unset($children[$key]);
                }
            }
        }

        if ($short_names) {
            array_walk(
                $children,
                function (&$_child) use ($class_map) {
                    $_child = $class_map->getShortName($_child);
                }
            );
        }

        $cache->set($key, $children);

        CApp::log(
            sprintf(
                'CACHE : Took %4.3f ms to build %s cache',
                (microtime(true) - $start) * 1000,
                $key
            )
        );

        return $children;
    }

    /**
     * Returns an object instance
     *
     * @param string $class Class
     *
     * @return null|CMbObject
     */
    private static function getClassInstance($class)
    {
        try {
            // TODO: Compatibility PHP 5.x-7.0 & 7.1 (see http://php.net/manual/fr/migration71.incompatible.php)
            @$object = new $class;
        } catch (Throwable $t) {
            // Executed only in PHP 7.1, will not match in PHP 5.x, 7.0
            return null;
        }

        return $object;
    }

    /**
     * Tells whether a given method is overridden (= not the in first declaring class)
     *
     * @param string $class  Class from where to search
     * @param string $method Method to search for
     * @param bool   $strict If strict mode enabled, the method must be declared in given class (not in a parent)
     *
     * @return bool
     * @throws ReflectionException
     */
    static function isMethodOverridden($class, $method, $strict = false)
    {
        // Method does not exist
        if (!method_exists($class, $method)) {
            return false;
        }

        $reflection       = new ReflectionMethod($class, $method);
        $_declaring_class = $reflection->getDeclaringClass();

        // In strict mode, method must be strictly declared in given class
        if ($strict && ($_declaring_class !== $class)) {
            return false;
        }

        $_parent_class = $_declaring_class->getParentClass();

        // Declaring class has no parent
        if (!$_parent_class) {
            return false;
        }

        // Declaring class is different from its own parent class and parent class declares the method too
        if (($_declaring_class->name !== $_parent_class->name) && method_exists($_parent_class->name, $method)) {
            return true;
        }

        // Check if the parent class is the first declaring one
        return static::isMethodOverridden($_parent_class, $method, $strict);
    }

    /**
     * Group installed classes by module names
     *
     * @param array $classes Class names
     *
     * @return array Array with module names as key and class names as values
     */
    static function groupClassesByModule($classes)
    {
        $grouped = [];
        foreach ($classes as $class) {
            $object = self::getClassInstance($class);
            if (!$object) {
                continue;
            }

            if ($module = $object->_ref_module) {
                $grouped[$module->mod_name][] = $class;
            }
        }

        return $grouped;
    }

    /**
     * Try to approximate ouput buffer bandwidth consumption
     * Won't take into account output_compression
     *
     * @return int Number of bytes
     */
    static function getOuputBandwidth()
    {
        // Already flushed
        // @fixme output_compression ignored!!
        $bandwidth = CHTMLResourceLoader::$flushed_output_length;
        // Still something to be flushed ?
        // @fixme output_compression ignored!!
        $bandwidth += ob_get_length();

        return $bandwidth;
    }

    /**
     * Try to approximate non ouput buffer bandwidth consumption
     * Won't take into account output_compression
     *
     * @return int Number of bytes
     */
    static function getOtherBandwidth()
    {
        $bandwidth = 0;

        // Add REQUEST params, FILES params, request and response headers to the size of the hit
        // Use of http_build_query() to approximate HTTP serialization
        $bandwidth += strlen(http_build_query($_REQUEST));
        $bandwidth += strlen(http_build_query($_FILES));
        $bandwidth += strlen(http_build_query(apache_request_headers()));
        $bandwidth += strlen(http_build_query(apache_response_headers()));

        // Add actual FILES sizes to the size of the hit
        foreach ($_FILES as $_files) {
            $_files_size = $_files["size"];
            $bandwidth   += is_array($_files_size) ? array_sum($_files_size) : $_files_size;
        }

        // Add extra bandwidth that may have been declared
        $bandwidth += self::$extra_bandwidth;

        return $bandwidth;
    }

    /**
     * Get polyfill request headers
     *
     * @return array
     */
    public static function getRequestHeaders($header = null)
    {
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
        } else {
            $headers = [];
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $headers[str_replace(
                        ' ',
                        '-',
                        ucwords(strtolower(str_replace('_', ' ', substr($name, 5))))
                    )] = $value;
                }
            }
        }

        if ($header) {
            $headers = array_change_key_case($headers, CASE_LOWER);

            return $headers[$header] ?? null;
        }

        return $headers;
    }

    /**
     * Get polyfill response headers
     *
     * @return array
     */
    public static function getResponseHeaders()
    {
        $return = [];
        $list   = headers_list();
        foreach ($list as $header) {
            $headers        = explode(':', $header);
            $_name          = $headers[0];
            $_value         = str_replace($_name . ':', '', $header);
            $return[$_name] = $_value;
        }

        return $return;
    }

    /**
     * Dump anything in devtool
     *
     * @param mixed  $var anything to dump
     * @param string $msg comment about the dump
     *
     * @return void
     */
    static function dump($var, $msg = null)
    {
        if (!CDevtools::isActive()) {
            return;
        }

        // datas
        $var = self::extracting($var);


        CDevtools::filePutContents('dump', $var, $msg);
    }

    /**
     * Extracting var to display in dev toolbar
     *
     * @param mixed $var       data to extract
     * @param int   $max_depth limit depth dump
     *
     * @return mixed
     */
    private static function extracting($var, $max_depth = null)
    {
        // SF var_dumper plateform requirement
        if (version_compare(PHP_VERSION, '5.5.9') === -1) {
            return '<pre>' . CMbString::truncate(print_r($var, true), 500, '...') . '</pre>';
        }

        if (!self::$extractors) {
            self::$extractors['cloner'] = new VarCloner();
            self::$extractors['dumper'] = new HtmlDumper();
        }

        if (!is_null($max_depth)) {
            $_clone = self::$extractors['cloner']->cloneVar($var)->withMaxDepth($max_depth);
        } else {
            $_clone = self::$extractors['cloner']->cloneVar($var);
        }

        return self::$extractors['dumper']->dump($_clone, true);
    }

    /**
     * Add throwable in dev toolbar
     *
     * @param Throwable $throwable
     *
     * @return void
     */
    static function error($throwable)
    {
        $error = self::extracting($throwable);
        CDevtools::filePutContents('error', $error);
    }


    /**
     * @param string $message Message to log
     * @param mixed  $data    Data to add to the log
     * @param int    $level   Use LoggerLevels::const
     *
     * @return bool
     * @throws Exception
     */
    static function log($message, $data = null, int $level = LoggerLevels::LEVEL_INFO): bool
    {
        // init logger
        if (is_null(static::$logger)) {
            static::setLogger();
        }

        // force array necessary for monolog's & parse log
        if (!is_null($data)) {
            $data = $data === false ? 0 : $data;
            $data = is_array($data) ? $data : [$data];
        } else {
            $data = [];
        }

        // Log data with ApplicationLoggerWrapper in elastic with a fallback to file
        $retour = static::$logger->log($level, $message, $data);

        // Devtools
        if ($retour && CDevtools::isActive()) {
            $datas = self::extracting(
                [
                    'message' => $message,
                    'data'    => $data,
                    'level'   => LoggerLevels::getLevelName($level),
                ]
            );
            CDevtools::filePutContents('log', $datas);
        }

        return $retour;
    }

    /**
     * Set logger for application channel
     *
     * @return void
     * @throws Exception
     *
     */
    private static function setLogger()
    {
        static::$logger = new ApplicationLoggerWrapper();
    }

    public static function isElasticLog(): bool
    {
        if (self::$elastic_log === null) {
            self::$elastic_log = (bool)CAppUI::conf('application_log_using_nosql');
        }

        return self::$elastic_log;
    }


    /**
     * Get the current request unique ID
     *
     * @return string
     */
    static function getRequestUID()
    {
        if (self::$requestUID === null) {
            self::initRequestUID();
        }

        return self::$requestUID;
    }

    /**
     * Initializes a unique request ID to identify current request
     *
     * @return void
     */
    private static function initRequestUID()
    {
        $user_id = CAppUI::$instance->user_id ?? null;
        $uid     = uniqid("", true);

        $address = CMbServer::getRemoteAddress();
        $ip      = $address["remote"];

        // MD5 is enough as it doesn't have to be crypto proof
        self::$requestUID = md5("$user_id/$uid/$ip");
    }

    /**
     * Execute a script on all servers
     *
     * @param string   $ips_list List of IP adresses
     * @param String[] $get      Parameters GET
     * @param String[] $post     Parameters POST
     *
     * @return array
     */
    static function multipleServerCall($ips_list, $get, $post = null)
    {
        $base = $_SERVER["SCRIPT_NAME"] . "?";
        foreach ($get as $_param => $_value) {
            $base .= "$_param=$_value&";
        }
        $base = substr($base, 0, -1);

        $address = [];
        if ($ips_list) {
            $address = preg_split("/\s*,\s*/", $ips_list, -1, PREG_SPLIT_NO_EMPTY);
            $address = array_flip($address);
        }

        foreach ($address as $_ip => $_value) {
            $address[$_ip] = self::serverCall("http://$_ip$base", $post);
        }

        return $address;
    }

    /**
     * Send the request on the server
     *
     * @param String   $url  URL
     * @param String[] $post Parameters POST
     *
     * @return bool|string|array
     */
    static function serverCall($url, $post = null)
    {
        CSessionHandler::writeClose();
        global $rootName;
        $session_name = CSessionManager::forgeSessionName($rootName);
        $cookie       = CValue::cookie($session_name);
        $result       = ["code" => "", "body" => ""];
        try {
            $source_http = new CSourceHTTP();
            $source_http->host = $url;
            $source_http->loggable = false;

            $http_client = new CExchangeHTTPClient($url);
            $http_client->_source = $source_http;
            $http_client->setCookie("$session_name=$cookie");
            $http_client->setUserAgent(CAppUI::conf('product_name') . CApp::getVersion()->toArray()["version"]);
            $http_client->setOption(CURLOPT_FOLLOWLOCATION, true);
            if ($post) {
                $request = $http_client->post(http_build_query($post));
            } else {
                $request = $http_client->get();
            }
        } catch (Exception $e) {
            CSessionHandler::start();
            $result["body"] = '<div class="small-error">' . $e->getMessage() . '</div>';

            return $result;
        }
        CSessionHandler::start();

        $result["code"] = $http_client->last_information["http_code"];
        $result["body"] = $request;

        return $result;
    }

    /**
     * Fetch a full page from query parameters
     *
     * @param array $query_params Query parameters
     *
     * @return string
     */
    static function fetchQuery($query_params)
    {
        $url = self::getLocalBaseUrl() . "?" . http_build_query($query_params, null, "&");

        $result = self::serverCall($url);

        return $result["body"];
    }

    /**
     * Get the local base url of the application
     *
     * @return string
     */
    static function getLocalBaseUrl()
    {
        preg_match("/https*:\/\/[^\/]+\/(.+)/u", CAppUI::conf("base_url"), $matches);

        return "http://127.0.0.1/" . $matches[1];
    }

    /**
     * Resolve a relative path to an absolute path
     *
     * @param string $path The relative path to resolve
     *
     * @return string
     */
    public static function resolvePath($path = "")
    {
        return __DIR__ . "/../../" . ltrim($path, "/\\");
    }

    /**
     * Executes a callback function according in a probabilistic way
     *
     * @param integer        $denominator Denominator's probability (1 / $denominator)
     * @param callable|array $callback    Function to call
     *
     * @return void
     */
    static function doProbably($denominator, $callback)
    {
        if (PHP_SAPI === "cli") {
            return;
        }

        if (!$denominator || mt_rand(1, $denominator) !== 1) {
            return;
        }

        self::registerCallback($callback);
    }

    /**
     * Register a callback called after giving hand back to the user
     *
     * @param callable $callback The callback
     *
     * @return void
     */
    static function registerCallback($callback)
    {
        if (!is_callable($callback)) {
            return;
        }

        self::$callbacks[] = function () use ($callback) {
            CSessionHandler::writeClose();

            call_user_func($callback);
        };
    }

    /**
     * Send email with system message source
     *
     * @param string      $subject        Mail subject
     * @param string      $body           Mail body
     * @param array       $to             Receivers IDs
     * @param array       $re             Replyto addresses
     * @param array       $bcc            Hidden copy addresses
     * @param array       $to_addresses   Specific to-address
     * @param CSourceSMTP $source         Specific SMTPSource
     * @param bool        $display_errors Display the errors, or not
     *
     * @return bool Send status
     * @throws Exception
     *
     */
    static function sendEmail(
        $subject,
        $body,
        $to = [],
        $re = [],
        $bcc = [],
        $to_addresses = [],
        CSourceSMTP $source = null,
        $display_errors = true
    ) {
        if (!$source || !$source->_id) {
            $source       = new CSourceSMTP();
            $source->name = 'system-message';
            $source->loadMatchingObject();
        }

        if (!$source->_id) {
            if ($display_errors) {
                CAppUI::displayAjaxMsg('CExchangeSource.none', UI_MSG_WARNING);
            }

            return false;
        }

        $ds = CSQLDataSource::get('std');

        $user       = new CMediusers();
        $recipients = [];

        if ($to) {
            if (is_array($to)) {
                $to = array_unique($to);

                $users = $user->loadList(
                    ['user_id' => $ds->prepareIn($to)]
                );

                $recipients = CMbArray::pluck($users, '_user_email');
                $recipients = array_filter($recipients);
            } else {
                $user->load($to);

                if ($user && $user->_id && $user->_user_email) {
                    $recipients[] = $user->_user_email;
                }
            }
        }

        if ($to_addresses) {
            if (is_array($to_addresses)) {
                foreach ($to_addresses as $_address) {
                    $recipients[] = $_address;
                }
            } else {
                $recipients[] = $to_addresses;
            }
        }

        $hidden_addresses = [];

        if ($bcc) {
            if (is_array($bcc)) {
                foreach ($bcc as $_bcc) {
                    $hidden_addresses[] = $_bcc;
                }
            } else {
                $hidden_addresses[] = $bcc;
            }
        }

        if (!$recipients && !$hidden_addresses) {
            return false;
        }

        try {
            $source->init();

            foreach ($recipients as $_recipient) {
                $source->addTo($_recipient);
            }

            foreach ($hidden_addresses as $_hidden_address) {
                $source->addBcc($_hidden_address);
            }

            $source->setSubject($subject);
            // HTML purification is done inside.
            $source->setBody($body);

            if ($re) {
                if (is_array($re)) {
                    foreach ($re as $_re) {
                        $source->addRe($_re);
                    }
                } else {
                    $source->addRe($re);
                }
            }

            $source->send();

            if ((!$source->asynchronous || $source->_skip_buffer) && $display_errors) {
                CAppUI::displayAjaxMsg('common-Notification sent', UI_MSG_OK);
            }
        } catch (phpmailerException $e) {
            if ($display_errors) {
                CAppUI::displayAjaxMsg($e->getMessage(), UI_MSG_ERROR);
            }

            return false;
        } catch (CMbException $e) {
            if ($display_errors) {
                CAppUI::displayAjaxMsg($e->getMessage(), UI_MSG_ERROR);
            }

            return false;
        }

        return true;
    }

    /**
     * Disable all object handlers, and object cache, for heavy data handling (like data imports)
     *
     * @return void
     */
    static function disableCacheAndHandlers()
    {
        // Desactivation des object handlers
        HandlerManager::disableObjectHandlers();

        // Désactivation des traitements sur les fichiers
        CFile::$migration_enabled = false;

        // Desactivation du cache d'objets
        CStoredObject::$useObjectCache = false;
        CSQLDataSource::$log           = false;
    }

    /**
     * Tells if the application is in readonly mode
     *
     * @return bool
     */
    static function isReadonly()
    {
        if (self::$readonly === null) {
            self::$readonly = CAppUI::conf("readonly");
        }

        return self::$readonly;
    }

    /**
     * Add a custom 'proxy' HTTP header for LB appliances
     *
     */
    public static function getProxyHeader(): string
    {
        $header = (file_exists(rtrim(CAppUI::conf('root_dir'), '/') . '/offline')) ? 'offline' : 'online';

        return "proxy:{$header}";
    }

    /**
     * Returns the registered shutdown callbacks
     *
     * @return array|SplPriorityQueue
     */
    static public function getShutdownCallbacks()
    {
        return static::$shutdown_callbacks;
    }

    /**
     * Executes the registered shutdown callbacks
     */
    static public function handleShutdownCallbacks()
    {
        foreach (static::$shutdown_callbacks as $_callback) {
            try {
                call_user_func($_callback);
            } catch (Exception $e) {
                trigger_error($e->getMessage(), E_USER_WARNING);
            }
        }
    }

    /**
     * @return string
     * @throws Exception
     */
    public static function getAppPrivateKeyPath(): string
    {
        return CAppUI::conf('app_private_key_filepath');
    }

    /**
     * @return string
     * @throws Exception
     */
    public static function getAppPublicKeyPath(): string
    {
        return CAppUI::conf('app_public_key_filepath');
    }

    /**
     * Get the application master key (for symmetric encryption)
     *
     * Can be generated using base64_encode(random_bytes(32))
     *
     * @return string
     * @throws CMbException
     */
    public static function getAppMasterKey(): string
    {
        $filepath = CAppUI::conf('app_master_key_filepath');

        if (!is_readable($filepath)) {
            throw new CMbException('common-error-Unable to read master key');
        }

        $key = file_get_contents($filepath);

        if ($key !== false) {
            if (strlen($key) > 32) {
                return substr($key, 0, 32);
            }

            return $key;
        }

        throw new CMbException('common-error-Unable to read master key');
    }

    public static function turnOnFetch(): void
    {
        self::$turn_off_fetch = false;
    }

    public static function setRipErrorCode(int $code): void
    {
        self::$rip_error_code = $code;
    }

    /**
     * Throws an exception is in a public environment.
     *
     * @throws PublicEnvironmentException
     */
    public static function failIfPublic(): void
    {
        if (self::getInstance()->isPublic()) {
            throw new PublicEnvironmentException(
                Response::HTTP_FORBIDDEN,
                'Permission denied'
            );
        }
    }

    /**
     * @param Request $request
     *
     * @return Response|void
     * @throws Exception
     */
    public function startForRequest(Request $request)
    {
        BlackfireHelper::addMarker(__FUNCTION__);

        if ($this->is_started) {
            throw new AppException(Response::HTTP_INTERNAL_SERVER_ERROR, 'The app is already started for request.');
        }

        $is_api = $this->isRequestApi($request);

        $this->checkMandatoryFiles();
        $this->includeConfigs();

        // Timezone
        date_default_timezone_set($this->config["timezone"]);

        // Register shutdown callbacks
        register_shutdown_function([__CLASS__, 'handleShutdownCallbacks']);

        // Init
        Cache::init(static::getAppIdentifier());
        CAppUI::init();
        CClassMap::init();
        CAutoloadAlias::register();

        // If an error occured too early (ParseError during the class autoloading) need to put this
        // @see vendor/symfony/error-handler/README.md
        HtmlErrorRenderer::setTemplate('templates/error.html.php');

        self::registerShutdown([CMbMutex::class, 'releaseMutexes'], self::MUTEX_PRIORITY);

        // Log queries
        if ($this->config['log_all_queries']) {
            $this->enableDataSourceLog();
        }

        // Offline mode
        if ($this->isOffline()) {
            throw UnavailableApplicationException::applicationIsDisabledBecauseOfMaintenance();
        }

        // Offline db
        if (!$this->isDatabaseAccessible()) {
            throw UnavailableApplicationException::databaseIsNotAccessible();
        }

        // Include config in DB
        if (CAppUI::conf("config_db")) {
            CMbConfig::loadValuesFromDB();
        }

        // Load modules
        CModule::loadModules();

        // Init shared memory, must be after DB init and Modules loading
        Cache::initDistributed();

        // set default CAppUI (legacy)
        CAppUI::$instance = CAppUI::initInstance();
        if ($is_api) {
            CAppUI::turnOffEchoStep();
            self::turnOffFetch();
        }

        // Load default preferences if not logged in
        CAppUI::loadPrefs();

        // Load locales
        CAppUI::loadCoreLocales();

        // Start chrono
        $this->startChrono();

        // Register configuration
        CConfiguration::registerAllConfiguration();

        // Handlers
        self::notify("BeforeMain");

        // Enable log
        $this->enableDataSourceLog();

        $this->is_started = true;
    }

    /**
     * @param CUser                    $ox_user
     * @param LogAuthBadge|null        $log_auth_badge
     * @param WeakPasswordBadge|null   $weak_password_badge
     * @param CUserAuthentication|null $auth
     *
     * @return void
     * @throws Exception
     */
    public function afterAuth(
        CUser $ox_user,
        ?LogAuthBadge $log_auth_badge,
        ?WeakPasswordBadge $weak_password_badge,
        ?CUserAuthentication $auth
    ): void {
        BlackfireHelper::addMarker('CApp::afterAuth');

        if ($log_auth_badge && $log_auth_badge->isEnabled()) {
            // Set the CAppUI::$instance->_user_group
            CAppUI::create($ox_user, $log_auth_badge->getMethod(), $weak_password_badge, $auth);
            CAppUI::buildPrefs();
        }

        // Show errors to admin
        CError::setDisplayMode((bool)CAppUI::pref('INFOSYSTEM'));

        // Load User Perms
        CPermission::loadUserPerms();

        // Load locales
        CAppUI::loadCoreLocales();

        // Init Application User
        CAppUI::initUser();

        // Set the GLOBAL $g from CAppUI::$instance->_user_group in order to be used by RequestGroup and others.
        global $g;
        $g = CAppUI::$instance->user_group;

        $disconnect_pref = (bool)CAppUI::pref('admin_unique_session');

        if ($auth !== null && $disconnect_pref) {
            $auth->disconnectSimilarOnes();
        }

        // Todo: Check if password must be changed (weak, remote) or if password has expired (force changing, rotation).
        // Todo: RGPD too
    }

    /**
     * @throws TestsException
     * @throws Exception
     */
    public function startForCli(string $command_name)
    {
        if ($this->is_started) {
            throw new Exception('The app is already started for cli.');
        }

        $this->checkMandatoryFiles();
        $this->includeConfigs();

        // Timezone
        date_default_timezone_set($this->config["timezone"]);

        // Init
        Cache::init(static::getAppIdentifier());
        CAppUI::init();
        CClassMap::init();
        CAutoloadAlias::register();

        CSessionHandler::setHandler(CAppUI::conf('session_handler'));
        CModule::loadModules();

        $this->authTestUser();

        CAppUI::turnOffEchoStep();
        static::setRipErrorCode(self::PHPUNIT_RIP_CODE);

        // Permissions
        CPermModule::loadUserPerms();
        CPermObject::loadUserPerms();

        // Include config in DB
        if (CAppUI::conf("config_db")) {
            CMbConfig::loadValuesFromDB();
        }

        // Init shared memory, must be after DB init and Modules loading
        Cache::initDistributed();

        // Start chrono
        $this->startChrono();

        // Register configuration
        CConfiguration::registerAllConfiguration();

        $this->is_started = true;

        // Log
        static::log(
            sprintf('%s with command %s on pid %s', __FUNCTION__, $command_name, getmypid()),
            [],
            LoggerLevels::LEVEL_DEBUG
        );
    }

    /**
     * @return void
     * @throws Exception
     */
    public function includeConfigs(): array
    {
        // include once
        if (!empty($this->config)) {
            return $this->config;
        }

        global $dPconfig; // GLOBALS legacy compat
        require $this->root_dir . "/includes/config_all.php";
        $this->config = $dPconfig;

        if (!is_array($this->config)
            || !array_key_exists('root_dir', $this->config)
            || !is_dir($this->config['root_dir'])
        ) {
            throw new AppException(Response::HTTP_INTERNAL_SERVER_ERROR, 'The root directory is misconfigured.');
        }

        return $dPconfig;
    }

    /**
     * @throws Exception
     */
    public function checkMandatoryFiles(): void
    {
        // Check if config file is present
        if (!is_file($this->root_dir . "/includes/config.php")) {
            throw new AppException(Response::HTTP_INTERNAL_SERVER_ERROR, 'The config file is not present.');
        }

        // Check if config_dist file is present
        if (!is_file($this->root_dir . "/includes/config_dist.php")) {
            throw new AppException(Response::HTTP_INTERNAL_SERVER_ERROR, 'The config definition file is not present.');
        }

        // Check if the config_all file is present
        if (!is_file($this->root_dir . "/includes/config_all.php")) {
            throw new AppException(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'The global config definition file is not present.'
            );
        }

        // Check if the classmap && classref files are presents
        if (
            !is_file($this->root_dir . "/includes/classmap.php")
            || !is_file($this->root_dir . "/includes/classref.php")
        ) {
            throw new AppException(Response::HTTP_INTERNAL_SERVER_ERROR, 'The class definition file is not present.');
        }
    }

    /**
     * Authenticate the PHPUnit user for tests purpose.
     *
     * @throws TestsException
     */
    private function authTestUser(): void
    {
        // User
        // todo remove after merged auth v3
        $user                = new CUser();
        $user->user_username = CUser::USER_PHPUNIT;
        $user_id             = $user->loadMatchingObjectEsc();

        if (!$user_id) {
            throw new TestsException(sprintf('Missing user_username %s', $user->user_username));
        }

        // CAppUI
        CAppUI::initInstance();
        CAppUI::$user                = $user;
        CAppUI::$instance->_ref_user = $user->loadRefMediuser();
        CAppUI::$instance->user_id   = $user->_id;

        if (($mediuser = $user->loadRefMediuser()) && ($function = $mediuser->loadRefFunction())) {
            // Avoid setting group config on a bad group
            // If $g is not initialized the first group (alphabetical order) will be returned and will change later
            global $g;
            $g = $function->group_id;
        }
    }

    /**
     * Must be the same here and SHM::init
     * We don't use CApp because it can be called in /install
     *
     * @param string|null $root_dir
     *
     * @return array|string|string[]|null Application identifier, in a pool of servers
     * @throws Exception
     */
    public static function getAppIdentifier(?string $root_dir = null)
    {
        if ($root_dir === null || $root_dir === '') {
            $root_dir = CAppUI::conf("root_dir");
        }

        return preg_replace("/[^\w]+/", "_", $root_dir);
    }

    /**
     * Registers a callback to execute on process shutdown
     *
     * @param callable $callback The callback to execute
     * @param string   $priority A string representing the priority of the callback execution
     */
    static public function registerShutdown(callable $callback, $priority = CApp::APP_PRIORITY)
    {
        // Initialize the priority queue
        if (!static::$shutdown_callbacks instanceof SplPriorityQueue) {
            static::$shutdown_callbacks = new SplPriorityQueue();
        }

        if ($priority === static::APP_PRIORITY) {
            // Static inner caches in order to ensure that APP callbacks are executed before the framework ones
            static $app_max_priority = 1000;
            static $framework_max_priority = null;

            if (is_null($framework_max_priority)) {
                $framework_max_priority = max(static::FRAMEWORK_PRIORITIES);
            }

            // Decrements THEN returns the value
            $priority = --$app_max_priority;

            if ($priority <= $framework_max_priority) {
                trigger_error(
                    "Register shutdown APP priority must be higher than '{$framework_max_priority}', given: '{$priority}'",
                    E_USER_ERROR
                );
            }
        } elseif (array_key_exists($priority, static::FRAMEWORK_PRIORITIES)) {
            $priority = static::FRAMEWORK_PRIORITIES[$priority];
        } else {
            trigger_error("Invalid register shutdown priority: '{$priority}'", E_USER_ERROR);
        }

        static::$shutdown_callbacks->insert($callback, $priority);
    }

    /**
     * @return void
     */
    private function enableDataSourceLog(): void
    {
        CSQLDataSource::$log = true;
        CRedisClient::$log   = true;
    }

    /**
     * Check if config is in offline mode
     */
    public function isOffline()
    {
        // Offline mode
        if ($this->config["offline"]) {
            return true;
        }

        // If offline period
        if ($this->config["offline_time_start"] && $this->config["offline_time_end"]) {
            $time               = time();
            $offline_time_start = strtotime($this->config["offline_time_start"]);
            $offline_time_end   = strtotime($this->config["offline_time_end"]);

            if (($time >= $offline_time_start) && ($time <= $offline_time_end)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if database is accessible
     * @return bool
     */
    public function isDatabaseAccessible(): bool
    {
        return (bool)@CSQLDataSource::get("std");
    }

    public static function getVersion(): Version
    {
        if (!static::$version) {
            $version_file_path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . Builder::VERSION_FILE;
            $version_data      = is_file($version_file_path) ? include $version_file_path : [];

            static::$version = new Version($version_data);
        }

        return static::$version;
    }

    public static function turnOffFetch(): void
    {
        self::$turn_off_fetch = true;
    }

    /**
     * @return void
     */
    private function startChrono(): void
    {
        self::$chrono       = new Chronometer();
        self::$chrono->main = true;
        self::$chrono->start();
    }

    /**
     * Todo: Be careful!
     *  First CApp::notify (BeforeMain) is called before $g guessing, and second one (AfterMain) after,
     *  so the BEFORE_MAIN and AFTER_MAIN events may not be called on the same handler!
     *
     * Subject notification mechanism
     *
     * TODO Implement to factorize
     *   on[Before|After][Store|Merge|Delete]()
     *   which have to get back de CPersistantObject layer
     *
     * @param string $message        The notification type
     * @param bool   $break_on_first Don't catch exceptions thrown by the handlers
     *
     * @throws Exception
     */
    public static function notify(string $message, bool $break_on_first = false): void
    {
        // Todo: PROBLEM: We do not know yet if the route is public
        if (self::getInstance()->isPublic()) {
            return;
        }

        $args = func_get_args();
        array_shift($args); // $message

        // Event Handlers
        HandlerManager::makeIndexHandlers();

        foreach (HandlerManager::getIndexHandlers() as $_handler) {
            $_trace = HandlerManager::mustLogHandler($_handler);

            try {
                if ($_trace) {
                    HandlerManager::trace('is called.', $_handler, "on$message", $args);
                }

                call_user_func_array([$_handler, "on$message"], $args);

                if ($_trace) {
                    HandlerManager::trace('has been called.', $_handler, "on$message", $args);
                }
            } catch (Exception $e) {
                if ($break_on_first) {
                    throw $e;
                } else {
                    CAppUI::setMsg($e, UI_MSG_ERROR);
                }
            }
        }
    }

    /**
     * Singelton
     *
     * @return CApp
     */
    public static function getInstance(): CApp
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Previously CApp::rip
     *
     * @param Request $request
     *
     * @return void
     * @throws Exception
     */
    public function stop(Request $request): void
    {
        BlackfireHelper::addMarker('CApp::stop');

        if (!$this->is_started) {
            throw new AppException(Response::HTTP_INTERNAL_SERVER_ERROR, "The app is not started.");
        }

        // Empty the message stack from remaining messages
        CAppUI::getMsg();

        // Cview checkin control
        if (!CView::$checkedin) {
            $_controller = $request->attributes->get('_controller');
            $msg         = "CView::checkin() has not been called in {$_controller}";

            if ($this->isRequestApi($request)) {
                self::log($msg);
            } else {
                trigger_error($msg);
            }
        }
        CView::disableSlave();

        // Handler
        self::notify("AfterMain");

        // Performance
        if (self::$performance['genere'] === null && self::$chrono) {
            if (self::$chrono->step > 0) {
                self::$chrono->stop();
            }

            // Requests after CApp::preparePerformance will not be logged!
            self::preparePerformance();
        }

        $this->is_stoped = true;
    }

    /**
     * @param Request $request
     *
     * @throws Exception
     */
    public function terminate(Request $request): void
    {
        if (!$this->is_stoped) {
            throw new AppException(Response::HTTP_INTERNAL_SERVER_ERROR, "The app is not start&stop correctly.");
        }

        $this->disableDataSourceLog();

        (AccessLogManager::createFromRequest($request))->log();

        $this->logLongRequest();

        self::triggerCallbacksFunc();
    }

    /**
     * @return void
     */
    public function disableDataSourceLog(): void
    {
        CSQLDataSource::$log = false;
        CRedisClient::$log   = false;
    }


    /**
     *
     * @return void
     */
    private function logLongRequest()
    {
        include $this->root_dir . "/includes/long_request_log.php";
    }

    /**
     * @return string
     */
    private function getRootDir()
    {
        return $this->root_dir;
    }

    /**
     * @param Request $request
     */
    public function setPublic(Request $request): void
    {
        // TODO [public] appfine refactoring for public routes
        $this->is_public = false; /*$this->isRequestApi($request) && $this->isRequestPublic($request);*/
    }

    public function isPublic(): bool
    {
        return $this->is_public;
    }

    /**
     * Used by AppListener
     */
    public static function initPublicEnvironment(): void
    {
        static::$readonly = true;
    }

    /**
     * Used by AppListener
     */
    public static function stopPublicEnvironment(): void
    {
        // We reset the readonly flag and recompute it.
        static::$readonly = null;
        static::isReadonly();
    }


    public static function getPerformance(): array
    {
        return static::$performance;
    }

    /**
     * NEVER CALL THIS FUNCTION.
     * Function only used for unit testing purpose.
     */
    public static function setInRip(bool $in_rip): void
    {
        if (PHP_SAPI !== 'cli') {
            throw new Exception(CAppUI::tr('CApp-Error-Do-not-call-SetInRip-out-of-tests'));
        }

        static::$in_rip = $in_rip;
    }

    /**
     * @return bool
     */
    public function isStarted(): bool
    {
        return $this->is_started;
    }

    /**
     * Used by legacy front controller
     * @return void
     */
    public function sendOfflineResponseAndDie($message)
    {
        $response = (new CMainController())->offline($message);
        echo $response->send();
        die();
    }
}
