<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 */

namespace Ox\Mediboard\Cabinet;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class to manage work accidents or occupational diseases
 */
class CAccidentTravail extends CMbObject {

  public $accident_travail_id;

  public $object_class;
  public $object_id;
  public $num_at_mp;                   // Num�ro accident de travail.
  public $num_organisme;               // R�gime, Caisse de rattachement, Centre de gestion pour le risque maladie.
  public $nature;                      // � AT � : Accident du Travail ou � MP � : Maladie Professionnelle
  public $type;                        // � I � : Initial, � P � : Prolongation, � R � : Rechute, � F � : Final
  public $feuille_at;
  public $date_debut_arret;
  public $date_fin_arret;
  public $date_debut_travail_leger;
  public $date_fin_travail_leger;
  public $datetime_at_mp;              // Date et heure d�clar�e de AT ou MP
  public $date_constatations;          // Date de l'accident ou de la premi�re constatation m�dicale de la maladie professionnelle
  public $constatations;               // Permet de d�crire le si�ge, la nature des l�sions ou de la maladie professionnelle, les s�quelles fonctionnelles.
  public $patient_employeur_nom;
  public $patient_employeur_adresse;
  public $patient_employeur_cp;
  public $patient_employeur_ville;
  public $patient_employeur_phone;
  public $patient_employeur_email;
  public $patient_visite_escalier;
  public $patient_visite_etage;
  public $patient_visite_appartement;
  public $patient_visite_batiment;
  public $patient_visite_code;
  public $patient_visite_adresse;
  public $patient_visite_cp;
  public $patient_visite_ville;
  public $patient_visite_phone;
  public $date_constat;                  // Date de gu�rison ou de consolidation
  public $constat;                       // � 1 � : Gu�rison avec retour � l'�tat ant�rieur � 2 � : Gu�rison apparente avec possibilit� rechute ult�rieure � 3 � : Consolidation avec s�quelles
  public $description_constat;           // Description des s�quelles du patient
  public $date_reprise;                  // Date de reprise du travail � temps complet
  public $sorties_autorisees;
  public $sorties_restriction;
  public $date_sortie;                   // Date de d�but des sorties autoris�es avec restriction d'horaires.
  public $sorties_sans_restriction;
  public $date_sortie_sans_restriction;  // Date de d�but des sorties autoris�es sans restriction d'horaires.
  public $motif_sortie_sans_restriction; // �l�ments d'ordre m�dical justifiant les sorties sans restriction d'horaires.
  public $date_debut_soins;
  public $date_fin_soins;
  public $consequences;

  public $_duree;
  public $_unite_duree;
  public $_patient_adresse_visite;

  /** @var CConsultation|CSejour */
  public $_ref_context;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'accident_travail';
    $spec->key   = 'accident_travail_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                                  = parent::getProps();
    $props['object_class']                  = 'enum notNull list|CConsultation|CSejour';
    $props['object_id']                     = 'ref class|CMbObject meta|object_class back|accident_travail';
    $props['num_at_mp']                     = 'numchar length|9';
    $props['num_organisme']                 = 'numchar length|9';
    $props['nature']                        = 'enum notNull list|AT|MP';
    $props['type']                          = 'enum notNull list|I|P|R|F';
    $props['feuille_at']                    = 'bool default|1';
    $props['date_debut_arret']              = 'date';
    $props['date_fin_arret']                = 'date moreEquals|date_debut_arret';
    $props['date_debut_travail_leger']      = 'date';
    $props['date_fin_travail_leger']        = 'date moreEquals|date_debut_travail_leger';
    $props['datetime_at_mp']                = 'dateTime';
    $props['date_constatations']            = 'date notNull';
    $props['constatations']                 = 'text helped';
    $props['patient_employeur_nom']         = 'str maxLength|66';
    $props['patient_employeur_adresse']     = 'str maxLength|38';
    $props['patient_employeur_cp']          = 'str maxLength|5';
    $props['patient_employeur_ville']       = 'str maxLength|38';
    $props['patient_employeur_phone']       = 'phone';
    $props['patient_employeur_email']       = 'email';
    $props['patient_visite_escalier']       = 'str maxLength|3';
    $props['patient_visite_etage']          = 'str maxLength|3';
    $props['patient_visite_appartement']    = 'str maxLength|5';
    $props['patient_visite_batiment']       = 'str maxLength|3';
    $props['patient_visite_code']           = 'str maxLength|8';
    $props['patient_visite_adresse']        = 'str maxLength|38';
    $props['patient_visite_cp']             = 'str maxLength|5';
    $props['patient_visite_ville']          = 'str maxLength|38';
    $props['patient_visite_phone']          = 'phone';
    $props['date_constat']                  = 'date';
    $props['constat']                       = 'enum list|1|2|3';
    $props['description_constat']           = 'str maxLength|200';
    $props['date_reprise']                  = 'date';
    $props['sorties_autorisees']            = 'bool default|0';
    $props['sorties_restriction']           = 'bool default|0';
    $props['sorties_sans_restriction']      = 'bool default|0';
    $props['date_sortie_sans_restriction']  = 'date';
    $props['motif_sortie_sans_restriction'] = 'text helped';
    $props['date_sortie']                   = 'date';
    $props['date_debut_soins']              = 'date';
    $props['date_fin_soins']                = 'date';
    $props['consequences']                  = 'enum list|arret|sans_arret';

    $props['_duree']                  = 'num min|0 max|364';
    $props['_unite_duree']            = 'enum list|j|m|a';
    $props['_patient_adresse_visite'] = 'bool default|0';

    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();
    $context     = $this->loadRefContext();
    $this->_view = CAppUI::tr("CAccidentTravail.nature.$this->nature") . " - " . $context->_view;

    if ($this->date_debut_arret && $this->date_fin_arret && ($this->date_debut_arret < $this->date_fin_arret)) {
      $days          = count(CMbDT::getDays($this->date_debut_arret, $this->date_fin_arret));
      $period_detail = CMbDT::achievedDurationsDT($this->date_debut_arret, $this->date_fin_arret);

      if (($days < 32) && $period_detail["month"] == 0) {
        $this->_duree       = $days;
        $this->_unite_duree = "j";
      }
      elseif (($days > 31) && ($period_detail["month"] > 0) && ($period_detail["year"] == 0)) {
        $this->_duree       = $period_detail["month"];
        $this->_unite_duree = "m";
      }
      elseif (($days > 365) && $period_detail["year"] > 0) {
        $this->_duree       = $period_detail["year"];
        $this->_unite_duree = "a";
      }
    }
  }

  /**
   * @inheritdoc
   */
  function store() {
    if ($this->date_fin_arret && $this->consequences == "sans_arret") {
      $this->date_fin_soins = $this->date_fin_arret;
    }

    parent::store();
  }

  /**
   * Load the consultation or sejour
   *
   * @return CConsultation|CSejour
   */
  public function loadRefContext() {
    return $this->_ref_context = $this->loadFwdRef('object_id', true);
  }
}
