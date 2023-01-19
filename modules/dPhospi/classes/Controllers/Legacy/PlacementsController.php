<?php
/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi\Controllers\Legacy;

use Exception;
use Ox\Core\CLegacyController;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\Repository\ServiceRepository;
use Ox\Mediboard\Hospi\Services\RegulationService;
use Ox\Mediboard\Mediusers\Repository\FunctionsRepository;
use Ox\Mediboard\Mediusers\Repository\MediusersRepository;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Placements tab managing
 */
class PlacementsController extends CLegacyController
{
    /**
     * Show the regulation view
     *
     * @return void
     * @throws Exception
     */
    public function regulationView(): void
    {
        $this->checkPermRead();

        $date_min     = CView::get("date_regulation", ["dateTime", "default" => CMbDT::dateTime("-24 hours")]);
        $services_id  = CView::get("services_id", "str", true);
        $types        = CView::get("type", "str", true);
        $praticien_id = CView::get("praticien_id", "ref class|CMediusers", true);
        $function_id  = CView::get("function_id", "ref class|CFunctions", true);
        $type_log     = CView::get("type_log", "enum list|create|store", true);
        $see_results  = CView::get("see_results", "bool default|0");

        CView::checkin();
        CView::enforceSlave();

        $services_select = explode(",", $services_id);
        CMbArray::removeValue("", $services_select);
        $types = explode(",", $types);
        CMbArray::removeValue("", $types);

        $filter               = new CSejour();
        $filter->_date_min    = $date_min;
        $filter->praticien_id = $praticien_id;

        if ($see_results) {
            $regulation_service = new RegulationService($type_log, $date_min, $praticien_id, $function_id, $services_select, $types);
            $sejours = $regulation_service->getSejoursByUserAction();
        } else {
            $service_repository = new ServiceRepository();
            $services           = $service_repository->findAllNotCancelledWithPerms();

            $mediusers_repository = new MediusersRepository();
            $praticiens           = $mediusers_repository->findAllPracticioner();
            foreach ($praticiens as $_prat) {
                $_prat->loadRefFunction();
            }

            $functions_repository  = new FunctionsRepository();
            $functions = $functions_repository->findAllSpecialties();
        }

        $tpl_name = "vw_regulation";

        if ($see_results) {
            $tpl_name = "vw_list_regulation";

            $params = [
                "sejours"  => $sejours,
                "date_min" => $date_min,
                "date_max" => $date_max,
            ];
        } else {
            $params = [
                "services"    => $services,
                "praticiens"  => $praticiens,
                "filter"      => $filter,
                "functions"   => $functions,
                "function_id" => $function_id,
                "services_id" => $services_select,
                "types"       => $types,
                "type_log"    => $type_log,
            ];
        }

        $this->renderSmarty(
            $tpl_name,
            $params
        );
    }
}
