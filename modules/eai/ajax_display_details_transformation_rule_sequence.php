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
use Ox\Core\CView;
use Ox\Interop\Eai\Transformations\CTransformationRuleSequence;
use Ox\Interop\Hl7\CHL7v2Message;

/**
 * View transformation rules EAI
 */
CCanDo::checkAdmin();

$transformation_ruleset_id       = CValue::getOrSession('transformation_ruleset_id', 'ref class|CTransformationRuleSet', true);
$transformation_rule_sequence_id = CView::get('transformation_rule_sequence_id', 'ref class|CTransformationRuleSequence', true);
$display_type                    = CView::get('display_type', 'str default|HL7');
CView::checkin();

$xml = null;
$transf_rule_sequence = new CTransformationRuleSequence();

if ($transformation_rule_sequence_id) {
    $transf_rule_sequence->load($transformation_rule_sequence_id);
    $transf_rule_sequence->loadRefsTransformationRules();
    $transf_rule_sequence->getMessage();

    if ($transf_rule_sequence->_message instanceof CHL7v2Message) {
        $xml = $transf_rule_sequence->_message->toXML(null, true)->saveXML();
    }
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("transformation_ruleset_id", $transformation_ruleset_id);
$smarty->assign("transf_rule_sequence", $transf_rule_sequence);
$smarty->assign("display_type", $display_type);
$smarty->assign("xml", $xml);
$smarty->display("inc_display_details_transformation_rule_sequence.tpl");
