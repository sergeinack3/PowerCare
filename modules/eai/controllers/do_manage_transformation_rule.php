<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Interop\Eai\Transformations\CTransformationRule;
use Ox\Interop\Eai\Transformations\CTransformationRuleSequence;

/**
 * Actor domain aed
 */
CCanDo::checkAdmin();

$transformation_rule_id_move = CValue::post("transformation_rule_id_move");
$direction                   = CValue::post("direction");

$transf_rule = new CTransformationRule();
$transf_rule->load($transformation_rule_id_move);

switch ($direction) {
  case "up":
    $transf_rule->rank--;
    break;

  case "down":
    $transf_rule->rank++;
    break;

  default:
}

$transf_rule_to_move                                  = new CTransformationRule();
$transf_rule_to_move->transformation_rule_sequence_id = $transf_rule->transformation_rule_sequence_id;
$transf_rule_to_move->rank                            = $transf_rule->rank;
$transf_rule_to_move->loadMatchingObject();

if ($transf_rule_to_move->_id) {
  $direction == "up" ? $transf_rule_to_move->rank++ : $transf_rule_to_move->rank--;
  $transf_rule_to_move->store();
}

$transf_rule->store();

/** @var CTransformationRuleSequence $actor */
$transf_rule_sequence = new CTransformationRuleSequence();
$transf_rule_sequence->load($transf_rule->transformation_rule_sequence_id);

/** @var CTransformationRule[] $transformation_rules */
$transformation_rules = $transf_rule_sequence->loadBackRefs("transformation_rules", "rank");

$i = 1;
foreach ($transformation_rules as $_trans_rule) {
  $_trans_rule->rank = $i;
  $_trans_rule->store();
  $i++;
}

CAppUI::stepAjax("CTransformationRule-msg-Move rank done");

CApp::rip();
