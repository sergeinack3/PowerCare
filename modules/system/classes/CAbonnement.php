<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Exception;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * Description
 */
class CAbonnement extends CMbObject {
  /** @var integer Primary key */
  public $abonnement_id;

  /** @var string */
  public $datetime;

  /** @var integer CMediusers ID */
  public $user_id;

  public $object_class;
  public $object_id;
  public $_ref_object;

  /** @var CMediusers User to notify */
  public $_ref_user;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec                        = parent::getSpec();
    $spec->table                 = 'abonnement';
    $spec->key                   = 'abonnement_id';
    $spec->uniques['abonnement'] = ['object_class', 'object_id', 'user_id'];

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                 = parent::getProps();
    $props['datetime']     = 'dateTime notNull';
    $props['user_id']      = 'ref class|CMediusers notNull back|related_abonnements';
    $props["object_id"]    = "ref notNull class|CMbObject meta|object_class cascade back|abonnements";
    $props["object_class"] = "str notNull class show|0";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function store() {
    if (!$this->_id) {
      $this->datetime = ($this->datetime) ?: CMbDT::dateTime();
    }

    $this->completeField(['object_class', 'object_id']);

    $object = $this->loadTargetObject();

    if (!$object || !$object->_id || !$object->canDo()->read) {
      return 'common-error-No permission on this object';
    }

    return parent::store();
  }

  /**
   * @inheritdoc
   */
  function loadRefUser($cached = true) {
    return $this->_ref_user = $this->loadFwdRef('user_id', $cached);
  }

  /**
   * S'abonner à un contexte
   *
   * @param CMbObject $object
   * @param null      $user_id
   *
   * @return null|string
   */
  static function subscribe(CMbObject $object, $user_id = null) {
    if (!$object || !$object->_id) {
      return 'common-error-Unable to load object';
    }

    $abonnement = new static();
    $abonnement->setObject($object);
    $abonnement->user_id = ($user_id) ?: CMediusers::get()->_id;

    return $abonnement->store();
  }

  static function getSubscribers(CMbObject $object) {
    if (!$object || !$object->_id) {
      return [];
    }

    $abonnement = new static();
    $abonnement->setObject($object);
    $abonnements = $abonnement->loadMatchingListEsc();

    if (!$abonnements) {
      return [];
    }

    return $user_ids = CMbArray::pluck($abonnements, 'user_id');
  }

  /**
   * @param CStoredObject $object
   *
   * @return void
   * @todo redefine meta raf
   * @deprecated
   */
  public function setObject(CStoredObject $object) {
    CMbMetaObjectPolyfill::setObject($this, $object);
  }

  /**
   * @param bool $cache
   *
   * @return bool|CStoredObject|CExObject|null
   * @throws Exception
   * @deprecated
   * @todo redefine meta raf
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
