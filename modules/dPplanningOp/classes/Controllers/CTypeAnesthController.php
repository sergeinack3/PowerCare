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
use Ox\Core\Api\Resources\Item;
use Ox\Core\CController;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CTypeAnesth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CTypeAnesthController
 */
class CTypeAnesthController extends CController {
  /**
   * @param RequestApi $request_api
   *
   * @return Response
   * @throws \Ox\Core\Api\Exceptions\ApiException
   * @api
   */
  public function listTypesAnesth(RequestApi $request_api): Response {
    $type_anesth = new CTypeAnesth();

    $group_id = CGroups::loadCurrent()->_id;
    $ds       = $type_anesth->getDS();

    $where = [
      "group_id" => $ds->prepare("= ?", $group_id) . " OR group_id IS NULL",
      "actif"    => "= '1'"
    ];

    $types_anesth = $type_anesth->loadList($where, $request_api->getSortAsSql(), $request_api->getLimitAsSql());

    $total = $type_anesth->countList($where);

    $resource = Collection::createFromRequest($request_api, $types_anesth);
    $resource->createLinksPagination($request_api->getOffset(), $request_api->getLimit(), $total);

    return $this->renderApiResponse($resource);
  }

  /**
   * @param RequestApi $request_api
   * @param CTypeAnesth $type_anesth
   *
   * @return Response
   * @throws \Ox\Core\Api\Exceptions\ApiException
   * @api
   */
  public function showTypeAnesth(RequestApi $request_api, CTypeAnesth $type_anesth): Response {
    return $this->renderApiResponse(Item::createFromRequest($request_api, $type_anesth));
  }
}
