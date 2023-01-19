<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi\Controllers;

use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CController;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CServicesController
 */
class CServicesController extends CController {
  /**
   * @param RequestApi $request_api
   *
   * @return Response
   * @throws \Ox\Core\Api\Exceptions\ApiException
   * @api
   */
  public function listServices(RequestApi $request_api): Response {
    $service = new CService();

    $ds = $service->getDS();

    $where = [
      "group_id" => $ds->prepare("= ?", CGroups::loadCurrent()->_id)
    ];

    $services = $service->loadList($where, "nom", $request_api->getLimitAsSql());

    $total = $service->countList($where);

    $resource = Collection::createFromRequest($request_api, $services);
    $resource->createLinksPagination($request_api->getOffset(), $request_api->getLimit(), $total);

    return $this->renderApiResponse($resource);
  }

  /**
   * @param RequestApi $request_api
   * @param CService    $service
   *
   * @return Response
   * @throws \Ox\Core\Api\Exceptions\ApiException
   * @api
   */
  public function showService(RequestApi $request_api, CService $service): Response {
    return $this->renderApiResponse(Item::createFromRequest($request_api, $service));
  }
}