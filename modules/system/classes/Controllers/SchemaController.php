<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Controllers;

use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CController;
use Ox\Core\CMbException;
use Ox\Core\CModelObject;
use Ox\Core\Kernel\Routing\RouteManager;
use Symfony\Component\HttpFoundation\Response;

class SchemaController extends CController
{
    /**
     * @param RequestApi $request
     *
     * @param string     $resource_type
     *
     * @return Response
     * @throws ApiException
     * @throws CMbException
     * @api
     */
    public function models(RequestApi $request, string $resource_type)
    {
        $model = CModelObject::getClassNameByResourceType($resource_type);
        if (!class_exists($model)) {
            throw new CMbException('Class does not exists ' . $model);
        }
        if (!is_subclass_of($model, CModelObject::class)) {
            throw new CMbException('Class does not extends CModelObject ' . $model);
        }

        /** @var CModelObject $instance */
        $instance = new $model();
        $schema   = $instance->getSchema($request->getFieldsets());

        $resource = new Collection($schema);
        $resource->setType('schema');

        return $this->renderApiResponse($resource);
    }

    /**
     * @param string $method
     * @param string $path (base_64 encoded)
     *
     * @return Response
     * @throws CMbException
     * @throws ApiException
     * @api
     */
    public function routes($method, $path): Response
    {
        $path = base64_decode($path);
        if (!$path[0] === '/') {
            $path = '/' . $path;
        }
        $route_manager = new RouteManager();
        $documentation = $route_manager->getOAS();

        if (!array_key_exists($path, $documentation['paths'])) {
            throw new CMbException('Undefined path in OAS : ' . $path);
        }

        $oas = $documentation['paths'][$path][$method];

        $resource = new Item($oas);
        $resource->setType('route_schema');

        return $this->renderApiResponse($resource);
    }
}
