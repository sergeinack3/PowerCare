<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Facturation\CDebiteur;

CCanDo::checkEdit();
$debiteur_id = CValue::get("debiteur_id");

$debiteur = new CDebiteur();
$debiteur->load($debiteur_id);

// Creation du template
$smarty = new CSmartyDP();

$smarty->assign("debiteur",  $debiteur);
$smarty->assign("debiteur_dec",  CValue::get("debiteur_desc", 0));

$smarty->display("vw_edit_debiteur");
