<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Controllers;

use Exception;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CController;
use Ox\Core\CModelObject;
use Ox\Core\CStoredObject;
use Ox\Core\Kernel\Exception\ControllerException;
use Symfony\Component\HttpFoundation\Response;


class HistoryController extends CController
{
    /**
     * @param RequestApi $request_api
     * @param string     $resource_type
     * @param int        $resource_id
     *
     * @return Response
     * @throws Exception
     * @api
     */
    public function list(string $resource_type, int $resource_id): Response
    {
        $object   = $this->getObjectFromRequirements($resource_type, $resource_id);
        $resource = new Collection($object->_ref_logs);

        return $this->renderApiResponse($resource);
    }

    /**
     * @param RequestApi $request_api
     *
     * @param string     $resource_type
     * @param int        $resource_id
     * @param int        $history_id
     *
     * @return Response
     * @throws ApiException
     * @throws ControllerException
     * @api
     */
    public function show(RequestApi $request_api, string $resource_type, int $resource_id, int $history_id): Response
    {
        $object  = $this->getObjectFromRequirements($resource_type, $resource_id);
        $history = $object->_ref_logs;

        $log_expected = null;
        foreach ($history as $log) {
            if ((int)$log->_id === (int)$history_id) {
                $log_expected = $log;
                break;
            }
        }

        if ($log_expected === null) {
            throw new ControllerException(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'Invalid resource identifiers.',
                [],
                2
            );
        }

        if ($request_api->getRequest()->query->getBoolean('loadResource')) {
            /** @var CStoredObject $target */
            $target   = $object->loadListByHistory($history_id);
            $resource = new Item($target);
            $resource->setType($resource_type);
        } else {
            $resource = new Item($log);
            $resource->setModelRelations('all');
        }

        return $this->renderApiResponse($resource);
    }

    /**
     * @param string $resource_type
     * @param int    $resource_id
     *
     * @return CStoredObject
     * @throws ControllerException|Exception
     */
    private function getObjectFromRequirements(string $resource_type, int $resource_id)
    {
        $object_class = CModelObject::getClassNameByResourceType($resource_type);
        /** @var CStoredObject $object */
        $object = new $object_class;
        $object->load($resource_id);

        if (!$object->_id) {
            throw new ControllerException(Response::HTTP_INTERNAL_SERVER_ERROR, 'Invalid resource identifiers.', [], 1
            );
        }

        $object->loadLogs();

        return $object;
    }
}
