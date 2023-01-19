<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\FieldSpecs\CTextSpec;
use Ox\Core\Handlers\Events\ObjectHandlerEvent;
use Ox\Import\Framework\ImportableInterface;
use Ox\Import\Framework\Matcher\MatcherVisitorInterface;
use Ox\Import\Framework\Persister\PersisterVisitorInterface;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CHtmlToPDF;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\IPatientRelated;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CPosition;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\Search\IIndexableObject;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * Le dossier d'anesthésie est une liaison entre une intervention et une consultation préanesthésique
 * Le dossier contient toutes les informations nécessaires à l'impression de fiches d'anesthésie pour le bloc
 *
 * @todo Renommer en CDossierAnesthesie
 */
class CConsultAnesth extends CMbObject implements IPatientRelated, IIndexableObject, ImportableInterface {
  // DB Table key
  public $consultation_anesth_id = null;

  // DB References
  public $consultation_id;
  public $operation_id;
  public $sejour_id;
  public $chir_id;

  // DB fields
  public $date_interv;
  public $libelle_interv;

  // @todo A supprimer
  public $antecedents;
  public $traitements;
  public $tabac;
  public $oenolisme;

  // Intubation
  public $mallampati;
  public $cormack;
  public $com_cormack;
  public $bouche;
  public $bouche_enfant;
  public $distThyro;
  public $tourCou;
  public $etatBucco;
  public $intub_difficile;
  public $mob_cervicale;
  public $risque_intub;

  // Criteres de ventilation
  public $plus_de_55_ans;
  public $imc_sup_26;
  public $edentation;
  public $ronflements;
  public $barbe;
  public $piercing;

  // Examen clinique
  public $examenCardio;
  public $examenPulmo;
  public $examenDigest;
  public $examenAutre;
  public $poids_stable;

  public $conclusion;
  public $premedication;
  public $prepa_preop;
  public $date_analyse;

  public $rai;
  public $hepatite_b;
  public $hepatite_c;
  public $inr;
  public $hb;
  public $tp;
  public $tca;
  public $tca_temoin;
  public $creatinine;
  public $fibrinogene;
  public $protides;
  public $na;
  public $k;
  public $chlore;
  public $tsivy;
  public $plaquettes;
  public $ecbu;
  public $ht;
  public $ht_final;
  public $result_ecg;
  public $result_rp;
  public $result_autre;
  public $result_com;
  public $histoire_maladie;

  // Check sur les codes cim10 de préfixe pour non-fumeur:
  //  F17 - T652 - Z720 - Z864 - Z587
  public $apfel_femme;
  public $apfel_non_fumeur;
  public $apfel_atcd_nvp;
  public $apfel_morphine;

  // Champs concernant l'intervention
  public $passage_uscpo;
  public $duree_uscpo;
  public $type_anesth;
  public $position_id;
  public $ASA;
  public $rques;
  public $strategie_antibio;
  public $strategie_prevention;
  public $depassement_anesth;
  public $au_total;

  public $autorisation;
  public $accord_patient_debout_aller;
  public $accord_patient_debout_retour;

  // Form fields
  public $_date_consult;
  public $_date_op;
  public $_sec_tsivy;
  public $_min_tsivy;
  public $_sec_tca;
  public $_min_tca;
  public $_intub_difficile;
  public $_clairance;
  public $_psa;
  public $_score_apfel;
  public $_docitems_from_consult;

  public $_passage_uscpo;
  public $_duree_uscpo;
  public $_type_anesth;
  public $_position_id;
  public $_ASA;
  public $_rques;
  public $_dh_anesth;
  public $_distance_op;
  public $_etat_dhe_anesth;
  public $_store_fiche_pdf;

  // Object References
  /** @var  CConsultation */
  public $_ref_consultation;
  /** @var  CMediusers */
  public $_ref_chir;
  /** @var  CTechniqueComp[] */
  public $_ref_techniques = [];
  /** @var  CConsultAnesth */
  public $_ref_last_consultanesth;
  /** @var  COperation */
  public $_ref_operation;
  /** @var  CSejour */
  public $_ref_sejour;
  /** @var  CPlageconsult */
  public $_ref_plageconsult;
  /** @var  CInfoChecklistItem[] */
  public $_refs_info_check_items = [];
  /** @var  CInfoChecklist[] */
  public $_refs_info_checklist = [];
  /** @var  CInfoChecklistItem */
  public $_ref_info_checklist_item;
  /** @var  CPosition */
  public $_ref_position;
  /** @var  array */
  public $_risques_anesth;
  /** @var  CExamLee */
  public $_ref_score_lee;
  /** @var  CExamMet */
  public $_ref_score_met;
  /** @var CExamHemostase */
  public $_ref_score_hemostase;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec = parent::getSpec();

    $spec->table = 'consultation_anesth';
    $spec->key   = 'consultation_anesth_id';

    $spec->events = array(
      "examen"            => array(
        "reference1" => array("COperation", "operation_id"),
        "reference2" => array("CSejour", "sejour_id"),
      ),
      "tab_examen_anesth" => array(
        'tab'        => true,
        "reference1" => array("COperation", "operation_id"),
        "reference2" => array("CSejour", "sejour_id"),
      ),
    );

      static $appFine = null;
      if ($appFine === null) {
          $appFine = CModule::getActive("appFineClient") !== null;
      }

      if ($appFine) {
          $spec->events["appFine"] = array(
              "reference1" => array("CMediusers", "praticien_id"),
              "reference2" => array("CPatient", "patient_id"),
          );
      }

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();

    $props["consultation_id"] = "ref notNull class|CConsultation cascade seekable show|0 back|consult_anesth";
    $props["operation_id"]    = "ref class|COperation seekable back|dossiers_anesthesie";
    $props["sejour_id"]       = "ref class|CSejour seekable back|consultations_anesths";
    $props["chir_id"]         = "ref class|CMediusers back|consults_anesth";

    $props["date_interv"]    = "date";
    $props["libelle_interv"] = "str";

    // @todo: Supprimer ces quatre champs
    $props["antecedents"] = "text confidential";
    $props["traitements"] = "text confidential";
    $props["tabac"]       = "text helped";
    $props["oenolisme"]   = "text helped";

    // Données examens complementaires
    $props["rai"]              = "enum list|?|NEG|POS default|? show|0";
    $props["hepatite_b"]       = "enum list|?|NEG|POS default|? show|0";
    $props["hepatite_c"]       = "enum list|?|NEG|POS default|? show|0";
    $props["inr"]              = "float min|0";
    $props["hb"]               = "float min|0 show|0";                   // g/dl
    $props["tp"]               = "float min|0 max|140 show|0";           // %
    $props["tca"]              = "numchar maxLength|2 show|0";           // secondes
    $props["tca_temoin"]       = "numchar maxLength|2 show|0";           // secondes
    $props["creatinine"]       = "float show|0";                         // mg/l
    $props["fibrinogene"]      = "float show|0";                         // g/l
    $props["protides"]         = "float show|0";                         // g/l
    $props["na"]               = "float min|0 show|0";                   // mmol/l
    $props["k"]                = "float min|0 show|0";                   // mmol/l
    $props["chlore"]           = "float min|0 show|0";                   // mmol/l
    $props["tsivy"]            = "time show|0";
    $props["plaquettes"]       = "numchar maxLength|4 pos show|0";       // (x1000)/mm3
    $props["ecbu"]             = "enum list|?|NEG|POS default|? show|0";
    $props["ht"]               = "float min|0 max|140 show|0";           // %
    $props["ht_final"]         = "float min|0 max|140 show|0";           // %
    $props["result_ecg"]       = "text helped";
    $props["result_rp"]        = "text helped";
    $props["result_autre"]     = "text helped";
    $props["result_com"]       = "text helped";
    $props["histoire_maladie"] = "text helped";
    $props["premedication"]    = "text helped";
    $props["prepa_preop"]      = "text helped";
    $props["date_analyse"]     = "date show|0";
    $props["apfel_femme"]      = "bool show|0";
    $props["apfel_non_fumeur"] = "bool show|0";
    $props["apfel_atcd_nvp"]   = "bool show|0";
    $props["apfel_morphine"]   = "bool show|0";

    // Champs pour les conditions d'intubation
    $props["mallampati"]      = "enum list|classe1|classe2|classe3|classe4|no_eval";
    $props["cormack"]         = "enum list|grade1|grade2|grade3|grade4";
    $props["com_cormack"]     = "text helped";
    $props["bouche"]          = "enum list|m20|m35|p35";
    $props["bouche_enfant"]   = "enum list|m3doigts|p3doigts|no_eval";
    $props["distThyro"]       = "enum list|m65|p65";
    $props["tourCou"]         = "enum list|p45";
    $props["etatBucco"]       = "text helped";
    $props["intub_difficile"] = "bool";
    $props["mob_cervicale"]   = "enum list|m80|80m100|p100";
    $props["plus_de_55_ans"]  = "bool";
    $props["imc_sup_26"]      = "bool";
    $props["edentation"]      = "bool";
    $props["ronflements"]     = "bool";
    $props["barbe"]           = "bool";
    $props["piercing"]        = "bool";
    $props["examenCardio"]    = "text helped";
    $props["examenPulmo"]     = "text helped";
    $props["examenDigest"]    = "text helped";
    $props["examenAutre"]     = "text helped";
    $props["poids_stable"]    = "bool default|0";
    $props["risque_intub"]    = "enum list|low|medium|high";

    $props["conclusion"] = "text helped seekable";

    // Champs concernant l'intervention
    if (CAppUI::conf("dPplanningOp COperation show_duree_uscpo") == 2) {
      $props["passage_uscpo"] = "bool notNull";
    }
    else {
      $props["passage_uscpo"] = "bool";
    }
    $props["duree_uscpo"]          = "num min|0 max|10 default|0";
    $props['type_anesth']          = 'ref class|CTypeAnesth back|consult_anesth';
    $props['position_id']          = "ref class|CPosition autocomplete|libelle back|positions_consult";
    $props['ASA']                  = 'enum list|1|2|3|4|5|6';
    $props['rques']                = 'text helped';
    $props['strategie_antibio']    = 'text helped';
    $props['strategie_prevention'] = 'text helped';
    $props["depassement_anesth"]   = "currency min|0 confidential show|0";
    $props["au_total"]             = "text helped";

    $props['autorisation'] = 'enum list|undefined|0|1 default|undefined';

    $props['accord_patient_debout_aller']  = 'bool default|0';
    $props['accord_patient_debout_retour'] = 'bool default|0';

    // Champs dérivés
    $props["_intub_difficile"] = "";
    $props["_clairance"]       = "";
    $props["_psa"]             = "";
    $props["_score_apfel"]     = "";

    $props["_passage_uscpo"]   = $props["passage_uscpo"];
    $props["_duree_uscpo"]     = $props["duree_uscpo"];
    $props["_type_anesth"]     = $props["type_anesth"];
    $props["_position_id"]     = $props["position_id"];
    $props["_ASA"]             = $props["ASA"];
    $props["_rques"]           = $props["rques"];
    $props["_dh_anesth"]       = $props["depassement_anesth"];
    $props["_store_fiche_pdf"] = 'bool default|0';

    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();

    // Vérification si intubation difficile
    $this->_intub_difficile =
      $this->intub_difficile == '1' ||
      (($this->mallampati === "classe3" || $this->mallampati === "classe4" ||
          $this->bouche === "m20" || $this->bouche === "m35" || $this->bouche_enfant === "m3doigts" ||
          $this->distThyro === "m65" || $this->tourCou === "p45" || $this->mob_cervicale === "m80" || $this->mob_cervicale === "80m100")
        && !empty($this->intub_difficile) && $this->intub_difficile != '0');

    $this->_sec_tsivy = intval(substr($this->tsivy, 6, 2));
    $this->_min_tsivy = intval(substr($this->tsivy, 3, 2));

    $this->_score_apfel = $this->apfel_femme + $this->apfel_non_fumeur + $this->apfel_atcd_nvp + $this->apfel_morphine;
  }

  /**
   * @see parent::updatePlainFields()
   */
  function updatePlainFields() {
    if ($this->_min_tsivy !== null && $this->_sec_tsivy !== null) {
      $this->tsivy = '00:' . ($this->_min_tsivy ? sprintf("%02d", $this->_min_tsivy) : '00') . ':';
      $this->tsivy .= ($this->_sec_tsivy ? sprintf("%02d", $this->_sec_tsivy) : '00');
    }

    parent::updatePlainFields();
  }

  /**
   * @see parent::completeLabelFields()
   */
  function completeLabelFields(&$fields, $params) {
    $this->loadRefConsultation()->completeLabelFields($fields, $params);
    $this->loadRefOperation();
    if ($this->_ref_sejour) {
      $this->_ref_sejour->completeLabelFields($fields, $params);
    }
  }

  /**
   * @see parent::loadRelPatient()
   */
  function loadRelPatient() {
    return $this->loadRefConsultation()->loadRefPatient();
  }

  /**
   * Charge le patient associé
   *
   * @return CPatient
   */
  function loadRefPatient() {
    return $this->loadRelPatient();
  }

  /**
   * Charge la consultation associée
   *
   * @return CConsultation
   */
  function loadRefConsultation() {
    if ($this->_ref_consultation) {
      return $this->_ref_consultation;
    }
    $consultation = $this->loadFwdRef("consultation_id", true);
    $this->_view  = $consultation->_view;

    return $this->_ref_consultation = $consultation;
  }

  /**
   * Charge la chirurgien associé
   *
   * @return CMediusers
   */
  function loadRefChir() {
    if ($this->chir_id) {
      $this->_ref_chir = $this->loadFwdRef("chir_id", true);
    }
    else {
      $this->_ref_chir = $this->loadRefConsultation()->loadRefPraticien();
    }
    $this->_ref_chir->loadRefFunction();

    return $this->_ref_chir;
  }

  /**
   * Charge l'opération associée
   * Value également le séjour associé
   *
   * @return COperation
   */
  function loadRefOperation() {
    /** @var COperation $operation */
    $operation = $this->loadFwdRef("operation_id", true);

    // Chargement du séjour associé
    if ($operation->_id) {
      $operation->loadRefPlageOp();
      $this->_ref_sejour = $operation->loadRefSejour();
    }
    else {
      $this->loadRefSejour();
    }

    $this->_passage_uscpo = $operation->passage_uscpo ?: $this->passage_uscpo;
    $this->_duree_uscpo   = $operation->duree_uscpo ?: $this->duree_uscpo;
    $this->_type_anesth   = $operation->type_anesth ?: $this->type_anesth;
    $this->_position_id   = $operation->position_id ?: $this->position_id;
    $this->_ASA           = $operation->ASA ?: $this->ASA;
    $this->_rques         = $operation->rques ?: $this->rques;
    $this->_dh_anesth     = $operation->depassement_anesth != '' ? $operation->depassement_anesth : $this->depassement_anesth;

    $this->loadRefPosition();

    return $this->_ref_operation = $operation;
  }

  /**
   * Calcul de la distance entre l'intervention et la CPA
   *
   * @return int la distance en jours
   */
  function loadDistanceOp() {
    $this->loadRefConsultation()->loadRefPlageConsult();
    $this->loadRefOperation();

    return $this->_distance_op = CMbDT::daysRelative($this->_ref_operation->date, $this->_ref_consultation->_date);
  }

  /**
   * Charge le séjour associé
   *
   * @return CSejour
   */
  function loadRefSejour() {
    $this->_ref_sejour = $this->loadFwdRef("sejour_id", true);
    $this->_ref_sejour->loadRefsFwd(true);

    return $this->_ref_sejour;
  }

  /**
   * @inheritdoc
   */
  function loadRefsFiles($where = array(), bool $with_cancelled = true) {
    parent::loadRefsFiles($where);

    if (!$this->_docitems_from_consult) {
      if (!$this->_ref_consultation) {
        $this->loadRefConsultation();
      }
      $this->_ref_consultation->_docitems_from_dossier_anesth = true;
      $this->_ref_consultation->loadRefsFiles($where, $with_cancelled);
      $this->_nb_cancelled_files += $this->_ref_consultation->_nb_cancelled_files;
      $this->_nb_cancelled_docs  += $this->_ref_consultation->_nb_cancelled_docs;
      $this->_ref_files          = $this->_ref_files + $this->_ref_consultation->_ref_files;
    }
  }

  /**
   * @inheritdoc
   */
  function loadRefsDocs($where = array(), bool $with_canelled = true) {
    parent::loadRefsDocs($where, $with_canelled);

    if (!$this->_docitems_from_consult) {
      if (!$this->_ref_consultation) {
        $this->loadRefConsultation();
      }
      $this->_ref_consultation->_docitems_from_dossier_anesth = true;
      $this->_ref_consultation->loadRefsDocs($where, $with_canelled);
      $this->_ref_documents = $this->_ref_documents + $this->_ref_consultation->_ref_documents;
    }

    return count($this->_ref_documents);
  }

  /**
   * @see parent::countDocs()
   */
  function countDocs() {
    $nbDocs = parent::countDocs();

    if (!$this->_docitems_from_consult) {
      // Ajout des documents des dossiers d'anesthésie
      if (!$this->_ref_consultation) {
        $this->loadRefConsultation();
      }
      $this->_ref_consultation->_docitems_from_dossier_anesth = true;
      $nbDocs                                                 += $this->_ref_consultation->countDocs();
    }

    return $this->_nb_docs = $nbDocs;
  }

  /**
   * @see parent::countFiles()
   */
  function countFiles($where = array()) {
    $nbFiles = parent::countFiles($where);

    if (!$this->_docitems_from_consult) {
      // Ajout des fichiers des dossiers d'anesthésie
      if (!$this->_ref_consultation) {
        $this->loadRefConsultation();
      }
      $this->_ref_consultation->_docitems_from_dossier_anesth = true;
      $nbFiles                                                += $this->_ref_consultation->countFiles();
    }

    return $this->_nb_files = $nbFiles;
  }

  /**
   * @see parent::loadView()
   */
  function loadView() {
    parent::loadView();
    $this->_ref_consultation = $this->_fwd["consultation_id"];
    $this->_ref_consultation->loadView();

    $this->loadRefOperation();

    if ($this->_duree_uscpo && !$this->duree_uscpo) {
      $this->duree_uscpo = $this->_duree_uscpo;
    }
  }

  /**
   * @see parent::loadComplete()
   */
  function loadComplete() {
    parent::loadComplete();
    $this->loadRefsInfoChecklistItem(true);
    $this->_ref_consultation->loadRefsExamsComp();
    $this->_ref_consultation->loadRefsExamNyha();
    $this->_ref_consultation->loadRefsExamPossum();

    $this->_ref_consultation->loadRefConsultAnesth();
    foreach ($this->_ref_consultation->_refs_dossiers_anesth as $_dossier_anesth) {
      $_dossier_anesth->loadRefOperation();
    }

    $this->loadRefOperation();
    $this->_ref_operation->loadRefTypeAnesth();

    $dossier_medical = $this->_ref_sejour->loadRefDossierMedical();
    $dossier_medical->loadRefsAntecedents();
    $dossier_medical->loadRefsTraitements();

    $patient         = $this->loadRefPatient();
    $dossier_medical = $patient->loadRefDossierMedical();
    $dossier_medical->loadRefsAntecedents();
    $dossier_medical->loadRefsTraitements();


    // Chargement des actes CCAM
    foreach ($this->_ref_consultation->loadRefsActesCCAM() as $_acte) {
      $_acte->loadRefsFwd();
    }
  }

  /**
   * @deprecated
   * @see parent::loadRefsFwd()
   */
  function loadRefsFwd() {
    $this->loadRefChir();

    // Chargement operation/sejour
    $this->loadRefOperation();
    $this->_ref_operation->loadRefsFwd();
    $this->_date_op =& $this->_ref_operation->_datetime;

    // Chargement consultation
    $this->loadRefConsultation();
    $this->_ref_consultation->loadRefsFwd();
    $this->_ref_plageconsult =& $this->_ref_consultation->_ref_plageconsult;
    $this->_date_consult     =& $this->_ref_consultation->_date;

    $this->loadPsaClairance();
  }

  /**
   * Load the PSA and the Clairance
   *
   * @return void
   */
  function loadPsaClairance() {
    if (!$this->_ref_consultation || !$this->_ref_consultation->_id) {
      $this->loadRefConsultation();
      $this->_ref_consultation->loadRefPatient();
    }
    $patient = $this->_ref_consultation->_ref_patient;
    $patient->loadRefLatestConstantes(null, null, $this->_ref_consultation, false);
    $const_med = $patient->_ref_constantes_medicales;
    $const_med->updateFormFields();

    // Calcul de la Clairance créatinine
    $age = intval($patient->_annees);
    if ($const_med->poids && $this->creatinine
      && $age && $age >= 18 && $age <= 110
      && $const_med->poids >= 35 && $const_med->poids <= 120
      && $this->creatinine >= 6 && $this->creatinine <= 70
    ) {
      $this->_clairance = $const_med->poids * (140 - $age) / (7.2 * $this->creatinine);
      if ($patient->sexe == 'm') {
        $this->_clairance *= 1.04;
      }
      else {
        $this->_clairance *= 0.85;
      }
      $this->_clairance = round($this->_clairance, 2);
    }

    // Calcul des Pertes Sanguines Acceptables
    if ($this->ht && $this->ht_final && $const_med->_vst) {
      $this->_psa = $const_med->_vst * ($this->ht - $this->ht_final) / 100;
    }
  }

  /**
   * Chargement des techniques complémentaires
   *
   * @return CTechniqueComp[]
   */
  function loadRefsTechniques() {
    $techniques = new CTechniqueComp();
    $where      = array(
      "consultation_anesth_id" => "= '$this->consultation_anesth_id'"
    );

    return $this->_ref_techniques = $techniques->loadList($where, "technique");
  }

  /**
   * @deprecated
   * @see parent::loadRefsBack()
   */
  function loadRefsBack() {
    parent::loadRefsBack();
    $this->loadRefsTechniques();
  }

  /**
   * @see parent::getTemplateClasses
   */
  function getTemplateClasses() {
    $this->loadRefsFwd();

    $tab = array();

    // Stockage des objects liés à l'opération
    $tab['CConsultAnesth'] = $this->_id;
    $tab['CConsultation']  = $this->_ref_consultation->_id;
    $tab['CPatient']       = $this->_ref_consultation->_ref_patient->_id;
    $tab['COperation']     = $this->_ref_operation->_id;
    $tab['CSejour']        = $this->_ref_operation->_ref_sejour->_id;

    return $tab;
  }

  /**
   * @see parent::getPerm()
   */
  function getPerm($permType) {
    if (!$this->_ref_consultation) {
      $this->loadRefConsultation();
    }

    switch ($permType) {
      case PERM_EDIT :
        return $this->_ref_consultation->getPerm($permType);
      default :
        // Droits sur l'opération
        if ($this->operation_id) {
          if (!$this->_ref_operation) {
            $this->loadRefOperation();
          }
          $canOper = $this->_ref_operation->getPerm($permType);
        }
        else {
          $canOper = false;
        }
        // Droits sur le séjour
        if ($this->sejour_id) {
          if (!$this->_ref_sejour) {
            $this->loadRefSejour();
          }
          $canSej = $this->_ref_sejour->getPerm($permType);
        }
        else {
          $canSej = false;
        }

        return $canOper || $canSej || $this->_ref_consultation->getPerm($permType);
    }
  }

  /**
   * @see parent::fillTemplate()
   */
  function fillTemplate(&$template) {
    $this->loadRefsFwd();
    $this->_ref_consultation->fillTemplate($template);
    $this->fillLimitedTemplate($template);
    $this->_ref_sejour->fillLimitedTemplate($template);
    $this->_ref_operation->fillLimitedTemplate($template);

    // Dossier médical
    $this->_ref_sejour->loadRefDossierMedical()->fillTemplate($template, "Sejour");

    if (CModule::getActive("dPprescription")) {
      $sejour = $this->_ref_sejour;
      $sejour->loadRefsPrescriptions();
      $prescription       = isset($sejour->_ref_prescriptions["pre_admission"]) ?
        $sejour->_ref_prescriptions["pre_admission"] :
        new CPrescription();
      $prescription->type = "pre_admission";
      $prescription->fillLimitedTemplate($template);
      $prescription       = isset($sejour->_ref_prescriptions["sejour"]) ?
        $sejour->_ref_prescriptions["sejour"] :
        new CPrescription();
      $prescription->type = "sejour";
      $prescription->fillLimitedTemplate($template);
      $prescription       = isset($sejour->_ref_prescriptions["sortie"]) ?
        $sejour->_ref_prescriptions["sortie"] :
        new CPrescription();
      $prescription->type = "sortie";
      $prescription->fillLimitedTemplate($template);
    }
  }

  /**
   * @see parent::fillLimitedTemplate
   */
  function fillLimitedTemplate(&$template) {
    global $rootName;
    $this->updateFormFields();

    $this->notify(ObjectHandlerEvent::BEFORE_FILL_LIMITED_TEMPLATE(), $template);

    $this->loadRefOperation();

    $anesth_section = CAppUI::tr('CConsultation._type.anesth');
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-tabac'), $this->tabac);
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-oenolisme'), $this->oenolisme);

    $dossier_medical = $this->loadRefPatient()->loadRefDossierMedical();
    $template->addDateProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-Date analysis'), $this->date_analyse);
    $template->addProperty("$anesth_section - " . CAppUI::tr('CDossierMedical-groupe_sanguin-desc'), $dossier_medical->groupe_sanguin . " " . $dossier_medical->rhesus);
    $template->addProperty("$anesth_section - " . CAppUI::tr('CDossierMedical-groupe_ok'), $dossier_medical->groupe_ok ? CAppUI::tr('common-Yes') : CAppUI::tr('common-No'));
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-rai'), $this->rai);
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-hb'), "$this->hb g/dl");
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-ht'), "$this->ht %");
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-ht_final'), "$this->ht_final %");
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-_psa'), "$this->_psa ml/GR");
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-plaquettes'), ($this->plaquettes * 1000) . "/mm3");
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-creatinine'), "$this->creatinine mg/l");
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-_clairance'), "$this->_clairance ml/min");
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-fibrinogene'), "$this->fibrinogene g/l");
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-protides'), "$this->protides g/l");
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-na'), "$this->na mmol/l");
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-k'), "$this->k mmol/l");
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-chlore'), "$this->chlore mmol/l");
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-tp'), "$this->tp %");
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-tca'), "$this->tca_temoin s / $this->tca s");
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-tsivy'), "$this->_min_tsivy min $this->_sec_tsivy s");
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-ecbu'), $this->ecbu);
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-hepatite_b'), $this->hepatite_b);
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-hepatite_c'), $this->hepatite_c);
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-inr'), $this->inr);
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-result_com'), $this->result_com);
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-histoire_maladie'), $this->histoire_maladie);

    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-prepa_preop'), $this->prepa_preop);
    $template->addMarkdown("$anesth_section - " . CAppUI::tr('CConsultAnesth-rques'), $this->getFormattedValue("_rques"));
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-premedication'), $this->premedication);
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-au_total'), $this->au_total);
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-score_lee'), $this->loadRefScoreLee()->_score_lee);
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-score_met'), $this->loadRefScoreMet()->_score_met);
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-score_hemostase'),
      $this->loadRefScoreHemostase()->_score_hemostase);

    $list  = CMbArray::pluck($this->loadRefsTechniques(), 'technique');
    $field = "$anesth_section - " . CAppUI::tr('CConsultAnesth-back-techniques');
    if (count($list)) {
      $template->addListProperty($field, $list);
    }
    else {
      $template->addProperty($field, CAppUI::tr("CConsultAnesth-back-techniques.empty"));
    }

    $infos = CMbArray::pluck($this->loadRefsInfoChecklistItem(true), '_view');
    $template->addListProperty("$anesth_section - " . CAppUI::tr('CInfoChecklistItem-title-send_to_patient'), $infos);

    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-ECG result|pl'), $this->result_ecg);
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-Pulmonary Radio Result|pl'), $this->result_rp);
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-examenCardio'), $this->examenCardio);
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-examenPulmo'), $this->examenPulmo);
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-examenDigest'), $this->examenDigest);
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-examenAutre'), $this->examenAutre);
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-poids_stable'), $this->poids_stable ? CAppUI::tr('CConsultAnesth-poids_stable') : "");

    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-bouche'), $this->getFormattedValue('bouche'), null, false);
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-bouche_enfant'), $this->getFormattedValue('bouche_enfant'), null, false);
    $template->addProperty("$anesth_section - " . CAppUI::tr('soins.tab.intubation'), CAppUI::tr("CConsultAnesth-_intub_" . ($this->_intub_difficile ? "difficile" : "pas_difficile")));

    // Distant fields de l'intervention
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-ASA'), $this->_ASA ? CAppUI::tr("CConsultAnesth.ASA.$this->_ASA") : "");
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-position_id'), $this->_ref_position->_view);
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-type_anesth'), $this->getFormattedValue("_type_anesth"));
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-_dh_anesth'), $this->_dh_anesth);

    $ventilation = $this->plus_de_55_ans ? CAppUI::tr('CConsultAnesth-More than 55 years') . ", " : "";
    $ventilation .= $this->imc_sup_26 ? CAppUI::tr('CConsultAnesth-BMI more than 26Kg / m²') . ", " : "";
    $ventilation .= $this->edentation ? CAppUI::tr('CConsultAnesth-edentation') . ", " : "";
    $ventilation .= $this->ronflements ? CAppUI::tr('CConsultAnesth-ronflements') . ", " : "";
    $ventilation .= $this->barbe ? CAppUI::tr('CConsultAnesth-barbe') : "";
    $ventilation .= $this->piercing ? CAppUI::tr('CConsultAnesth-piercing') : "";
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-legend-Criteria for ventilation'), $ventilation ? $ventilation : CAppUI::tr('common-None'), null, false);

    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-distThyro'), $this->getFormattedValue('distThyro'), null, false);
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-mob_cervicale'), $this->getFormattedValue('mob_cervicale'), null, false);
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-etatBucco'), $this->etatBucco);
    $template->addProperty(
      "$anesth_section - " . CAppUI::tr('CConsultAnesth-accord_patient_debout_aller'),
      CAppUI::tr('CConsultAnesth-accord_patient_debout_aller') . " : " . ($this->accord_patient_debout_aller
      ? CAppUI::tr('common-Yes') : CAppUI::tr('common-No'))
    );

    $dossier_medical->loadRefsEtatsDents();

    $etats       = array();
    $sEtatsDents = "";

    if (is_array($dossier_medical->_ref_etats_dents)) {
      foreach ($dossier_medical->_ref_etats_dents as $etat) {
        if ($etat->etat != null) {
          switch ($etat->dent) {
            case 10:
            case 50:
              $position = "Central haut";
              break;
            case 30:
            case 70:
              $position = "Central bas";
              break;
            default:
              $position = $etat->dent;
          }
          if (!isset ($etats[$etat->etat])) {
            $etats[$etat->etat] = array();
          }
          $etats[$etat->etat][] = $position;
        }
      }
    }

    foreach ($etats as $key => $list) {
      sort($list);
      $sEtatsDents .= "- " . ucfirst($key) . " : " . implode(", ", $list) . "<br/>";
    }

    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-Dental numbering'), $sEtatsDents, null, false);

    $img_dents = '<img src="/' . $rootName . '/images/pictures/dents.png" alt="' . CAppUI::tr('CConsultAnesth-Dental picture') . '" />';

    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-Dental picture'), $img_dents, null, false);

    $img = "";
    if ($this->mallampati) {
      $img = CAppUI::tr('CConsultAnesth.mallampati.' . $this->mallampati) . '<br />
        <img src="/' . $rootName . '/images/pictures/' . $this->mallampati . '.png" alt="' . CAppUI::tr('CConsultAnesth.mallampati.' . $this->mallampati) . '" />';
    }

    // Récupérer le dernier score de Cormack
    if (!$this->cormack && !$this->com_cormack) {
      $patient = $this->loadRefPatient();
      $this->getLastCormackValues($patient->_id);
    }

    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-mallampati'), $img, null, false);
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-Mallampati (text only)'), $this->getFormattedValue("mallampati"));
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-cormack-court'), $this->cormack ? $this->getFormattedValue("cormack") : '-');
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-Cormack score (comment)'), $this->com_cormack, false);
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-rques-court'), $this->conclusion);
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-APFEL score'), $this->_score_apfel);
    $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-strategie_antibio') . " ", $this->strategie_antibio);

    if (CAppUI::gconf("dPcabinet CConsultAnesth see_strategie_prevention")) {
      $template->addProperty("$anesth_section - " . CAppUI::tr('CConsultAnesth-strategie_prevention'), $this->strategie_prevention);
    }

    // Constantes médicales dans le contexte de la consultation
    $this->loadRefConsultation();
    $patient = $this->loadRefPatient();
    $patient->loadRefLatestConstantes(null, null, $this->_ref_consultation, false);
    $const_dates = $patient->_latest_constantes_dates;
    $const_med   = $patient->_ref_constantes_medicales;
    $const_med->updateFormFields();

    $grid_complet = CConstantesMedicales::buildGridLatest($const_med, $const_dates, true);
    $grid_minimal = CConstantesMedicales::buildGridLatest($const_med, $const_dates, false);
    $grid_valued  = CConstantesMedicales::buildGridLatest($const_med, $const_dates, false, true);

    CConstantesMedicales::addConstantesTemplate($template, $grid_complet, $grid_minimal, $grid_valued, "Anesthésie");

    if (CModule::getActive("forms")) {
      CExObject::addFormsToTemplate($template, $this, "Anesthésie");
    }

    $this->notify(ObjectHandlerEvent::AFTER_FILL_LIMITED_TEMPLATE(), $template);
  }

  /**
   * @see parent::canDeleteEx()
   */
  function canDeleteEx() {
    // Date dépassée
    $this->completeField("consultation_id");

    $consult = $this->loadRefConsultation();
    $consult->loadRefPlageConsult();

    if ($consult->_ref_plageconsult->date < CMbDT::date() && !$this->_ref_module->canDo()->edit) {
      return CAppUI::tr('CConsultAnesth-msg-Unable to delete an anesthesia folder from a past consultation');
    }

    return parent::canDeleteEx();
  }

  /**
   * @see parent::store()
   */
  function store() {
    $this->completeField("operation_id", "chir_id");

    if ($this->operation_id && ($this->fieldModified("operation_id") || !$this->_id)) {
      $op = new COperation();
      $op->load($this->operation_id);
      $this->sejour_id = $op->sejour_id;

      // Report du type d'anesth sur l'intervention
      if ($op->type_anesth) {
          $this->type_anesth = $op->type_anesth;
      }

      if (CModule::getActive("maternite")) {
        $sejour = $op->loadRefSejour();
        if ($sejour->grossesse_id) {
          $this->completeField("consultation_id");
          $consult               = $this->loadRefConsultation();
          $consult->grossesse_id = $sejour->grossesse_id;
          if ($msg = $consult->store()) {
            return $msg;
          }
        }
      }
    }
    if (!$this->_id) {
      $user = CMediusers::get();
      if (!$this->chir_id && $user->isAnesth()) {
        $this->chir_id = $user->_id;
      }
    }

    if ($this->_id && $this->_store_fiche_pdf) {
      $this->storeFicheAnesthPdf();
    }

    return parent::store();
  }

  /**
   * @see parent::delete()
   */
  function delete() {
    // Suppression des fichiers
    $this->loadRefsFiles();
    foreach ($this->_ref_files as $_fichier) {
      if ($msg = $_fichier->delete()) {
        return $msg;
      }
    }

    // Suppression des documents
    $this->loadRefsDocs();
    foreach ($this->_ref_documents as $_document) {
      if ($msg = $_document->delete()) {
        return $msg;
      }
    }

    return parent::delete();
  }

  /**
   * Get the patient_id of CMbobject
   *
   * @return CPatient
   */
  function getIndexablePatient() {
    return $this->loadRelPatient();
  }

  /**
   * Loads the related fields for indexing datum (patient_id et date)
   *
   * @return array
   */
  function getIndexableData() {
    $consult              = $this->loadRefConsultation();
    $prat                 = $this->getIndexablePraticien();
    $patient              = $this->getIndexablePatient();
    $array["id"]          = $this->_id;
    $array["author_id"]   = $prat->_id;
    $array["prat_id"]     = $prat->_id;
    $array["title"]       = $consult->type;
    $array["body"]        = $this->getIndexableBody("");
    $array["date"]        = str_replace("-", "/", $consult->loadRefPlageConsult()->date);
    $array["function_id"] = $prat->function_id;
    $array["group_id"]    = $prat->loadRefFunction()->group_id;
    $array["patient_id"]  = $patient->_id;
    $sejour_id            = $this->loadRefSejour()->_id;
    if ($sejour_id) {
      $array["object_ref_id"]    = $sejour_id;
      $array["object_ref_class"] = $this->loadRefSejour()->_class;
    }
    else {
      $array["object_ref_id"]    = $this->_id;
      $array["object_ref_class"] = $this->_class;
    }

    return $array;
  }

  /**
   * Redesign the content of the body you will index
   *
   * @param string $content The content you want to redesign
   *
   * @return string
   */
  function getIndexableBody($content) {
    $this->loadRefConsultation();
    $fields        = $this->_ref_consultation->getTextcontent();
    $fields_anesth = array();

    foreach ($this->_specs as $_name => $_spec) {
      if ($_spec instanceof CTextSpec) {
        $fields_anesth[] = $_name;
      }
    }

    foreach ($fields_anesth as $_field_anesth) {
      $content .= " " . $this->$_field_anesth;
    }
    foreach ($fields as $_field) {
      $content .= " " . $this->_ref_consultation->$_field;
    }

    return $content;
  }

  /**
   * Get the praticien_id of CMbobject
   *
   * @return CMediusers
   */
  function getIndexablePraticien() {
    $consult = $this->loadRefConsultation();
    $prat    = $consult->loadRefPraticien();

    return $prat;
  }

  /**
   * Gets icon for current patient event
   *
   * @return array
   */
  function getEventIcon() {
    return array(
      'icon'  => 'fas fa-eye-dropper me-event-icon',
      'color' => 'steelblue',
      'title' => CAppUI::tr($this->_class),
    );
  }

  /**
   * Chargement des item de checklist utilisé
   *
   * @param bool $reponse Réponse
   *
   * @return CInfoChecklistItem[]
   */
  function loadRefsInfoChecklistItem($reponse = false) {
    $where                       = array();
    $where["consultation_class"] = " = 'CConsultAnesth'";
    if ($reponse) {
      $where["reponse"] = " = '1'";
    }
    $this->_refs_info_check_items = $this->loadBackRefs("info_check_item", null, null, "info_checklist_item_id", null, null, "", $where);
    if ($reponse) {
      foreach ($this->_refs_info_check_items as $_item) {
        $_item->loadRefInfoChecklist();
      }
    }

    return $this->_refs_info_check_items;
  }

  /**
   * Chargement de la liste complète des infos de checklist
   *
   * @return CInfoChecklist[]
   */
  function loadRefsInfoChecklist() {
    $this->loadRefsInfoChecklistItem();
    if (!$this->_ref_chir) {
      $this->loadRefChir();
    }
    $infos = CInfoChecklist::loadListWithFunction($this->_ref_consultation->_ref_chir->function_id);
    foreach ($infos as $_info) {
      foreach ($this->_refs_info_check_items as $_item) {
        if ($_item->info_checklist_id == $_info->_id) {
          $_info->_item_id = $_item->_id;
        }
      }
    }

    $this->_ref_info_checklist_item = new CInfoChecklistItem();
    $this->_refs_info_checklist     = $infos;
  }

  /**
   * Récupérer les données de Cormack
   *
   * @param integer $patient_id L'id du patient
   *
   * @return array
   */
  function getLastCormackValues($patient_id) {
    $ds                    = $this->getDS();
    $ljoin                 = array();
    $ljoin["consultation"] = "consultation.consultation_id = consultation_anesth.consultation_id";

    $where                                = array();
    $where["consultation.patient_id"]     = $ds->prepare("= ?", $patient_id);
    $where["consultation_anesth.cormack"] = " is not null";

    $order = "consultation_anesth_id DESC";

    $countLine = $this->countList($where, null, $ljoin);

    if ($countLine) {
      $infos = $this->loadList($where, $order, 1, null, $ljoin);

      foreach ($infos as $info) {
        $this->cormack     = $info->cormack;
        $this->com_cormack = $info->com_cormack;
      }
    }

    return $this;
  }

  /**
   * Récupération des risques moebius de la consultation d'anesthésie
   *
   * @return array
   * @throws Exception
   */
  function loadRefsRisques() {
    if (!CModule::getActive("moebius")) {
      return $this->_risques_anesth = [];
    }

    $this->_risques_anesth = [
      "risque_cardio"       => $this->loadUniqueBackRef("risque_cardio"),
      "risque_douleur"      => $this->loadUniqueBackRef("risque_douleur"),
      "risque_hemorragique" => $this->loadUniqueBackRef("risque_hemorragique"),
      "risque_intubation"   => $this->loadUniqueBackRef("risque_intubation"),
      "risque_nutritionnel" => $this->loadUniqueBackRef("risque_nutritionnel"),
      "risque_renal"        => $this->loadUniqueBackRef("risque_renal"),
      "risque_respiratoire" => $this->loadUniqueBackRef("risque_respiratoire"),
      "risque_thrombotique" => $this->loadUniqueBackRef("risque_thrombotique"),
      "risque_allergique"   => $this->loadUniqueBackRef("risque_allergique"),
      "risque_apnee"        => $this->loadUniqueBackRef("risque_apnee"),
      "risque_nvpo"         => $this->loadUniqueBackRef("risque_nvpo"),
      "risque_cognitif"     => $this->loadUniqueBackRef("risque_cognitif"),
      "risque_vas"          => $this->loadUniqueBackRef("risque_vas"),
      "risque_dentaire"     => $this->loadUniqueBackRef("risque_dentaire"),
      "risque_jeun"         => $this->loadUniqueBackRef("risque_jeun"),
      "risque_infectieux"   => $this->loadUniqueBackRef("risque_infectieux"),
    ];

    foreach ($this->_risques_anesth as $_risque_anesth) {
        if (!$_risque_anesth->_id) {
            $_risque_anesth->consultation_anesth_id = $this->_id;
        }
    }

    return $this->_risques_anesth;
  }

  /**
   * Load the CExamLee object
   *
   * @return CExamLee
   */
  function loadRefScoreLee() {
    return $this->_ref_score_lee = $this->loadUniqueBackRef("score_lee");
  }

  /**
   * Load the CExamMet object
   *
   * @return CExamMet
   */
  function loadRefScoreMet() {
    return $this->_ref_score_met = $this->loadUniqueBackRef("score_met");
  }

  /**
   * Load the CExamHemostase object
   *
   * @return CExamHemostase|null
   * @throws Exception
   */
  function loadRefScoreHemostase() {
    return $this->_ref_score_hemostase = $this->loadUniqueBackRef("score_hemostase");
  }

  /**
   * Intégration de la fiche d'anesthésie en PDF aux documents de la consultation
   *
   * @return void
   */
  function storeFicheAnesthPdf() {
    $this->completeField("consultation_id", "chir_id");
    $model = CCompteRendu::getSpecialModel($this->loadRefChir(), "CConsultAnesth", "[FICHE ANESTH]");

    $name_file = CAppUI::tr("mod-dPcabinet-tab-print_fiche") . " " . CMbDT::dateTime();

    $file    = new CFile();
    $file->setObject($this);
    $file->author_id        = CMediusers::get()->_id;
    $file->file_name        = $name_file;
    $file->file_type        = "application/pdf";
    $file->file_category_id = $model->file_category_id;
    $file->fillFields();
    $file->updateFormFields();

    //Dans le cas où le model est utilisé, il retourne un pdf qu'il faut stocker tel quel
    if ($model->_id) {
        $url = array(
            "m"                 => "cabinet",
            "a"                 => "print_fiche",
            "dossier_anesth_id" => $this->_id,
            "print"             => false,
            "dialog"            => 1,
        );
        $content = CApp::fetchQuery($url);
        $file->setContent($content);
    } else {
        //Dans le cas des fiches d'anesth en html, il faut les transformer en pdf pour ensuite les stocker
        $content = CApp::fetch(
            'dPcabinet',
            'print_fiche',
            [
                'dossier_anesth_id' => $this->_id,
                'offline' => true,
                'display' => true
            ]
        );

        $root_dir = CAppUI::conf('root_dir');
        $content = str_replace('src="images/pictures/classe', 'src="' . $root_dir . '/images/pictures/classe', $content);
        $style = file_get_contents($root_dir . '/style/mediboard_ext/main.css');
        $style .= "\n" . file_get_contents($root_dir . '/style/mediboard_ext/oldTables.css');

        $style = preg_replace('#/\*[^*]*\*+([^\/][^*]*\*+)*/#m', '', $style);

        $content = <<<EOF
        <html>
          <head>
            <style type="text/css">
              {$style}
            </style>
          </head>
          <body>
            {$content}
          </body>
        </html>
        EOF;

        $htmltopdf = new CHtmlToPDF();
        $htmltopdf->generatePDF($content, false, new CCompteRendu(), $file, true, false);
    }

    $file->store();
  }

  /**
   * Chargement de la consultation préanesthésique pour l'opération courante
   *
   * @param bool $cache Utilisation du cache
   *
   * @return CMediusers
   */
  function loadRefPosition() {
    return $this->_ref_position = $this->loadFwdRef("_position_id", true);
  }

  /**
   * @inheritDoc
   */
  public function matchForImport(MatcherVisitorInterface $matcher): ImportableInterface {
    return $matcher->matchConsultationAnesth($this);
  }

  /**
   * @inheritDoc
   */
  public function persistForImport(PersisterVisitorInterface $persister): ImportableInterface {
    return $persister->persistObject($this);
  }
}
