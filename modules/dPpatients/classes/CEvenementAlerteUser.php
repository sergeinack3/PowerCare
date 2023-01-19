<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Exception;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * Règle concernant l'alerte des évènements patient
 */
class CEvenementAlerteUser extends CMbObject {
  // DB Table key
  public $alert_user_id;

  // DB Fields
  public $object_class;
  public $object_id;
  public $_ref_object;
  public $user_id;

  // Object References
  /** @var CMediusers $_ref_user */
  public $_ref_user;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'evenement_alert_user';
    $spec->key   = 'alert_user_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $specs                 = parent::getProps();
    $specs["object_id"]    = "ref notNull class|CStoredObject meta|object_class back|users_alert_evt cascade";
    $specs["object_class"] = "enum notNull list|CRegleAlertePatient|CEvenementPatient show|0";
    $specs["user_id"]      = "ref notNull class|CMediusers back|alert_evt_user";

    return $specs;
  }

  /**
   * Chargement de l'utilisateur
   *
   * @return CMediusers
   */
  function loadRefUser() {
    return $this->_ref_user = $this->loadFwdRef("user_id", true);
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
