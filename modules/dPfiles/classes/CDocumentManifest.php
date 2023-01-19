<?php
/**
 * @package Mediboard\dPfiles
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files;

use Exception;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Interop\Eai\CInteropActor;
use Ox\Mediboard\Patients\CPatient;

/**
 * Description
 */
class CDocumentManifest extends CMbObject {
  /** @var integer Primary key */
  public $document_manifest_id;

  public $version;
  public $repositoryUniqueID;
  public $repositoryUniqueIDExternal;
  public $created_datetime;
  public $last_update;
  public $treated_datetime;
  public $type;
  public $status;
  public $actor_id;
  public $actor_class;
  public $patient_id;
  public $patient_reference;
  public $author_given;
  public $author_family;
  public $initiator;

  /** @var CDocumentReference[] */
  public $_ref_documents_reference;

  /** @var CInteropActor */
  public $_ref_actor;

  /** @var CPatient */
  public $_ref_patient;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table  = "document_manifest";
    $spec->key    = "document_manifest_id";

    $spec->uniques["actor"] = array ("actor_id", "actor_class", "repositoryUniqueID");

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();

    $props["version"]             = "str";
    $props["repositoryUniqueID"]  = "str notNull";
    $props["repositoryUniqueIDExternal"] = "str";
    $props["status"]               = "str";
    $props["created_datetime"]    = "dateTime notNull";
    $props["last_update"]         = "dateTime notNull";
    $props["treated_datetime"]    = "dateTime notNull";
    $props["type"]                = "enum list|XDS|DMP|FHIR|HL7|ZEPRA default|XDS";
    $props["actor_id"]            = "ref notNull class|CInteropActor meta|actor_class back|document_manifest";
    $props["actor_class"]         = "str notNull class maxLength|80";
    $props["patient_id"]          = "ref notNull class|CPatient back|document_manifest";
    $props["patient_reference"]   = "text";
    $props["author_family"]       = "str";
    $props["author_given"]        = "str";
    $props["initiator"]           = "enum list|client|server default|client";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function store() {
    if (!$this->_id) {
      $this->created_datetime = "now";
    }

    $this->last_update = "now";

    return parent::store();
  }

  /**
   * Load documents reference
   *
   * @return CDocumentReference[]
   * @throws Exception
   */
  function loadRefsDocumentsReferences() {
    return $this->_ref_documents_reference = $this->loadBackRefs("documents_reference");
  }

  /**
   * Set actor on CDocumentManifest
   *
   * @param CInteropActor $actor actor
   *
   * @return void
   */
  function setActor(CInteropActor $actor) {
    $this->actor_class = $actor->_class;
    $this->actor_id    = $actor->_id;
  }

  /**
   * Load actor
   *
   * @return CInteropActor|CStoredObject
   * @throws Exception
   */
  function loadRefActor() {
    return $this->_ref_actor = $this->loadFwdRef("actor_id");
  }

  /**
   * Load patient
   *
   * @param bool $cache Use cache
   *
   * @return CPatient|CStoredObject
   * @throws Exception
   */
  function loadRefPatient($cache = true) {
    return $this->_ref_patient = $this->loadFwdRef("patient_id", $cache);
  }
}
