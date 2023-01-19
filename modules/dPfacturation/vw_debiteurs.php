<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Facturation\CDebiteur;

CCanDo::checkEdit();

$debiteur = new CDebiteur();
$debiteurs = $debiteur->loadList(null, "numero");

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("debiteurs", $debiteurs);

$smarty->display("vw_debiteurs");
