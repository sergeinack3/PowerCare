<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbException;
use Ox\Core\CView;
use Ox\Interop\Hl7\CExchangeHL7v2;

/**
 * Define master idex missing
 */
CCanDo::checkAdmin();

$receiver_id = CView::get("receiver_id", "str");
$limit       = CView::get("limit"      , "str default|100");
$blank       = CView::get("blank"      , "bool default|1");
$sous_type   = CView::get("sous_type"  , "str");
CView::checkin();

if (!$receiver_id) {
  CAppUI::stepAjax('Veuillez indiquer un destinataire', UI_MSG_ERROR);
}

$exchange = new CExchangeHL7v2();
$where["receiver_id"] = " = '$receiver_id'";
if ($sous_type) {
  $where["sous_type"]   = " = '$sous_type'";
}

$leftjoin = array();
$leftjoin['content_tabular'] = 'exchange_hl7v2.message_content_id = content_tabular.content_id';

$pattern_sql = "\\\^&&ISO\\\^PI";
$where["content_tabular.content"] = "REGEXP '($pattern_sql)'";

CAppUI::stepAjax("Message à rejouer : ". $exchange->countList($where, null, $leftjoin));
if ($blank) {
  CAppUI::stepAjax('Essai à blanc');
  return;
}

$exchanges = $exchange->loadList($where, "date_production ASC", "$limit", null, $leftjoin);

$pattern_php = "\^&&ISO\^PI";
foreach ($exchanges as $_exchange) {
  if (!preg_match("#$pattern_php#", $_exchange->_message)) {
    CAppUI::stepAjax("Je ne dois pas passer", UI_MSG_WARNING);
    continue;
  }
  $_exchange->_message = str_replace("^&&ISO^PI", "^MBAGL&&ISO^PI", $_exchange->_message);

  try {
    $_exchange->send();
    CAppUI::stepAjax("Échange envoyé");
  }
  catch (CMbException $e) {
    $_exchange->send_datetime = "";
    $_exchange->store();
    CAppUI::stepAjax("Erreur sur l'envoi", UI_MSG_WARNING);
  }
}
