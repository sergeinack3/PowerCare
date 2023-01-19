<?php
/**
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds;

use Exception;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;

/**
 * State of the document send on the Document Repository
 */
class CXDSDocument extends CMbObject {
  /** @var integer Primary key */
  public $xds_document_id;
  public $object_class;
  public $object_id;
  public $version;
  public $date;
  public $etat;
  public $visibilite;
  public $patient_id;

  // Meta
  public $_ref_object;

  /**
   * Initialize the class specifications
   *
   * @return CMbFieldSpec
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table  = "xds_document";
    $spec->key    = "xds_document_id";
    return $spec;
  }
  
  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();

    $props["object_id"]    = "ref notNull class|CDocumentItem meta|object_class cascade back|xds_documents";
    $props["object_class"] = "str notNull class show|0";
    $props["version"]      = "num";
    $props["date"]         = "dateTime";
    $props["etat"]         = "str";
    $props["visibilite"]   = "enum list|0|1";
    $props["patient_id"]   = "ref class|CPatient show|1 cascade back|xds_documents";

    return $props;
  }

  /**
   * Return the last send to the Document Repository
   *
   * @param String $document_id Id
   * @param String $class       Class
   *
   * @return void
   */
  function getLastSend($document_id, $class) {
    $this->object_class = $class;
    $this->object_id    = $document_id;
    $this->loadMatchingObject("date DESC");
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
   * @return mixed
   * @throws Exception
   */
  public function loadTargetObject($cache = true) {
    return CMbMetaObjectPolyfill::loadTargetObject($this, $cache);
  }

  /**
   * @inheritDoc
   * @todo remove
   */
  function loadRefsFwd() {
    parent::loadRefsFwd();
    $this->loadTargetObject();
  }
}
