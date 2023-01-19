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
use Ox\Interop\Eai\CInteropSender;
use Ox\Interop\Eai\Transformations\CTransformationRuleSequence;
use Ox\Interop\Hl7\CReceiverHL7v2;

/**
 * Edit transformaiton ruleset EAI
 */
CCanDo::checkAdmin();

$transformation_ruleset_id       = CValue::getOrSession("transformation_ruleset_id");
$transformation_rule_sequence_id = CValue::getOrSession("transformation_rule_sequence_id");

$transf_rule_sequence = new CTransformationRuleSequence();
$transf_rule_sequence->load($transformation_rule_sequence_id);

$standards = [];
foreach (CTransformationRuleSequence::STANDARDS_ALLOWED as $_standard_class) {
    $class     = new $_standard_class();
    $standards = array_merge($class->getObjects(), $standards);
}

$standards_flat = [];
foreach ($standards as $_standard_name => $_standards) {
    foreach ($_standards as $_domain_name => $_domains) {
        foreach ($_domains as $_profil_name => $_profils) {
            foreach ($_profils as $_transaction_name => $_transactions) {
                foreach ($_transactions as $_event_name => $_event) {
                    $standards_flat[] = [
                        "standard"     => $_standard_name,
                        "domain"       => $_domain_name,
                        "profil"       => $_profil_name,
                        "transaction"  => $_transaction_name,
                        "message_type" => $_event,
                    ];
                }
            }
        }
    }
}

$receiver  = new CReceiverHL7v2();
$receivers = $receiver->loadList(['actif' => " = '1' "]);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("transf_ruleset_id", $transformation_ruleset_id);
$smarty->assign("transf_rule_sequence_id", $transformation_rule_sequence_id);
$smarty->assign("transf_rule_sequence", $transf_rule_sequence);
$smarty->assign("standards", $standards);
$smarty->assign("standards_flat", $standards_flat);
$smarty->assign("receivers", $receivers);
$smarty->assign("senders", CInteropSender::getObjects());
$smarty->display("inc_edit_transformation_rule_sequence.tpl");
