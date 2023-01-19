<?php

namespace Ox\Mediboard\Patients\Controllers\Legacy;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\Patients\CReleveRedon;

class RedonController extends CLegacyController
{
    /**
     * Store all redon statements
     *
     * @return mixed
     * @throws Exception
     */
    public function storeAllReleveRedons()
    {
        $this->checkPermEdit();

        $releve_redons = CView::post("redons", "str");

        CView::checkin();

        $releve_redons         = json_decode(stripslashes($releve_redons), true);
        $counter_redon         = 0;
        $constante_medicale_id = null;

        foreach ($releve_redons as $_releve_redon) {
            $releve_redon                            = new CReleveRedon();
            $releve_redon->redon_id                  = $_releve_redon["redon_id"];
            $releve_redon->date                      = $_releve_redon["date"];
            $releve_redon->qte_observee              = $_releve_redon["qte_observee"];
            $releve_redon->vidange_apres_observation = $_releve_redon["vidange_apres_observation"];
            $releve_redon->constantes_medicales_id   = $constante_medicale_id;
            $releve_redon->_qte_diff                 = $_releve_redon["qte_diff"];

            if ($msg = $releve_redon->store()) {
                CAppUI::stepAjax($msg, UI_MSG_ERROR);

                return $msg;
            }

            if (!$constante_medicale_id) {
                $constante_medicale_id = $releve_redon->constantes_medicales_id;
            }

            $counter_redon++;
        }

        CAppUI::stepAjax(CAppUI::tr("CReleveRedon-msg-create") . " x $counter_redon");
    }
}
