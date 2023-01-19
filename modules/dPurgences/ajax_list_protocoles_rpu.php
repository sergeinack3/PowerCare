<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Urgences\CProtocoleRPU;

CCanDo::checkEdit();

$protocole_rpu = new CProtocoleRPU();

$protocoles_rpu = $protocole_rpu->loadGroupList();

CStoredObject::massLoadFwdRef($protocoles_rpu, "responsable_id");
CStoredObject::massLoadFwdRef($protocoles_rpu, "uf_soins_id");
CStoredObject::massLoadFwdRef($protocoles_rpu, "charge_id");
CStoredObject::massLoadFwdRef($protocoles_rpu, "box_id");
CStoredObject::massLoadFwdRef($protocoles_rpu, "mode_entree_id");

/** @var CProtocoleRPU $_protocole_rpu */
foreach ($protocoles_rpu as $_protocole_rpu) {
  $_protocole_rpu->loadRefUfSoins();
  $_protocole_rpu->loadRefCharge();
  $_protocole_rpu->loadRefResponsable()->loadRefFunction();
  $_protocole_rpu->loadRefBox();
  $_protocole_rpu->loadRefModeEntree();
}

// Création de template
$smarty = new CSmartyDP();

$smarty->assign("protocoles_rpu", $protocoles_rpu);

$smarty->display("inc_list_protocoles_rpu");