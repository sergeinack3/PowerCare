<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Mediboard\Facturation\CEcheance;
use Ox\Mediboard\Facturation\CEditJournal;
use Ox\Mediboard\Facturation\CEditPdf;
use Ox\Mediboard\Facturation\CFacture;
use Ox\Mediboard\Facturation\CRelance;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();
$facture_class  = CValue::get("facture_class");
$facture_id     = CValue::get("facture_id");
$relance_id     = CValue::get("relance_id");
$echeance_id    = CValue::get("echeance_id");
$prat_id        = CValue::get("prat_id");
$date_min       = CValue::get("_date_min", CMbDT::date());
$date_max       = CValue::get("_date_max", CMbDT::date());
$type_relance   = CValue::get("type_relance");
$type_pdf       = CValue::get("type_pdf", "bvr");
$factures       = CValue::get("factures", array());
$echeances      = CValue::get("echeances", array());
$tiers_soldant  = CValue::get("tiers_soldant", 0);
$no_printed     = CValue::get("no_printed", 0);

//impression
$factures_id = array();
foreach ($factures as $value) {
  $factures_id[$value] = $value;
}

$echeances_id = array();
foreach ($echeances as $_echeance_id) {
  $echeances_id[$_echeance_id] = $_echeance_id;
}

if ($type_pdf == "relance" && !$facture_class) {
  $relance = new CRelance();
  $relance->load($relance_id);
  $facture_class = $relance->object_class;
}

$factures = array();
$facture = new $facture_class;
//si on a une facture_id on la charge
if ($facture_id) {
  $facture->load($facture_id);
  $factures[$facture_id] = $facture;
}
elseif (count($factures_id)) {
  $where = array();
  $where["facture_id"] = CSQLDataSource::prepareIn(array_keys($factures_id));
  $factures = $facture->loadList($where);
}
else {
  $where = array();
  $where["praticien_id"] = " = '$prat_id'";
  $where["cloture"]      = "BETWEEN '$date_min' AND '$date_max'";
  $factures = $facture->loadList($where, "facture_id DESC", null, "facture_id");
}

$facture_pdf = new CEditPdf();
$facture_pdf->factures = $factures;

$echeance = new CEcheance();
$echeances = $echeance->loadList(
  array(
    "echeance_id" => $echeances_id ? CSQLDataSource::prepareIn($echeances_id) : "= '$echeance_id'",
  ),
  "date ASC"
);
$facture_pdf->echeances = $echeances;

foreach ($facture_pdf->factures as $key_facture => $the_facture) {
  if ($no_printed == 1 && (($the_facture->bill_date_printed && ($type_pdf == "BVR_TS" || $type_pdf == "bvr" || $type_pdf == "bvr_justif"))
    || ($the_facture->justif_date_printed && ($type_pdf == "BVR_TS" || $type_pdf == "justificatif" || $type_pdf == "bvr_justif")))) {
    unset($facture_pdf->factures[$key_facture]);
    continue;
  }
}

if ($type_pdf == "bvr") {
  $facture_pdf->editFactureBVR();
}
elseif ($type_pdf == "bvr_TS") {
  $facture_pdf->editFactureBVR("TS");
}
elseif ($type_pdf == "justificatif") {
  $facture_pdf->editJustificatif();
}
elseif ($type_pdf == "bvr_justif") {
  $facture_pdf->editFactureBVRJustif();
}
elseif ($type_pdf == "impression") {
  $facture_pdf->printBill($tiers_soldant);

  $journal_pdf = new CEditJournal();
  $journal_pdf->type_pdf = "debiteur";
  $journal_pdf->factures = $factures;
  foreach ($journal_pdf->factures as $fact) {
    /** @var CFacture $fact */
    $fact->loadRefsObjects();
    $fact->loadRefPatient();
    $fact->loadRefPraticien();
    $fact->loadRefsReglements();
    $fact->isRelancable();
  }
  $journal_pdf->editJournal(false);

  $journal_pdf->type_pdf = "checklist";
  $journal_pdf->editJournal(false);

  if (!$facture_id) {
    unset($_GET["suppressHeaders"]);
  }
}
elseif ($type_pdf == "relance") {
  $relance = new CRelance();
  $relance->load($relance_id);
  if ($relance->_id) {
    $facture_pdf->factures = array($relance->loadRefFacture());
  }
  $facture_pdf->relance = $relance;
  $facture_pdf->editRelance();
}