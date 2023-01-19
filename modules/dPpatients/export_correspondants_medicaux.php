<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Mediusers\CMediusers;

$medecin_nom         = CValue::getOrSession("nom");
$medecin_prenom      = CValue::getOrSession("prenom");
$medecin_function_id = CValue::getOrSession("function_id");
$medecin_cp          = CValue::getOrSession("cp");
$medecin_ville       = CValue::getOrSession("ville");
$medecin_type        = CValue::getOrSession("type", "medecin");
$medecin_disciplines = CValue::getOrSession("disciplines");

$current_user = CMediusers::get();
$is_admin     = $current_user->isAdmin();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("is_admin", $is_admin);
$smarty->assign("medecin_nom", $medecin_nom);
$smarty->assign("medecin_prenom", $medecin_prenom);
$smarty->assign("medecin_function_id", $medecin_function_id);
$smarty->assign("medecin_cp", $medecin_cp);
$smarty->assign("medecin_ville", $medecin_ville);
$smarty->assign("medecin_type", $medecin_type);
$smarty->assign("medecin_disciplines", $medecin_disciplines);
$smarty->display("export_correspondants_medicaux.tpl");
