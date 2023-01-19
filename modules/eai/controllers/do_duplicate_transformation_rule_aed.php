<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Interop\Eai\Transformations\CTransformationRule;

/**
 * Duplicate an transformation to another (or the same) category
 */
CCanDo::checkAdmin();

$eai_transformation_rule_id     = CValue::post("eai_transformation_rule_id");
$transformation_ruleset_id  = CValue::post("transformation_ruleset_id");
$transformation_ruleset_dest_id = CValue::post("transformation_ruleset_dest_id");

$transf_rule = new CTransformationRule();

// On duplique toutes les règles de la catégorie
if ($transformation_ruleset_id) {
  $transf_rule->transformation_rule_sequence_id = $transformation_ruleset_id;
  /** @var $transf_rules CTransformationRule[] */
  $transf_rules = $transf_rule->loadMatchingList();

  foreach ($transf_rules as $_transf_rule) {
    $msg = $_transf_rule->duplicate($transformation_ruleset_dest_id);
    CAppUI::displayMsg($msg, "CTransformationRule-msg-create");
  }
}
// On duplique une seule règle
else {
  $transf_rule->load($eai_transformation_rule_id);

  $msg = $transf_rule->duplicate($transformation_ruleset_dest_id);
  CAppUI::displayMsg($msg, "CTransformationRule-msg-create");
}

CAppUI::js(CValue::post("callback")."()");
