<?php

/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Bloc\Controllers;

use DateTime;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CController;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Mediusers\CMediusers;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CPlagesOpsController
 */
class CPlagesOpsController extends CController
{
    /**
     * @param RequestApi $request_api
     *
     * @return Response
     * @api
     */
    public function listPlages(RequestApi $request_api): Response
    {
        $date    = $request_api->getRequest()->get("date");
        $bloc_id = $request_api->getRequest()->get("bloc");
        $chir_id = $request_api->getRequest()->get("chir");

        $plage = new CPlageOp();

        $ds = $plage->getDS();

        $where   = [];
        $ljoin   = [];
        $groupby = null;

        if ($date) {
            $begin = (new DateTime($date))->modify("first day of this month")->format("Y-m-d");
            $end   = (new DateTime($date))->modify("last day of this month")->format("Y-m-d");

            $now = (new DateTime())->format('Y-m-d');

            if ($begin < $now) {
                $begin = $now;
            }

            $where["plagesop.date"] = $ds->prepare("BETWEEN ?1 AND ?2", $begin, $end);
        }

        if ($bloc_id) {
            $where["sallesbloc.bloc_id"] = $ds->prepare("= ?", $bloc_id);
            $ljoin["sallesbloc"]         = "sallesbloc.salle_id = plagesop.salle_id";

            $groupby = "plagesop.plageop_id";
        }

        if ($chir_id) {
            $chir    = CMediusers::get($chir_id);
            $where[] = $ds->prepare("plagesop.chir_id = ? ", $chir_id) . " OR " . $ds->prepare(
                "plagesop.spec_id = ?",
                $chir->function_id
            );
        }

        $plages = $plage->loadList($where, "date ASC", $request_api->getLimitAsSql(), $groupby, $ljoin);

        foreach ($plages as $_plage) {
            $this->formatPlage($_plage);
        }

        $total = $plage->countList($where, $groupby, $ljoin);

        $resource = Collection::createFromRequest($request_api, $plages);

        $resource->createLinksPagination($request_api->getOffset(), $request_api->getLimit(), $total);

        return $this->renderApiResponse($resource);
    }

    /**
     * @param RequestApi $request_api
     * @param CPlageOp    $plage
     *
     * @return Response
     * @api
     */
    public function showPlage(RequestApi $request_api, CPlageOp $plage): Response
    {
        $this->formatPlage($plage);

        return $this->renderApiResponse(Item::createFromRequest($request_api, $plage));
    }

    /**
     * Formattage de propriétés d'une plage
     *
     * @param CPlageOp $plage
     */
    private function formatPlage(CPlageOp $plage): void
    {
        $plage->multicountOperations();

        // Temps des interventions
        $time_ops = $plage->_fill_time;

        $hours = floor($time_ops / 3600);
        $mins  = floor($time_ops / 60 % 60);

        $plage->_time_ops = sprintf('%02d:%02d:00', $hours, $mins);

        $plage->_fill_rate = floor($plage->_fill_rate);
    }
}
