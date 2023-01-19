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
use Ox\Interop\Eai\Transformations\CTransformationRule;

/**
 * View stats transformations for transformation rule
 */
CCanDo::checkAdmin();

$transformation_rule_id = CValue::getOrSession("transformation_rule_id");

$transf_rule = new CTransformationRule();
$transf_rule->load($transformation_rule_id);

foreach ($transf_rule->loadRefsEAITransformation("actor_class ASC, actor_id ASC") as $_transformation) {
  $_transformation->loadRefActor();
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("transf_rule", $transf_rule);
$smarty->display("inc_show_stats_transformations.tpl");
