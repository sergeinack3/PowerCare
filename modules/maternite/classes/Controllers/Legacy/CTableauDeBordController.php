<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Maternite\Controllers\Legacy;

use Ox\Core\CLegacyController;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;

class CTableauDeBordController extends CLegacyController
{
    /**
     * Affiche les consultations dans le tableau de bord
     * @throws \Exception
     */
    public function ajax_tdb_consultations()
    {
        $this->checkPermRead();

        $date             = CView::get("date", "date default|now");
        $show_all_consult = CView::get("show_all_consult", "bool default|0");

        CView::checkin();
        $group = CGroups::loadCurrent();

        $consultation = new CConsultation();

        $where                                 = [];
        $where["consultation.grossesse_id"]    = "IS NOT NULL";
        $where["consultation.annule"]          = "= '0'";
        $where["plageconsult.date"]            = "= '$date'";
        $where["functions_mediboard.group_id"] = "= '$group->_id'";

        $ljoin                        = [];
        $ljoin["plageconsult"]        = "plageconsult.plageconsult_id = consultation.plageconsult_id";
        $ljoin["users_mediboard"]     = "plageconsult.chir_id = users_mediboard.user_id";
        $ljoin["functions_mediboard"] = "functions_mediboard.function_id = users_mediboard.function_id";

        $curr_user = CMediusers::get();
        if ($curr_user->isSageFemme() && !$show_all_consult) {
            $where["plageconsult.chir_id"] = CSQLDataSource::prepareIn(
                CMbArray::pluck($curr_user->loadListFromType(["Sage Femme"]), "_id")
            );
        }

        /** @var CConsultation[] $listConsults */
        $listConsults = $consultation->loadList($where, "heure ASC", null, null, $ljoin);

        $plages = CStoredObject::massLoadFwdRef($listConsults, "plageconsult_id");
        CStoredObject::massLoadFwdRef($plages, "chir_id");
        CStoredObject::massLoadFwdRef($listConsults, "sejour_id");
        $grossesses = CStoredObject::massLoadFwdRef($listConsults, "grossesse_id");
        $patientes  = CStoredObject::massLoadFwdRef($grossesses, "parturiente_id");
        CStoredObject::massLoadBackRefs($patientes, "bmr_bhre");

        foreach ($listConsults as $_consult) {
            $_consult->loadRefPraticien();
            $_consult->loadRefPlageConsult();
            $_consult->loadRefSejour()->loadRefGrossesse();
            $_consult->loadRefGrossesse()->loadRefParturiente()->updateBMRBHReStatus($_consult);
        }


        $this->renderSmarty(
            "inc_tdb_consultations.tpl",
            [
                "date"             => $date,
                "listConsults"     => $listConsults,
                'show_all_consult' => $show_all_consult,

            ]
        );
    }
}
