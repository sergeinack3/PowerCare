<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Soins\CChungScore;

CCanDo::checkRead();

$sejour_id      = CView::get("sejour_id", "ref class|CSejour");
$chung_score_id = CView::get("chung_score_id", "ref class|CChungScore");
$digest         = CView::get("digest", "bool");

CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$chung_score = new CChungScore();
if (!$chung_score->load($chung_score_id)) {
  $patient = $sejour->loadRefPatient();
  $sejour->loadRefLastOperation(true);

  $chung_score->sejour_id = $sejour_id;
  $chung_score->datetime  = CMbDT::dateTime();

  $end_operation = CMbDT::addDateTime($sejour->_ref_last_operation->temp_operation, $sejour->_ref_last_operation->_datetime);
  if ($sejour->_ref_last_operation->fin_op) {
    $end_operation = $sejour->_ref_last_operation->fin_op;
  }
  list($constantes_perop, $dates_perop) = CConstantesMedicales::getLatestFor(
    $patient,
    $chung_score->datetime,
    array("EVA", "pouls", "ta", "frequence_respiratoire", "douleur_en"),
    $sejour,
    true,
    $end_operation
  );

  if ($constantes_perop->EVA !== null) {
    $chung_score->pain = "0";
    if ($constantes_perop->EVA < 4) {
      $chung_score->pain = "2";
    }
    elseif ($constantes_perop->EVA >= 4 && $constantes_perop->EVA <= 6) {
      $chung_score->pain = "1";
    }
  }
  elseif ($constantes_perop->douleur_en != null) {
    $chung_score->pain = "0";
    if ($constantes_perop->douleur_en < 4) {
      $chung_score->pain = "2";
    }
    elseif ($constantes_perop->douleur_en >= 4 && $constantes_perop->douleur_en <= 6) {
      $chung_score->pain = "1";
    }
  }

  list($constantes_preop, $dates_preop) = CConstantesMedicales::getLatestFor(
    $patient,
    $sejour->_ref_last_operation->_datetime,
    array("EVA", "pouls", "ta", "frequence_respiratoire"),
    $sejour
  );

  if ($constantes_preop->ta
    && $constantes_perop->ta
    && $constantes_preop->pouls
    && $constantes_perop->pouls
    && $constantes_preop->frequence_respiratoire
    && $constantes_perop->frequence_respiratoire
  ) {
    $chung_score->vital_signs = "0";
    $ta_preop                 = explode("|", $constantes_preop->ta);
    $ta_perop                 = explode("|", $constantes_perop->ta);
    $ta_preop_moy             = ($ta_preop[0] + 2 * $ta_preop[1]) / 3;
    $ta_perop_moy             = ($ta_perop[0] + 2 * $ta_perop[1]) / 3;
    $variation_ta             = abs(($ta_preop_moy - $ta_perop_moy) / $ta_preop_moy);
    $variation_pouls          = abs(($constantes_preop->pouls - $constantes_perop->pouls) / $constantes_preop->pouls);
    $variation_freq_respi     = abs(
      ($constantes_preop->frequence_respiratoire - $constantes_perop->frequence_respiratoire)
      / $constantes_preop->frequence_respiratoire
    );

    if ($variation_ta < 0.2 && $variation_pouls < 0.2 && $variation_freq_respi < 0.2) {
      $chung_score->vital_signs = "2";
    }
    elseif ($variation_ta < 0.4 && $variation_pouls < 0.4 && $variation_freq_respi < 0.4) {
      $chung_score->vital_signs = "1";
    }
  }

  $chung_score->total = intval($chung_score->vital_signs)
    + intval($chung_score->activity)
    + intval($chung_score->nausea)
    + intval($chung_score->pain)
    + intval($chung_score->bleeding);
}

$smarty = new CSmartyDP();
$smarty->assign("sejour", $sejour);
$smarty->assign("chung_score", $chung_score);
$smarty->assign("digest", $digest);
$smarty->display("inc_edit_chung_score");