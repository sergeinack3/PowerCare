<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbException;
use Ox\Core\CView;
use Ox\Interop\Hl7\CExchangeHL7v2;
use Ox\Mediboard\Hospi\CAffectation;

CCanDo::checkAdmin();

$receiver_id      = CView::get("receiver_id", "str");
$limit            = CView::get("limit"      , "str default|100");
$blank            = CView::get("blank"      , "bool default|1");
$sous_type        = CView::get("sous_type"  , "str");
$value_practicien = CView::get("value_practicien"  , "str");
$partner          = CView::get("partner"  , "str default|WEB100T");
CView::checkin();

if (!$receiver_id) {
  CAppUI::stepAjax('Veuillez indiquer un destinataire', UI_MSG_ERROR);
}

if (!$value_practicien) {
  CAppUI::stepAjax('Veuillez indiquer la valeur du praticien à rechercher', UI_MSG_ERROR);
}

if ($partner !== "WEB100T" && $partner !== "HM") {
  CAppUI::stepAjax('Le partenaire passé en paramètre est incorrecte. Valeurs souhaitées : WEB100T ou HM', UI_MSG_ERROR);
}

$exchange = new CExchangeHL7v2();
$where["receiver_id"] = " = '$receiver_id'";
if ($sous_type) {
  $where["sous_type"]   = " = '$sous_type'";
}

$leftjoin = array();
$leftjoin['content_tabular'] = 'exchange_hl7v2.message_content_id = content_tabular.content_id';

// Pour Web100T = value_practicien\\\^\\\^\\\^\\\^\\\^\\\^\\\^\\\^WEB100T&&L\\\^L\\\^\\\^\\\^RI\\\^\\\^\\\^\\\^\\\^\\\^\\\^\\\^\\\^\\\^
// Pour HM      = value_practicien\\\^\\\^\\\^\\\^\\\^\\\^\\\^\\\^&&\\\^L\\\^\\\^\\\^RI\\\^\\\^\\\^\\\^\\\^\\\^\\\^\\\^\\\^\\\^

if ($partner == "WEB100T") {
  $pattern_sql = "$value_practicien\\\^\\\^\\\^\\\^\\\^\\\^\\\^\\\^WEB100T&&L\\\^L\\\^\\\^\\\^RI\\\^\\\^\\\^\\\^\\\^\\\^\\\^\\\^\\\^\\\^";
}
elseif ($partner == "HM") {
  $pattern_sql = "$value_practicien\\\^\\\^\\\^\\\^\\\^\\\^\\\^\\\^&&\\\^L\\\^\\\^\\\^RI\\\^\\\^\\\^\\\^\\\^\\\^\\\^\\\^\\\^\\\^";
}

$where["content_tabular.content"] = "REGEXP '($pattern_sql)'";

CAppUI::stepAjax("Message à rejouer : ". $exchange->countList($where, null, $leftjoin));
if ($blank) {
  CAppUI::stepAjax('Essai à blanc');
  return;
}

$exchanges = $exchange->loadList($where, "date_production ASC", "$limit", null, $leftjoin);

// Pattern final pour HM et WEB100T = 10100842904^DUPUY JAMAL^Adel^^^^^^ASIP-SANTE-PS&1.2.250.1.71.4.2.1&ISO^L^^^RPPS
// Pattern message WEB100T = h.joly^^^^^^^^WEB100T&&L^L^^^RI^^^^^^^^^^
// Pattern message HM      = 98M007^^^^^^^^&&^L^^^RI^^^^^^^^^^

if ($partner == "WEB100T") {
  $pattern_message = "\|\|".$value_practicien."\^\^\^\^\^\^\^\^WEB100T&&L\^L\^\^\^RI\^\^\^\^\^\^\^\^\^\^";
  $pattern_message_2 = "||".$value_practicien."^^^^^^^^WEB100T&&L^L^^^RI^^^^^^^^^^";
}
elseif ($partner == "HM") {
  $pattern_message = "\|\|".$value_practicien."\^\^\^\^\^\^\^\^&&\^L\^\^\^RI\^\^\^\^\^\^\^\^\^\^";
  $pattern_message_2 = "||".$value_practicien."^^^^^^^^&&^L^^^RI^^^^^^^^^^";
}

foreach ($exchanges as $_exchange) {
  $pattern_php = "^^^^^^ASIP-SANTE-PS&1.2.250.1.71.4.2.1&ISO^L^^^RPPS";

  $target = $_exchange->loadTargetObject();
  if (!$target->_id) {
    $_exchange->delete();
    continue;
  }

  $sejour = null;
  switch ($target->_class) {
    case "CSejour":
      $sejour = $target;
      break;
    case "CAffectation":
      /** @var CAffectation $target */
      $sejour = $target->loadRefSejour();
      break;
    default;
  }

  if (!$sejour || !$sejour->_id) {
    CAppUI::stepAjax("Impossible de récupérer le séjour", UI_MSG_WARNING);
    continue;
  }

  $praticien = $sejour->loadRefPraticien();
  if (!$praticien->rpps) {
    CAppUI::stepAjax("Pas de RPPS pour le praticien $praticien->_view. Séjour : $sejour->_id", UI_MSG_WARNING);
    continue;
  }

  // Ajout des deux pipes du début + info praticien + fin du PV1.7
  $pattern_php = "||".$praticien->rpps."^".$praticien->_user_last_name."^".$praticien->_user_first_name.$pattern_php;

  if (!preg_match("#$pattern_message#", $_exchange->_message)) {
    CAppUI::stepAjax("Je ne dois pas passer", UI_MSG_WARNING);
    continue;
  }

  $_exchange->_message = str_replace($pattern_message_2, $pattern_php, $_exchange->_message);
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
