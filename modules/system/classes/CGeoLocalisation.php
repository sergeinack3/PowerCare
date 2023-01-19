<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * Description
 */
class CGeoLocalisation extends CMbObject {
  /** @var integer Primary key */
  public $geolocalisation_id;

  public $lat_lng;
  public $commune_insee;

  public $processed;

  public $object_class;
  public $object_id;
  public $_ref_object;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec                    = parent::getSpec();
    $spec->table             = 'geolocalisation';
    $spec->key               = 'geolocalisation_id';
    $spec->uniques['object'] = array('object_class', 'object_id');

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                  = parent::getProps();
    $props['commune_insee'] = 'str';
    $props['processed']     = 'bool default|0';
    $props['lat_lng']       = 'text';
    $props["object_id"]     = "ref notNull class|CStoredObject meta|object_class cascade back|geolocalisation";
    $props['object_class']  = 'enum list|' . implode('|', CApp::getChildClasses(IGeocodable::class, false, true)) . ' notNull';

    return $props;
  }

  /**
   * @inheritdoc
   */
  function store() {
    if (!$this->_id) {
      $this->processed = '0';
    }
//    if (!$this->_id || ($this->fieldModified('lat_lng') || $this->fieldModified('commune_insee'))) {
//      $this->processed = ($this->lat_lng || $this->commune_insee) ? '1' : '0';
//    }

    return parent::store();
  }

  function isProcessed() {
    return ($this->processed);
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
