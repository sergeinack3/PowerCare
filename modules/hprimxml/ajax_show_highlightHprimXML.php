<?php
/**
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Hprimxml\CHPrimXMLDocument;
use Ox\Interop\Hprimxml\CHPrimXMLEvenementsPatients;
use Ox\Interop\Hprimxml\CHPrimXMLEvenementsServeurEtatsPatient;
use Ox\Interop\Hprimxml\Event\CHPrimXMLEventPatient;
use Ox\Interop\Hprimxml\Event\CHPrimXMLEventServeurActivitePmsi;

$message      = CView::post('message', 'str');
$message_type = CView::post('message_type', 'str');
CView::checkin();

$message = utf8_decode(stripslashes($message));

if ($message_type === 'patients') {
    /** @var CHPrimXMLEvenementsPatients $dom_evt */
    $dom_evt = CHPrimXMLEventPatient::getHPrimXMLEvenements($message);
}

if ($message_type === 'pmsi') {
    /** @var CHPrimXMLEvenementsServeurEtatsPatient $dom_evt */
    $dom_evt = CHPrimXMLEventServeurActivitePmsi::getHPrimXMLEvenements($message);
}

$treehprimxml = CHPrimXMLDocument::parse($dom_evt);

$smarty = new CSmartyDP();
$smarty->assign('message', $message);
$smarty->assign('treehprimxml', $treehprimxml);
$smarty->display('inc_highlightHprimXML.tpl');
