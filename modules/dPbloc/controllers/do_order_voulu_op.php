<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CValue;
use Ox\Mediboard\Bloc\CPlageOp;

/**
 * dPbloc
 */
$plageop_id = CValue::post("plageop_id");

$plageop = new CPlageOp();
$plageop->load($plageop_id);

$plageop->loadRefsOperations(false, "rank, rank_voulu, horaire_voulu", true);

foreach ($plageop->_ref_operations as $_id => $_interv) {
  if (
      !$_interv->rank &&
      !$_interv->rank_voulu &&
      !$_interv->horaire_voulu
  ) {
    unset($plageop->_ref_operations[$_id]);
  }
}

if (!empty($plageop->_ref_operations)) {
  $plageop->reorderOp(CPlageOp::RANK_VALIDATE);
}

CAppUI::stepAjax("Placement effectué");
CApp::rip();
