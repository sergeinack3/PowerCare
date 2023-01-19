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
use Ox\Interop\Eai\Transformations\CTransformationRuleSet;

/**
 * Edit transformaiton ruleset EAI
 */
CCanDo::checkAdmin();

$transformation_ruleset_id = CValue::getOrSession("transformation_ruleset_id");

$transf_ruleset = new CTransformationRuleSet();
$transf_ruleset->load($transformation_ruleset_id);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("transf_ruleset", $transf_ruleset);
$smarty->display("inc_edit_transformation_ruleset.tpl");
