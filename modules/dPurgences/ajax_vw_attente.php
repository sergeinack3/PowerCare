<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Urgences\CRPU;

$attente      = CValue::get("attente");
$rpu_id       = CValue::get("rpu_id");
$pec_inf      = CValue::get("pec_inf");
$type_attente = CValue::get("type_attente");

// Chargement du rpu
$rpu = new CRPU();
$rpu->load($rpu_id);
$rpu->loadRefsAttentes();
$rpu->loadRefsLastAttentes();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("rpu", $rpu);

if ($pec_inf) {
  $smarty->display("inc_vw_rpu_pec_inf");
}
elseif (!$attente) {
  $smarty->display("inc_vw_rpu_attente");
}
else {
  $smarty->assign("type_attente", $type_attente);
  $smarty->assign("attente", $rpu->_ref_last_attentes[$type_attente]);
  $smarty->display("inc_vw_fin_attente");
}
