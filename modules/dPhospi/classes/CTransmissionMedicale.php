<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\PlanSoins\CAdministration;
use Ox\Mediboard\Prescription\CCategoryPrescription;
use Ox\Mediboard\Prescription\CPrescriptionLine;
use Ox\Mediboard\Search\IIndexableObject;

/**
 * Class CTransmissionMedicale
 *
 * @property CPrescriptionLine|CCategoryPrescription|CAdministration _ref_object
 */
class CTransmissionMedicale extends CMbObject implements IIndexableObject {
  // DB Table key
  public $transmission_medicale_id;

  // DB Fields
  public $sejour_id;
  public $consult_id;
  public $cible_id;
  public $user_id;
  public $degre;
  public $date;
  public $date_max;
  public $text;
  public $type;
  public $libelle_ATC;
  public $locked;
  public $cancellation_date;
  public $dietetique;
  public $duree;

  public $object_class;
  public $object_id;
  public $_ref_object;

  /** @var CSejour */
  public $_ref_sejour;

  /** @var CMediusers */
  public $_ref_user;

  /** @var CCible */
  public $_ref_cible;

  // Form fields
  public $_cible;
  public $_text_data;
  public $_text_action;
  public $_text_result;
  public $_log_lock;
  public $_force_new_cible;
  /* @var CTransmissionMedicale[] */
  public $_trans_sibling;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec                 = parent::getSpec();
    $spec->table          = 'transmission_medicale';
    $spec->key            = 'transmission_medicale_id';
    $spec->xor["context"] = array("sejour_id", "consult_id");
    $spec->measureable    = true;

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                      = parent::getProps();
    $props["object_id"]         = "ref class|CMbObject meta|object_class back|transmissions cascade";
    $props["object_class"]      = "enum list|CPrescriptionLineElement|CPrescriptionLineMedicament|CPrescriptionLineComment|" .
      "CCategoryPrescription|CAdministration|CPrescriptionLineMix show|0";
    $props["sejour_id"]         = "ref class|CSejour back|transmissions";
    $props["consult_id"]        = "ref class|CConsultation back|transmissions";
    $props["cible_id"]          = "ref class|CCible back|transmissions";
    $props["user_id"]           = "ref notNull class|CMediusers back|transmissions";
    $props["degre"]             = "enum notNull list|low|high default|low";
    $props["date"]              = "dateTime notNull";
    $props["date_max"]          = "dateTime";
    $props["text"]              = "text helped|type|object_id";
    $props["type"]              = "enum list|data|action|result";
    $props["libelle_ATC"]       = "text";
    $props["locked"]            = "bool default|0";
    $props["cancellation_date"] = "dateTime";
    $props["dietetique"]        = "bool default|0";
    $props["duree"]             = "num";
    $props["_text_data"]        = "text helped|type|object_id";
    $props["_text_action"]      = "text helped|type|object_id";
    $props["_text_result"]      = "text helped|type|object_id";

    return $props;
  }

  /**
   * Charge le séjour
   *
   * @return CSejour
   */
  function loadRefSejour() {
    return $this->_ref_sejour = $this->loadFwdRef("sejour_id", true);
  }

  /**
   * Charge l'utilisateur
   *
   * @return CMediusers
   */
  function loadRefUser() {
    /** @var CMediusers $user */
    $user = $this->loadFwdRef("user_id", true);
    $user->loadRefFunction();

    $this->_view = "Transmission de $user->_view";

    return $this->_ref_user = $user;
  }

  /**
   * Charge la cible
   *
   * @return CCible
   */
  function loadRefCible() {
    return $this->_ref_cible = $this->loadFwdRef("cible_id", true);
  }

  /**
   * @see parent::loadRefsFwd()
   */
  function loadRefsFwd() {
    parent::loadRefsFwd();
    $this->loadTargetObject();
    $this->loadRefSejour();
    $this->loadRefUser();
  }

  /**
   * @see parent::canEdit()
   */
  function canEdit() {
    $nb_hours      = CAppUI::gconf("soins Other max_time_modif_suivi_soins");
    $datetime_max  = CMbDT::dateTime("+ $nb_hours HOURS", $this->date);
    $modif_for_all =
      CMbDT::timeRelative($this->date, CMbDT::dateTime(), "%02d") < CAppUI::gconf("soins Transmissions period_modif_for_all");

    return $this->_canEdit = ((CMbDT::dateTime() < $datetime_max) && (CAppUI::$instance->user_id == $this->user_id)) || $modif_for_all;
  }

  /**
   * @see parent::loadTargetObject()
   */
  function loadTargetObject($cache = true) {
    CMbMetaObjectPolyfill::loadTargetObject($this, $cache);

    if ($this->object_class == "CPrescriptionLineMix" && !$this->_ref_object->_ref_lines) {
      $this->_ref_object->loadRefsLines();
    }

    return $this->_ref_object;
  }

  function calculCibles(&$cibles = array()) {
    $state = $this->locked ? "closed" : "opened";
    if ($this->object_id && $this->object_class) {
      // Ligne de medicament, cible => classe ATC
      if ($this->object_class == "CPrescriptionLineMedicament") {
        $this->_cible = $this->_ref_object->_ref_produit->_ref_ATC_2_libelle;
      }

      // Ligne d'element, cible => categorie
      if ($this->object_class == "CPrescriptionLineElement") {
        $this->_cible = $this->_ref_object->_ref_element_prescription->_ref_category_prescription->_view;
      }

      // Administration => ATC ou categorie
      if ($this->object_class == "CAdministration") {
        $this->_ref_object->loadTargetObject();
        if (in_array($this->_ref_object->object_class, array("CPrescriptionLineMedicament", "CPrescriptionLineMixItem"))) {
          $this->_cible = $this->_ref_object->_ref_object->_ref_produit->_ref_ATC_2_libelle;
        }
        if ($this->_ref_object->object_class == "CPrescriptionLineElement") {
          $this->_cible = $this->_ref_object->_ref_object->_ref_element_prescription->_ref_category_prescription->_view;
        }
      }

      if ($this->object_class == "CCategoryPrescription") {
        $this->_cible = $this->_ref_object->_view;
      }

      if ($this->object_class == "CPrescriptionLineMix") {
        $this->_cible = "Perfusion";
      }
    }

    if ($this->libelle_ATC) {
      $this->_cible = $this->libelle_ATC;
    }

    if ($this->_cible && !isset($cibles["opened"][$this->_cible]) && !isset($cibles["closed"][$this->_cible])) {
      $cibles[$state][$this->_cible] = $this->_cible;
    }
  }

  /**
   * @see parent::store()
   */
  function store() {
    $this->completeField("sejour_id", "cible_id", "libelle_ATC", "object_id", "object_class");

    /** @var self $old */
    $old = $this->loadOldObject();

    if ($this->libelle_ATC || ($this->object_id && $this->object_class)) {
      // Création de la cible si elle n'existe pas (ou changement de cible)
      if ((!$this->_id && !$this->cible_id) || ($this->fieldModified("object_id") || $this->fieldModified("libelle_ATC"))) {
        $cible = new CCible();
        if ($this->object_id && $this->object_class) {
          $cible->object_id    = $this->object_id;
          $cible->object_class = $this->object_class;
        }
        else {
          $cible->libelle_ATC = $this->libelle_ATC;
        }
        $cible->sejour_id = $this->sejour_id;

        if ($this->_force_new_cible || !$cible->loadMatchingObject()) {
          $cible->datetime = $this->date;
          $cible->store();
        }

        $this->cible_id = $cible->_id;
      }

      // Si une cible est définie, on unlock la précédente transmission sur la même cible
      // (classe ATC ou catégorie de prescription)
      if ($this->sejour_id) {
          $trans           = new CTransmissionMedicale();
          $trans->cible_id = $this->cible_id;

          $trans->sejour_id = $this->sejour_id;
          $trans->locked    = 1;
          $trans->loadMatchingObject("transmission_medicale_id DESC");

          if ($trans->_id && $trans->_id != $this->_id) {
              $trans->locked = 0;
              $trans->store();
          }
      }
    }

    //Annulation d'une administration d'un PSL
    if ($this->object_class == "CAdministration" && CModule::getActive('psl')) {
       $this->loadTargetObject();
       if ($this->_ref_object->quantite == 0) {
          $psl = $this->_ref_object->loadRefPsl();
          if ($psl->_id) {
              $psl->administration_id = "";
              $psl->date_sortie       = "";
             if ($msg = $psl->store()) {
                 return $msg;
             }
          }
       }
    }

    if ($msg = parent::store()) {
      return $msg;
    }

    // En cas de changement de cible, on supprime l'ancienne si plus de transmissions rattachées
    if ($old->_id && $old->cible_id !== $this->cible_id) {
      $old_cible = $old->loadRefCible();
      unset($old_cible->_count["transmissions"]);
      if (!$old_cible->countTransmissions()) {
        $old_cible->delete();
      }
    }

    return null;
  }

  /**
   * @see parent::delete()
   */
  function delete() {
    $this->completeField("cible_id");
    $cible = $this->loadRefCible();

    if ($msg = parent::delete()) {
      return $msg;
    }

    // Si la cible n'est plus rattachée à aucune transmission, alors on la supprime
    if (!$cible->countTransmissions()) {
      $cible->delete();
    }

    return null;
  }

  /**
   * @see parent::getPerm()
   */
  function getPerm($perm) {
    if (!isset($this->_ref_sejour->_id)) {
      $this->loadRefsFwd();
    }

    return $this->_ref_sejour->getPerm($perm);
  }

  /**
   * Get the patient_id of CMbobject
   *
   * @return CPatient
   */
  function getIndexablePatient() {
    $this->loadRefSejour();
    $this->_ref_sejour->loadRelPatient();

    return $this->_ref_sejour->_ref_patient;
  }

  /**
   * Loads the related fields for indexing datum (patient_id et date)
   *
   * @return array
   */
  function getIndexableData() {
    $prat                      = $this->getIndexablePraticien();
    $array["id"]               = $this->_id;
    $array["author_id"]        = $this->user_id;
    $array["prat_id"]          = $prat->_id;
    $array["title"]            = $this->type;
    $array["body"]             = $this->text;
    $array["date"]             = str_replace("-", "/", $this->date);
    $array["function_id"]      = $prat->function_id;
    $array["group_id"]         = $prat->loadRefFunction()->group_id;
    $array["patient_id"]       = $this->getIndexablePatient()->_id;
    $array["object_ref_id"]    = $this->loadRefSejour()->_id;
    $array["object_ref_class"] = $this->loadRefSejour()->_class;

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
    return $content;
  }

  /**
   * Get the praticien_id of CMbobject
   *
   * @return CMediusers
   */
  function getIndexablePraticien() {
    return $this->loadRefSejour()->loadRefPraticien();
  }

  /**
   * Get transmissions friends
   *
   * @return CTransmissionMedicale[]
   */
  function loadRefsTransmissionsSibling() {
    $trans_by_types = array("data" => null, "action" => null, "result" => null);
    unset($trans_by_types[$this->type]);

    foreach ($trans_by_types as $name_type => $trans_type) {
      $where = array();
      if ($this->cible_id) {
        $where["cible_id"] = " = '$this->cible_id'";
      }
      else {
        $where["cible_id"] = " IS NULL";
        //Récupération des transmission notées en même temps (+/- 1 seconde)
        $datetime_min  = CMbDT::dateTime("-1 second", $this->date);
        $datetime_max  = CMbDT::dateTime("+1 second", $this->date);
        $where["date"] = " BETWEEN '$datetime_min' AND '$datetime_max'";
      }
      $where["type"]         = " = '$name_type'";
      $where["object_id"]    = $this->object_id ? " = '$this->object_id'" : "IS NULL";
      $where["object_class"] = $this->object_class ? " = '$this->object_class'" : "IS NULL";
      $where["sejour_id"]    = " = '$this->sejour_id'";
      $transmission          = new CTransmissionMedicale();
      $transmission->loadObject($where, "date DESC");
      $trans_by_types[$name_type] = $transmission;
    }

    return $this->_trans_sibling = $trans_by_types;
  }

  /**
   * @param CStoredObject $object
   * @deprecated
   * @todo redefine meta raf
   * @return void
   */
  public function setObject(CStoredObject $object) {
    CMbMetaObjectPolyfill::setObject($this, $object);
  }
}
