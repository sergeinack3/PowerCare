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
use Ox\Core\CMbSecurity;
use Ox\Core\CStoredObject;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Eai\CMbOID;
use Ox\Mediboard\Patients\CPatient;

/**
 * Description
 */
class CDocumentReference extends CMbObject {
  /** @var integer Primary key */
  public $document_reference_id;

  public $hash;
  public $size;
  public $version;
  public $status;
  public $security_label;
  public $uniqueID;
  public $created_datetime;
  public $last_update;
  public $object_id;
  public $object_class;
  public $document_manifest_id;
  public $parent_id;
  public $actor_id;
  public $actor_class;
  public $initiator;
  public $metadata;

  // behavior_fields
  public $_document_manifest_type;

  /** @var CDocumentItem */
  public $_ref_object;
  /** @var CInteropActor */
  public $_ref_actor;
  /** @var CDocumentManifest */
  public $_ref_document_manifest;
  /** @var CDocumentReference */
  public $_ref_parent;
  /** @var CPatient */
  public $_ref_patient;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table  = "document_reference";
    $spec->key    = "document_reference_id";

    $spec->uniques["actor"] = array ("actor_id", "actor_class", "uniqueID");

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();

    $props["object_id"]            = "ref notNull class|CMbObject meta|object_class back|document_reference";
    $props["object_class"]         = "enum notNull list|CFile|CCompteRendu";
    $props["hash"]                 = "str";
    $props["status"]               = "str";
    $props["size"]                 = "num";
    $props["version"]              = "str";
    $props["security_label"]       = "str";
    $props["uniqueID"]             = "str notNull";
    $props["created_datetime"]     = "dateTime notNull";
    $props["last_update"]          = "dateTime notNull";
    $props["document_manifest_id"] = "ref class|CDocumentManifest notNull back|documents_reference";
    $props["parent_id"]            = "ref class|CDocumentReference back|parent_document_reference";
    $props["actor_id"]             = "ref notNull class|CInteropActor meta|actor_class back|actor_document_reference";
    $props["actor_class"]          = "str notNull class maxLength|80";
    $props["initiator"]            = "enum list|client|server default|client";
    $props["metadata"]             = "text";

    return $props;
  }

  /**
   * @inheritDoc
   */
  function store() {
    if (!$this->_id) {
      $this->created_datetime = "now";

      if (!$this->document_manifest_id) {
        $this->generateDocumentManifest();
      }
    }

    $this->last_update = "now";

    return parent::store();
  }

  /**
   * Generate document manifest
   *
   * @param string $initiator client|server
   *
   * @throws Exception
   *
   * @return CDocumentManifest|string Document manifest
   */
  function generateDocumentManifest($initiator = "client") {
    $actor   = $this->loadRefActor();
    $docItem = $this->loadRefObject();

    $document_manifest = new CDocumentManifest();
    $document_manifest->setActor($actor);
    $document_manifest->repositoryUniqueID = "urn:oid:".CMbOID::getOIDFromClass($docItem).".".hexdec(uniqid());
    $document_manifest->status             = "current";
    $document_manifest->initiator          = $initiator;
    $document_manifest->patient_id         = $this->loadRelPatient()->_id;
    $document_manifest->treated_datetime   = "now";
    if ($this->_document_manifest_type) {
      $document_manifest->type = $this->_document_manifest_type;
    }

    if ($msg = $document_manifest->store()) {
      return $msg;
    }

    $this->document_manifest_id = $document_manifest->_id;

    return $document_manifest;
  }

  /**
   * Set object
   *
   * @param CMbObject $object Object
   *
   * @return void
   */
  function setObject(CMbObject $object) {
    $this->object_class = $object->_class;
    $this->object_id    = $object->_id;
  }

  /**
   * Load Ref Object
   *
   * @return CDocumentItem|void
   * @throws Exception
   */
  function loadRefObject() {
    if (!$this->object_class) {
      return;
    }
    $this->_ref_object = new $this->object_class;
    $this->_ref_object->load($this->object_id);

    return $this->_ref_object;
  }

  /**
   * Load patient
   *
   * @return CPatient
   * @throws Exception
   */
  function loadRelPatient() {
    if (!$this->_ref_object) {
      $this->loadRefObject();
    }

    return $this->_ref_patient = $this->_ref_object->loadRelPatient();
  }

  /**
   * Load document manifest
   *
   * @return CDocumentReference|CStoredObject
   * @throws Exception
   */
  function loadRefDocumentManifest() {
    return $this->_ref_document_manifest = $this->loadFwdRef("document_manifest_id");
  }

  /**
   * Load document reference parent
   *
   * @return CDocumentReference|CStoredObject
   * @throws Exception
   */
  function loadRefParentDocumentReference() {
    return $this->_ref_parent = $this->loadFwdRef("parent_id");
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
}
