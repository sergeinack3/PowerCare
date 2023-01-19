<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use Exception;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Request\RequestFormats;
use Ox\Core\Api\Resources\AbstractResource;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\Api\Serializers\JsonApiSerializer;
use Ox\Core\Auth\User;
use Ox\Core\Kernel\Exception\ControllerException;
use Ox\Core\Kernel\Exception\HttpException;
use Ox\Core\Module\CModule;
use Ox\Core\Vue\OxSmarty;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\CSourceHTTP;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Spatie\ArrayToXml\ArrayToXml;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Route;


class CController extends AbstractController
{
    /**
     * @param mixed $var
     *
     * @return void
     */
    public function dump($var): void
    {
        CApp::dump($var);
    }

    /**
     * @param string $content
     * @param int    $status  The response status code
     * @param array  $headers An array of response headers
     *
     * @return Response
     */
    public function renderResponse($content, $status = 200, $headers = []): Response
    {
        return new Response($content, $status, $headers);
    }

    /**
     * @param mixed $data    The response data
     * @param int   $status  The response status code
     * @param array $headers An array of response headers
     * @param bool  $json    If the data is already a JSON string
     *
     * @return JsonResponse
     */
    public function renderJsonResponse($data, $status = 200, $headers = [], $json = true): JsonResponse
    {
        return new JsonResponse($data, $status, $headers, $json);
    }

    /**
     * @param array $data
     * @param int   $status
     * @param array $headers
     * @param bool  $convert
     *
     * @return Response
     */
    public function renderXmlResponse(array $data, $status = 200, $headers = [], $convert = true): Response
    {
        if ($convert) {
            $data = ArrayToXml::convert($data, '', true, '');
        }

        $response = new Response($data, $status, $headers);
        $response->headers->set('content-type', RequestFormats::FORMAT_XML);

        return $response;
    }

    /**
     * @param AbstractResource $resource
     *
     * @param int              $status
     * @param array            $headers
     *
     * @return Response
     */
    public function renderApiResponse(
        AbstractResource $resource,
        int $status = 200,
        array $headers = [],
        bool $is_etaggable = true
    ): Response {
        // Set resource router to inject him in when transform CModelObject::getApiLink
        if ($resource->needRouter()) {
            $resource->setRouter($this->container->get('router'));
        }

        switch ($resource->getFormat()) {
            // XML
            case RequestFormats::FORMAT_XML:
                $datas    = $resource->xmlSerialize();
                $response = $this->renderXmlResponse($datas, $status, $headers);
                break;
            // JSON
            case RequestFormats::FORMAT_JSON:
            default:
                $response = $this->renderJsonResponse($resource, $status, $headers, false);
                $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);
                if ($resource->getSerializer() === JsonApiSerializer::class) {
                    $response->headers->set('content-type', RequestFormats::FORMAT_JSON_API);
                }
        }

        if ($is_etaggable) {
            $etag = $this->createEtag($resource->getDatasTransformed(), $resource->getRequestUrl());
            $response->setEtag($etag);
        }

        return $response;
    }

    private function createEtag(array $datas, $url): string
    {
        return md5(serialize($datas) . $url);
    }

    /**
     * @param string $file The file path
     *
     * @return JsonResponse|Response
     * @throws ControllerException
     */
    public function renderFileResponse($file)
    {
        if (!file_exists($file)) {
            throw new ControllerException(Response::HTTP_INTERNAL_SERVER_ERROR, 'Invalid file ' . $file);
        }

        $infos   = pathinfo($file);
        $content = file_get_contents($file);

        switch ($infos['extension']) {
            case 'json':
                return $this->renderJsonResponse($content);
            case 'html':
            case 'htm':
            default:
                return $this->renderResponse($content);
        }
    }

    protected function selfRequest(CSourceHTTP $source, string $method = 'GET', array $options = []): Response
    {
        if (!$source->active) {
            throw new ControllerException(Response::HTTP_INTERNAL_SERVER_ERROR, CAppUI::tr('access-forbidden'));
        }

        $client   = $source->getClient();
        $response = $client->request(
            $method,
            $source->host,
            array_merge(['ox-token' => $source->getToken(), $options])
        );

        $response_header = $response->getHeaders();
        $http_code       = $response->getStatusCode();
        $acq             = $response->getBody()->__toString();

        // For public routes do not send a HTTP_UNAUTHORIZED response
        if ($http_code == Response::HTTP_UNAUTHORIZED) {
            throw new ControllerException(Response::HTTP_INTERNAL_SERVER_ERROR, CAppUI::tr('access-forbidden'));
        }

        return new Response(
            $acq,
            $http_code,
            $this->prepareHeaderFromResponse($response_header)
        );
    }

    /**
     * @return string|null The module name
     * @throws Exception
     */
    public function getModuleName(): ?string
    {
        return CClassMap::getInstance()->getClassMap(static::class)->module;
    }

    /**
     * @return ReflectionClass
     * @throws ControllerException
     */
    public function getReflectionClass(): ?ReflectionClass
    {
        try {
            return new ReflectionClass($this);
        } catch (ReflectionException $e) {
            throw new ControllerException(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'Unable to construct the ReflectionClass'
            );
        }
    }

    /**
     * @param string $method_name
     *
     * @return ReflectionMethod
     * @throws ControllerException
     */
    public function getReflectionMethod($method_name): ?ReflectionMethod
    {
        try {
            return ($this->getReflectionClass())->getMethod($method_name);
        } catch (ReflectionException $e) {
            throw new ControllerException(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
        }
    }

    /**
     * Get CController onstance from attributes _controller in Request
     *
     * @param Request $request
     *
     * @return mixed
     * @throws ControllerException
     */
    public static function getControllerFromRequest(Request $request)
    {
        $_controller = $request->attributes->get('_controller');
        $_controller = explode('::', $_controller);
        $_controller = $_controller[0];
        if (!class_exists($_controller)) {
            throw new ControllerException(Response::HTTP_INTERNAL_SERVER_ERROR, "Invalid controller {$_controller}.");
        }

        return new $_controller;
    }


    /**
     * @param Route $route
     *
     * @return CController
     * @throws ControllerException
     */
    public static function getControllerFromRoute(Route $route)
    {
        $controller_name = $route->getDefault('_controller');

        $_controller = explode('::', $controller_name ?? '');
        $_controller = $_controller[0];
        if (!class_exists($_controller)) {
            throw new ControllerException(Response::HTTP_INTERNAL_SERVER_ERROR, "Invalid controller {$_controller}.");
        }

        return new $_controller;
    }

    /**
     * Get method
     *
     * @param Route $route
     *
     * @return string
     * @throws ControllerException
     */
    public static function getMethodFromRoute(Route $route)
    {
        $controller_name = $route->getDefault('_controller') ?? "";

        $_controller = explode('::', $controller_name);
        $method      = $_controller[1];
        $controller  = $_controller[0];

        if (!method_exists(new $controller, $method)) {
            throw new ControllerException(Response::HTTP_INTERNAL_SERVER_ERROR, "Invalid method {$method}.");
        }

        return $method;
    }

    /**
     * @return string
     */
    public function getRootDir(): string
    {
        return dirname(__DIR__, 2);
    }


    final protected function getApplicationUrl(): string
    {
        return rtrim(CAppUI::conf('external_url'), '/');
    }

    public function checkPermEdit(CStoredObject $object, Request $request = null): bool
    {
        $can = $object->canDo();

        return (bool)$can->edit;
    }

    public function checkPermRead(CStoredObject $object, Request $request = null): bool
    {
        $can = $object->canDo();

        return (bool)$can->read;
    }

    public function checkPermAdmin(CStoredObject $object, Request $request = null): bool
    {
        $can = $object->canDo();

        return (bool)$can->admin;
    }

    /**
     * Get and check if module is active
     *
     * @param string $mod_name
     *
     * @return string
     * @throws HttpException
     */
    protected function getActiveModule(string $mod_name): string
    {
        // dP add super hack
        if (CModule::getActive($mod_name) === null) {
            $mod_name = 'dP' . $mod_name;

            if (CModule::getActive($mod_name) === null) {
                throw new HttpException(
                    Response::HTTP_NOT_FOUND,
                    "Module '{$mod_name}' does not exists or is not active",
                );
            }
        }

        return $mod_name;
    }

    /**
     * Store item and return an API response with item in content
     *
     * @param CStoredObject $object
     * @param int           $status
     * @param array         $headers
     *
     * @return Response|null
     * @throws ApiException
     * @throws CMbException
     */
    public function storeObjectAndRenderApiResponse(
        CStoredObject $object,
        int $status = Response::HTTP_CREATED,
        array $headers = []
    ): ?Response {
        /** @var Item $item */
        if (!$item = $this->storeObject($object)) {
            return null;
        }

        return $this->renderApiResponse($item, $status, $headers);
    }

    /**
     * Store collection and return an API response with items in content
     *
     * @param CModelObjectCollection $object_collection
     * @param int                    $status
     * @param array                  $headers
     *
     * @return Response|null
     * @throws ApiException
     * @throws CMbException
     */
    public function storeCollectionAndRenderApiResponse(
        CModelObjectCollection $object_collection,
        int $status = Response::HTTP_CREATED,
        array $headers = []
    ): ?Response {
        if (!$collection = $this->storeCollection($object_collection)) {
            return null;
        }

        return $this->renderApiResponse($collection, $status, $headers);
    }

    /**
     * @throws ApiException
     * @throws CMbException
     */
    protected function storeObject(CStoredObject $object, bool $return_item = true)
    {
        if (!$this->checkPermEdit($object)) {
            throw new CMbException('Access denied');
        }

        if ($msg = $object->store()) {
            throw new CMbException($msg);
        }

        return ($return_item) ? new Item($object) : $object;
    }

    /**
     * Delete an object, throw an exception if a failure occure.
     *
     * @throws CMbException
     */
    protected function deleteObject(CStoredObject $object): void
    {
        if (!$this->checkPermEdit($object)) {
            throw new CMbException('Access denied');
        }

        if ($msg = $object->delete()) {
            throw new CMbException($msg);
        }
    }

    /**
     * @throws ApiException|CMbException
     */
    protected function storeCollection(CModelObjectCollection $collection): Collection
    {
        $objects = [];

        foreach ($collection as $object) {
            $objects[] = $this->storeObject($object, false);
        }

        return new Collection($objects);
    }

    /**
     * @todo remove
     */
    public function generateUrl(
        string $route,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        // Controller called in legacy context (index.php) so use legacy kernel instance
        if ($this->container === null) {
            return "null";
            //            return CLegacyController::generateUrl($route, $parameters, $referenceType);
        }

        return parent::generateUrl($route, $parameters, $referenceType);
    }

    protected function prepareHeaderFromResponse(array $response_headers): array
    {
        unset($response_headers['Server']);
        unset($response_headers['X-Powered-By']);
        unset($response_headers['X-Mb-RequestInfo']);
        unset($response_headers['Set-Cookie']);
        unset($response_headers['Transfer-Encoding']);

        return $response_headers;
    }


    protected function getCUser(): ?CUser
    {
        $user = parent::getUser();

        if ($user === null) {
            return null;
        }

        if (!$user instanceof User) {
            throw new Exception('Not supported');
        }

        return $user->getOxUser();
    }

    protected function getCMediusers(): ?CMediusers
    {
        $user = $this->getCUser();

        if ($user !== null) {
            $mediuser = $user->loadRefMediuser();

            if ($mediuser->_id) {
                return $mediuser;
            }
        }

        return null;
    }
}
