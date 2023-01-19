<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Search\IIndexableObject;
use Ox\Mediboard\System\CAlert;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * Permet d'ajouter des observations médicales à un séjour
 */
class CObservationMedicale extends CMbObject implements IIndexableObject {
  // DB Table key
  public $observation_medicale_id;

  // DB Fields
  public $sejour_id;
  public $user_id;

  public $degre;
  public $date;
  public $text;
  public $type;
  public $cancellation_date;
  public $etiquette;
  public $duree;

  public $object_class;
  public $object_id;
  public $_ref_object;

  /** @var CSejour */
  public $_ref_sejour;

  /** @var CMediusers */
  public $_ref_user;

  /** @var CAlert */
  public $_ref_alerte;

  static $tag_alerte = "observation";

  public const ETIQUETTES = [
      "cardio_vasculaire",
      "pneumologie",
      "endocrinologie",
      "gastro_enterologie",
      "cancero",
      "neurologie",
      "dietetique",
      "gynecologie",
      "nephrologie",
      "oncologie",
      "psychiatrie",
      "rhumatologie",
      "orthopedie",
      "biologie",
      "imagerie"
  ];

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec              = parent::getSpec();
    $spec->table       = 'observation_medicale';
    $spec->key         = 'observation_medicale_id';
    $spec->measureable = true;

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();

    $props["object_id"]         = "ref class|CPrescriptionLineAny meta|object_class cascade back|observations";
    $props["object_class"]      = "str show|0";
    $props["sejour_id"]         = "ref notNull class|CSejour back|observations";
    $props["user_id"]           = "ref notNull class|CMediusers back|observations";
    $props["degre"]             = "enum notNull list|low|high|info default|low";
    $props["date"]              = "dateTime notNull";
    $props["text"]              = "text helped|degre markdown";
    $props["type"]              = "enum list|reevaluation|synthese|communication";
    $props["cancellation_date"] = "dateTime";
    $props["etiquette"]         = "enum list|" . implode("|", self::ETIQUETTES);
    $props["duree"]             = "num";

    return $props;
  }

  /**
   * @see parent::canEdit()
   */
  function canEdit() {
    $nb_hours     = CAppUI::gconf("soins Other max_time_modif_suivi_soins");
    $datetime_max = CMbDT::dateTime("+ $nb_hours HOURS", $this->date);

    return $this->_canEdit = (CMbDT::dateTime() < $datetime_max) && (CAppUI::$instance->user_id == $this->user_id);
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

    $this->_view = "Observation du Dr $user->_view";

    return $this->_ref_user = $user;
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
   * Chargement de l'alerte
   *
   * @return void
   */
  function loadRefAlerte() {
    $this->_ref_alerte = new CAlert();
    $this->_ref_alerte->setObject($this);
    $this->_ref_alerte->tag   = self::$tag_alerte;
    $this->_ref_alerte->level = "medium";
    $this->_ref_alerte->loadMatchingObject();
  }

  function store() {
    $msg_alerte = "";

    $manual_alerts = CAppUI::gconf("soins Observations manual_alerts");
    if ($manual_alerts) {
      $this->completeField("degre", "text");
      if (!$this->_id || $this->fieldModified("text") || $this->fieldModified("degre")) {
        $msg_alerte = CAppUI::tr("CObservationMedicale-degre") . ": " . $this->getFormattedValue("degre") . "\n" . $this->text;
      }
    }

    if ($msg = parent::store()) {
      return $msg;
    }

    if ($manual_alerts) {
      $this->loadRefAlerte();

      if ($msg_alerte) {
        $this->_ref_alerte->handled  = 0;
        $this->_ref_alerte->comments = $msg_alerte;
        if ($msg = $this->_ref_alerte->store()) {
          return $msg;
        }
      }
    }

    return null;
  }

  static function massLoadRefAlerte(&$observations = array(), $handled = true) {
    $alerte = new CAlert();
    $where  = array(
      "object_class" => "= 'CObservationMedicale'",
      "object_id"    => CSQLDataSource::prepareIn(CMbArray::pluck($observations, "_id")),
      "level"        => "= 'medium'",
      "tag"          => "= '" . self::$tag_alerte . "'"
    );

    if (!$handled) {
      $where["handled"] = "= '0'";
    }

    $alertes = $alerte->loadList($where);

    CStoredObject::massLoadFwdRef($alertes, "handled_user_id");

    foreach ($alertes as $_alerte) {
      $observations[$_alerte->object_id]->_ref_alerte = $_alerte;
    }

    foreach ($observations as $_observation) {
      if (!$_observation->_ref_alerte) {
        $_observation->_ref_alerte = new CAlert();
      }
      $_observation->_ref_alerte->loadRefHandledUser();
    }
  }

  /**
   * @see parent::check()
   */
  /*
  function check(){
    if (!$this->_id && $this->degre == "info" && $this->text == "Visite effectuée") {
      if ($this->countNotifSiblings()) {
        return "Notification deja effectuée";
      }
    }
    return parent::check();
  }
  */

  /**
   * Compte les visites effectuées
   *
   * @return int
   */
  function countNotifSiblings() {
    $date               = CMbDT::date($this->date);
    $observation        = new CObservationMedicale();
    $where              = array();
    $where["sejour_id"] = " = '$this->sejour_id'";
    $where["user_id"]   = " = '$this->user_id'";
    $where["degre"]     = " = 'info'";
    $where["date"]      = " LIKE '$date%'";
    $where["text"]      = " = 'Visite effectuée'";

    return $observation->countList($where);
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
    $sejour = $this->loadRefSejour();
    $sejour->loadRelPatient();

    return $sejour->_ref_patient;
  }

  /**
   * Loads the related fields for indexing datum (patient_id et date)
   *
   * @return array
   */
  function getIndexableData() {
    /**@var $user CMediusers* */
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
   * @param CStoredObject $object
   * @deprecated
   * @todo redefine meta raf
   * @return void
   */
  public function setObject(CStoredObject $object) {
    CMbMetaObjectPolyfill::setObject($this, $object);
  }

  /**
   * @param bool $cache
   * @deprecated
   * @todo redefine meta raf
   * @return bool|CStoredObject|CExObject|null
   * @throws Exception
   */
  public function loadTargetObject($cache = true) {
    return CMbMetaObjectPolyfill::loadTargetObject($this, $cache);
  }
}

