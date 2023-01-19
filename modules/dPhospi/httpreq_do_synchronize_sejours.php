<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Mediboard\Hospi\CAffectation;

CCanDo::checkAdmin();

$affectation = new CAffectation;
$dateMin     = CValue::get("dateMin", "YYYY-MM-JJ");
$where       = array();
if ($dateMin != "YYYY-MM-JJ") {
  $where["sortie"] = ">= '$dateMin 00:00:00'";
}

$listAffectations = $affectation->loadList($where);

$entrees = 0;
$sorties = 0;

foreach ($listAffectations as &$curr_aff) {
  $curr_aff->loadRefsFwd();

  $changeSejour = 0;

  if (!$curr_aff->_ref_prev->affectation_id && $curr_aff->sejour_id) {
    if ($curr_aff->entree != $curr_aff->_ref_sejour->entree_prevue) {
      $curr_aff->_ref_sejour->entree_prevue = $curr_aff->entree;
      $changeSejour                         = 1;
      $entrees++;
    }
  }
  if (!$curr_aff->_ref_next->affectation_id && $curr_aff->sejour_id) {
    if ($curr_aff->sortie != $curr_aff->_ref_sejour->sortie_prevue) {
      $curr_aff->_ref_sejour->sortie_prevue = $curr_aff->sortie;
      $changeSejour                         = 1;
      $sorties++;
    }
  }
  if ($changeSejour) {
    if ($msg = $curr_aff->store()) {
      CAppUI::stepAjax("Erreur avec l'affectation $curr_aff->_id : $msg", UI_MSG_ERROR);
    }
  }
}

$result = $entrees + $sorties;

CAppUI::stepAjax("$result sejour(s) modifiés : $entrees entrée(s) et $sorties sortie(s)", UI_MSG_OK);

