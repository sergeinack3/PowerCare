<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

use DateTime;
use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbRange;
use Ox\Core\Module\CModule;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CTransmissionMedicale;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\PlanSoins\CAdministration;
use Ox\Mediboard\Prescription\CFunctionCategoryPrescription;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;
use Ox\Mediboard\Mpm\CPrisePosologie;

/**
 * Elément central de la planification d'évenements (aka séances) SSR par un rééducateur
 * et concernant un séjour et une ligne de prescription de ce séjour, pour une date donnée
 */
class CEvenementSSR extends CMbObject {
  // DB Table key
  public $evenement_ssr_id;

  // DB Fields
  public $prescription_line_element_id;
  public $sejour_id;
  public $debut; // DateTime
  public $duree; // Durée en minutes
  public $therapeute_id;
  public $therapeute2_id;
  public $therapeute3_id;
  public $equipement_id;
  public $realise;
  public $annule;
  public $remarque;
  public $seance_collective_id; // Evenement lié a une seance collective
  public $type_seance;
  public $nb_patient_seance;
  public $nb_intervenant_seance;
  public $plage_id;
  public $plage_groupe_patient_id;
  public $niveau_individuel;
  public $patient_missing;

  // Seances collectives
  public $_ref_element_prescription;
  public $_ref_seance_collective;

  // Derived Fields
  /** @var bool */
  public $_traite;
  /** @var string */
  public $_heure_fin;
  /** @var string */
  public $_heure_deb;
  /** @var int */
  public $_count_actes;

  // Behaviour Fields
  public $_nb_decalage_min_debut;
  public $_nb_decalage_heure_debut;
  public $_nb_decalage_jour_debut;
  public $_nb_decalage_duree;

  // References
  /** @var  CEquipement */
  public $_ref_equipement;
  /** @var  CSejour */
  public $_ref_sejour;
  /** @var  CMediusers */
  public $_ref_therapeute;
  /** @var  CActeCdARR[] */
  public $_ref_actes_cdarr;
  /** @var  CActeCsARR[] */
  public $_ref_actes_csarr;
  /** @var  CActeSSR[] */
  public $_ref_actes;
  /** @var  CEvenementSSR[] */
  public $_ref_evenements_seance = [];
  /** @var  CPrescriptionLineElement */
  public $_ref_prescription_line_element;
  /** @var  CRHS */
  public $_ref_rhs;
  /** @var  CTransmissionMedicale[] */
  public $_ref_transmissions;
  /** @var  CActePrestationSSR[] */
  public $_refs_prestas_ssr;
  /** @var CPlageSeanceCollective */
  public $_ref_plage_seance_collective;
  /** @var CPlageGroupePatient */
  public $_ref_plage_groupe_patient;

  // Behaviour field
  public $_traitement;
  public $_debut;
  public $_fin;
  public $_duree;
  public $_no_validation = false;
  public $_transmission = false;
  public $_administre = true;
  public $_counter_prestas_ssr = array();

  /**
   * @see parent::getSpecs()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'evenement_ssr';
    $spec->key   = 'evenement_ssr_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();

    $props["prescription_line_element_id"] = "ref class|CPrescriptionLineElement back|evenements_ssr";
    $props["sejour_id"]                    = "ref class|CSejour show|0 back|evenements_ssr";
    $props["debut"]                        = "dateTime show|0";

    $props["_heure_deb"] = "time show|1";
    $props["_heure_fin"] = "time show|1";
    $props["duree"]      = "num min|0";

    $props["therapeute_id"]           = "ref class|CMediusers back|evenements_ssr";
    $props["therapeute2_id"]          = "ref class|CMediusers back|evenements_ssr2";
    $props["therapeute3_id"]          = "ref class|CMediusers back|evenements_ssr3";
    $props["equipement_id"]           = "ref class|CEquipement back|evenements_ssr";
    $props["realise"]                 = "bool default|0";
    $props["annule"]                  = "bool default|0";
    $props["remarque"]                = "str";
    $props["seance_collective_id"]    = "ref class|CEvenementSSR back|evenements_ssr";
    $props["type_seance"]             = "enum list|dediee|non_dediee|collective default|dediee";
    $props["nb_patient_seance"]       = "num";
    $props["nb_intervenant_seance"]   = "num";
    $props["plage_id"]                = "ref class|CPlageSeanceCollective back|plage_evt_ssr";//Ne pas mettre de cascade!!
    $props["plage_groupe_patient_id"] = "ref class|CPlageGroupePatient back|evenements_ssr";
    $props["niveau_individuel"]       = "enum list|1|2|3|4|5";
    $props["patient_missing"]         = "bool default|0";

    $props["_transmission"]            = "text";
    $props["_traite"]                  = "bool";
    $props["_nb_decalage_min_debut"]   = "num";
    $props["_nb_decalage_heure_debut"] = "num";
    $props["_nb_decalage_jour_debut"]  = "num";
    $props["_nb_decalage_duree"]       = "num";

    $props["_debut"] = "dateTime notNull";
    $props["_fin"]   = "dateTime notNull moreThan|_debut";
    $props["_duree"] = "num";

    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_traite    = $this->realise || $this->annule;
    $this->_heure_deb = CMbDT::time($this->debut);
    $this->_duree     = $this->seance_collective_id ? $this->loadRefSeanceCollective()->duree : $this->duree;
    $this->_heure_fin = CMbDT::time("+ $this->_duree MINUTES", $this->debut);

    if ($this->plage_groupe_patient_id && !$this->debut) {
      $plage_groupe = $this->loadRefPlageGroupePatient();

      $debut        = CMbDT::date("$plage_groupe->groupe_day this week") . " " . $plage_groupe->heure_debut;
      $this->_debut = $debut;
      $this->_fin   = CMbDT::dateTime("+ $this->duree minutes", $debut);
    }
  }

  /**
   * @see parent::check()
   */
  function check() {
    if ($this->_forwardRefMerging) {
      return null;
    }

    // Vérouillage d'un événement traité
    $this->completeField("realise", "annule", "nb_patient_seance", "nb_intervenant_seance");
    $this->_traite = $this->realise || $this->annule;
    if ($this->_traite && !$this->_traitement) {
      return CAppUI::tr("CEvenementSSR-already_treaty");
    }

    // Evénement dans les bornes du séjour
    $this->completeField("sejour_id", "debut");
    $sejour = $this->loadRefSejour();

    // Vérifier seulement les jours car les sorties peuvent être imprécises pour les hospit de jours
    if ($sejour->_id && $this->debut) {
      $date_debut  = CMbDT::date($this->debut);
      $date_entree = CMbDT::date(CMbDT::date($sejour->entree));
      $date_sortie = CMbDT::date(CMbDT::date($sejour->sortie));
      if (!CMbRange::in($date_debut, $date_entree, $date_sortie)) {
        return CAppUI::tr("CEvenementSSR-off_date_sejour");
      }
    }

    // Si le thérapeute n'a pas d'identifiant CdARR
    $this->completeField("therapeute_id");
    if ($this->fieldModified("realise") || $this->fieldModified("nb_patient_seance") || $this->fieldModified("nb_intervenant_seance")) {
      $therapeute = $this->loadRefTherapeute();
      if (!$therapeute->code_intervenant_cdarr) {
        return CAppUI::tr("CMediusers-code_intervenant_cdarr-none");
      }
    }

    //Vérification que l'utilisateur a le droit de créer des séance pour le rééducateur
    $this->loadRefTherapeute();
    if (!$this->_id && $this->therapeute_id && CAppUI::gconf("ssr general create_evt_user_can_edit")
      && !$this->_ref_therapeute->getPerm(PERM_EDIT)
    ) {
      return CAppUI::tr("CEvenementSSR-PERM_EDIT-therapeute");
    }

    if (!$this->_id && $this->sejour_id && CAppUI::gconf("ssr general lock_add_evt_conflit") && !$this->plage_groupe_patient_id) {
      $heure_deb = CMbDT::time($this->debut);
      $heure_fin = CMbDT::time("+ $this->duree minutes", $heure_deb);
      list($sql, $conflits) = $this->searchConflits($this->sejour_id, array(CMbDT::date($this->debut)), $heure_deb, $heure_fin, false);
      if (count($conflits)) {
        CStoredObject::massLoadFwdRef($conflits, "prescription_line_element_id");
        $sejours = CStoredObject::massLoadFwdRef($conflits, "sejour_id");
        CStoredObject::massLoadFwdRef($sejours, "patient_id");
        $msg_error = count($conflits) . " " . CAppUI::tr("CEvenementSSR-conflits") . " ";
        foreach ($conflits as $_evt_conflit) {
          /* @var CEvenementSSR $_evt_conflit */
          $_evt_conflit->loadRefSejour()->loadRefPatient();
          $_evt_conflit->loadRefPrescriptionLineElement();
          if ($_evt_conflit->type_seance == "collective" && !$_evt_conflit->seance_collective_id) {
            $msg_error .= CAppUI::tr("CEvenementSSR-seance_collective_id");
          }
          else {
            $msg_error .= $_evt_conflit->_ref_sejour->_ref_patient->_view;
          }
          $msg_error .= " - " . $_evt_conflit->_ref_prescription_line_element->_ref_element_prescription->_view;
          $msg_error .= " - " . CMbDT::format($_evt_conflit->debut, CAppUI::conf("datetime"));
          $msg_error .= " ($_evt_conflit->_duree" . CAppUI::tr("common-minute|pl") . ")";
        }

        return $msg_error;
      }
    }

    return parent::check();
  }

  /**
   * @see parent::store()
   */
  function store() {
    $this->completeField('seance_collective_id', "therapeute_id", "type_seance", "debut", "sejour_id");

    $therapeute = $this->loadRefTherapeute();
    // Si le thérapeute n'a pas d'identifiant CdARR
    if (!$therapeute->code_intervenant_cdarr) {
      return CAppUI::tr("CMediusers-code_intervenant_cdarr-none");
    }

    //Si la configuration n'autorise pas la validation des actes dans le futur, il ne doit pas être enregistré
    if ($this->_id && !CAppUI::gconf("ssr validation validation_actes_futur") &&
      ($this->fieldModified("realise", 1) || $this->fieldModified("annule", 1)) && CMbDT::date($this->debut) > CMbDT::date()) {
      return CAppUI::tr("CEvenementSSR-validation_actes_futur-no");
    }

    $create = $this->realise && ($this->fieldModified("realise", 1) || $this->fieldModified("nb_patient_seance") || $this->fieldModified("nb_intervenant_seance"));
    $delete = ($this->annule && $this->fieldModified("annule", 1)) || (!$this->annule && !$this->realise && $this->_id);

    if (($create || $delete) && $this->sejour_id) {
      $therapeute->loadRefIntervenantCdARR();
      $this->changeLignesRhs($create, $delete, $therapeute);
    }

    //Récupération du début de la séance collective (utile dans le décalage de séance)
    if ($this->seance_collective_id) {
      $this->debut = $this->loadRefSeanceCollective()->debut;
      $this->niveau_individuel = null;
    }

    if ($delete && $this->_traitement && !$this->sejour_id) {
      $count_not_facture = 0;
      foreach ($this->loadRefsEvenementsSeance() as $_event) {
        if ($_event->annule) {
          $count_not_facture++;
        }
        elseif (!$_event->getRHS()->facture) {
          $count_not_facture++;
          $_event->realise               = 0;
          $_event->annule                = $this->annule;
          $_event->nb_patient_seance     = "";
          $_event->nb_intervenant_seance = "";
          if ($msg = $_event->store()) {
            return $msg;
          }
        }
      }
      if (!$count_not_facture) {
        return CAppUI::tr("CRHS-failed-rhs_collectif-facture");
      }
    }

    //Aucune modification doit être effectuée lorsque le rhs est déjà facturé
    $this->getRHS();
    if ($this->_id && $this->sejour_id && $this->_ref_rhs->_id && $this->_ref_rhs->facture) {
      return CAppUI::tr("CRHS-failed-rhs-facture");
    }

    $recalculate = $this->_id && (($this->realise && ($this->fieldModified("nb_patient_seance") || $this->fieldModified("nb_intervenant_seance"))) || $delete) ? true : false;

    $field_modified_realise = $this->fieldModified("realise");
    $field_modified_annule  = $this->fieldModified("annule");
    $field_modified_debut   = $this->fieldModified("debut");

    // Standard store
    if ($msg = parent::store()) {
      return $msg;
    }

    if (CAppUI::gconf("ssr soins generation_plan_soins")) {
      $this->updatePlanificationSoin($field_modified_realise, $field_modified_annule, $field_modified_debut);

      //Ajout de la transmission à la dernière administration notée
      if ($this->_transmission && $this->sejour_id) {
        $this->clearBackRefCache("administrations_evt");
        $administrations =
          $this->loadBackRefs("administrations_evt", null, null, null, null, null, "administration", array("planification" => " = '0'"));
        $administration  = end($administrations);
        if ($administration && $administration->_id) {
          $transmission = new CTransmissionMedicale();
          $transmission->setObject($administration);
          $transmission->sejour_id = $this->sejour_id;
          $transmission->date      = "now";
          $transmission->text      = $this->_transmission;
          $transmission->type      = "data";
          $transmission->user_id   = CMediusers::get()->_id;
          if ($msg = $transmission->store()) {
            return $msg;
          }
        }
      }
    }

    if ($recalculate) {
      if ($this->therapeute_id && !$this->seance_collective_id && $this->type_seance == "collective") {
        foreach ($this->loadRefsEvenementsSeance() as $_event) {
          $_event->getRHS()->recalculate();
        }
      }
      else {
        $this->getRHS()->recalculate();
      }
    }
  }

  function updatePlanificationSoin($field_modified_realise, $field_modified_annule, $field_modified_debut) {

    // On sort si on est sur une seance collective (seance fictive)
    if (!$this->sejour_id || !CModule::getActive("planSoins")) {
      return;
    }

    // Chargement de la planification, si elle n'existe pas, on la crée
    $planifications =
      $this->loadBackRefs("administrations_evt", null, null, null, null, null, "planification", array("planification" => " = '1'"));
    $planification  = is_countable($planifications) && count($planifications) > 0 ? reset($planifications) : false;
    if (!$planification) {
      $prise               = new CPrisePosologie();
      $prise->object_class = "CPrescriptionLineElement";
      $prise->object_id    = $this->prescription_line_element_id;
      $prise->loadMatchingObject();

      $planification                    = new CAdministration();
      $planification->object_class      = "CPrescriptionLineElement";
      $planification->object_id         = $this->prescription_line_element_id;
      $planification->quantite          = 1;
      $planification->dateTime          = $this->debut;
      $planification->administrateur_id = $this->therapeute_id;
      if ($prise->_id) {
        $planification->prise_id = $prise->_id;
      }
      else {
        $planification->unite_prise = "aucune_prise";
      }
      $planification->evenement_ssr_id     = $this->_id;
      $planification->_force_planification = 1;
      $planification->planification        = 1;

      return $planification->store();
    }

    // Si le début de la planification a été modifié, on la sauvegarde
    if ($field_modified_debut) {
      $planification->dateTime = $this->debut;

      return $planification->store();
    }

    if ($field_modified_realise) {
      if ($this->realise) {
        // Chargement de la planification puis création de l'administration
        if ($this->_administre) {
          $administrations               =
            $this->loadBackRefs("administrations_evt", null, null, null, null, null, "planification", array("planification" => " = '1'"));
          $administration                = reset($administrations);
          $administration->_id           = "";
          $administration->planification = "0";
          $administration->store();
        }
      }
      else {
        // Suppression de l'administration
        $administrations =
          $this->loadBackRefs("administrations_evt", null, null, null, null, null, "administration", array("planification" => " = '0'"));
        $administration  = reset($administrations);
        if ($administration && $administration->_id) {
          $administration->delete();
        }
      }
    }

    if ($field_modified_annule) {
      if ($this->annule) {
        // Chargement de la planification puis création de l'administration avec une quantité 0 pour noter l'annulation
        $administrations               =
          $this->loadBackRefs("administrations_evt", null, null, null, null, null, "planification", array("planification" => " = '1'"));
        $administration                = reset($administrations);
        $administration->_id           = "";
        $administration->planification = "0";
        $administration->quantite      = 0;
        $administration->store();
      }
      else {
        // Suppression de l'administration
        $administrations = $this->loadBackRefs(
          "administrations_evt", null, null, null, null, null, "administration", array("planification" => " = '0'", "quantite" => " = '0'")
        );
        $administration  = reset($administrations);
        if ($administration && $administration->_id) {
          $administration->delete();
        }
      }
    }
  }

  function changeLignesRhs($create, $delete, $therapeute) {
    $code_intervenant_cdarr = $therapeute->_ref_intervenant_cdarr->code;
    // Création du RHS au besoins
    $rhs = $this->getRHS();
    if (!$rhs->_id && $this->realise) {
      $rhs->store();
    }
    if (!$rhs->_id) {
      return null;
    }

    if ($rhs->facture == 1) {
      CAppUI::stepAjax(CAppUI::tr("CRHS.charged"), UI_MSG_WARNING);
    }
    $this->loadView();
    $cdarrs = $this->loadRefsActesCdARR();

    // Complétion de la ligne RHS
    foreach ($cdarrs as $_acte_cdarr) {
      $ligne                         = new CLigneActivitesRHS();
      $ligne->rhs_id                 = $rhs->_id;
      $ligne->executant_id           = $therapeute->_id;
      $ligne->code_activite_cdarr    = $_acte_cdarr->code;
      $ligne->code_intervenant_cdarr = $code_intervenant_cdarr;
      $ligne->loadMatchingObject();
      if ($ligne->_id && $delete) {
        if ($msg = $ligne->delete()) {
          return $msg;
        }
      }
      elseif ($create) {
        $ligne->crementDay($this->debut, $this->realise ? "inc" : "dec");
        $ligne->auto = "1";
        $ligne->store();
      }
    }

    $csarrs = $this->loadRefsActesCsARR();

    foreach ($csarrs as $_acte_csarr) {
      $ligne                         = new CLigneActivitesRHS();
      $ligne->rhs_id                 = $rhs->_id;
      $ligne->executant_id           = $therapeute->_id;
      $ligne->code_activite_csarr    = $_acte_csarr->code;
      $ligne->code_intervenant_cdarr = $code_intervenant_cdarr;
      $ligne->modulateurs            = $_acte_csarr->modulateurs ?: "";
      $ligne->phases                 = $_acte_csarr->phases ?: "";
      $ligne->commentaire            = $_acte_csarr->commentaire ?: "";
      $ligne->extension              = $_acte_csarr->extension ?: "";
      $ligne->nb_patient_seance      = $this->nb_patient_seance ?: "";
      $ligne->nb_intervenant_seance  = $this->nb_intervenant_seance ?: "";
      $ligne->loadMatchingObject();
      if ($ligne->_id && $delete) {
        if ($msg = $ligne->delete()) {
          return $msg;
        }
      }
      elseif ($create) {
        $ligne->crementDay($this->debut, $this->realise ? "inc" : "dec", $_acte_csarr->quantite);
        $ligne->auto = "1";
        $ligne->store();
      }
    }

    // Prestations SSR
    $this->loadRefsActesPrestationsSSR();
    foreach (array($this->_refs_prestas_ssr) as $_prestas) {
      if (is_array($_prestas)) {
        foreach ($_prestas as $_presta) {
          $ligne                = new CLigneActivitesRHS();
          $ligne->rhs_id        = $rhs->_id;
          $ligne->executant_id  = $therapeute->_id;
          $ligne->code_activite = $_presta->code;
          $ligne->type_activite = $_presta->type;
          $ligne->loadMatchingObject();
          if ($ligne->_id && $delete) {
            if ($msg = $ligne->delete()) {
              return $msg;
            }
          }
          elseif ($create) {
            $ligne->crementDay($this->debut, $this->realise ? "inc" : "dec", $_presta->quantite);
            $ligne->auto = "1";
            $ligne->store();
          }
        }
      }
    }
  }

  /**
   * @see parent::canDeleteEx()
   */
  function canDeleteEx() {
    if ($msg = parent::canDeleteEx()) {
      return $msg;
    }

    // Impossible de supprmier un événement réalisé
    $this->completeField("realise", "annule");
    $this->_traite = $this->realise || $this->annule;
    if ($this->realise && !$this->_traitement) {
      return CAppUI::tr("CEvenementSSR-msg-delete-failed-realise");
    }

    return null;
  }

  /**
   * @throws Exception
   * @see parent::loadView()
   */
  function loadView() {
    parent::loadView();

    $sejour  = $this->loadRefSejour();
    $patient = $sejour->loadRefPatient();

    if ($this->seance_collective_id && (!$this->debut || !$this->duree)) {
      $this->loadRefSeanceCollective();
      $this->debut = $this->_ref_seance_collective->debut;
      $this->duree = $this->_ref_seance_collective->duree;
    }

    $this->loadRefPlageGroupePatient();

    $this->_view = "$patient->_view - " . CMbDT::dateToLocale(CMbDT::date($this->debut));

    $this->loadRefsActesCdARR();
    $this->loadRefsActesCsARR();
    $this->loadRefsActesPrestationsSSR();

    if (!$this->sejour_id) {
      $this->loadRefsEvenementsSeance();
      foreach ($this->_ref_evenements_seance as $_evt_seance) {
        $_evt_seance->loadRefSejour()->loadRefPatient();
      }
    }
    $this->loadRefsTransmissions();
    $this->updateFormFields();
  }

  /**
   * Charge la ligne de prescription associée
   *
   * @return CPrescriptionLineElement
   */
  function loadRefPrescriptionLineElement() {
    /** @var CPrescriptionLineElement $line */
    $line = $this->loadFwdRef("prescription_line_element_id", true);

    // Prescription may not be active
    if ($line) {
      $line->loadRefElement();
    }

    if (!$line->_id && !$this->sejour_id && count($this->_ref_evenements_seance)) {
      $line = reset($this->_ref_evenements_seance)->loadRefPrescriptionLineElement();
    }

    return $this->_ref_prescription_line_element = $line;
  }

  /**
   * Charge le séjour associé
   *
   * @return CSejour
   */
  function loadRefSejour() {
    return $this->_ref_sejour = $this->loadFwdRef("sejour_id", true);
  }

  /**
   * Charge l'équipement associé
   *
   * @return CEquipement
   */
  function loadRefEquipement() {
    return $this->_ref_equipement = $this->loadFwdRef("equipement_id", true);
  }

  /**
   * Charge le therapeute associé
   *
   * @param bool $cached Use cache
   *
   * @return CMediusers
   */
  function loadRefTherapeute($cached = true) {
    return $this->_ref_therapeute = $this->loadFwdRef("therapeute_id", $cached);
  }

  /**
   * Charge la séance parente, dans le cas des séances collectives
   *
   * @return CEvenementSSR
   */
  function loadRefSeanceCollective() {
    return $this->_ref_seance_collective = $this->loadFwdRef("seance_collective_id", true);
  }

  /**
   * Chage les actes CdARR
   *
   * @return CActeCdARR[]
   */
  function loadRefsActesCdARR() {
    return $this->_ref_actes_cdarr = $this->loadBackRefs("actes_cdarr");
  }

  /**
   * Charge les actes CsARR
   *
   * @return CActeCsARR[]
   */
  function loadRefsActesCsARR() {
    return $this->_ref_actes_csarr = $this->loadBackRefs("actes_csarr");
  }

  /**
   * Charge les prestations SSR
   *
   * @return CActePrestationSSR[]
   */
  function loadRefsActesPrestationsSSR() {
    $prestas_ssr = $this->loadBackRefs("prestas_ssr", "code ASC", null, null, null, null, "prestas_H+", array("type" => " = 'presta_ssr'"));

    $this->_counter_prestas_ssr = array();
    foreach ($prestas_ssr as $_presta_ssr) {
      if (!isset($this->_counter_prestas_ssr[$_presta_ssr->code]['quantity'])) {
        $this->_counter_prestas_ssr[$_presta_ssr->code]['quantity'] = 0;
      }
      $this->_counter_prestas_ssr[$_presta_ssr->code]['quantity'] += $_presta_ssr->quantite;
      $this->_counter_prestas_ssr[$_presta_ssr->code]['acte_prestation_id'] = $_presta_ssr->_id;
    }

    return $this->_refs_prestas_ssr = $prestas_ssr;
  }

  /**
   * Charge tous les actes
   *
   * @return CActeSSR[][] Actes classés par type
   */
  function loadRefsActes() {
    $actes_cdarr = $this->loadRefsActesCdARR();
    $actes_csarr = $this->loadRefsActesCsARR();
    $this->loadRefsActesPrestationsSSR();

    return $this->_ref_actes = array(
      "cdarr"   => $actes_cdarr,
      "csarr"   => $actes_csarr,
      "prestas" => $this->_refs_prestas_ssr,
    );
  }

  /**
   * Chargement les séances filles dans les cas des séances collectives
   * Une séance fille par séjour
   *
   * @return CEvenementSSR[]
   */
  function loadRefsEvenementsSeance() {
    return $this->_ref_evenements_seance = $this->loadBackRefs("evenements_ssr");
  }

  /**
   * Charge le RHS correspondant à l'évenement
   *
   * @return CRHS
   */
  function getRHS() {
    $rhs = new CRHS();
    if ($this->sejour_id) {
      $rhs->sejour_id   = $this->sejour_id;
      $rhs->date_monday = CMbDT::date("last monday", CMbDT::date("+1 day", CMbDT::date($this->debut)));
      $rhs->loadMatchingObject();
    }

    return $this->_ref_rhs = $rhs;
  }

  /*
   * Récupération des transmissions notées pour les administrations
   */
  function loadRefsTransmissions() {
    $transmissions = array();
    if (!$this->sejour_id && count($this->_ref_evenements_seance)) {
      foreach ($this->_ref_evenements_seance as $_seance) {
        $_seance->loadRefsTransmissions();
        $transmissions = array_merge($transmissions, $_seance->_ref_transmissions);
      }
    }
    elseif (CModule::getActive("planSoins")) {
      $administrations = $this->loadBackRefs("administrations_evt", null, null, null, null, null, "administration", array("planification" => " = '0'"));
      if ($administrations) {
        foreach ($administrations as $_administration) {
          /* @var CAdministration $_administration */
          $_administration->loadRefsTransmissions();
          $transmissions = array_merge($transmissions, $_administration->_ref_transmissions);
        }
      }
    }

    return $this->_ref_transmissions = $transmissions;
  }

  /**
   * Donne le nombre de jours d'activités visibles pour le rééducateur dans la semaine demandée
   *
   * @param string $user_id Identifiant de rééducateur
   * @param string $date    Jour définissant la semaine englobante
   *
   * @return int 5, 6 ou 7 jours, suivant si les samedi et/ou dimanche sont ouvrés
   */
  static function getNbJoursPlanning($user_id, $date) {
    $sunday   = CMbDT::date("next sunday", CMbDT::date("- 1 DAY", $date));
    $saturday = CMbDT::date("-1 DAY", $sunday);

    $_evt                         = new CEvenementSSR();
    $where                        = array();
    $ljoin                        = array();
    $ljoin["sejour"]              = "sejour.sejour_id = evenement_ssr.sejour_id";
    $where["sejour.group_id"]     = " = '" . CGroups::loadCurrent()->_id . "'";
    $where["evenement_ssr.debut"] = "BETWEEN '$sunday 00:00:00' AND '$sunday 23:59:59'";
    if ($user_id) {
      $where["evenement_ssr.therapeute_id"] = " = '$user_id'";
    }
    $count_event_sunday = $_evt->countList($where, null, $ljoin);

    $where_col           = $where;
    $ljoin_col           = array();
    $ljoin_col[]         = "evenement_ssr AS evt_seance ON (evt_seance.seance_collective_id = evenement_ssr.evenement_ssr_id)";
    $ljoin_col["sejour"] = "sejour.sejour_id = evt_seance.sejour_id";
    $where_col[]         = "evenement_ssr.sejour_id IS NULL";
    $count_event_sunday  += $_evt->countList($where_col, null, $ljoin_col);
    $nb_days             = 7;

    // Si aucun evenement le dimanche
    if (!$count_event_sunday) {
      $nb_days                          = 6;
      $where["evenement_ssr.debut"]     = "BETWEEN '$saturday 00:00:00' AND '$saturday 23:59:59'";
      $where_col["evenement_ssr.debut"] = "BETWEEN '$saturday 00:00:00' AND '$saturday 23:59:59'";
      $count_event_saturday             = $_evt->countList($where, null, $ljoin);
      $count_event_saturday             += $_evt->countList($where_col, null, $ljoin_col);
      // Aucun evenement le samedi et aucun le dimanche
      if (!$count_event_saturday) {
        $nb_days = 5;
      }
    }

    return $nb_days;
  }

  /**
   * Find all therapeutes for a patient
   *
   * @param string $patient_id  Patient
   * @param string $function_id May restrict to a function
   *
   * @return CMediusers[]
   */
  static function getAllTherapeutes($patient_id, $function_id = null) {
    // Filter on patient
    $join["sejour"]      = "sejour.sejour_id = evenement_ssr.sejour_id";
    $where["patient_id"] = "= '$patient_id'";

    // Filter on function
    if ($function_id) {
      $join["users_mediboard"] = "users_mediboard.user_id = evenement_ssr.therapeute_id";
      $where["function_id"]    = "= '$function_id'";
    }

    // Load grouped
    $group      = "therapeute_id";
    $evenement  = new self;
    $evenements = $evenement->loadList($where, null, null, $group, $join);

    // Load therapeutes
    $therapeutes = CMbObject::massLoadFwdRef($evenements, "therapeute_id");
    foreach ($therapeutes as $_therapeute) {
      $_therapeute->loadRefFunction();
    }

    return $therapeutes;
  }

  /**
   * Find all therapeutes having planned events
   *
   * @param string $min Minimal date to start from
   * @param string $max Maximal date to stop to
   *
   * @return array[CMediusers]
   */
  static function getActiveTherapeutes($min, $max) {
    $max            = CMbDT::date("+1 DAY", $max);
    $query          = "SELECT DISTINCT therapeute_id FROM `evenement_ssr` 
      WHERE debut BETWEEN '$min' AND '$max'";
    $that           = new self;
    $ds             = $that->_spec->ds;
    $therapeute_ids = $ds->loadColumn($query);

    $therapeute = new CMediusers();

    return $therapeute->loadAll($therapeute_ids);
  }

  /**
   * @see parent::delete()
   */
  function delete() {
    $this->completeField("seance_collective_id");
    $seance_collective_id = $this->seance_collective_id;

    // Standard delete
    if ($msg = parent::delete()) {
      return $msg;
    }

    if ($seance_collective_id) {
      $seance = new CEvenementSSR();
      $seance->load($seance_collective_id);

      // Suppression de la seance si plus aucune backref
      if ($seance->countBackRefs("evenements_ssr") == 0) {
        if ($msg = $seance->delete()) {
          return $msg;
        }
      }
    }
  }

  /**
   * Compte le nombre d'évenement non validé pour un rééducateur
   *
   * @param int    $kine_id Kiné
   * @param string $date    Date
   * @param string $type    Module/Type de séjour
   *
   * @return array
   */
  static function countByWeekNbNotValide($kine_id, $date, $type) {
    $query1 = "SELECT  count(DISTINCT(evenement_ssr.evenement_ssr_id)) AS nb_evts, WEEK(evenement_ssr.debut) AS period
      FROM evenement_ssr
      LEFT JOIN evenement_ssr AS evt_seance ON (evt_seance.evenement_ssr_id = evenement_ssr.seance_collective_id)
      LEFT JOIN `sejour` ON sejour.sejour_id = evenement_ssr.sejour_id
      WHERE `evenement_ssr`.`realise`  = '0'
      AND `evenement_ssr`.`annule`  = '0'
      AND sejour.sejour_id IS NOT NULL
      AND `sejour`.`type`  = '$type'
      AND `sejour`.`group_id`  = '".CGroups::loadCurrent()->_id."'
      AND evenement_ssr.therapeute_id = '$kine_id'
      AND evenement_ssr.debut < '$date 00:00:00'
      GROUP BY period
      ORDER BY evenement_ssr.debut DESC";
    $ds      = CSQLDataSource::get("std");
    $results = $ds->loadList($query1);

    $nb_evts_by_week = array();
    foreach ($results as $_result) {
      $nb_evts_by_week[$_result["period"]] = $_result["nb_evts"];
    }
    krsort($nb_evts_by_week);

    return $nb_evts_by_week;
  }

  /**
   * Recherche les conflits potentiels avant la création de l'évènement
   *
   * @param int    $sejour_id  Séjour
   * @param array  $days       Jours à prendre en compte
   * @param string $heure_deb  Heure de début
   * @param string $heure_fin  Heure de fin
   * @param bool   $only_count Retourne un compte ou la liste
   *
   * @return array|int
   */
  function searchConflits($sejour_id, $days, $heure_deb, $heure_fin, $only_count = false) {
    $sql = "";
    foreach ($days as $day_used) {
      if ($sql) {
        $sql .= " OR ";
      }
      $sql .= "(debut BETWEEN '$day_used 00:00:00' AND '$day_used 23:59:59')";
    }

    $where                    = array();
    $where[]                  = $sql;
    $where["sejour_id"]       = " = '$sejour_id'";
    $where["evenement_ssr.patient_missing"] = " = '0'";
    $seance_ssr_patient = new CEvenementSSR();
    $conflits           = $seance_ssr_patient->loadList($where, "debut", null, "evenement_ssr_id");

    foreach ($conflits as $_evt_ssr) {
      $date_evt = CMbDT::date($_evt_ssr->debut);
      $evt_fin  = $date_evt . " " . $heure_fin;
      if (!($_evt_ssr->debut < $evt_fin && $_evt_ssr->_heure_fin > $heure_deb)) {
        unset($conflits[$_evt_ssr->_id]);
      }
    }

    return $only_count ? count($conflits) : array($sql, $conflits);
  }

  /**
   * Chargement de la liste des utilisateurs possibles du planning
   *
   * @param array $where Condition
   *
   * @return CMediusers[]
   */
  static function loadRefExecutants($group_id, $function_id = null) {
    if (CModule::getActive("dPprescription")) {
      $where   = array();
      $where[] = "functions_mediboard.group_id = '$group_id'";
      $where["users_mediboard.actif"] = " = '1'";
      $where[] = "users_mediboard.fin_activite IS NULL OR users_mediboard.fin_activite > '".CMbDT::date()."'";
      if ($function_id) {
        $where[] = "functions_mediboard.function_id = '$function_id'";
      }

      return CFunctionCategoryPrescription::getAllExecutants($where);
    }
    $user = new CMediusers();

    return $user->loadKines(null, $function_id);
  }

  /**
   * Charge la plage de séance collective associée
   *
   * @return CPlageSeanceCollective
   * @throws Exception
   */
  public function loadRefPlageSeanceCollective() {
    return $this->_ref_plage_seance_collective = $this->loadFwdRef("plage_id", true);
  }

  /**
   * Load the patient group range
   *
   * @return CPlageGroupePatient
   * @throws Exception
   */
  public function loadRefPlageGroupePatient() {
    return $this->_ref_plage_groupe_patient = $this->loadFwdRef("plage_groupe_patient_id", true);
  }

  /**
   * Supprime l'ensemble des événements SSR en collision en fonction d'un ensemble de jours.
   *
   * @param array|bool $days_list            (opt) Liste des jours concernés
   * @param bool|int   $niveau               (opt) Niveau de priorité appliqué
   * @param bool|int   $sejour_id            (opt) Sejour concerné (par défaut, récupère celui de la séance)
   * @param string|int $compare_niveaux_mode (opt) Comparateur de niveaux à utiliser (ignoré si sans niveau)
   * @param bool|int   $use_plage            (opt) Tenir compte des plages collectives (si false, se base sur l'evenement)
   *
   * @return array Messages d'erreurs des suppressions
   */
  function deleteCollectivesByPlage($days_list = false, $niveau = false, $sejour_id = false, $compare_niveaux_mode = ">", $use_plage = true) {
    if (!CAppUI::gconf("ssr general lock_add_evt_conflit")) {
      return;
    }
    $evt_to_delete = $this->getCollectivesCollisions($days_list, $niveau, $sejour_id, $compare_niveaux_mode, $use_plage);
    $msgs          = array();
    foreach ($evt_to_delete as $_evt) {
      if ($msg = $_evt->delete()) {
        CAppUI::setMsg($msg, UI_MSG_ERROR);
        $msgs[] = $msg;
        continue;
      }
      CAppUI::displayMsg($msg, "CEvenementSSR-msg-delete");
    }

    return $msgs;
  }

  /**
   * Supprime les événements individuels en collision
   * Retourne FAUX si au moins un événement en collision a un niveau de priorité supérieur
   *
   * @return bool
   */
  function deleteIndividuellesCollisions() {
    if (!CAppUI::gconf("ssr general lock_add_evt_conflit")) {
      return true;
    }
    $evenements_collision = $this->getIndividuellesCollisions();
    foreach ($evenements_collision as $_evenement) {
      if ((!$this->niveau_individuel && $_evenement->niveau_individuel) ||
        ($_evenement->niveau_individuel && $_evenement->niveau_individuel < $this->niveau_individuel)) {
        return false;
      }

      if ($this->niveau_individuel && ($_evenement->niveau_individuel > $this->niveau_individuel || !$_evenement->niveau_individuel)) {
        if ($msg = $_evenement->delete()) {
          CAppUI::setMsg($msg, UI_MSG_ERROR);
          $msgs[] = $msg;
          continue;
        }
        CAppUI::displayMsg($msg, "CEvenementSSR-msg-delete");
      }
    }
    return true;
  }

  /**
   * Récupère l'ensemble des événements SSR collectifs en collision en fonction d'un ensemble de jours
   *
   * @param array|bool $days_list            (opt) Liste des jours concernés
   * @param bool|int   $niveau               (opt) Niveau de priorité appliqué
   * @param bool|int   $sejour_id            (opt) Sejour concerné (par défaut, récupère celui de la séance)
   * @param string|int $compare_niveaux_mode (opt) Comparateur de niveaux à utiliser (ignoré si sans niveau)
   * @param bool|int   $use_plage            (opt) Tenir compte des plages collectives (si false, se base sur l'evenement)
   *
   * @return CEvenementSSR[]
   */
  function getCollectivesCollisions($days_list = false, $niveau = false, $sejour_id = false, $compare_niveaux_mode = ">", $use_plage = true) {
    if (!CAppUI::gconf("ssr general lock_add_evt_conflit")) {
      return array();
    }
    if (!$days_list) {
      $days_list = array(CMbDT::date(null, $this->debut));
    }
    // Défaut : Utilisation des plages collectives : Nous ne regardons que les plages collectives
    $plage_debut_field = "plage_collective.debut";
    $plage_fin_field   = "DATE_ADD($plage_debut_field, INTERVAL plage_collective.duree MINUTE)";
    $ljoin             = array("plage_collective" => "plage_collective.plage_id = evenement_ssr.plage_id");
    if (!$use_plage) {
      // Utilisation sans plage collective (pour la comparaison de niveau, cela s'effectue après la requête)
      // Pour les date et la durée, nous devons nous baser sur celle de l'événement (ind.) ou celle du parent (coll.)
      $ljoin             = array(
        "`evenement_ssr` as seance_collective ON evenement_ssr.seance_collective_id = seance_collective.evenement_ssr_id"
      );
      $plage_debut_field = "TIME(IF(evenement_ssr.seance_collective_id IS NULL, evenement_ssr.debut, seance_collective.debut))";
      $plage_fin_field   =
        "DATE_ADD($plage_debut_field, 
          INTERVAL IF(evenement_ssr.seance_collective_id IS NULL, evenement_ssr.duree, seance_collective.duree) MINUTE)";
    }
    $fin       = CMbDT::time("+$this->duree MINUTES", $this->debut);
    $debut     = CMbDT::time(null, $this->debut);
    $sejour_id = $sejour_id ?: $this->sejour_id;
    $where     = array(
      "evenement_ssr.sejour_id" => " = '$sejour_id'",
      "evenement_ssr.realise"   => "= '0'",
      "DATE(evenement_ssr.debut) " . CSQLDataSource::prepareIn($days_list),
      "evenement_ssr.patient_missing" => "= '0'",
    );
    if ($niveau && $use_plage) {
      $where = array_merge($where, array("niveau" => "$compare_niveaux_mode $niveau"));
    }
    $where         = array_merge(
      $where,
      array(
        "$plage_debut_field < '$fin'",
        "$plage_fin_field > '$debut'"
      )
    );
    $evt_to_delete = $this->loadList($where, "evenement_ssr.debut", null, null, $ljoin);
    if (!$use_plage) {
      CStoredObject::massLoadFwdRef($evt_to_delete, 'plage_id');

      foreach ($evt_to_delete as $_key => $_evt) {
        $plage = $_evt->loadRefPlageSeanceCollective();
        if (($plage->niveau && $plage->niveau > $niveau
            && $compare_niveaux_mode === "<")
          || (!$plage->_id && $plage->niveau < $niveau
            && $compare_niveaux_mode === ">")
        ) {
          unset($evt_to_delete[$_key]);
        }
      }
    }

    return $evt_to_delete;
  }

  /**
   * Récupère l'ensemble des événements SSR en collision en fonction d'un ensemble de jours
   *
   * @return CEvenementSSR[]
   */
  function getIndividuellesCollisions() {
    $where = array(
      "evenement_ssr.sejour_id" => " = '$this->sejour_id'",
      "evenement_ssr.debut" => "< '" . CMbDT::dateTime("+ $this->duree minutes", $this->debut)  . "'",
      "DATE_ADD(evenement_ssr.debut, INTERVAL evenement_ssr.duree MINUTE) 
        > '$this->debut'",
      "evenement_ssr.evenement_ssr_id" => "<> '$this->_id'",
      "evenement_ssr.seance_collective_id" => "IS NULL",
      "evenement_ssr.type_seance" => "<> 'collective'",
      "evenement_ssr.patient_missing" => "= '0'",
    );
    return $this->loadList($where);
  }

  /**
   * Récupération de la liste des thérapeute de l'évènement dans un tableau
   *
   * @return array Therapeute list
   */
  public function getTherapeutes() {
    $therapeute_list = array($this->therapeute_id);
    if ($this->seance_collective_id) {
      $_seance_collective = $this->loadRefSeanceCollective();
      $therapeute_list[]  = $_seance_collective->therapeute_id;
      if ($_seance_collective->therapeute2_id) {
        $therapeute_list[] = $_seance_collective->therapeute2_id;
      }
      if ($_seance_collective->therapeute3_id) {
        $therapeute_list[] = $_seance_collective->therapeute3_id;
      }
    }
    else {
      if ($this->therapeute2_id) {
        $therapeute_list[] = $this->therapeute2_id;
      }
      if ($this->therapeute3_id) {
        $therapeute_list[] = $this->therapeute3_id;
      }
    }

    return $therapeute_list;
  }

  /**
   * Edit SSR events for a group range
   *
   * @param CSejour       $sejour    Sejour
   * @param DateTime      $datetime  Datetime
   * @param CEvenementSSR $event_ssr SSR event
   * @param array         $acte      Acte information
   *
   * @return void
   * @throws Exception
   */
  static function editSSREventsForGroupRange(CSejour $sejour, $datetime, CEvenementSSR $event_ssr, $acte) {
    $ljoin = array(
      "acte_csarr" => "acte_csarr.evenement_ssr_id = evenement_ssr.evenement_ssr_id"
    );

    $where = array(
      "evenement_ssr.plage_groupe_patient_id" => " = '$event_ssr->plage_groupe_patient_id'",
      "evenement_ssr.debut"                   => " = '$datetime'",
      "evenement_ssr.realise"                 => " = '0'",
      "evenement_ssr.annule"                  => " = '0'",
      "acte_csarr.code"                       => " = '" . $acte["code"]. "'",
      "evenement_ssr.sejour_id"               => " = '$event_ssr->sejour_id'"
    );

    $evenement_ssr = new CEvenementSSR();
    $events_ssr    = $evenement_ssr->loadList($where, null, null, null, $ljoin);

    foreach ($events_ssr as $_event_ssr) {
      if ($acte["delete"] == 0) {
        if ((($_event_ssr->duree != $acte["duree"]) || ($_event_ssr->type_seance != $acte["type_seance"]) ||
          ($_event_ssr->therapeute_id != $acte["executant"]))) {

          $_event_ssr->duree         = $acte["duree"];
          $_event_ssr->type_seance   = $acte["type_seance"];
          $_event_ssr->therapeute_id = $acte["executant"];

          // Remove canceled because the event is modified
          if ($_event_ssr->annule) {
            $_event_ssr->annule = 0;
          }

          // Create new event because the event is realized yet
          $statut = "modify";

          /*if ($_event_ssr->realise) {
           $_event_ssr->evenement_ssr_id = null;
           $_event_ssr->realise = 0;

           CApp::dump($_event_ssr);

           $statut = "create";
         }*/

          $msg = $_event_ssr->store();
          CAppUI::displayMsg($msg, "$_event_ssr->_class-msg-$statut");
        }

        $event_actes_csarr = $_event_ssr->loadRefsActesCsARR();

        if ($event_actes_csarr && count($event_actes_csarr)) {
          foreach ($event_actes_csarr as $_acte_ssr) {
            if (($_acte_ssr->code == $acte["code"]) && ($_event_ssr->sejour_id == $_acte_ssr->sejour_id) && ($_event_ssr->_id == $_acte_ssr->evenement_ssr_id)) {
              $_acte_ssr->_modulateurs = explode("|", $acte["modulateurs"]);
              $_acte_ssr->extension    = $acte["extension"];

              $msg = $_acte_ssr->store();
              CAppUI::displayMsg($msg, "$_acte_ssr->_class-msg-modify");
            }
            else {
              // Add Acts
              $acte_csarr                   = new CActeCsARR();
              $acte_csarr->evenement_ssr_id = $_event_ssr->_id;
              $acte_csarr->sejour_id        = $_event_ssr->sejour_id;
              $acte_csarr->code             = $acte["code"];
              $acte_csarr->_modulateurs     = explode("|", $acte["modulateurs"]);
              $acte_csarr->extension        = $acte["extension"];

              $msg = $acte_csarr->store();
              CAppUI::displayMsg($msg, "$acte_csarr->_class-msg-create");
            }
          }
        }
      }
      elseif ($acte["delete"] == 1) {
        $msg = $_event_ssr->delete();
        CAppUI::displayMsg($msg, "$_event_ssr->_class-msg-delete");
      }
    }
  }
}
