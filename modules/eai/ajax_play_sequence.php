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
use Ox\Interop\Eai\Transformations\CTransformationRuleSequence;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\CHL7v2Message;
use \Ox\Core\CAppUI;

CCanDo::checkAdmin();

$sequence_id = CView::get('sequence_id', 'ref class|CTransformationRuleSequence');
CView::checkin();

$sequence = new CTransformationRuleSequence();
$sequence->load($sequence_id);

$content = $sequence->message_example;
// Vérification en entrée de la chaine
CTransformation::wellFormedContent($content);

/** @var CTransformationRule $_rule */
foreach ($sequence->loadRefsTransformationRules(['active' => " = '1' "]) as $_rule) {
    $content = $_rule->apply($content);
}

// Vérification en sortie que la chaîne est bien un message HL7
CTransformation::wellFormedContent($content, 'output');

$hl7_message = new CHL7v2Message();
$hl7_message->parse($content);

$smarty = new CSmartyDP();
$smarty->assign('sequence', $sequence);
$smarty->assign('hl7_message', $hl7_message);
$smarty->display('inc_apply_rule');
