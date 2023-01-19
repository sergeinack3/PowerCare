<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CPack;

/**
 * Interface des packs de documents
 */
CCanDo::checkRead();

$filtre = new CPack();

$filtre->user_id      = CValue::getOrSession("user_id");
$filtre->function_id  = CValue::getOrSession("function_id");
$filtre->object_class = CValue::getOrSession("filter_class");

$filtre->loadRefOwner();

$classes = CCompteRendu::getTemplatedClasses();

$module = CModule::getActive("dPcompteRendu");
$is_admin = $module && $module->canAdmin();
$access_function = $is_admin || CAppUI::gconf("dPcompteRendu CCompteRenduAcces access_function");

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("classes"        , $classes);
$smarty->assign("filtre"         , $filtre);
$smarty->assign("access_function", $access_function);

$smarty->display("vw_idx_packs");
