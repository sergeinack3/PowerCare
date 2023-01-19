<?php

/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp\Controllers;

use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\CController;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\CompteRendu\CTemplateManager;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\FieldsSIH;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class COperationsController
 */
class COperationsController extends CController
{
    use FieldsSIH;

    /**
     * @param RequestApi $request_api
     * @param CPlageOp    $plage
     *
     * @return Response
     * @throws \Ox\Core\Api\Exceptions\ApiException
     * @api
     */
    public function listOperationsForPlage(RequestApi $request_api, CPlageOp $plage): Response
    {
        $operations = $plage->loadRefsOperations(false);

        $resource = Collection::createFromRequest($request_api, $operations);

        return $this->renderApiResponse($resource);
    }

    /**
     * @param RequestApi $request_api
     *
     * @return Response
     * @api
     */
    public function getFields(RequestApi $request_api): Response
    {
        $operation = COperation::findOrNew($request_api->getRequest()->get('operation_id'));

        $template = new CTemplateManager();

        if (!$operation->_id) {
            $template->valueMode = false;
        }

        $operation->fillTemplate($template);

        $fields = $this->computeFields($template->sections);

        return $this->renderJsonResponse(json_encode($fields));
    }
}
