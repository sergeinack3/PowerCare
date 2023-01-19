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
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Interop\Eai\Transformations\CTransformationRuleSequence;
use Ox\Interop\Hl7\CHL7v2Message;
use Ox\Interop\Hl7\CHL7v2Transformation;

/**
 * View transformation rules EAI
 */
CCanDo::checkAdmin();

$trans_rule_sequence_id = CView::get("transf_rule_sequence_id", "num");
$target = CView::get("target", "str");

CView::checkin();

$trans_rule_sequence = new CTransformationRuleSequence();
$trans_rule_sequence->load($trans_rule_sequence_id);

$profil_name   = $trans_rule_sequence->profil;

// Récupération des infos dans le message d'exemple
$hl7_message = new CHL7v2Message();
$hl7_message->parse($trans_rule_sequence->message_example);
$message_name = 'CHL7Event' . $hl7_message->event_name;
$version = $hl7_message->version;

$error = null;

// Seulement l'arbre des évènements HL7
if ($message_name && strpos($message_name, "CHL7Event") !== false) {
  $temp = explode("_", $message_name);

  $event_name = CMbArray::get($temp, 0);
  $version    = $version ? $version : CAppUI::conf("hl7 default_version");
  $extension = null;

  if (CMbArray::get($temp, 1)) {
    $extension = CAppUI::conf("hl7 default_fr_version");
  }

  $message = str_replace("CHL7Event", "", $event_name);

  if ($extension) {
    $where["extension"] = " = '$extension'";
  }

  $event_class_name = "CHL7v2Event". $message;
  $event_class = new $event_class_name();
  $messageNameXpath = '';
  if ($event_class) {
      $messageNameXpath = $event_class->event_type."_".$event_class->code;
  }

  $trans = new CHL7v2Transformation($version, $extension, $message, $messageNameXpath);
  $tree = $trans->getSegments();

  $smarty = new CSmartyDP("modules/hl7");
  $smarty->assign("profil"    , $profil_name);
  $smarty->assign("version"   , $version);
  $smarty->assign("extension" , $extension);
  $smarty->assign("message"   , $message);
  $smarty->assign("tree"      , $tree);
  $smarty->assign("target"    , $target);

  $smarty->display("inc_transformation_hl7.tpl");
}
else {
  $error = !$message_name ? "CTransformationRule-msg-choose message" : "CTransformationRule-msg-message not supported";

  $smarty = new CSmartyDP();
  $smarty->assign("error", $error);
  $smarty->display("inc_target_transformation_rule.tpl");
}

CApp::rip();
