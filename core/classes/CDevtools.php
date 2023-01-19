<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use ErrorException;
use Exception;
use OxDevtools\Devtools;
use PDO;
use SqlFormatter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\ContextProvider\CliContextProvider;
use Symfony\Component\VarDumper\Dumper\ContextProvider\SourceContextProvider;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\Dumper\ServerDumper;

/**
 * Prepare and store data for browser devtools extension
 */
class CDevtools
{
    /** @var string */
    public const PATH_TMP = '/tmp/devtools/';

    /** @var string */
    public const REQUEST_HEADER = 'ox-devtools';

    /** @var int */
    public const QUERY_MAX_LENGTH = 10000;

    /** @var Request $request */
    private static $request;

    /** @var Response $response */
    private static $response;

    /**
     * @var int[]
     */
    public static $count = [
        'dump'   => 0,
        'error'  => 0,
        'log'    => 0,
        'query'  => 0,
        'report' => 0,
    ];

    /** @var bool Enable devtools extension mode */
    public static bool $is_active = false;

    /**
     * Add sql query in dev toolbar
     *
     * @param string $message info
     * @param string $dsn     data source
     * @param string $step    time
     * @param string $total   total time
     * @param string $query   query
     *
     * @return void
     * @throws Exception
     */
    private static function queryTrace(string $query, $time, $dsn): void
    {
        $message = "Dsn: {$dsn}, Time: {$time}";

        $query = static::truncateQuery($query);

        $html = '<div class="SqlFormater">' . SqlFormatter::format(utf8_encode($query)) . '</div>';

        CDevtools::filePutContents('query', $html, $message);
    }

    /**
     * Add queryReport to dev toolbar
     *
     * @param string $info   query signature
     * @param string $query  request
     * @param string $distri #
     *
     * @return void
     * @throws Exception
     */
    private static function queryReport(string $info, string $query, string $distri): void
    {
        $message = str_replace('^', '', $info);

        $query = static::truncateQuery($query);

        $html = '<div class="SqlFormater">' . SqlFormatter::format($query) . $distri . '</div>';

        CDevtools::filePutContents('report', $html, $message);
    }

    private static function truncateQuery($query): string
    {
        return strlen($query) > static::QUERY_MAX_LENGTH ? substr($query, 0, 100) . '...' : $query;
    }

    /**
     * @param mixed $data
     *
     * @return void
     * @throws Exception
     */
    public static function filePutContents(string $type, $data, ?string $message = null): void
    {
        $dir = static::getDir();

        $file = $dir . "/" . CApp::getRequestUID() . ".html";

        $time = CMbDT::getDateTimeFromFormat('U.u', microtime(true));
        $time = $time ? substr($time->format('H:i:s.u'), 0, 12) : null;

        $traces     = debug_backtrace();
        $trace_key  = 1;
        $file_trace = $traces[$trace_key]['file'] ?? null;
        $line_trace = $traces[$trace_key]['line'] ?? null;

        // ignore error handler trace
        if (strpos($file_trace, '/CError.php') !== false) {
            $trace_key = 2;
            $trace     = $traces[$trace_key];
            if (array_key_exists('file', $trace)) {
                $file_trace = $trace['file'];
                $line_trace = $trace['line'];
            }
        }

        $type_display = ucfirst($type);
        $file_display = str_replace(CAppUI::conf('root_dir'), '', $file_trace) . ':' . $line_trace;

        // Remote call ide
        if ($ide_url = CAppUI::conf("dPdeveloppement ide_url")) {
            $url          = str_replace("%file%", urlencode($file_trace), $ide_url) . ":$line_trace";
            $file_display = "<a target='ide-launch-iframe' href='{$url}' title='Open in IDE'>{$file_display}</a>";
        }

        $titre = "{$type_display} at {$time} in {$file_display}";

        $content = "<div><div class='titre_dump $type'>$titre";
        $content .= $message ? "<br><span>" . utf8_encode(ucfirst($message)) . "</span>" : "";
        $content .= "</div>" . $data . "</div>";
        if (file_put_contents($file, $content, FILE_APPEND)) {
            static::$count[$type]++;
        }
    }

    /**
     * @param mixed $var
     *
     * @throws ErrorException
     */
    public static function dumpInConsole($var = null): void
    {
        $cloner         = new VarCloner();
        $fallbackDumper = \in_array(\PHP_SAPI, ['cli', 'phpdbg']) ? new CliDumper() : new HtmlDumper();
        $dumper         = new ServerDumper(
            'tcp://127.0.0.1:9912',
            $fallbackDumper,
            [
                'cli'    => new CliContextProvider(),
                'source' => new SourceContextProvider(),
            ]
        );
        $dumper->dump($cloner->cloneVar($var));
    }


    /**
     * @return false|string
     * @throws Exception
     */
    public static function getJson()
    {
        if (!static::$is_active) {
            return;
        }

        // Init from CApp::performance (null if not in peace)
        $dataSourceTime       = CApp::$performance["dataSourceTime"] ?? 0;
        $nosqlTime            = CApp::$performance["nosqlTime"] ?? 0;
        $objects              = CApp::$performance['objectCounts'] ?? [];
        $cachables            = CApp::$performance['cachableCounts'] ?? [];
        $cache                = CApp::$performance['cache'] ?? ['totals' => []];
        $dataSources          = CApp::$performance['dataSources'] ?? [];
        $dataSourcesCount     = CApp::$performance['dataSourceCount'] ?? 0;
        $transport_tiers      = CApp::$performance['transportTiers'] ?? 0;
        $transport_tiers_time = CApp::$performance['transportTiers']['total']['time'] ?? 0;
        $dataSourceTime       = CApp::$performance["dataSourceTime"] ?? 0;
        $nosqlTime            = CApp::$performance["nosqlTime"] ?? 0;

        // Request (legacy compat)
        if (!static::$request) {
            // Debug upload with devtools
            $_FILES          = [];
            static::$request = Request::createFromGlobals();
        }

        $request_time_float = static::$request->server->get("REQUEST_TIME_FLOAT");
        $server_protocol    = static::$request->server->get("SERVER_PROTOCOL");
        $request_method     = static::$request->server->get("REQUEST_METHOD");
        $request_uri        = static::$request->server->get("REQUEST_URI");
        $http_host          = static::$request->server->get("HTTP_HOST");
        $server_software    = ['software' => static::$request->server->get("SERVER_SOFTWARE")];
        $request_headers    = [];
        foreach (static::$request->headers as $header_name => $header_value) {
            $request_headers[$header_name] = $header_value[0];
        }

        // Size
        $memory = CMbString::toDecaBinary(memory_get_peak_usage(true));
        $size   = CMbString::toDecaBinary(ob_get_length());

        // Response
        if (!static::$response) {
            // Legacy
            static::$response = new Response(null, http_response_code(), CApp::getResponseHeaders());
        }
        $status_code      = static::$response->getStatusCode();
        $response_headers = [];
        foreach (static::$response->headers as $header_name => $header_value) {
            $response_headers[$header_name] = $header_value[0];
        }

        // init
        $genere   = round(microtime(true) - $request_time_float, 3);
        $time     = CMbDT::getDateTimeFromFormat('U.u', $request_time_float);
        $time     = $time ? substr($time->format('H:i:s.u'), 0, 12) : '';
        $phpTime  = $genere - $dataSourceTime - $nosqlTime - $transport_tiers_time;
        $php_info = [
            'Version'    => phpversion(),
            'Extensions' => count(get_loaded_extensions()),
        ];

        if (CSQLDataSource::get('std')) {
            /** @var PDO $pdo */
            $pdo   = CSQLDataSource::get('std')->link;
            $mysql = [
                'Version' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
                'Status'  => $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS),
            ];
        }

        $os = [
            'Name'    => php_uname('s'),
            'Version' => php_uname('v'),
            'Type'    => php_uname('m'),
        ];

        $shm = [
            "Engine"  => Cache::getLayerEngine(Cache::OUTER),
            "Version" => Cache::getLayerEngineVersion(Cache::OUTER),
        ];

        $dshm = [
            "Engine"  => Cache::getLayerEngine(Cache::DISTR),
            "Version" => Cache::getLayerEngineVersion(Cache::DISTR),
        ];

        $opcache_infos = opcache_get_configuration();
        $opcache       = $opcache_infos["version"] ?? '';

        $http = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $client_ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $client_ip = $_SERVER['REMOTE_ADDR'];
        }

        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $user_agent = explode(' ', $user_agent)[0];

        $type = 'document';
        if (
            isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) {
            $type = 'xhr';
        }

        // Request
        $request = array_merge(
            [
                'Time'     => $time,
                'Protocol' => $server_protocol,
                'Method'   => $request_method,
                'Uri'      => $request_uri,
                'Path'     => $http . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
                'Agent'    => $user_agent,
                'Ip'       => $client_ip,
                'Type'     => $type,
            ],
            $request_headers
        );

        // Response
        $status   = Response::$statusTexts[$status_code] ?? 'undefined';
        $content  = isset($response_headers['Content-type']) ? explode(
            ';',
            $response_headers['Content-type']
        )[0] : 'undefined';
        $response = array_merge(
            [
                'Code'    => $status_code,
                'Status'  => $status,
                'Content' => $content,
            ],
            $response_headers
        );

        // Server
        $server = [
            'PHP'          => $php_info,
            'SERVER'       => $server_software,
            'OS'           => $os,
            'MYSQL'        => $mysql ?? '',
            'SHM'          => $shm ?? '',
            'DSHM'         => $dshm ?? '',
            'OPCODE CACHE' => $opcache,
        ];

        // Security
        $security = [
            'auth_method' => CAppUI::$instance->auth_method,
            'user_id'     => CAppUI::$instance->user_id,
        ];

        // Session
        $session  = [];
        $_SESSION = $_SESSION ?? [];
        foreach ($_SESSION as $key => $value) {
            if ($key === 'AppUI') {
                $value = 'Instance of CAppUI';
            }
            $session[$key] = $value;
        }

        // Data sources
        $data_sources = [];
        foreach ($dataSources as $key => $datas) {
            $_key                = strtoupper($key);
            $data_sources[$_key] = [
                'Stat'       => round($datas['time'] * 100 / $dataSourceTime) . '%',
                'Count'      => $datas['count'],
                'Query time' => round($datas['time'] * 1000, 3) . 'ms',
                'Fetch time' => round($datas['timeFetch'] * 1000, 3) . 'ms',
            ];
        }

        // Cache
        $cache_none  = [];
        $cache_inner = [];
        $cache_outer = [];
        $cache_distr = [];

        foreach ($cache['totals'] as $_key => $_value) {
            if ($_value['NONE'] > 0) {
                $cache_none[$_key] = $_value['NONE'];
            }
            if ($_value['INNER'] > 0) {
                $cache_inner[$_key] = $_value['INNER'];
            }
            if ($_value['OUTER'] > 0) {
                $cache_outer[$_key] = $_value['OUTER'];
            }
            if ($_value['DISTR'] > 0) {
                $cache_distr[$_key] = $_value['DISTR'];
            }
        }

        // Included files
        $included_files = get_included_files();
        $includes       = [];
        $root_dir       = CAppUI::conf("root_dir");
        foreach ($included_files as $key => $files) {
            $files            = str_replace($root_dir, '', $files);
            $key              = 'ox';
            $key              = strpos(
                $files,
                DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR
            ) !== false ? 'vendor' : $key;
            $key              = strpos(
                $files,
                DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR
            ) !== false ? 'tmp' : $key;
            $includes[$key][] = $files;
        }

        // Construct datas
        $datas = [
            'request_id'         => trim(CApp::getRequestUID()),
            'request_time_float' => $request_time_float,
            'time'               => $genere,
            'status'             => $status_code,
            'request_uri'        => $request_uri,
            'http_host'          => $http_host,
            'time_php'           => round($phpTime * 100 / $genere) . "%",
            'time_sql'           => round($dataSourceTime * 100 / $genere) . "%",
            'time_nosql'         => round($nosqlTime * 100 / $genere) . "%",
            'size'               => $size,
            'memory'             => $memory,
            'dump_count'         => static::$count['dump'],
            'error_count'        => static::$count['error'],
            'log_count'          => static::$count['log'],
            'query_count'        => $dataSourcesCount,
            'trace_count'        => array_sum(static::$count) > 0 ?: 1,
            'server'             => $server,
            'detail'             => [
                'Request'  => $request,
                'Response' => $response,
                'Security' => $security,
                'Session'  => $session,
                'Get'      => $_GET ?? [],
                'Post'     => $_POST ?? [],
                'Cookies'  => $_COOKIE ?? [],
            ],
            'performance'        => [
                'Time'            => [
                    'Total'     => $genere . "sec",
                    'Php'       => [
                        'Time' => round($phpTime * 1000, 3) . 'ms',
                        'Stat' => round($phpTime * 100 / $genere) . '%',
                    ],
                    'Sql'       => [
                        'Time' => round($dataSourceTime * 1000, 3) . 'ms',
                        'Stat' => round($dataSourceTime * 100 / $genere) . '%',
                    ],
                    'Nosql'     => [
                        'Time' => round($nosqlTime * 1000, 3) . 'ms',
                        'Stat' => round($nosqlTime * 100 / $genere) . '%',
                    ],
                    'Transport' => [
                        'Time' => round($transport_tiers_time * 1000, 3) . 'ms',
                        'Stat' => round($transport_tiers_time * 100 / $genere) . '%',
                    ],
                ],
                'Output'          => [
                    'Length' => $size,
                    'Memory' => $memory,
                ],
                'Data source'     => $data_sources,
                'Transport tiers' => $transport_tiers,
                'Objets'          => [
                    'Total'     => count($objects) + count($cachables),
                    'Loads'     => $objects,
                    'Cachables' => $cachables,
                ],
                'Included files'  => [
                    'Total'  => count($included_files),
                    'Ox'     => isset($includes['ox']) ? count($includes['ox']) : 0,
                    'Vendor' => isset($includes['vendor']) ? count($includes['vendor']) : 0,
                    'Tmp'    => isset($includes['tmp']) ? count($includes['tmp']) : 0,
                ],
                'Cache'           => [
                    'Total' => $cache['totals'],
                    'NONE'  => $cache_none,
                    'INNER' => $cache_inner,
                    'OUTER' => $cache_outer,
                    'DISTR' => $cache_distr,
                ],
            ],
        ];

        // Encode
        return static::jsonEncode($datas);
    }

    /**
     * @return false|string
     */
    private static function jsonEncode(array $datas)
    {
        // Replace null values with empty string to avoid errors on utf8_encode.
        $datas = CMbArray::mapRecursive(
            function ($data) {
                return $data === null ? '' : $data;
            },
            $datas
        );

        $datas = CMbArray::mapRecursive('utf8_encode', $datas, true);
        $json  = json_encode($datas);
        if (!$json) {
            $error_info = [
                json_last_error() => json_last_error_msg(),
            ];

            $json = json_encode($error_info);
        }

        return $json;
    }

    /**
     * @return string
     * @throws Exception
     */
    private static function getDir(): string
    {
        $root_dir = CAppUI::conf('root_dir');
        $dir      = $root_dir . static::PATH_TMP;
        CMbPath::forceDir($dir);

        return $dir;
    }

    /**
     * @return string
     * @throws Exception
     */
    public static function getDevtoolsUrl(): string
    {
        return CAppUI::conf('external_url') . static::PATH_TMP;
    }

    /**
     * @return bool|int
     * @throws Exception
     */
    public static function makeTmpFile()
    {
        // dir
        $dir = static::getDir();

        // Sets access and modification time of file
        touch($dir);

        // Purge dir
        CApp::doProbably(
            25,
            function (): void {
                static::purgeTmp();
            }
        );

        // Files
        $file_json = $dir . "/" . CApp::getRequestUID() . ".json";
        $file_html = $dir . "/" . CApp::getRequestUID() . ".html";

        // Html
        if (!file_exists($file_html)) {
            file_put_contents($file_html, "");
        }

        // Queries
        static::collectQueries();

        // Json
        $content = static::getJson();

        return file_put_contents($file_json, $content);
    }

    private static function collectQueries(): void
    {
        // Query trace
        foreach (CSQLDataSource::$log_entries as [$query, $time, $dsn]) {
            static::queryTrace($query, $time, $dsn);
        }

        // Query Report
        CSQLDataSource::buildReport(10);
        $report_output = CSQLDataSource::displayReport(null, false);

        foreach ($report_output as [$ds, $count, $time, $sample, $distribution]) {
            $info = "dsn: {$dsn}, Time: {$time}, Count: {$count}";
            static::queryReport($info, $sample, $distribution);
        }
    }

    /**
     * Garbage collector
     *
     * @return void|bool
     * @throws Exception
     */
    private static function purgeTmp()
    {
        $dir = static::getDir();

        foreach (glob($dir . '*') as $file) {
            // 1 hour === 3600 seconds
            if (time() - filectime($file) > 3600) {
                static::deleteTmpFile($file);
            }
        }
    }

    /**
     * @param string $file
     *
     * @return void
     */
    private static function deleteTmpFile(string $file): void
    {
        if (is_file($file) && strpos($file, static::PATH_TMP) !== false && strpos($file, '..') === false) {
            unlink($file);
        }
    }

    public static function init(): bool
    {
        // openxtrem/devtools is a dev requirement
        if (class_exists(Devtools::class)) {
            static::$is_active = true;
        }

        return static::$is_active;
    }

    /**
     * @return bool
     */
    public static function isActive(): bool
    {
        return static::$is_active;
    }
}
