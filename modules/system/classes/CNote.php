<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;
use Exception;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Admin\CPermObject;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * Classe CNote.
 *
 * @abstract Permet de créer des notes sur n'importe quel objet
 */
class CNote extends CMbObject {
  // DB Table key
  public $note_id;

  // DB Fields
  public $user_id;
  public $public;
  public $degre;
  public $date;
  public $libelle;
  public $text;

  public $object_class;
  public $object_id;
  public $_ref_object;

  /** @var CMediusers */
  public $_ref_user;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'note';
    $spec->key   = 'note_id';
    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();
    $props["user_id"]    = "ref class|CMediusers back|owned_notes";
    $props["public"]     = "bool notNull default|1";
    $props["degre"]      = "enum notNull list|low|medium|high default|low";
    $props["date"]       = "dateTime notNull";
    $props["libelle"]    = "str notNull";
    $props["text"]       = "text";
    $props["object_id"]    = "ref notNull class|CMbObject meta|object_class cascade back|notes";
    $props["object_class"] = "str notNull class show|0";
    return $props;
  }

  /**
   * @return CMediusers
   */
  function loadRefUser() {
    $user = $this->loadFwdRef("user_id", true);
    $this->_view = "Note écrite par ".$user->_view;
    return $this->_ref_user = $user;
  }

  /**
   * @inheritdoc
   */
  function getPerm($perm) {
    $curr_user = CMediusers::get();

    // Owner has always permission
    if ($this->user_id === $curr_user->_id) {
      return true;
    }

    // Existing private note
    if ($this->_id && !$this->public) {
      $perm_obj = new CPermObject();
      $perm_obj->user_id = $curr_user->_id;
      $perm_obj->object_class = $this->_class;
      $perm_obj->object_id = $this->_id;
      $perm_obj->loadMatchingObject();
      return $perm_obj->permission >= $perm;
    }

    // Read permission an object for note edition
    return $this->loadTargetObject()->getPerm(PERM_READ);
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
