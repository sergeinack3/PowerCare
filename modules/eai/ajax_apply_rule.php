<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCando;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Eai\Transformations\CTransformation;
use Ox\Interop\Eai\Transformations\CTransformationRule;
use Ox\Interop\Hl7\CHL7v2Message;

CCanDo::checkAdmin();

$rule_id = CView::get('rule_id', 'ref class|CTransformationRule');
CView::checkin();

$rule = new CTransformationRule();
$rule->load($rule_id);

$content = $rule->loadRefTransformationRuleSequence()->message_example;
// Vérification en entrée
CTransformation::wellFormedContent($content);
$content = $rule->apply($content);

// Vérification en sortie
CTransformation::wellFormedContent($content, 'output');

$hl7_message = new CHL7v2Message();
$hl7_message->parse($content);

$smarty = new CSmartyDP();
$smarty->assign('hl7_message', $hl7_message);
$smarty->assign('rule', $rule);
$smarty->display('inc_apply_rule');
