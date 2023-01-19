<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\CompteRendu\CTemplateManager;

/**
 * field template selector modal
 */
CCanDo::checkRead();

$object_class = CValue::get("class");

/** @var $object CMbObject */
$object = new $object_class;

$template = new CTemplateManager();
$object->fillTemplate($template);

$tr_patient = CAppUI::tr('CPatient');
if (isset($template->sections[$tr_patient])) {
  unset($template->sections[$tr_patient][$tr_patient." - ".CAppUI::tr('CPatient-last name')]);
  unset($template->sections[$tr_patient][$tr_patient." - ".CAppUI::tr('CPatient-birth name')]);
  unset($template->sections[$tr_patient][$tr_patient." - ".CAppUI::tr('CPatient-first name')]);
}

// Smarty
$smarty = new CSmartyDP();

$smarty->assign("template", $template);
$smarty->assign("class",    $object_class);

$smarty->display("vw_fields_template_selector");

