<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CMbDT;
use Ox\Core\CMbPdf;
use Ox\Core\CValue;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Facturation\CFactureCabinet;
use Ox\Mediboard\Facturation\CEditPdf;
use Ox\Mediboard\Facturation\CFacture;
use Ox\Mediboard\Facturation\CRelance;
use Ox\Mediboard\Files\CFile;

CCanDo::checkEdit();
$_date_min      = CValue::post("_date_min");
$_date_max      = CValue::post("_date_max");
$type_relance   = CValue::post("type_relance", 0);
$facture_class  = CValue::post("facture_class");
$chirSel        = CValue::post("chir");

if ($_date_min) {
  /* @var CFactureCabinet $facture*/
  $facture  = new $facture_class;

  $where = array();
  if ($facture_class == "CFactureEtablissement") {
    $where["temporaire"] = " = '0'";
  }
  if (($chirSel && $facture_class == "CFactureEtablissement") || $facture_class == "CFactureCabinet") {
    $where["praticien_id"] =" = '$chirSel' ";
  }
  $where[] = "(du_patient <> '0' AND patient_date_reglement IS NULL)
            || (du_tiers <> '0' AND tiers_date_reglement IS NULL)";
  $where[] = "cloture IS NOT NULL AND cloture <= '$_date_max'";
  $where[] = "NOT EXISTS (
    SELECT * FROM `facture_relance`
    WHERE `facture_relance`.`object_id` = ".$facture->_spec->table.".`facture_id`
    AND facture_relance.object_class = '$facture_class'
    AND facture_relance.numero >= '$type_relance'
  )";
  $where["no_relance"] = " = '0'";
  $where["annule"]     = " = '0'";
  $where["extourne"]   = " = '0'";

  $factures_ids = $facture->loadIds($where, "cloture DESC", null, "facture_id");

  $nb_generate_pdf_relance = CAppUI::gconf("dPfacturation CRelance nb_generate_pdf_relance");
  $factures = array();
  foreach ($factures_ids as $facture_id) {
    if (count($factures) >= $nb_generate_pdf_relance) {
      continue;
    }
    /* @var CFacture $_facture*/
    $_facture = new $facture_class;
    $_facture->load($facture_id);
    $_facture->loadRefPatient();
    $_facture->loadRefsObjects();
    $_facture->loadRefsReglements();
    $_facture->loadRefsRelances();
    $_facture->isRelancable();
    $not_exist_objets = !count($_facture->_ref_consults) && !count($_facture->_ref_sejours);
    if (!$_facture->_is_relancable || count($_facture->_ref_relances)+1 < $type_relance || $not_exist_objets) {
      continue;
    }
    else {
      $factures[$facture_id] = $_facture;
    }
  }

  if (count($factures)) {
    $patient_ids = array();
    $facture_pdf = new CEditPdf();
    $facture_pdf->pdf = new CMbPdf('P', 'mm');
    $facture_pdf->pdf->setPrintHeader(false);
    $facture_pdf->pdf->setPrintFooter(false);
    $facture_pdf->font = "vera";
    $facture_pdf->fontb = $facture_pdf->font."b";
    foreach ($factures as $_facture) {
      $relance = new CRelance();
      $relance->object_id    = $_facture->_id;
      $relance->object_class = $_facture->_class;
      if ($msg = $relance->store()) {
        return $msg;
      }

      $facture_pdf->facture = $_facture;
      $facture_pdf->patient = $facture_pdf->facture->_ref_patient;
      $facture_pdf->facture->_ref_patient->loadRefsCorrespondantsPatient();
      $facture_pdf->praticien = $facture_pdf->facture->loadRefPraticien();
      $facture_pdf->facture->loadRefAssurance();
      $facture_pdf->function_prat = $facture_pdf->praticien->loadRefFunction();
      $facture_pdf->group = $facture_pdf->function_prat->loadRefGroup();

      $facture_pdf->relance = $relance;
      $facture_pdf->editRelancePdf();
      $patient_ids[$facture_pdf->patient->_id] = $_facture->_id;
    }

    $num_frais[1] = CAppUI::gconf("dPfacturation CRelance add_first_relance");
    $num_frais[2] = CAppUI::gconf("dPfacturation CRelance add_second_relance");
    $num_frais[3] = CAppUI::gconf("dPfacturation CRelance add_third_relance");
    //Si plusieurs relances en cours pour un patient, création d'un pdf général de relance
    foreach ($patient_ids as $patient_id => $facture_id) {
      $relances_patient = CRelance::loadRelanceEmisePatient($patient_id);
      if (count($relances_patient) > 1) {
        // Création du pdf
        $relance_pdf = new CEditPdf();
        $relance_pdf->pdf = new CMbPdf('P', 'mm');
        $relance_pdf->pdf->setPrintHeader(false);
        $relance_pdf->pdf->setPrintFooter(false);
        $relance_pdf->font = "vera";
        $relance_pdf->fontb = $relance_pdf->font."b";
        $relance_pdf->facture = $factures[$facture_id];
        $relance_pdf->patient = $relance_pdf->facture->_ref_patient;
        $relance_pdf->facture->_ref_patient->loadRefsCorrespondantsPatient();
        $relance_pdf->praticien = $relance_pdf->facture->loadRefPraticien();
        $relance_pdf->facture->loadRefAssurance();
        $relance_pdf->function_prat = $relance_pdf->praticien->loadRefFunction();
        $relance_pdf->group = $relance_pdf->function_prat->loadRefGroup();
        $relance_pdf->relance = reset($relances_patient);
        $relance_pdf->editRelanceEntete();
        $relance_pdf->editRelanceListCorps($relances_patient, $num_frais);
        $relance_pdf->editRelancePied();

        //Récupération de la consultation
        $where = array();
        $where["patient_id"] = "= '$patient_id'";
        $order               = "plageconsult.date DESC, consultation.heure DESC";
        $leftjoin            = array();
        $leftjoin["plageconsult"] = "consultation.plageconsult_id = plageconsult.plageconsult_id";
        $last_consultation = new CConsultation();
        $last_consultation->loadObject($where, $order, null, $leftjoin);

        //Stockage du pdf de relance sur la dernière consultation
        $file = new CFile();
        $file->setObject($last_consultation);
        $file->file_name = "Relances_en_cours_".CMbDT::date();
        $file->loadMatchingObject();
        $file->fillFields();
        $file->setContent($relance_pdf->pdf->Output($file->file_name.'.pdf', "S"));
        CFile::shrinkPDF($file->_file_path);
        $file->file_type = "application/pdf";
        $file->store();
      }
    }
    $facture_pdf->pdf->Output('Relances.pdf', "I");
  }
}
else {
  $do = new CDoObjectAddEdit("CRelance");
  $do->doIt();
}
