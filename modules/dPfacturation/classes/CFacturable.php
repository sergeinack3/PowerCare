<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Facturation;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Ccam\CCodable;

/**
 * Facturable
 *
 */
class CFacturable extends CCodable {

  /** @var  CFacture[] */
  public $_ref_factures = array();
  /** @var  CFacture */
  public $_ref_facture;

  /**
   * Charge la facture associée à l'objet facturable
   *
   * @return CFacture
   */
  function loadRefFacture($not_definitive_only = false) {
    if ($this->_class == "CSejour" && !CAppUI::gconf("dPplanningOp CFactureEtablissement use_facture_etab")) {
      return $this->_ref_facture = new CFactureEtablissement();
    }

    /*CConsultation*/
    if ($this->_ref_factures && count($this->_ref_factures) && $this->_ref_facture) {
      return $this->_ref_facture;
    }
    switch ($this->_class) {
      case "CConsultation":
        $this->completeField('patient_id', 'sejour_id');
        $facture_class = $this->sejour_id ? "CFactureEtablissement" : "CFactureCabinet";
        $facture_table = $this->sejour_id ? "facture_etablissement" : "facture_cabinet";
        $patient_id = $this->patient_id;
        break;
      case "CSejour":
        $facture_class = "CFactureEtablissement";
        $facture_table = "facture_etablissement";
        $patient_id = $this->patient_id;
        break;
      case "CEvenementPatient":
        $facture_class = "CFactureCabinet";
        $facture_table = "facture_cabinet";
        $dossier_medical = $this->loadRefDossierMedical();
        $patient_id = $dossier_medical->object_class == "CPatient" ? $dossier_medical->object_id : $dossier_medical->loadRefObject()->patient_id;
        break;
    }

    if (!CModule::getActive("dPfacturation")) {
      return $this->_ref_facture = isset($facture_class) ? new $facture_class() : null;
    }

    $ljoin                                  = array();
    $ljoin["facture_liaison"]               = "facture_liaison.facture_id = $facture_table.facture_id";
    $where                                  = array();
    $where["facture_liaison.facture_class"] = " = '$facture_class'";
    $where["facture_liaison.object_id"]     = " = '$this->_id'";
    $where["facture_liaison.object_class"]  = " = '$this->_class'";
    $where["$facture_table.patient_id"]     = " = '$patient_id'";
    if ($not_definitive_only) {
      $where["$facture_table.definitive"] = "= '0'";
    }
    if ($this->_class == "CSejour" && $this->_bill_prat_id) {
      $where["facture_etablissement.praticien_id"] = " = '$this->_bill_prat_id'";
    }
    /* @var CFacture $facture */
    $facture             = new $facture_class();
    $order = "$facture_table.annule ASC, numero ASC, facture_id DESC";
    $this->_ref_factures = $facture->loadList($where, $order, null, "facture_id", $ljoin);

    $this->_ref_facture  = reset($this->_ref_factures);
    if (!$this->_ref_facture) {
      $this->_ref_facture = new $facture_class();
    }
    foreach ($this->_ref_factures as $_facture) {
      $_facture->loadRefAssurance();
    }
    if ($this->_class == "CSejour") {
      $this->_ref_facture->loadRefsReglements();
    }

    return $this->_ref_facture;
  }

  /**
   * @return int
   * @throws Exception
   */
  public function leftToPayForPatient() {
    $ljoin = [
      "facture_liaison" => "facture_liaison.facture_id = facture_cabinet.facture_id",
    ];

    $where = [
      "facture_liaison.facture_class" => " = 'CFactureCabinet'",
      "facture_liaison.object_id"     => " = '$this->_id'",
      "facture_liaison.object_class"  => " = '$this->_class'",
      "facture_cabinet.patient_id"    => " = '$this->patient_id'",
    ];

    $order = "facture_cabinet.annule ASC, facture_cabinet.numero ASC, facture_cabinet.facture_id DESC";

    $factures = (new CFactureCabinet())->loadList($where, $order, '0, 1', null, $ljoin) ?? [];
    $facture  = (count($factures) > 0) ? reset($factures) : new CFactureCabinet();


    $du_restant_patient = $facture->du_patient;

    // Calcul des dus
    $facture->loadRefsReglements();
    foreach ($facture->_ref_reglements as $_reglement) {
      if ($_reglement->emetteur == "patient") {
        $du_restant_patient -= $_reglement->montant;
      }
    }

    $facture->loadRefsAvoirs();
    $du_restant_patient           -= $facture->_montant_avoir;
    $facture->_du_restant_patient = $du_restant_patient;
    $this->_ref_facture           = $facture;

    return $facture->_du_restant_patient;
  }
}
