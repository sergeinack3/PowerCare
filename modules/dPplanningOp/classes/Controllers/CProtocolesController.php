<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp\Controllers;


use Exception;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CController;
use Ox\Core\CMbArray;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CProtocole;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CProtocolesController
 */
class CProtocolesController extends CController
{
    /**
     * @param RequestApi $request_api
     *
     * @return Response
     * @throws Exception
     * @api
     */
    public function listProtocoles(RequestApi $request_api): Response
    {
        $libelle    = utf8_decode($request_api->getRequest()->get("libelle"));
        $chir_id    = $request_api->getRequest()->get("chir_id", CMediusers::get()->_id);
        $for_sejour = $request_api->getRequest()->get('for_sejour', 0);

        $protocole = new CProtocole();
        $ds        = $protocole->getDS();

        $where = [
            'libelle' . ($for_sejour ? '_sejour' : null) => $ds->prepareLike("%$libelle%"),
        ];

        $chir = CMediusers::get($chir_id);

        $functions_ids   = CMbArray::pluck($chir->loadRefsSecondaryFunctions(), "function_id");
        $functions_ids[] = $chir->function_id;

        $where[] = "chir_id " . $ds->prepare("= ?", $chir_id)
            . " OR function_id " . $ds->prepareIn($functions_ids)
            . " OR group_id " . $ds->prepare("= ?", CGroups::loadCurrent()->_id);

        if ($for_sejour) {
            $where['for_sejour'] = "= '1'";
        }

        $protocoles = $protocole->loadList($where, $request_api->getSortAsSql(), $request_api->getLimitAsSql());

        $total = $protocole->countList($where);

        $resource = Collection::createFromRequest($request_api, $protocoles);
        $resource->createLinksPagination($request_api->getOffset(), $request_api->getLimit(), $total);

        return $this->renderApiResponse($resource);
    }

    /**
     * @param RequestApi $request_api
     * @param CProtocole  $protocole
     *
     * @return Response
     * @throws ApiException
     * @api
     */
    public function showProtocole(RequestApi $request_api, CProtocole $protocole): Response
    {
        return $this->renderApiResponse(Item::createFromRequest($request_api, $protocole));
    }
}
