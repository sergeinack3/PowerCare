<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Ssr\CEvenementSSR;

CCanDo::checkRead();
$sejour_id      = CView::get("sejour_id", "ref class|CSejour");
$type_seance    = CView::get("_type_seance", "enum list|dediee|non_dediee|collective default|dediee");
$_days          = CView::get("_days", "str");
$_sejours_guids = CView::get("_sejours_guids", "str");
$_sejours_guids = json_decode(utf8_encode(stripslashes($_sejours_guids)), true);
$date           = CView::get("date", "date default|now", true);
$_heure_deb     = CView::get("_heure_deb", "time");
$duree          = CView::get("duree", "num");
CView::checkin();

CAccessMedicalData::logAccess("CSejour-$sejour_id");

$monday    = CMbDT::date("last monday", CMbDT::date("+1 day", $date));
$heure_fin = CMbDT::time("+ $duree minutes", $_heure_deb);
$days      = array();
foreach ($_days as $_number) {
  $days[] = CMbDT::date("+$_number DAYS", $monday);
}

$seance_ssr_patient = new CEvenementSSR();
list($sql, $conflits) = $seance_ssr_patient->searchConflits($sejour_id, $days, $_heure_deb, $heure_fin, false);

if ($type_seance == "collective" && is_countable($_sejours_guids) && count($_sejours_guids)) {
  $sejours_ids = array();
  foreach ($_sejours_guids as $sejour_guid => $_sejour) {
    if ($_sejour["checked"] == 1) {
      list($class, $sejour_checked_id) = explode('-', $sejour_guid);
      $sejours_ids[] = $sejour_checked_id;
    }
  }

  $where               = array();
  $where[]             = $sql;
  $where[]             = "sejour_id " . CSQLDataSource::prepareIn($sejours_ids);
  $seance_ssr_patient  = new CEvenementSSR();
  $conflits_collectifs = $seance_ssr_patient->loadList($where, "debut", null, "evenement_ssr_id");
  foreach ($conflits_collectifs as $_evt_ssr) {
    $evt_fin = CMbDT::date($_evt_ssr->debut) . " " . $heure_fin;
    if (!($_evt_ssr->debut < $evt_fin && $_evt_ssr->_heure_fin > $_heure_deb)) {
      unset($conflits_collectifs[$_evt_ssr->_id]);
    }
  }
  $conflits = array_merge($conflits, $conflits_collectifs);
}

/* @var CEvenementSSR[] $conflits */
foreach ($conflits as $_conflit) {
  $_conflit->loadRefSejour()->loadRefPatient();
  $_conflit->loadRefPrescriptionLineElement();
}

$smarty = new CSmartyDP("modules/ssr");

$smarty->assign("conflits", $conflits);

$smarty->display("vw_alert_conflit_planification");
