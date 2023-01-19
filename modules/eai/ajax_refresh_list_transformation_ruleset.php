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
 * View transformations EAI
 */
CCanDo::checkAdmin();

$transformation_ruleset_id = CValue::get("transformation_ruleset_id");

$transf_ruleset  = new CTransformationRuleSet();
/** @var CTransformationRuleSet[] $transf_rulesets */
$transf_rulesets = $transf_ruleset->loadList();
foreach ($transf_rulesets as $_transf_ruleset) {
  $_transf_ruleset->loadRefsTransformationRuleSequences();
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("transf_rulesets", $transf_rulesets);
$smarty->display("inc_list_transformation_ruleset.tpl");
