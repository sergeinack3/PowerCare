<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Bloc\Controllers;

use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CController;
use Ox\Core\CMbDT;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Etablissement\CGroups;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CBlocOperatoireController
 */
class CBlocsOperatoiresController extends CController {
  /**
   * @param RequestApi $request_api
   *
   * @return Response
   * @api
   */
  public function listBlocs(RequestApi $request_api): Response {
    $bloc = new CBlocOperatoire();

    $ds = $bloc->getDS();

    $where = [
      "bloc_operatoire.group_id" => $ds->prepare("= ?", CGroups::loadCurrent()->_id)
    ];

    $blocs = $bloc->loadList($where, "nom", $request_api->getLimitAsSql());

    $total = $bloc->countList($where);

    $resource = Collection::createFromRequest($request_api, $blocs);
    $resource->createLinksPagination($request_api->getOffset(), $request_api->getLimit(), $total);

    return $this->renderApiResponse($resource);
  }

  /**
   * @param RequestApi     $request_api
   * @param CBlocOperatoire $bloc
   *
   * @return Response
   * @api
   */
  public function showBloc(RequestApi $request_api, CBlocOperatoire $bloc):Response {
    return $this->renderApiResponse(Item::createFromRequest($request_api, $bloc));
  }

  /**
   * @param RequestApi $request_api
   *
   * @return Response
   * @api
   */
  public function listBlocsWithPlages(RequestApi $request_api): Response {
    $month = $request_api->getRequest()->get("mois", CMbDT::transform(null, null, "%Y-%m"));

    $bloc = new CBlocOperatoire();

    $ds = $bloc->getDS();

    $where = [
      "plagesop.date" => $ds->prepareLike("$month%"),
      "bloc_operatoire.group_id" => $ds->prepare("= ?", CGroups::loadCurrent()->_id)
    ];

    $ljoin = [
      "sallesbloc" => "sallesbloc.bloc_id = bloc_operatoire.bloc_operatoire_id",
      "plagesop"   => "plagesop.salle_id = sallesbloc.salle_id"
    ];

    $blocs = $bloc->loadList($where, "nom", $request_api->getLimitAsSql(), "bloc_operatoire.bloc_operatoire_id", $ljoin);

    $total = $bloc->countList($where, "bloc_operatoire.bloc_operatoire_id", $ljoin);

    $resource = Collection::createFromRequest($request_api, $blocs);
    $resource->createLinksPagination($request_api->getOffset(), $request_api->getLimit(), $total);

    return $this->renderApiResponse($resource);
  }
}