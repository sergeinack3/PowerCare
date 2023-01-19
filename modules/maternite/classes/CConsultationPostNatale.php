<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Maternite;

use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CConstantesMedicales;

/**
 * Conusltation post natale
 */
class CConsultationPostNatale extends CMbObject {
  // DB Table key
  public $consultation_post_natale_id;

  // DB Fields
  public $dossier_perinat_id;
  public $date;
  public $consultant_id;
  public $patho_postacc;
  public $hospi_postacc;
  public $date_hospi_postacc;
  public $duree_hospi_postacc;
  public $motif_hospi_postacc;
  public $troubles_fct;
  public $doul_pelv;
  public $pert_urin;
  public $leucorrhees;
  public $pertes_gaz;
  public $metrorragies;
  public $pertes_fecales;
  public $compl_episio;
  public $baby_blues;
  public $autres_troubles;
  public $desc_autres_troubles;
  public $retour_couches;
  public $date_retour_couches;
  public $reprise_rapports;
  public $contraception;
  public $desc_contraception;
  public $exam_seins;
  public $exam_cic_perin;
  public $exam_cic_cesar;
  public $exam_speculum;
  public $exam_TV;
  public $exam_stat_pelv;
  public $exam_stat_pelv_testing;
  public $exam_autre;
  public $exam_conclusion;
  public $infos_remises;
  public $exam_comp_FCV;
  public $exam_comp_biologie;
  public $exam_comp_autre;
  public $exam_comp_autre_desc;
  public $reeduc;
  public $reeduc_perin;
  public $reeduc_abdo;
  public $reeduc_autre;
  public $reeduc_autre_desc;
  public $contraception_presc;
  public $autre_contraception_presc;
  public $arret_travail;

  /** @var CConstantesMedicales */
  public $_ref_constantes;
  /** @var CMediusers */
  public $_ref_consultant;
  /** @var CDossierPerinat */
  public $_ref_dossier_perinat;
  /** @var CConsultationPostNatEnfant[] */
  public $_ref_consult_enfants;
  /** @var CConsultationPostNatEnfant[] */
  public $_ref_consult_enfants_by_naissance;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "consultation_post_natale";
    $spec->key   = "consultation_post_natale_id";

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                              = parent::getProps();
    $props["dossier_perinat_id"]        = "ref notNull class|CDossierPerinat back|consultations_post_natales";
    $props["date"]                      = "date notNull";
    $props["consultant_id"]             = "ref notNull class|CMediusers back|consult_postnatales";
    $props["patho_postacc"]             = "bool";
    $props["hospi_postacc"]             = "bool";
    $props["date_hospi_postacc"]        = "date";
    $props["duree_hospi_postacc"]       = "num";
    $props["motif_hospi_postacc"]       = "str";
    $props["troubles_fct"]              = "bool";
    $props["doul_pelv"]                 = "bool";
    $props["pert_urin"]                 = "bool";
    $props["leucorrhees"]               = "bool";
    $props["pertes_gaz"]                = "bool";
    $props["metrorragies"]              = "bool";
    $props["pertes_fecales"]            = "bool";
    $props["compl_episio"]              = "bool";
    $props["baby_blues"]                = "bool";
    $props["autres_troubles"]           = "bool";
    $props["desc_autres_troubles"]      = "str";
    $props["retour_couches"]            = "bool";
    $props["date_retour_couches"]       = "date";
    $props["reprise_rapports"]          = "bool";
    $props["contraception"]             = "enum list|aucune|piluleop|sterilet|implant|preserv|progest|anneau|autre";
    $props["desc_contraception"]        = "str";
    $props["exam_seins"]                = "enum list|norm|anorm";
    $props["exam_cic_perin"]            = "enum list|norm|anorm";
    $props["exam_cic_cesar"]            = "enum list|norm|anorm";
    $props["exam_speculum"]             = "enum list|norm|anorm";
    $props["exam_TV"]                   = "enum list|norm|anorm";
    $props["exam_stat_pelv"]            = "enum list|norm|anorm";
    $props["exam_stat_pelv_testing"]    = "num min|0 max|5";
    $props["exam_autre"]                = "text";
    $props["exam_conclusion"]           = "enum list|normal|sequelles";
    $props["infos_remises"]             = "text";
    $props["exam_comp_FCV"]             = "bool";
    $props["exam_comp_biologie"]        = "bool";
    $props["exam_comp_autre"]           = "bool";
    $props["exam_comp_autre_desc"]      = "str";
    $props["reeduc"]                    = "bool";
    $props["reeduc_perin"]              = "bool";
    $props["reeduc_abdo"]               = "bool";
    $props["reeduc_autre"]              = "bool";
    $props["reeduc_autre_desc"]         = "str";
    $props["contraception_presc"]       = "enum list|aucune|pilule|preserv|progest|implant|anneau|sterilet|autre";
    $props["autre_contraception_presc"] = "str";
    $props["arret_travail"]             = "bool";

    return $props;
  }

  /**
   * Chargement du consultant
   *
   * @return CMediusers
   */
  function loadRefConsultant() {
    return $this->_ref_consultant = $this->loadFwdRef("consultant_id", true);
  }

  /**
   * Chargement du dossier périnatal
   *
   * @return CDossierPerinat
   */
  function loadRefParturiente() {
    return $this->_ref_dossier_perinat = $this->loadFwdRef("dossier_perinat_id", true);
  }

  /**
   * Chargement du relevés de constantes maternels lors de la consultation postnatale
   *
   * @return CConstantesMedicales
   */
  public function loadRefConstantesMaternelles() {
    $this->_ref_constantes = $this->loadUniqueBackRef('contextes_constante');
    if (!$this->_ref_constantes->_id) {
      $patient_id                = $this->loadRefParturiente()->loadRefGrossesse()->parturiente_id;
      $constantes                = new CConstantesMedicales();
      $constantes->patient_id    = $patient_id;
      $constantes->context_class = $this->_class;
      $constantes->context_id    = $this->_id;
      $constantes->datetime      = CMbDT::dateTime();
      $constantes->user_id       = CMediusers::get()->_id;
      $this->_ref_constantes     = $constantes;
    }
    $this->_ref_constantes->updateFormFields();

    return $this->_ref_constantes;
  }

  /**
   * Chargement le questionnaire pour chaque enfant
   *
   * @return CConsultationPostNatEnfant[]
   */
  public function loadRefsConsultEnfants() {
    $this->_ref_consult_enfants = $this->loadBackRefs('consult_mater_enfants');

    $this->_ref_consult_enfants_by_naissance = array();
    foreach ($this->_ref_consult_enfants as $_consult_enfant) {
      $this->_ref_consult_enfants_by_naissance[$_consult_enfant->naissance_id] = $_consult_enfant;
    }

    return $this->_ref_consult_enfants;
  }
}