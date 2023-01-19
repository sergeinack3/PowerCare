<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// Recuperation de l'id de l'acte CCAM
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\SalleOp\CActeCCAM;

$acte_ccam_id = CValue::getOrSession("acte_ccam_id");

// Chargement de l'acte CCAM
$acte = new CActeCCAM();
$acte->load($acte_ccam_id);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("acte_ccam", $acte);
$smarty->display("inc_vw_reglement_ccam");
