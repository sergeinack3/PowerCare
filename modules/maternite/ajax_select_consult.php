<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;

CCanDo::checkEdit();

$patient_id = CView::get("patient_id", "ref class|CPatient");

CView::checkin();

$consult = new CConsultation();

$ljoin = [
  "plageconsult" => "plageconsult.plageconsult_id = consultation.plageconsult_id"
];

$where = [
  "patient_id" => "= '$patient_id'",
  "date"       => "= '" . CMbDT::date() . "'",
  "grossesse_id" => "IS NOT NULL"
];

$consults = $consult->loadList($where, "heure", null, null, $ljoin);

foreach ($consults as $_consult) {
  $_consult->canDo();
  $_consult->loadRefPraticien();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("consults", $consults);

$smarty->display("inc_select_consult");
