<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Exception;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Core\CStoredObject;
use Ox\Import\Framework\ImportableInterface;
use Ox\Import\Framework\Matcher\MatcherVisitorInterface;
use Ox\Import\Framework\Persister\PersisterVisitorInterface;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Traitement
 */
class CTraitement extends CMbObject implements ImportableInterface {
    /** @var string */
    public const RESOURCE_TYPE = "traitement";

    /** @var string */
    public const RELATION_DOSSIER_MEDICAL = 'medicalRecord';

    /** @var string */
    public const FIELDSET_OWNER = 'owner';

    public const FIELDSET_TARGET = 'target';

  // DB Table key
  public $traitement_id;

  // DB fields
  public $debut;
  public $fin;
  public $traitement;
  public $dossier_medical_id;
  public $annule;

  public $owner_id;
  public $creation_date;

  // Form Fields
  public $_search;

  /** @var CDossierMedical */
  public $_ref_dossier_medical;

  /** @var CMediusers|null */
  private $_ref_owner;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'traitement';
    $spec->key   = 'traitement_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                       = parent::getProps();
    $props["debut"]              = "date progressive fieldset|default";
    $props["fin"]                = "date progressive moreEquals|debut fieldset|default";
    $props["traitement"]         = "text helped seekable fieldset|default";
    $props["dossier_medical_id"] = "ref notNull class|CDossierMedical show|0 back|traitements cascade fieldset|target";
    $props["annule"]             = "bool show|0 fieldset|default";
    $props["owner_id"]           = "ref class|CMediusers back|traitements fieldset|owner";
    $props["creation_date"]      = "dateTime fieldset|owner";

    $props["_search"] = "str";

    return $props;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = CMbString::truncate($this->traitement, 40);
  }

  /**
   * @inheritDoc
   */
  function getPerm($permType)
  {
      return ($this->_id ? $this->loadRefDossierMedical()->getPerm($permType) : null) && parent::getPerm($permType);
  }

  /**
   * Charge le dossier médical
   *
   * @return CDossierMedical
   * @throws Exception
   */
  function loadRefDossierMedical() {
    return $this->_ref_dossier_medical = $this->loadFwdRef("dossier_medical_id");
  }

  /**
   * @return CStoredObject|null
   * @throws Exception
   */
  function loadRefOwner() {
    return $this->_ref_owner = $this->loadFwdRef("owner_id");
  }

  /**
   * @see parent::store()
   */
  function store() {
    // Save owner and creation date
    if (!$this->_id) {
      if (!$this->creation_date) {
        $this->creation_date = CMbDT::dateTime();
      }

      if (!$this->owner_id) {
        $this->owner_id = CMediusers::get()->_id;
      }
    }

    return parent::store();
  }

  /**
   * Update owner and creation date from user logs
   *
   * @return void
   * @throws \Exception
   */
  function updateOwnerAndDates() {
    if (!$this->_id || $this->owner_id && $this->creation_date) {
      return;
    }

    if (empty($this->_ref_logs)) {
      $this->loadLogs();
    }

    $first_log = $this->_ref_first_log;

    if ($first_log) {
      $this->owner_id      = $first_log->user_id;
      $this->creation_date = $first_log->date;

      $this->rawStore();
    }
  }

    /**
     * @return Item|null
     * @throws ApiException
     */
    public function getResourceMedicalRecord(): ?Item
    {
        $dossier_medical = $this->loadRefDossierMedical();
        if (!$dossier_medical || !$dossier_medical->_id) {
            return null;
        }

        return new Item($dossier_medical);
    }

  /**
   * @see parent::loadView()
   */
  function loadView() {
    parent::loadView();
    $this->loadRefDossierMedical();
  }

    public function matchForImport(MatcherVisitorInterface $matcher): ImportableInterface
    {
        return $matcher->matchTraitement($this);
    }

    public function persistForImport(PersisterVisitorInterface $persister): ImportableInterface
    {
        return $persister->persistObject($this);
    }
}

