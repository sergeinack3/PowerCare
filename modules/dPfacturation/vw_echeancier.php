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

CCanDo::checkEdit();
$facture_id    = CValue::getOrSession("facture_id");
$facture_class = CValue::getOrSession("facture_class");

$facture = new $facture_class;
$facture->load($facture_id);
$facture->loadRefsEcheances();

// Creation du template
$smarty = new CSmartyDP();

$smarty->assign("facture"  , $facture);

$smarty->display("vw_echeancier");