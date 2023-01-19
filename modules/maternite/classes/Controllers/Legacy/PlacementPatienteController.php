<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Maternite\Controllers\Legacy;

use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CModeEntreeSejour;
use Ox\Mediboard\PlanningOp\CSejour;

class PlacementPatienteController extends CLegacyController
{
    public function pecPatiente(): void
    {
        $this->checkPermEdit();

        $sejour_id = CView::get("sejour_id", "ref class|CSejour");

        CView::checkin();

        $modes_entree = CModeEntreeSejour::listModeEntree();

        $sejour = new CSejour();
        $sejour->load($sejour_id);
        $sejour->loadRefEtablissementProvenance();

        if (!$sejour->_id) {
            $sejour->mode_entree = "8";

            foreach ($modes_entree as $_mode_entree) {
                if ($_mode_entree->code == "8") {
                    $sejour->mode_entree_id = $_mode_entree->_id;
                    break;
                }
            }
        }

        $patient = $sejour->loadRefPatient();
        $patient->loadLastGrossesse();

        $consult                    = new CConsultation();
        $consult->_ref_chir         = new CMediusers();
        $consult->_datetime         = CMbDT::dateTime();
        $consult->_active_grossesse = 1;
        $consult->sejour_id         = $sejour->_id;
        $consult->patient_id        = $patient->_id;
        $consult->grossesse_id      = $patient->_ref_last_grossesse->_id;

        $consult->loadRefPatient();
        $consult->loadRefGrossesse();
        $consult->loadRefSuiviGrossesse();

        $curr_user = CMediusers::get();

        if ($curr_user->isPraticien() || $curr_user->isSageFemme()) {
            $consult->_prat_id  = $curr_user->_id;
            $consult->_ref_chir = $curr_user;
        }

        $services = CService::loadServicesObstetrique(false);

        if ($first_service = reset($services)) {
            $sejour->service_id = $first_service->_id;
        }

        $show_sejour = false;

        if ($sejour->_id || ($consult->_ref_chir->_id && $consult->_ref_chir->_ref_function->create_sejour_consult)) {
            $show_sejour = true;
        }

        $terme_min = CMbDT::date("- " . CAppUI::gconf("maternite CGrossesse min_check_terme") . " DAYS");
        $terme_max = CMbDT::date("+ " . CAppUI::gconf("maternite CGrossesse max_check_terme") . " DAYS");

        $this->renderSmarty(
            'inc_pec_patiente',
            [
                'consult'      => $consult,
                'sejour'       => $sejour,
                'services'     => $services,
                'show_sejour'  => $show_sejour,
                'terme_min'    => $terme_min,
                'terme_max'    => $terme_max,
                'modes_entree' => $modes_entree,
            ]
        );
    }
}
