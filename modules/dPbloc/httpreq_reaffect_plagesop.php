<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CValue;
use Ox\Mediboard\Bloc\CPlageOp;

/**
 * R�affectation automatique des plages op�ratoires
 */
CCanDo::checkAdmin();

$mode_real = CValue::get("mode_real", 1);

$date = CMbDT::date();
$plage = new CPlageOp();
$where = array();
$where["plagesop.spec_repl_id"] = "IS NOT NULL";
$where["plagesop.delay_repl"]   = "IS NOT NULL";
$where[] = "`plagesop`.`date` < DATE_ADD('$date', INTERVAL `plagesop`.`delay_repl` DAY)";
$where[] = "`plagesop`.`date` >= '$date'";
$where["operations.operation_id"] = "IS NULL";
$order = "`plagesop`.`date`, `plagesop`.`debut`";
$limit = null;
$group = "plagesop.plageop_id";
$ljoin = array();
$ljoin["operations"] = "operations.plageop_id = plagesop.plageop_id AND operations.annulee = '0'";
/** @var CPlageOp[] $plages */
$plages = $plage->loadList($where, $order, $limit, $group, $ljoin);

$count = count($plages);
CAppUI::stepAjax("Lancement � '$date' en mode '$mode_real': '$count' plages trouv�es");

foreach ($plages as $_plage) {
  if ($mode_real) {
    // Suppression des interventions annul�es de cette plage pour les mettre en hors plannifi�
    foreach ($_plage->loadRefsOperations() as $_operation) {
      $_operation->plageop_id = "";
      $_operation->store();
    }
    // R�affectation de la plage
    $_plage->spec_id = $_plage->spec_repl_id;
    $_plage->chir_id = "";
    if ($msg = $_plage->store()) {
      CAppUI::stepAjax($msg, UI_MSG_ERROR);
    }
    else {
      CAppUI::stepAjax("Plage '$_plage->_id' mise � jour", UI_MSG_OK);
    }
  }
  else {
    $_plage->loadRefChir();
    $_plage->loadRefSpec();
    $_plage->loadRefSpecRepl();
    if ($_plage->chir_id) {
      $from = "Dr ".$_plage->_ref_chir->_view;
    }
    else {
      $from = $_plage->_ref_spec->_view;
    }

    $to = $_plage->_ref_spec_repl->_view;
    $msg = "plage du '$_plage->date' de '$_plage->debut' � '$_plage->fin': r�attribution de '$from' vers '$to'";
    CAppUI::stepAjax($msg, UI_MSG_OK);
  }
}

