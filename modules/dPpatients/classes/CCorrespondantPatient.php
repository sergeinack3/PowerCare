<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbModelNotFoundException;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CMbString;
use Ox\Core\CPerson;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Correspondants du patient
 */
class CCorrespondantPatient extends CPerson implements IGroupRelated
{
  /** @var string  */
  public const RESOURCE_TYPE = 'correspondantPatient';

  public const RELATION_REPRESENTANT_LEGAL = "representant_legal";
  public const PARENTES_TUTELLE = [
      "curateur" => "curateur",
      "tuteur" => "tuteur"
  ];

  private const TYPES_CORRESPONDANT = [
      "assurance",
      "autre",
      "confiance",
      "employeur",
      "inconnu",
      "prevenir",
      "representant_legal",
      "representant_th",
      "transport",
      "parent_proche",
      "ne_pas_prevenir",
      "soins_domicile"
  ];

  // DB Table key
  public $correspondant_patient_id;

  // Owner
  public $function_id;
  public $group_id;

  // DB Fields
  public $patient_id;
  public $relation;
  public $relation_autre;
  public $nom;
  public $surnom;
  public $nom_jeune_fille;
  public $prenom;
  public $sex;
  public $naissance;
  public $adresse;
  public $cp;
  public $ville;
  public $tel;
  public $tel_autre;
  public $mob;
  public $fax;
  public $urssaf;
  public $parente;
  public $parente_autre;
  public $email;
  public $remarques;
  public $ean;
  public $ean_base;
  public $type_pec;
  public $assure_id;
  public $ean_id;
  public $date_debut;
  public $date_fin;
  public $num_assure;
  public $employeur;
  public $_annees;

  // Form fields
  public $_duplicate;
  public $_is_obsolete = false;


  /** @var CFunctions */
  public $_ref_function;
  /** @var CGroups */
  public $_ref_group;
  /** @var CPatient */
  public $_ref_patient;
  /** @var CDirectiveAnticipee[] */
  public $_refs_directives_anticipees;

  /**
   * Initialize object specification
   *
   * @return CMbObjectSpec the spec
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "correspondant_patient";
    $spec->key   = "correspondant_patient_id";

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();

    $props["function_id"]     = "ref class|CFunctions back|correspondantpatients_function fieldset|default";
    $props["group_id"]        = "ref class|CGroups back|correspondants fieldset|default";
    $props["patient_id"]      = "ref class|CPatient cascade back|correspondants_patient fieldset|default";
    $props["relation"]        = "enum list|".implode("|", self::TYPES_CORRESPONDANT)." default|prevenir fieldset|default";
    $props["relation_autre"]  = "str fieldset|extra";
    $props["nom"]             = "str notNull seekable confidential fieldset|default";
    $props["surnom"]          = "str seekable fieldset|extra";
    $props["nom_jeune_fille"] = "str fieldset|default";
    $props["prenom"]          = "str fieldset|default";
    $props["naissance"]       = "birthDate mask|99/99/9999 format|$3-$2-$1 fieldset|default";
    $props["sex"]             = "enum list|f|m|u default|u fieldset|default";
    $props["adresse"]         = "text fieldset|default";
    [$min_cp, $max_cp] = CPatient::getLimitCharCP();
    $props["cp"]            = "str minLength|$min_cp maxLength|$max_cp fieldset|default";
    $props["ville"]         = "str confidential fieldset|default";
    $props["tel"]           = "phone confidential fieldset|default";
    $props["tel_autre"]     = "str maxLength|20 fieldset|default";
    $props["mob"]           = "phone confidential fieldset|default";
    $props["fax"]           = "phone confidential fieldset|extra";
    $props["urssaf"]        = "numchar length|11 confidential fieldset|extra";
    $props["parente"]       = "enum list|ami|ascendant|autre|beau_fils|colateral|collegue|compagnon|conjoint|curateur|directeur" .
      "|divers|employeur|employe|enfant|enfant_adoptif|entraineur|epoux|frere|grand_parent|mere|pere" .
      "|petits_enfants|proche|proprietaire|soeur|tuteur fieldset|default";
    $props["parente_autre"] = "str fieldset|extra";
    $props["email"]         = "email fieldset|default";
    $props["remarques"]     = "text fieldset|extra";
    $props["ean"]           = "str maxLength|30 fieldset|extra";
    $props["ean_base"]      = "str maxLength|30 fieldset|extra";
    $props["type_pec"]      = "enum list|TG|TP|TS fieldset|extra";
    $props["assure_id"]     = "str maxLength|30 fieldset|extra";
    $props["ean_id"]        = "str maxLength|5 fieldset|extra";
    $props["date_debut"]    = "date fieldset|default";
    $props["date_fin"]      = "date fieldset|default";
    $props["num_assure"]    = "str maxLength|30 fieldset|extra";
    $props["employeur"]     = "ref class|CCorrespondantPatient back|employeur fieldset|extra";
    $props["_annees"]       = "num show|1";

    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->mapPerson();

    $this->_view = $this->relation ?
      CAppUI::tr("CCorrespondantPatient.relation." . $this->relation) :
      $this->relation_autre;

    $this->_longview = "$this->nom $this->prenom";

    if ($this->date_fin && $this->date_fin < CMbDT::date()) {
      $this->_is_obsolete = true;
    }
  }

  /**
   * @see parent::updatePlainFields()
   */
  function updatePlainFields() {
    parent::updatePlainFields();

    if ($this->nom) {
      $this->nom = CMbString::upper($this->nom);
    }

    if ($this->nom_jeune_fille) {
      $this->nom_jeune_fille = CMbString::upper($this->nom_jeune_fille);
    }

    if ($this->prenom) {
      $this->prenom = CMbString::capitalize(CMbString::lower($this->prenom));
    }
  }

    /**
     * @throws CMbModelNotFoundException
     * @see parent::store()
     */
    public function store()
    {
        $this->checkTutelle();
        if (!$this->_id && !$this->date_fin && !$this->date_debut) {
            $this->date_debut = CMbDT::date();
        }

        // Création d'un correspondant en mode cabinets distincts
        if (!$this->_id) {
            if (CAppUI::isCabinet()) {
                $this->function_id = CMediusers::get()->function_id;
            } elseif (CAppUI::isGroup()) {
                $this->group_id = CMediusers::get()->loadRefFunction()->group_id;
            }
        }

        if ($this->_duplicate) {
            $this->nom .= " (Copy)";

            $this->_id        = null;
            $this->date_debut = CMbDT::date();
            $this->date_fin   = "";

            $this->_duplicate = null;
        }

        return parent::store();
    }

  /**
   * Load patient
   *
   * @return CPatient
   */
  function loadRefPatient() {
    return $this->_ref_patient = $this->loadFwdRef("patient_id");
  }

  /**
   * Chargement de la fonction reliée
   *
   * @return CFunctions
   */
  function loadRefFunction() {
    return $this->_ref_function = $this->loadFwdRef("function_id", true);
  }

  /**
   * Chargement de la fonction reliée
   *
   * @return CGroups
   */
  function loadRefGroup() {
    return $this->_ref_group = $this->loadFwdRef("group_id", true);
  }

  /**
   * Chargement des directives anticipées
   *
   * @return CDirectiveAnticipee[]
   */
  function loadRefsDirectivesAnticipees() {
    return $this->_refs_directives_anticipees = $this->loadBackRefs("directives_detenteur");
  }

  /**
   * @see parent::getSexFieldName()
   */
  function getSexFieldName() {
    return "sex";
  }

  /**
   * @see parent::getPrenomFieldName()
   */
  function getPrenomFieldName() {
    return "prenom";
  }

  /**
   * @inheritdoc
   */
  function getNomFieldName() {
    return 'nom';
  }

  /**
   * @inheritdoc
   */
  function getNaissanceFieldName() {
    return 'naissance';
  }

  /**
   * Map the class variable with CPerson variable
   *
   * @return void
   */
  function mapPerson() {
    $this->_p_city                = $this->ville;
    $this->_p_postal_code         = $this->cp;
    $this->_p_street_address      = $this->adresse;
    $this->_p_phone_number        = $this->tel;
    $this->_p_fax_number          = $this->fax;
    $this->_p_mobile_phone_number = $this->mob;
    $this->_p_email               = $this->email;
    $this->_p_first_name          = $this->prenom;
    $this->_p_last_name           = $this->nom;
    $this->_p_birth_date          = $this->naissance;
    $this->_p_maiden_name         = $this->nom_jeune_fille;
  }

  /**
   * Calcul l'âge du patient en années
   *
   * @param string $date Date de référence pour le calcul, maintenant si null
   *
   * @return int l'age du patient en années
   */
  function evalAge($date = null) {
    $achieved = CMbDT::achievedDurations($this->naissance, $date);

    return $this->_annees = $achieved["year"];
  }

    /**
     * @return CGroups|null
     * @throws Exception
     */
    public function loadRelGroup(): ?CGroups
    {
        if ($this->group_id) {
            return CGroups::get($this->group_id);
        } elseif ($this->function_id) {
            return $this->loadRefFunction()->loadRefGroup();
        }

        return null;
    }

    /**
     * @throws CMbModelNotFoundException
     */
    public function checkTutelle(): void
    {
        if (
            (!$this->date_debut || $this->date_debut < CMbDT::date())
            && (!$this->date_fin || $this->date_fin > CMbDT::date())
            && $this->relation === self::RELATION_REPRESENTANT_LEGAL
            && in_array($this->parente, self::PARENTES_TUTELLE)
        ) {
            $patient = CPatient::findOrFail((int)$this->loadRefPatient()->_id);
            $patient->tutelle = $this->parente === self::PARENTES_TUTELLE["curateur"] ? "curatelle" : "tutelle";
            $patient->store();
        }
    }
}
