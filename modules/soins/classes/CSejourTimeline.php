<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Soins;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Brancardage\CBrancardage;
use Ox\Mediboard\Cabinet\CExamIgs;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;

/**
 * Description
 */
class CSejourTimeline implements IShortNameAutoloadable {

  /** @var CSejour The sejour */
  public $sejour;

  /** @var array The events */
  public $events;

  /** @var array An event count by type and categorie */
  public $counts;

  /** @var array The list of the weeks displayed under the menu */
  public $weeks;

  /** @var array The list of the months displayed under the menu */
  public $months;

  /** @var array The categories to display */
  public $displayed_categories = array();

  /** @var array The events to display */
  public $displayed_events = array();

  /** @var array The types of event */
  public static $categories = array(
    'movements'            => array(
      'affectation_begin',
      'affectation_end',
      'CBrancardageItem',
      '_icon'       => 'fa fa-calendar',
      '_menu_style' => ' position: relative; left: -35px;'
    ),
    'documents_sejour'     => array(
      'CCompteRendu',
      'CFile',
      'CExObject',
      '_icon'       => 'fa fa-file',
      '_menu_style' => ''
    ),
    'prescriptions'        => array(
      'CPrescriptionLine_begin',
      'CPrescriptionLine_end',
      'CAdministration',
      '_icon'       => 'icon icon-i-pharmacy',
      '_menu_style' => ''
    ),
    'consultations_sejour' => array(
      'CConsultation',
      'CConsultAnesth',
      'VisiteAnesth',
      '_icon'       => 'fa fa-user-md',
      '_menu_style' => ''
    ),
    'saisies'              => array(
      'score',
      'CConstantesMedicales',
      'CObservationMedicale',
      'CTransmissionMedicale',
      '_icon'       => 'fa fa-align-justify',
      '_menu_style' => ' position: relative; left: 40px;'
    ),
    'operations'           => array(
      'COperation',
      '_icon'       => 'icon-i-surgery',
      '_menu_style' => ' width: 160px; height: 20px; position: relative; left: 50px;'
    )
  );

  /**
   * CTimelineSejour constructor.
   *
   * @param integer $sejour_id  The sejour id
   * @param string  $categories The category to display
   * @param string  $events     The events to display
   */
  public function __construct($sejour_id, $categories = 'all', $events = 'all') {
    $this->sejour = CMbObject::loadFromGuid("CSejour-$sejour_id");
    $this->events = array();

    $dossier_addictologie = $this->sejour->loadRefDossierAddictologie();

    if (CModule::getActive('addictologie') && $dossier_addictologie && $dossier_addictologie->_id) {
      self::$categories['objectifs_soins'] = array(
        'objectifs_soins_open',
        'objectifs_soins_achieved',
        'objectifs_soins_not_achieved',
        '_icon'       => 'fa fa-tasks',
        '_menu_style' => ' width: 120px; position: relative; left: 60px;'
      );

      self::$categories['pathologies'] = array(
        'pathologie_begin',
        'pathologie_end',
        '_icon'       => 'fa fa-heartbeat',
        '_menu_style' => ' width: 120px; position: relative; left: 80px;'
      );

      self::$categories['suivis'] = array(
        'suivi_begin',
        'suivi_end',
        '_icon'       => 'fas fa-notes-medical',
        '_menu_style' => ' width: 120px; position: relative; left: 90px;'
      );

      self::$categories['notes_suite'] = array(
        'note_suite_medical',
        'note_suite_psycho',
        'note_suite_social',
        'note_suite_other',
        '_icon'       => 'far fa-sticky-note',
        '_menu_style' => ' width: 150px; position: relative; left: 110px;'
      );

      // Remove the display of consultations/Score IGS
      self::$categories['consultations_sejour'] = array(
        'CConsultation',
        '_icon'       => 'fa fa-user-md',
        '_menu_style' => ''
      );

      // Remove the display of brancardage
      self::$categories['movements'] = array(
      'affectation_begin',
      'affectation_end',
      '_icon'       => 'fa fa-calendar',
      '_menu_style' => ' position: relative; left: -35px;'
    );

      self::$categories['saisies'] = array(
        'CConstantesMedicales',
        'CObservationMedicale',
        'CTransmissionMedicale',
        '_icon'       => 'fa fa-align-justify',
        '_menu_style' => ' position: relative; left: 40px;'
      );

      // Don't show operation and consult anesth
      unset(self::$categories["operations"]);
    }

    foreach (self::$categories as $_category => $_types) {
      $this->counts[$_category] = array();

      $this->displayed_categories[$_category] = ($categories == 'all' || $_category == $categories) ? true : false;

      foreach ($_types as $_type) {
        $this->counts[$_category][$_type] = 0;
        $this->displayed_events[$_type]   = ($events == 'all' || $_type == $events) ? true : false;
      }
    }
  }

  /**
   * Get the events from the sejour
   *
   * @return void
   */
  public function get() {
    foreach (self::$categories as $_category => $_events) {
      if ($this->displayed_categories[$_category]) {
        switch ($_category) {
          case 'movements':
            $this->getMovements();
            break;
          case 'documents_sejour':
            $this->getDocuments();
            break;
          case 'prescriptions':
            $this->getPrescriptions();
            break;
          case 'consultations_sejour':
            $this->getConsultations();
            break;
          case 'saisies':
            $this->getSaisies();
            break;
          case 'operations':
            $this->getOperations();
            break;
          case 'pathologies':
            $this->getPathologies();
            break;
          case 'suivis':
            $this->getSuivis();
            break;
          case 'objectifs_soins':
            $this->getObjectifsSoins();
            break;
          case 'notes_suite':
            $this->getNotesSuite();
            break;
          default:
        }
      }
    }

    /* We sort the events by date and times */
    krsort($this->events);
    foreach ($this->events as $_year => $_events_by_year) {
      krsort($this->events[$_year]);
      foreach ($_events_by_year as $_date => $_events_by_date) {
        krsort($this->events[$_year][$_date]);
      }
    }

    /* Count the events by categories */
    $total = 0;
    foreach ($this->counts as $_category => $_types) {
      $count = 0;
      foreach ($_types as $_count) {
        $count += $_count;
      }
      $total += $count;

      $this->counts[$_category]['_total'] = $count;
    }
    $this->counts['_total'] = $total;

    $this->getMonths();
  }

  /**
   * Get the events of the movement category (affactations, brancardage, begin and end of the sejour
   *
   * @return void
   */
  protected function getMovements() {
    /* Sortie */
    if ($this->displayed_events['affectation_end']) {
      if ($this->sejour->sortie_reelle) {
        $this->sejour->loadRefModeSortie();
        $this->addEvent($this->sejour->sortie_reelle, 'affectation_end', $this->sejour);
      }
      elseif ($this->sejour->sortie_prevue) {
        $this->addEvent($this->sejour->sortie_prevue, 'affectation_end', $this->sejour);
      }
    }

    /* Brancardage */
    if (!CModule::getActive('addictologie') && CModule::getActive('brancardage') && $this->displayed_events['CBrancardageItem']) {
      /** @var CBrancardage[] $brancardages */
      $brancardages      = $this->sejour->loadBackRefs('sejour_brancard');
      $brancardage_items = CMbObject::massLoadBackRefs($brancardages, 'brancardage_items');
      CMbObject::massLoadFwdRef($brancardages, 'origine_id');
      $personnels = CMbObject::massLoadFwdRef($brancardage_items, 'pec_user_id');
      $users      = CMbObject::massLoadFwdRef($personnels, 'user_id');
      CMbObject::massLoadFwdRef($users, 'function_id');
      CMbObject::massLoadFwdRef($brancardage_items, 'transport_id');
      CMbObject::massLoadFwdRef($brancardage_items, 'destination_id');
      foreach ($brancardages as $_brancardage) {
        $_brancardage->loadRefItems();
        $_brancardage->loadRefOrigine();
        foreach ($_brancardage->_ref_items as $_item) {
          $_item->_ref_brancardage = $_brancardage;
          $_item->loadRefTransport();
          $_item->loadRefOrigine();
          $_item->_ref_origine->updateFormFields();
          $_item->loadRefDestination();
          $_item->_ref_destination->updateFormFields();
          $_item->updateFormFields();
          $_item->loadRefPersonnel();
          if ($_item->_ref_personnel->_id) {
            $_item->_ref_personnel->loadRefUser()->loadRefFunction();
          }

          if ($_item->arrivee) {
            $datetime = $_item->arrivee;
          }
          elseif ($_item->depart) {
            $datetime = $_item->depart;
          }
          elseif ($_item->prise_en_charge) {
            $datetime = $_item->prise_en_charge;
          }
          elseif ($_item->demande_brancard) {
            $datetime = $_item->demande_brancard;
          }
          elseif ($_item->patient_pret) {
            $datetime = $_item->patient_pret;
          }
          else {
            continue;
          }
          $this->addEvent($datetime, 'CBrancardageItem', $_item);
        }
      }
    }

    /* Affectations */
    if ($this->displayed_events['affectation_begin'] || $this->displayed_events['affectation_end']) {
      $this->sejour->loadRefsAffectations();
      CMbObject::massLoadFwdRef($this->sejour->_ref_affectations, 'service_id');
      CMbObject::massLoadFwdRef($this->sejour->_ref_affectations, 'lit_id');
      foreach ($this->sejour->_ref_affectations as $_affectation) {
        $_affectation->loadRefLit();
        $_affectation->loadRefService();
        $_affectation->updateFormFields();
        if ($this->displayed_events['affectation_begin']) {
          $this->addEvent($_affectation->entree, 'affectation_begin', $_affectation);
        }
        if ($this->displayed_events['affectation_end']) {
          $this->addEvent($_affectation->sortie, 'affectation_end', $_affectation);
        }
      }
    }

    /* Entrée du séjour */
    if ($this->displayed_events['affectation_begin']) {
      if ($this->sejour->entree_reelle) {
        $this->sejour->loadRefModeEntree();
        $this->addEvent($this->sejour->entree_reelle, 'affectation_begin', $this->sejour);
      }
      elseif ($this->sejour->entree_prevue) {
        $this->addEvent($this->sejour->entree_prevue, 'affectation_begin', $this->sejour);
      }
    }
  }

  /**
   * Get the events related to the documents (CCompteRendu, CFile and CExObject)
   *
   * @return void
   */
  protected function getDocuments() {
    /* Comptes rendus */
    if ($this->displayed_events['CCompteRendu']) {
      $this->sejour->loadRefsDocItems(false);
      foreach ($this->sejour->_ref_documents as $_document) {
        $_document->loadRefAuthor();
        $_document->loadTargetObject();
        $_document->loadFile();
        $this->addEvent($_document->creation_date, 'CCompteRendu', $_document);
      }
    }

    /* CFiles */
    if ($this->displayed_events['CFile']) {
      $this->sejour->loadRefsFiles();
      foreach ($this->sejour->_ref_files as $_file) {
        $_file->loadRefAuthor();
        $_file->loadTargetObject();
        $this->addEvent($_file->file_date, 'CFile', $_file);
      }
    }

    /* CExObject */
    if ($this->displayed_events['CExObject']) {
      $this->sejour->loadRefsForms();
      foreach ($this->sejour->_ref_forms as $_form) {
        $_form->loadTargetObject();
        $_form->loadRefExObject();
        $_form->_ref_ex_object->loadRefOwner();
        $_form->_ref_ex_object->loadRefExClass();
        $this->addEvent($_form->datetime_create, 'CExObject', $_form);
      }
    }
  }

  /**
   * Get the events related to the prescription (administrations, begin and end of a line)
   *
   * @return void
   */
  protected function getPrescriptions() {
    $prescription = $this->sejour->loadRefPrescriptionSejour();
    if ($prescription->_id) {
      $prescription->loadRefsLinesElement();
      $prescription->loadRefsLinesElementByCat();
      $prescription->loadRefsLinesMed();
      $prescription->loadRefsPrescriptionLineMixes();
      CPrescription::massLoadAdministrations(
        $prescription,
        array(CMbDT::date(null, $this->sejour->entree), CMbDT::date(null, $this->sejour->sortie))
      );

      /* CPrescriptionLineElement */
      $users = CMbObject::massLoadFwdRef($prescription->_ref_prescription_lines_element, 'praticien_id');
      CMbObject::massLoadFwdRef($prescription->_ref_prescription_lines_element, 'element_prescription_id');
      foreach ($prescription->_ref_prescription_lines_element as $_line) {
        $_line->loadRefPraticien();
        $_line->_ref_praticien->loadRefFunction();
        $_line->loadRefElement();
        $_line->updateFormFields();
        /* Fin */
        if ($this->displayed_events['CPrescriptionLine_end']) {
          $this->addEvent($_line->_fin_reelle, 'CPrescriptionLine_end', $_line);
        }
        /* Administrations */
        if ($this->displayed_events['CAdministration']) {
          foreach ($_line->_ref_administrations as $_administration) {
            $_administration->loadRefAdministrateur();
            $_administration->loadTargetObject();
            $_administration->loadTargetObject();
            $_administration->_ref_object->loadRefElement()->loadRefCategory();
            $this->addEvent($_administration->dateTime, 'CAdministration', $_administration);
          }
        }

        /* Début */
        if ($this->displayed_events['CPrescriptionLine_begin']) {
          $this->addEvent($_line->_debut_reel, 'CPrescriptionLine_begin', $_line);
        }
      }

      /* CPrescriptionLineMedicament */
      $users = CMbObject::massLoadFwdRef($prescription->_ref_prescription_lines, 'praticien_id');
      foreach ($prescription->_ref_prescription_lines as $_line) {
        $_line->loadRefPraticien();
        $_line->_ref_praticien->loadRefFunction();
        $_line->updateFormFields();

        /* Fin */
        if ($this->displayed_events['CPrescriptionLine_end']) {
          $this->addEvent($_line->_fin_reelle, 'CPrescriptionLine_end', $_line);
        }

        /* Administrations */
        if ($this->displayed_events['CAdministration']) {
          foreach ($_line->_ref_administrations as $_administration) {
            $_administration->loadRefAdministrateur();
            $_administration->loadTargetObject();
            $_administration->_ref_object->loadRefProduit();
            $this->addEvent($_administration->dateTime, 'CAdministration', $_administration);
          }
        }

        /* Début */
        if ($this->displayed_events['CPrescriptionLine_begin']) {
          $this->addEvent($_line->_debut_reel, 'CPrescriptionLine_begin', $_line);
        }
      }

      /* CPrescriptionLineMix */
      $users = CMbObject::massLoadFwdRef($prescription->_ref_prescription_line_mixes, 'praticien_id');
      CMbObject::massLoadBackRefs($prescription->_ref_prescription_line_mixes, 'lines_mix', 'solvant');
      foreach ($prescription->_ref_prescription_line_mixes as $_line) {
        $_line->loadRefPraticien();
        $_line->_ref_praticien->loadRefFunction();
        $_line->loadRefsLines();
        $_line->updateFormFields();

        /* Fin */
        if ($this->displayed_events['CPrescriptionLine_end']) {
          $this->addEvent($_line->_fin, 'CPrescriptionLine_end', $_line);
        }

        /* Administrations */
        if ($this->displayed_events['CAdministration']) {
          foreach ($_line->_ref_lines as $_line_item) {
            $_line_item->loadRefsAdministrations();
            foreach ($_line_item->_ref_administrations as $_administration) {
              $_administration->loadRefAdministrateur();
              $_administration->loadTargetObject();
              $_administration->_ref_object->loadRefProduit();
              $this->addEvent($_administration->dateTime, 'CAdministration', $_administration);
            }
          }
        }

        /* Début */
        if ($this->displayed_events['CPrescriptionLine_begin']) {
          $this->addEvent($_line->_debut, 'CPrescriptionLine_begin', $_line);
        }
      }
    }
  }

  /**
   * Get the events related to consultations (consultations, anaesthesia consultation, visits)
   *
   * @return void
   */
  protected function getConsultations() {
    /* CConsultations */
    if ($this->displayed_events['CConsultation']) {
      $this->sejour->loadRefsConsultations();
      $plages = CMbObject::massLoadFwdRef($this->sejour->_ref_consultations, 'plageconsult_id');
      $users  = CMbObject::massLoadFwdRef($plages, 'chir_id');
      CMbObject::massLoadFwdRef($users, 'function_id');
      CMbObject::massLoadFwdRef($this->sejour->_ref_consultations, 'categorie_id');

      foreach ($this->sejour->_ref_consultations as $_consultation) {
        $_consultation->loadRefPraticien();
        $_consultation->loadRefCategorie();
        $_consultation->countDocItems();
        $this->addEvent($_consultation->_datetime, 'CConsultation', $_consultation);
      }
    }

    if (!CModule::getActive('addictologie')) {
      /* CConsultAnesth */
      if ($this->displayed_events['CConsultAnesth']) {
        $this->sejour->loadRefsConsultAnesth();
        if ($this->sejour->_ref_consult_anesth->_id) {
          $consult_anesth = $this->sejour->_ref_consult_anesth;
          $consult_anesth->loadRefChir()->loadRefFunction();
          $consult_anesth->loadRefConsultation();
          $consult_anesth->_ref_consultation->loadRefPlageConsult();
          $this->addEvent($consult_anesth->_ref_consultation->_datetime, 'CConsultAnesth', $consult_anesth);
        }
      }

      /* Visite anesth */
      if ($this->displayed_events['VisiteAnesth']) {
        $this->sejour->loadRefsOperations();
        foreach ($this->sejour->_ref_operations as $_operation) {
          if ($_operation->date_visite_anesth) {
            $_operation->loadRefVisiteAnesth();
            $datetime = "{$_operation->date_visite_anesth} {$_operation->time_visite_anesth}";
            $this->addEvent($datetime, 'VisiteAnesth', $_operation);
          }
        }
      }
    }
  }

  /**
   * Get events related to the acquisition of data (constants, scores, transmissions, observations)
   *
   * @return void
   */
  protected function getSaisies() {
    /* Transmissions */
    if ($this->displayed_events['CTransmissionMedicale']) {
      $this->sejour->loadRefsTransmissions();
      foreach ($this->sejour->_ref_transmissions as $_transmission) {
        $_transmission->loadRefUser();
        $this->addEvent($_transmission->date, 'CTransmissionMedicale', $_transmission);
      }
    }

    /* Observations */
    if ($this->displayed_events['CObservationMedicale']) {
      $this->sejour->loadRefsObservations();
      foreach ($this->sejour->_ref_observations as $_observation) {
        $_observation->loadRefUser();
        $this->addEvent($_observation->date, 'CObservationMedicale', $_observation);
      }
    }

    /* Score IGS */
    if (!CModule::getActive('addictologie')) {
      if ($this->displayed_events['score']) {
        /** @var CExamIgs[] $igs_scores */
        $igs_scores = $this->sejour->loadBackRefs('exams_igs');
        foreach ($igs_scores as $_score) {
          $this->addEvent($_score->date, 'score', $_score);
        }

        /* Score Chung */
        /** @var CChungScore[] $chung_scores */
        $chung_scores = $this->sejour->loadBackRefs('chung_scores');
        foreach ($chung_scores as $_score) {
          $this->addEvent($_score->datetime, 'score', $_score);
        }
      }
    }

    /* CConstantesMedicales */
    if ($this->displayed_events['CConstantesMedicales']) {
      /** @var CConstantesMedicales[] $constantes */
      $constantes = $this->sejour->loadBackRefs('contextes_constante');
      foreach ($constantes as $_constante) {
        $_constante->loadRefUser()->loadRefFunction();
        $_constante->getValuedConstantes();
        $this->addEvent($_constante->datetime, 'CConstantesMedicales', $_constante);
      }
    }
  }

  /**
   * Get the events related to the timings of the operations
   *
   * @return void
   */
  protected function getOperations() {
    $this->sejour->loadRefsOperations();

    $salles = CMbObject::massLoadFwdRef($this->sejour->_ref_operations, 'salle_id');
    CMbObject::massLoadFwdRef($salles, 'bloc_id');
    $users = CMbObject::massLoadFwdRef($this->sejour->_ref_operations, 'chir_id');
    CMbObject::massLoadFwdRef($users, 'function_id');
    foreach ($this->sejour->_ref_operations as $_operation) {
      $_operation->_ref_sejour = $this->sejour;
      $_operation->updateFormFields();
      $_operation->loadRefSalle();
      $_operation->loadRefChir();
      $_operation->_ref_chir->loadRefFunction();
      $_operation->loadExtCodesCCAM();
      if (CModule::getActive('brancardage')) {
        $_operation->_ref_brancardage = new CBrancardage();
      }

      $this->addEvent($_operation->_datetime_best, 'COperation', $_operation);
    }
  }

  /**
   * Get the events related to the pathologies
   *
   * @return void
   */
  protected function getPathologies() {
    $dossier_addictologie = $this->sejour->loadRefDossierAddictologie();
    $pathologies          = $dossier_addictologie->loadRefsPathologiesAddictologie();

    foreach ($pathologies as $_pathologie) {
      $_pathologie->loadRefTypePathologie();
      $_pathologie->loadRefMotifFinPathologie();

      if ($this->displayed_events['pathologie_begin']) {
        $this->addEvent($_pathologie->debut . " 00:00:00", 'pathologie_begin', $_pathologie);
      }
      if ($_pathologie->fin && $this->displayed_events['pathologie_end']) {
        $this->addEvent($_pathologie->fin . " 23:59:59", 'pathologie_end', $_pathologie);
      }
    }
  }

  /**
   * Get the events related to the suivis
   *
   * @return void
   */
  protected function getSuivis() {
    $dossier_addictologie = $this->sejour->loadRefDossierAddictologie();
    $suivis               = $dossier_addictologie->loadRefsSuivisAddictologie();

    foreach ($suivis as $_suivi) {
      $_suivi->loadRefTypeSuiviAddiction();

      if ($this->displayed_events['suivi_begin']) {
        $this->addEvent($_suivi->date_debut . " 00:00:00", 'suivi_begin', $_suivi);
      }
      if ($_suivi->date_fin && $this->displayed_events['suivi_end']) {
        $this->addEvent($_suivi->date_fin . " 23:59:59", 'suivi_end', $_suivi);
      }
    }
  }

  /**
   * Get the events related to the ObjectifsSoins
   *
   * @return void
   */
  protected function getObjectifsSoins() {
    $objectifsSoins = $this->sejour->loadRefsObjectifsSoins();
    foreach ($objectifsSoins as $_objectif) {
      $_objectif->loadRefUser();

      if ($this->displayed_events['objectifs_soins_open'] && $_objectif->statut == "ouvert") {
        $this->addEvent($_objectif->date, 'objectifs_soins_open', $_objectif);
      }
      elseif ($this->displayed_events['objectifs_soins_achieved'] && $_objectif->statut == "atteint") {
        $this->addEvent($_objectif->date, 'objectifs_soins_achieved', $_objectif);
      }
      elseif ($this->displayed_events['objectifs_soins_not_achieved'] && $_objectif->statut == "non_atteint") {
        $this->addEvent($_objectif->date, 'objectifs_soins_not_achieved', $_objectif);
      }
    }
  }

  /**
   * Get the events related to the notes suite
   *
   * @return void
   */
  protected function getNotesSuite() {
    $dossier_addictologie = $this->sejour->loadRefDossierAddictologie();
    $notes_suite          = $dossier_addictologie->loadRefsNotesSuite();

    foreach ($notes_suite as $_note) {
      $_note->loadRefOwner()->loadRefFunction();
      $_note->loadRefUser()->loadRefFunction();

      if ($this->displayed_events['note_suite_medical'] && $_note->type == "medical") {
        $this->addEvent($_note->date . " 00:00:00", 'note_suite_medical', $_note);
      }
      elseif ($this->displayed_events['note_suite_psycho'] && $_note->type == "psycho") {
        $this->addEvent($_note->date . " 00:00:00", 'note_suite_psycho', $_note);
      }
      elseif ($this->displayed_events['note_suite_social'] && $_note->type == "social") {
        $this->addEvent($_note->date . " 00:00:00", 'note_suite_social', $_note);
      }
      elseif ($this->displayed_events['note_suite_other'] && $_note->type == "other") {
        $this->addEvent($_note->date . " 00:00:00", 'note_suite_other', $_note);
      }
    }
  }

  /**
   * Get the weeks for displaying in the timeline menu
   *
   * @return void
   */
  protected function getWeeks() {
    $this->weeks = array();

    foreach ($this->events as $_year => $_events) {
      foreach ($_events as $_date => $_event) {
        $week = CMbDT::format($_date, '%W');
        if (!array_key_exists($week, $this->weeks)) {
          $_begin_week        = CMbDT::format($_date, '%u') == 1 ? $_date : CMbDT::date('previous monday', $_date);
          $_end_week          = CMbDT::format($_date, '%u') == 7 ? $_date : CMbDT::date('next sunday', $_date);
          $this->weeks[$week] = array(
            'date'  => $_date,
            'begin' => $_begin_week,
            'end'   => $_end_week,
            'days'  => array($_date)
          );
        }
        else {
          $this->weeks[$week]['days'][] = $_date;
        }
      }
    }
  }

  /**
   * Get the months for displaying in the timeline menu
   *
   * @return void
   */
    protected function getMonths() {
    $this->months = array();

    foreach ($this->events as $_year => $_events) {
      foreach ($_events as $_date => $_event) {
        $month = CMbDT::format($_date, '%Y-%m');
        if (!array_key_exists($month, $this->months)) {
          $_begin_week        = CMbDT::format($_date, '%u') == 1 ? $_date : CMbDT::date('previous monday', $_date);
          $_end_week          = CMbDT::format($_date, '%u') == 7 ? $_date : CMbDT::date('next sunday', $_date);
          $this->months[$_year][$month] = array(
            'date'  => $_date,
            'begin' => $_begin_week,
            'end'   => $_end_week,
            'days'  => array($_date)
          );
        }
        else {
          $this->months[$_year][$month]['days'][] = $_date;
        }
      }
    }

    foreach ($this->months as $_year => $months) {
      foreach ($months as $_month => $_date) {
        sort($this->months[$_year][$_month]['days']);
        $this->months[$_year][$_month]['date'] = end($this->months[$_year][$_month]['days']);
      }
    }
  }

  /**
   * @param string    $datetime The datetime
   * @param string    $type     The type of event
   * @param CMbObject $object   The object
   * @param string    $field    The field (used for the template)
   *
   * @return void
   */
  protected function addEvent($datetime, $type, $object, $field = null) {
    list($date, $time) = explode(' ', $datetime);
    $time = CMbDT::format($datetime, '%H:%M');

    $year = CMbDT::format($datetime, '%Y');
    if (!array_key_exists($year, $this->events)) {
      $this->events[$year] = array();
    }

    if (!array_key_exists($date, $this->events[$year])) {
      $this->events[$year][$date] = array();
    }

    if (!array_key_exists($time, $this->events[$year][$date])) {
      $this->events[$year][$date][$time] = array();
    }

    if (!array_key_exists($type, $this->events[$year][$date][$time])) {
      $this->events[$year][$date][$time][$type] = array();
    }

    $category                       = self::getCategoryForType($type);
    $this->counts[$category][$type] += 1;

    /* In case of the prescription lines, we regroup the prescription that begins or ends at the same time, made by the same user */
    if (in_array($type, array('CPrescriptionLine_begin', 'CPrescriptionLine_end'))) {
      $event = new CTimelineEvent($datetime, $type, $object, $object->_ref_praticien);
      $index = count($this->events[$year][$date][$time][$type]);

      foreach ($this->events[$year][$date][$time][$type] as $_key => $_event) {
        if ($_event->user->_id == $object->_ref_praticien->_id) {
          $event = $_event;
          $event->addObject($object);
          $index = $_key;
        }
      }

      $this->events[$year][$date][$time][$type][$index] = $event;
    }
    else {
      $this->events[$year][$date][$time][$type][] = new CTimelineEvent($datetime, $type, $object);
    }
  }

  /**
   * Get the category of an event type
   *
   * @param string $type The type of event
   *
   * @return string
   */
  protected static function getCategoryForType($type) {
    foreach (self::$categories as $_category => $_types) {
      if (in_array($type, $_types)) {
        return $_category;
      }
    }

    return '';
  }
}

