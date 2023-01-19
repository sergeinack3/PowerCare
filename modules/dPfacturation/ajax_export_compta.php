<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Mediboard\Facturation\CFactureEtablissement;

$out = fopen('php://output', 'w');
header('Content-Type: application/csv');
header('Content-Disposition: attachment; filename="ExportCompta.xls"');

$facture_class = CValue::get("facture_class", 'CFactureEtablissement');
$factures_id = CValue::get("factures", array());
$factures_id = explode("|", $factures_id);

$where = array();
$where["facture_id"] = CSQLDataSource::prepareIn(array_values($factures_id));

$facture = new $facture_class;
$factures = $facture->loadList($where);

// Ligne d'entête
$fields = array();
$fields[] = "Date";
$fields[] = "Facture";
$fields[] = "Patient";
$fields[] = "Débiteur";
$fields[] = "Montant";

fputcsv($out, $fields, ';');

foreach ($factures as $_facture) {
  /* @var CFactureEtablissement $_facture*/
  $_facture->loadRefPatient();
  $_facture->loadRefsObjects();
  $_facture->loadRefsReglements();
  $_facture->loadRefAssurancePatient();
  $fields = array();
  $fields["Date"]     = CMbDT::format($_facture->cloture, CAppUI::conf("date"));
  $fields["Facture"]  = $_facture->_id;
  $fields["Patient"]  = $_facture->_ref_patient;
  $fields["Débiteur"] = $_facture->_assurance_patient_view;
  $fields["Montant"]  = sprintf("%.2f", $_facture->_montant_avec_remise);
  fputcsv($out, $fields, ';');
}
