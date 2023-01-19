<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CValue;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

$function_id = CValue::get("function_id");
$date        = CValue::get("date");
$start       = CMbDT::date("this monday", $date);
if ($start > $date) {
  $start = CMbDT::date("last monday", $date);
}
$end          = CMbDT::date("next sunday", $start);

$muser = new CMediusers();
$musers = $muser->loadProfessionnelDeSanteByPref(PERM_READ, $function_id);

$function = new CFunctions();
$function->load($function_id);

echo "<h1>".$function->_view." (".CMbDT::format($start, CAppUI::conf('longdate'))." - ".CMbDT::format($end, CAppUI::conf('longdate')).")</h1>";

$pconsult = new CPlageconsult();
$ds = $pconsult->getDS();
$where = array();
$where[] = "chir_id ".$ds->prepareIn(array_keys($musers))." OR remplacant_id ".$ds->prepareIn(array_keys($musers));
$where["date"] = " BETWEEN '$start' AND '$end' ";

/** @var CPlageconsult[] $pconsults */
$pconsults = $pconsult->loadList($where, "date", null, "chir_id");

$pconsults_by_date_and_prat = array();

if (!count($pconsults)) {
  echo "<div class='small-info'>Les praticiens de ce cabinet n'ont pas de plages de consultations sur cette période</div>";
  CApp::rip();
}

foreach ($pconsults as $_pc) {
  unset($_GET["chirSel"]);
  $_pc->loadRefChir();
  $_pc->loadRefRemplacant();
  echo "<h2>";
  echo $_pc->_ref_chir->_view;
  if ($_pc->remplacant_id) {
    echo CAppUI::tr("CConsultation.replaced_by")." : ".$_pc->_ref_remplacant->_view;
  }
  echo "</h2>";

  $params = array(
    "debut"     => $date,
    "chirSel"   => $_pc->chir_id,
    "print"     => 1,
    "show_free" => 1
  );

  echo CApp::fetch("dPcabinet", "ajax_vw_planning", $params);
  echo "<hr class=\"pagebreak\">";
}