<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;

$prat_id = CView::get("prat_id", "num");
$date    = CView::get('date'   , "date default|now");
CView::checkin();

$visites_day = 0;
if ($prat_id && $date) {
  $ljoin = array();
  $ljoin["plageconsult"] = "plageconsult.plageconsult_id = consultation.plageconsult_id";
  $where = array();
  $where["plageconsult.date"]    = " = '$date'";
  $where["plageconsult.chir_id"] = " = '$prat_id'";
  $where["consultation.visite_domicile"] = " = '1'";
  $consultation = new CConsultation();
  $nb_consult = $consultation->countList($where, null, $ljoin);
  $visites_day = $nb_consult ? $nb_consult : 0;
}

CApp::json($visites_day, "text/plain");
