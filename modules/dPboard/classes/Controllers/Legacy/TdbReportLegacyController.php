<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Board\Controllers\Legacy;

use Exception;
use Ox\Core\CLegacyController;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Board\TdbReport;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Soins\Controllers\Legacy\DossierSoinsController;

class TdbReportLegacyController extends CLegacyController
{
    /**
     * @throws Exception
     */
    public function viewTransmissionReport(): void
    {
        $this->checkPermRead();

        $praticien_id = CView::get("praticien_id", "ref class|CMediusers default|" . CMediusers::get()->_id);

        CView::checkin();

        $praticien = CMediusers::findOrFail($praticien_id);
        $tdbreport = new TdbReport($praticien);

        $tdbreport->getTransmissionReport($praticien);

        $sejours    = $tdbreport->getSejours();
        $praticiens = $tdbreport->getPraticiens();

        $this->renderSmarty(
            "vw_bilan_transmissions",
            [
                "sejours"      => $sejours,
                "praticiens"   => $praticiens,
                "praticien_id" => $praticien_id,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function viewCodingReport(): void
    {
        $this->checkPermEdit();

        $date_min     = CView::get("_date_min", "date default|" . CMbDT::date("-1 day"), true);
        $date_max     = CView::get("_date_max", "date default|now", true);
        $praticien_id = CView::get("chir", "str", true);

        CView::checkin();
        CView::enableSlave();

        $praticien = CMediusers::findOrNew($praticien_id);
        $user      = CMediusers::get();

        if (!$praticien->_id && $user->isProfessionnelDeSante()) {
            $praticien_id = $user->_id;
        }

        $tdbreport = new TdbReport($user);

        $tdbreport->getCodingReport($date_min, $date_max);

        $praticiens = $tdbreport->getPraticiens();
        $filter     = $tdbreport->getFilter();
        $blocs      = (new CBlocOperatoire())->loadGroupList();

        $this->renderSmarty(
            "vw_bilan_actes_realises",
            [
                "filter"       => $filter,
                "praticiens"   => $praticiens,
                "praticien_id" => $praticien_id,
                "blocs"        => $blocs,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function viewPrescriptionReport(): void
    {
        (new DossierSoinsController())->viewBilanPrescription();
    }
}
