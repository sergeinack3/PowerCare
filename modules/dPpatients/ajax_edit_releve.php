<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CReleveRedon;

CCanDo::checkEdit();

$releve_id = CView::get("releve_id", "ref class|CReleveRedon");

CView::checkin();

$releve = new CReleveRedon();
$releve->load($releve_id);

$prev_releve = new CReleveRedon();

$where = [
  "redon_id" => "= '$releve->redon_id'",
  "date"     => "< '$releve->date'",
];

$prev_releve->loadObject($where, "date DESC");

// On simule une quantité à 0 pour que la différence soit toujours égale à la quantité observée
if ($prev_releve->vidange_apres_observation) {
  $prev_releve->qte_observee = 0;
}

$releve->_qte_diff = $releve->qte_observee - $prev_releve->qte_observee;

$qte_for_diff = $prev_releve->vidange_apres_observation ? 0 : $prev_releve->qte_observee;

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("releve", $releve);
$smarty->assign("prev_releve", $prev_releve);
$smarty->assign("qte_for_diff", $qte_for_diff);

$smarty->display("inc_edit_releve");