<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Status\Controllers;

use DateTime;
use Exception;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CMbConfig;
use Ox\Core\CMbPath;
use Ox\Core\CMbString;
use Ox\Core\Composer\CComposer;
use Ox\Core\Kernel\Exception\ControllerException;
use Ox\Core\Libraries\CLibrary;
use Ox\Status\StatusException;
use Ox\Status\StatusPDO;
use Ox\Status\Mappers\ConfigsMapper;
use Ox\Status\Mappers\LogMapper;
use Ox\Status\Models\PathAccess;
use Ox\Status\Models\PHPExtension;
use Ox\Status\Models\PHPVersion;
use Ox\Status\Models\UrlRestriction;
use Ox\Status\Transformers\ErrorTransformer;
use SplFileInfo;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StatusController
{
    /** @var CMbConfig $config */
    private $configs;

    /** @var StatusPDO $pdo */
    private $pdo;

    /**
     * StatusController constructor.
     *
     * @return CMbConfig
     * @throws Exception
     */
    public function getConfigs(): CMbConfig
    {
        if ($this->configs === null) {
            $this->configs = $this->loadConfigs();
        }

        return $this->configs;
    }

    /**
     * @return StatusPDO
     * @throws Exception
     */
    public function getPdo(): StatusPDO
    {
        if ($this->pdo === null) {
            $configs   = $this->getConfigs();
            $this->pdo = $this->loadStatusPDO($configs);
        }

        return $this->pdo;
    }

    /**
     * @return Response
     * @throws ControllerException|Exception
     * @api public
     */
    public function home(): Response
    {
        $file     = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'default.htm';
        $base_url = $this->getConfigs()->get('external_url');
        $endpoint = (substr($base_url, -1) === '/' ? $base_url : $base_url . '/') . 'status';
        $response = new Response(file_get_contents($file));

        // send helper endpoint to front app
        $response->headers->set('X-OXAPI-ENDPOINT', $endpoint);
        $response->setContent(str_replace('{{ox-endpoint}}', $endpoint, $response->getContent()));

        return $response;
    }

    /**
     * @return Response
     * @api
     */
    public function infos(): Response
    {
        // Unset auth infos
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            unset($_SERVER['PHP_AUTH_USER']);
        }
        if (isset($_SERVER['PHP_AUTH_PW'])) {
            unset($_SERVER['PHP_AUTH_PW']);
        }

        // start buffer
        ob_start();

        // output
        phpinfo();

        // get & clean content buffer
        $html = ob_get_clean();

        return new Response($html);
    }

    /**
     * @return JsonResponse
     * @throws ApiException
     * @api
     */
    public function requirements(): JsonResponse
    {
        // Datas
        $datas = [];

        // Php extensions
        $php_extensions          = (new PHPExtension())->getAll();
        $datas['php_extensions'] = [];
        foreach ($php_extensions as $extension) {
            $datas['php_extensions'][] = [
                'name'        => $extension->name,
                'description' => $extension->description,
                'mandatory'   => (bool)$extension->mandatory,
                'reasons'     => $extension->reasons[0],
                'check'       => (bool)$extension->check(),
            ];
        }

        // Urls restrictions
        $url_restrictions          = (new UrlRestriction())->getAll();
        $datas['url_restrictions'] = [];
        foreach ($url_restrictions as $url) {
            $datas['url_restrictions'][] = [
                'url'         => $url->url,
                'description' => $url->description,
                'check'       => (bool)$url->check(),
            ];
        }

        // Php version
        $php_version          = (new PHPVersion())->getAll();
        $datas['php_version'] = [
            'version_required'  => $php_version->name,
            'description'       => $php_version->description,
            'check'             => $php_version->check(),
            'version_installed' => $php_version->getVersionInstalled(),
        ];

        // Sql version
        $sql_version          = ($this->getPdo()->createMySqlVersion())->getAll();
        $datas['sql_version'] = [
            'version_required'  => $sql_version->name,
            'description'       => $sql_version->description,
            'check'             => $sql_version->check(),
            'version_installed' => $sql_version->getVersionInstalled(),
        ];

        // Path access
        $path_access          = (new PathAccess())->getAll();
        $datas['path_access'] = [];
        foreach ($path_access as $path) {
            $datas['path_access'][] = [
                'path'        => $path->path,
                'description' => $path->description,
                'check'       => (bool)$path->check(),
            ];
        }

        // Resource
        $resource = new Item($datas);
        $resource->setType('requirements');

        return new JsonResponse($resource, 200, [], false);
    }

    /**
     * @return JsonResponse
     * @throws ApiException
     * @api
     */
    public function libraries(): JsonResponse
    {
        // Datas
        $library = new CLibrary();
        CLibrary::init();

        $datas = [
            'check'           => CLibrary::checkAll(),
            'count_all'       => count(CLibrary::$all),
            'count_installed' => CLibrary::countLibraries(),
            'count_old'       => count(CLibrary::getOldLibraries()),
            'libraries'       => [],
        ];
        foreach ($library::$all as $library) {
            $datas['libraries'][] = [
                'name'         => $library->name,
                'description'  => $library->description,
                'url'          => $library->url,
                'license'      => $library->getLicence(),
                'distribution' => $library->fileName,
                'is_installed' => (bool)$library->isInstalled(),
                'is_uptodate'  => $library->getUpdateState(),
            ];
        }

        // Resource
        $resource = new Item($datas);
        $resource->setType('libraries');

        return new JsonResponse($resource, 200, [], false);
    }


    /**
     * @return JsonResponse
     * @throws ApiException
     * @api
     */
    public function packages(): JsonResponse
    {
        // datas
        $composer = new CComposer();
        $datas    = [
            'version'  => $composer->getVersion(),
            'urls'     => [
                'Composer'  => CComposer::URL_COMPOSER,
                'Packagist' => CComposer::URL_PACKAGIST,
                'Packages'  => CComposer::URL_PACKAGIST_PACKAGES,
            ],
            'count'    => [
                'required'  => (int)$composer->countPackages(),
                'installed' => (int)$composer->countPackagesInstalled(),
            ],
            'packages' => [],
        ];

        foreach ($composer->getPackages() as $_package) {
            $datas['packages'][] = [
                'name'              => $_package->name,
                'version_required'  => $_package->version_required,
                'version_installed' => $_package->version_installed,
                'description'       => $_package->description,
                'license'           => $_package->license,
                'is_installed'      => (bool)$_package->is_installed,
                'is_dev'            => (bool)$_package->is_dev,
            ];
        }

        // Resource
        $resource = new Item($datas);
        $resource->setType('libraries');

        return new JsonResponse($resource, 200, [], false);
    }

    /**
     * @return JsonResponse
     * @throws ApiException
     * @api
     */
    public function configs(): JsonResponse
    {
        $mapper   = new ConfigsMapper($this->getConfigs());
        $resource = new Item($mapper);
        $resource->setType('configs');

        return new JsonResponse($resource, 200, [], false);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws ApiException
     * @api
     */
    public function logs(Request $request): JsonResponse
    {
        $file        = $this->getConfigs()->get('root_dir') . '/tmp/mediboard.log';
        $request_api = RequestApi::createFromRequest($request);
        $app_logs    = [];
        if (file_exists($file)) {
            $app_logs = CMbPath::tailWithSkip($file, $request_api->getLimit(), $request_api->getOffset());
            $app_logs = explode("\n", $app_logs);
            $app_logs = array_reverse($app_logs);
        }

        $datas = [];
        foreach ($app_logs as $log) {
            $datas[] = new LogMapper($log);
        }

        $resource = new Collection($datas);
        $resource->setType('logs')
            ->setRequestUrl($request_api->getRequest()->getUri())
            ->createLinksPagination($request_api->getOffset(), $request_api->getLimit());


        return new JsonResponse($resource, 200, [], false);
    }


    /**
     * @param RequestApi $request_api
     *
     * @return JsonResponse
     * @throws ApiException
     * @api
     */
    public function errors(Request $request): Response
    {
        $request_api = RequestApi::createFromRequest($request);

        $orderby          = $request_api->getSortAsSql('error_log_id DESC');
        $limit            = $request_api->getLimit();
        $offset           = $request_api->getOffset();
        $error_logs       = $this->getPdo()->listErrors($orderby, $offset, $limit);
        $count_error_logs = $this->getPdo()->countErrors();

        $datas = [];
        foreach ($error_logs as $error) {
            $datas[] = (new ErrorTransformer($error))->transform();
        }

        $resource = new Collection($datas);
        $resource->setType('logs')
            ->setRequestUrl($request_api->getRequest()->getUri())
            ->createLinksPagination($request_api->getOffset(), $request_api->getLimit(), $count_error_logs);

        return new JsonResponse($resource, 200, [], false);
    }


    /**
     * @return JsonResponse
     * @throws ApiException
     * @api
     */
    public function bufferStatistics(): JsonResponse
    {
        $path_buffer = $this->getConfigs()->get('root_dir') . '/tmp/errors';
        $spl_info    = new SplFileInfo($path_buffer);

        $dt = new DateTime();
        $dt->setTimestamp($spl_info->getMTime());

        $files = glob($path_buffer . '/*.log');

        $datas = [
            'path'        => $path_buffer,
            'last_update' => $dt->format(DateTime::ATOM),
            'size'        => CMbString::toDecaBinary($spl_info->getSize()),
            'files_count' => count($files),
        ];

        $resource = new Item($datas);
        $resource->setType('errors_buffer');

        return new JsonResponse($resource, 200, [], false);
    }

    /**
     * @return JsonResponse
     * @throws ApiException
     * @api
     */
    public function authenticationCheck(): JsonResponse
    {
        $datas = [
            'status'  => 200,
            'message' => 'succes',
        ];

        $resource = new Item($datas);
        $resource->setType('authentication_check');

        return new JsonResponse($resource, 200, [], false);
    }

    /**
     * @return JsonResponse
     * @throws ApiException
     * @api
     */
    public function dependancesCheck(): JsonResponse
    {
        // Datas
        CLibrary::init();
        $composer = new CComposer();
        $datas    = [
            'libraries_check' => CLibrary::checkAll(),
            'packages_check'  => $composer->checkAll(),
        ];

        $resource = new Item($datas);
        $resource->setType('dependances_check');

        return new JsonResponse($resource, 200, [], false);
    }


    /**
     * @return CMbConfig
     * @throws Exception
     */
    private function loadConfigs(): CMbConfig
    {
        try {
            // legacy
            global $mbpath;
            $mbpath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR;
            $config = new CMbConfig();
            $config->load();

            return $config;
        } catch (Exception $e) {
            throw new StatusException('Unable to load Configurations.', 500);
        }
    }

    /**
     * @param CMbConfig $configs
     *
     * @return StatusPDO
     * @throws Exception
     */
    private function loadStatusPDO($configs): StatusPDO
    {
        try {
            $dbhost = $configs->get('db std dbhost');
            $dbname = $configs->get('db std dbname');
            $dbuser = $configs->get('db std dbuser');
            $dbpass = $configs->get('db std dbpass');

            return new StatusPDO($dbhost, $dbuser, $dbpass, $dbname);
        } catch (Exception $e) {
            throw new StatusException('Unable to load PDO.', 500);
        }
    }


    /**
     * Auth only the user_username 'admin' with a user_salt mdp
     * Used by StatusAuthListener
     *
     * @param Request $request
     *
     * @return bool
     * @throws StatusException
     */
    public static function doAuth(Request $request): bool
    {
        $authorization = $request->headers->get('authorization');
        if ($authorization === null || strpos($authorization, 'Basic') !== 0) {
            throw new StatusException('Authentication failed (invalid authorization)', 401);
        }

        // Basic admin:azerty
        $b64 = explode(' ', $authorization)[1];
        [$username, $password] = explode(':', base64_decode($b64));

        if ($username === null || $password === null) {
            throw new StatusException('Authentication failed (invalid credentials)', 401);
        }

        // query bd
        $admin_user = (new self())->getPdo()->getAdminUser();

        if (!$admin_user || !$admin_user->user_salt) {
            throw new StatusException('Authentication failed (invalid admin user)', 401);
        }

        $password = hash('SHA256', $admin_user->user_salt . $password);
        if ($username !== $admin_user->user_username || $password !== $admin_user->user_password) {
            throw new StatusException('Authentication failed (wrong credentials)', 401);
        }

        return true;
    }
}
