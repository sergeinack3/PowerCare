<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbSecurity;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Interop\Hl7\CExchangeHL7v2;
use Ox\Interop\Hl7\CHL7v2Message;
use Ox\Mediboard\System\CContentTabular;

CCanDo::checkAdmin();

$sender_guid = CView::get('sender_guid', 'str');
$blank       = CView::get('blank', 'bool');
$reprocess   = CView::get('reprocess', 'num default|1');
$limit       = CView::get('limit', 'num default|30');
$message_id  = CView::get('message_id', 'str');
$date_min    = CView::get("date_min", ["dateTime", "default" => CMbDT::dateTime('-7 day')], true);
$date_max    = CView::get("date_max", ["dateTime", "default" => CMbDT::dateTime('+1 day')], true);
CView::checkin();

if (!$sender_guid) {
    CAppUI::stepAjax("Paramètres manquants", UI_MSG_ERROR);
}

$sender = CMbObject::loadFromGuid("$sender_guid");
if (!$sender || !$sender->_id) {
    CAppUI::stepAjax("Impossible de charger le connecteur", UI_MSG_ERROR);
}

$exchange                     = new CExchangeHL7v2();
$where                        = [];
$where['sender_id']           = " = '$sender->_id' ";
$where['sender_class']        = " = '$sender->_class' ";
$where['statut_acquittement'] = " = 'AR' ";
$where['reprocess']           = " < '$reprocess' ";
$where['type']                = " = 'DEC' ";
$where['sous_type']           = " = 'PCD01' ";
if ($message_id) {
    $where['exchange_hl7v2_id'] = " = '$message_id' ";
}
$where['date_production'] = "  BETWEEN '$date_min' AND '$date_max' ";

$exchanges       = $exchange->loadList($where, null, $limit);
$exchanges_total = $exchange->countList($where);

CApp::log($exchanges_total . ' échanges ont été retrouvés');
CApp::log(count($exchanges) . ' échanges vont être traités sur cette requête');

if ($blank) {
    CApp::log('Essai à blanc terminé');

    return;
}

$charset = array_merge(range('A', 'Z'), range(0, 9));
$charset = array_values(array_diff($charset, CMbSecurity::AMBIGUOUS_CHARACTERS));

CStoredObject::massLoadFwdRef($exchanges, 'message_content_id');

foreach ($exchanges as $_exchange) {
    CApp::log('Echange récupéré : ' . $_exchange->_id);
    CApp::log('Date de l\'échange : ' . $_exchange->date_production);

    $hl7_message = $_exchange->getMessage();
    if (!$hl7_message) {
        // On store reprocess pour ne pas les récupérer en boucle avec l'outil
        $_exchange->reprocess++;
        $_exchange->store();
        continue;
    }

    /** @var CContentTabular $content_tabular */
    $content_tabular = $_exchange->loadFwdRef('message_content_id');
    if (!$content_tabular || !$content_tabular->_id) {
        // On store reprocess pour ne pas les récupérer en boucle avec l'outil
        $_exchange->reprocess++;
        $_exchange->store();
        continue;
    }

    $xml             = $hl7_message->toXML(null, true);
    $node            = $xml->queryNode('//OBR.18');
    $node->nodeValue = CMbSecurity::getRandomAlphaNumericString($charset, 20);

    $hl7_message_new = new CHL7v2Message();
    $hl7_message_new->parse($xml->toER7($hl7_message));

    $content_tabular->content = $hl7_message_new->data;
    CApp::log('Résultat de l\'enregistrement du nouveau contenu : ' . $content_tabular->store());

    $_exchange->_message = $content_tabular->content;
    CApp::log('Echange modifié : ' . $_exchange->_id);

    $_exchange->reprocessing();
}

CAppUI::js("nextOru()");
