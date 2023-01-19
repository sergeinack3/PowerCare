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
use Ox\Interop\Eai\Transformations\CTransformationRuleSequence;

/**
 * Edit transformaiton rule EAI
 */
CCanDo::checkAdmin();

$transformation_rule_sequence_id = CValue::getOrSession("transformation_rule_sequence_id");
$transformation_rule_id          = CValue::getOrSession("transformation_rule_id");

$transf_rule = new CTransformationRule();
$transf_rule->load($transformation_rule_id);

$transf_rule_sequence = new CTransformationRuleSequence();
$transf_rule_sequence->load($transformation_rule_sequence_id);
$transformation_ruleset_id = $transf_rule_sequence->transformation_ruleset_id;

// Liste des actions paramétrables
$action_params = array('trim', 'sub', 'pad', 'map', 'insert');

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("transf_rule", $transf_rule);
$smarty->assign("action_params", $action_params);
$smarty->assign("transf_rule_sequence_id", $transformation_rule_sequence_id);
$smarty->assign("transformation_ruleset_id", $transformation_ruleset_id);

$smarty->display("inc_edit_transformation_rule.tpl");
