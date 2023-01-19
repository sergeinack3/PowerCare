<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CView;
use Ox\Mediboard\Ssr\CBilanSSR;

$technicien_id           = CView::post("technicien_id", "ref class|CTechnicien");
$show_cancelled_services = CView::post("show_cancelled_services", 'bool default|0');
$service_id              = CView::post("service_id", "ref class|CService");
$date                    = CView::post("date", "date default|now");
CView::checkin();

$sejours = CBilanSSR::loadSejoursSSRfor(null, $date, $show_cancelled_services);
foreach ($sejours as $_sejour) {
  // Filtre sur service
  if ($service_id && $_sejour->service_id != $service_id) {
    unset($sejours[$_sejour->_id]);
    continue;
  }
  $bilan_ssr = $_sejour->loadRefBilanSSR();
  if (!$bilan_ssr->_id) {
    $bilan_ssr->sejour_id = $_sejour->_id;
  }
  $bilan_ssr->technicien_id = $technicien_id;
  $msg                      = $bilan_ssr->store();
  $type_store               = !$bilan_ssr->_id ? "create" : "modify";
  CAppUI::displayMsg($msg, "CBilanSSR-msg-$type_store");
}

echo CAppUI::getMsg();
CApp::rip();
