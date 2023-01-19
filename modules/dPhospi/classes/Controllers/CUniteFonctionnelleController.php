<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi\Controllers;

use Exception;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CController;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CUniteFonctionnelleController
 */
class CUniteFonctionnelleController extends CController {
  /**
   * @param RequestApi $request_api
   * @param string      $type
   *
   * @return Response
   * @throws Exception
   * @api
   */
  public function listUfs(RequestApi $request_api, $type): Response {
    $type_sejour = $request_api->getRequest()->get("type_sejour");
    $group_id    = $request_api->getRequest()->get('sih_group_id', CGroups::loadCurrent()->_id);

    $uf = new CUniteFonctionnelle();
    $ds = $uf->getDS();

    $where = [
      'group_id' => $ds->prepare('= ?', $group_id),
      "type" => $ds->prepare("= ?", $type)
    ];

    if ($type_sejour) {
      $where["type_sejour"] = $ds->prepare("= ?", $type_sejour);
    }

    $ufs = $uf->loadList($where, $request_api->getSortAsSql(), $request_api->getLimitAsSql());

    $total = $uf->countList($where);

    $resource = Collection::createFromRequest($request_api, $ufs);
    $resource->createLinksPagination($request_api->getOffset(), $request_api->getLimit(), $total);

    return $this->renderApiResponse($resource);
  }

  /**
   * @param RequestApi         $request_api
   * @param CUniteFonctionnelle $uf
   *
   * @return Response
   * @throws ApiException
   * @api
   */
  public function showUf(RequestApi $request_api, CUniteFonctionnelle $uf): Response {
    return $this->renderApiResponse(Item::createFromRequest($request_api, $uf));
  }
}
