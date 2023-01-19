<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Eai\CInteropNorm;
use Ox\Interop\Eai\CMessageSupported;

/**
 * Refresh message supported for an actor
 */
CCanDo::checkRead();

$message_supported_id = CValue::get("message_supported_id");
$family_name          = CValue::get("family_name");
$category_name        = CValue::get("category_name");
$category_uid        = CValue::get("category_uid");
$uid                  = CValue::get("uid");

$message_supported = new CMessageSupported();
$message_supported->load($message_supported_id);
/** @var CInteropNorm $family */
$family = new $family_name();
$family->getCategoryVersions();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("_families", $family);
$smarty->assign("_message_supported", $message_supported);
$smarty->assign("_family_name"      , $family_name);
$smarty->assign("_category_name"    , $category_name);
$smarty->assign("uid"              , $uid);
$smarty->assign("category_uid"      , $category_uid);
$smarty->display("inc_active_message_supported_form.tpl");
