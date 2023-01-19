<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\PlanningOp\CModeEntreeSejour;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\CMergeLog;

CCanDo::checkRead();

$sejour_id       = CView::post("sejour_id", "ref class|CSejour");
$sejour_id_merge = CView::post("sejour_id_merge", "ref class|CSejour");
$praticien_id    = CView::post("praticien_id", "ref class|CMediusers");
$uf_soins_id     = CView::post("uf_soins_id", "ref class|CUniteFonctionnelle");
$mode_entree     = CView::post("mode_entree", "str");
$mode_entree_id  = CView::post("mode_entree_id", "ref class|CModeEntreeSejour");
$ATNC            = CView::post("ATNC", "bool");
$callback        = CView::post("callback", "str");

CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

$sejour->_create_affectations = false;
$sejour->_apply_sectorisation = false;

$sejour->type           = "comp";
$sejour->praticien_id   = $praticien_id;
$sejour->uf_soins_id    = $uf_soins_id;
$sejour->mode_entree    = $mode_entree;
$sejour->mode_entree_id = $mode_entree_id;
$sejour->sortie_prevue  = CMbDT::dateTime("+4 days", $sejour->entree_prevue);
$sejour->_hour_sortie_prevue = null;
$sejour->libelle        = CAppUI::tr("CDossierPerinat-accouchement");
$sejour->charge_id      = CAppUI::conf("maternite placement charge_id_dhe", $sejour->loadRefEtablissement());

if ($ATNC !== null) {
    $sejour->ATNC = $ATNC;
}

if (!$sejour->mode_entree && !$sejour->mode_entree_id) {
    $use_custom_mode_entree = CAppUI::conf("dPplanningOp CSejour use_custom_mode_entree");

    $modes_entree = CModeEntreeSejour::listModeEntree($sejour->group_id);

    if ($use_custom_mode_entree && count($modes_entree)) {
        foreach ($modes_entree as $_mode_entree) {
            if ($_mode_entree->code == "8") {
                $sejour->mode_entree_id = $_mode_entree->_id;
                break;
            }
        }
    }
    else {
        $sejour->mode_entree = "8";
    }
}

if ($sejour_id_merge) {
  $sejour_merge = new CSejour();
  $sejour_merge->load($sejour_id_merge);

  $sejour_merge->_create_affectations = false;
  $sejour_merge->_apply_sectorisation = false;

  if ($ATNC !== null) {
    $sejour_merge->ATNC = $ATNC;
  }

  foreach ($sejour_merge->loadRefsAffectations() as $_affectation) {
    $_affectation->delete();
  }

  $duree = CMbDT::daysRelative($sejour_merge->entree, $sejour_merge->sortie);
  $sejour->sortie_prevue = CMbDT::dateTime("+$duree days", $sejour->entree_prevue);

  $merge_log = CMergeLog::logStart(CUser::get()->_id, $sejour, [$sejour_merge], false);

  try {
      $sejour->merge(array($sejour_merge), false, $merge_log);
      $merge_log->logEnd();
  } catch (Throwable $t) {
      $merge_log->logFromThrowable($t);
      CAppUI::setMsg($t->getMessage(), UI_MSG_ERROR);
      echo CAppUI::getMsg();
      CApp::rip();
  }

  CAppUI::setMsg("CSejour-Sejours merged", UI_MSG_OK);
} else {
    $msg = $sejour->store();

    CAppUI::setMsg($msg ?: CAppUI::tr("CSejour-msg-modify"), $msg ? UI_MSG_ALERT : UI_MSG_OK);
}

echo CAppUI::getMsg();

CAppUI::callbackAjax($callback, $sejour->_id);
