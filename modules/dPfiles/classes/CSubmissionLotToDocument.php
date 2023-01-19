<?php
/**
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files;

use Exception;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CStoredObject;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * Description
 */
class CSubmissionLotToDocument extends CMbObject {
  /**
   * @var integer Primary key
   */
  public $submissionlot_document_id;
  public $submissionlot_id;
  public $object_class;
  public $object_id;
  public $_ref_object;

  /**
   * Initialize the class specifications
   *
   * @return CMbObjectSpec
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table  = "submissionlot_document";
    $spec->key    = "submissionlot_document_id";
    return $spec;  
  }
  
  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();

    $props["object_id"]        = "ref notNull class|CMbObject meta|object_class cascade back|submission_lots_document";
    $props["submissionlot_id"] = "ref class|CSubmissionLot back|submissionset_document";
    $props["object_class"]     = "enum list|CCompteRendu|CFile notNull";

    return $props;
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

  /**
   * @inheritDoc
   * @todo remove
   */
  function loadRefsFwd() {
    parent::loadRefsFwd();
    $this->loadTargetObject();
  }
}
