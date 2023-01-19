<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// Object binding
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CValue;
use Ox\Mediboard\Cabinet\CPlageconsult;

$obj = new CPlageconsult();
$obj->bind($_POST);

$del    = CValue::post("del", 0);
$repeat = min(CValue::post("_repeat", 0), 100);

if ($del) {
    // Suppression des plages
    $obj->load();
    while ($repeat > 0) {
        if (!$obj->_id) {
            CAppUI::setMsg("Plage non trouvée", UI_MSG_ERROR);
        } else {
            $obj->loadRefsConsultations();
            $obj->loadRefPauses();
            if (count($obj->_ref_consultations) === count($obj->_ref_pauses)) {
                foreach ($obj->loadRefPauses() as $_pause) {
                    $_pause->delete();
                }
            }
            if ($msg = $obj->delete()) {
                CAppUI::setMsg("Plage non supprimée", UI_MSG_ERROR);
                CAppUI::setMsg("Plage du $obj->date: $msg", UI_MSG_ERROR);
            } else {
                CAppUI::setMsg("Plage supprimée", UI_MSG_OK);
            }
        }
        $repeat -= $obj->becomeNext();
    }

    CValue::setSession("plageconsult_id");
} else {
    $consultation_ids = $obj->_consultation_categorie_ids;

    if ($obj->_id) {
        // Modification des plages
        while ($repeat > 0) {
            if ($obj->_id) {
                if ($msg = $obj->store()) {
                    CAppUI::setMsg("Plage non mise à jour", UI_MSG_ERROR);
                    CAppUI::setMsg("Plage du $obj->date: $msg", UI_MSG_ERROR);
                } else {
                    CAppUI::setMsg("Plage mise à jour", UI_MSG_OK);
                }
            }
            $repeat                           -= $obj->becomeNext();
            $obj->_consultation_categorie_ids = $consultation_ids;
        }
    } else {
        // Creation des plages
        while ($repeat > 0) {
            if ($msg = $obj->store()) {
                CAppUI::setMsg("Plage non créée", UI_MSG_ERROR);
                CAppUI::setMsg("Plage du $obj->date: $msg", UI_MSG_ERROR);
            } else {
                CAppUI::setMsg("Plage créée", UI_MSG_OK);
            }
            $repeat                           -= $obj->becomeNext();
            $obj->_consultation_categorie_ids = $consultation_ids;
            $obj->_id                         = null;
        }
    }
}

if (!CValue::post('modal')) {
    CAppUI::redirect("m=$m");
} else {
    echo CAppUI::getMsg();
}

CApp::rip();
