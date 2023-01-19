<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp;

use DateTime;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Mediboard\Mediusers\CMediusers;

class COperationGarrot extends CMbObject {
  /** @var integer Primary key */
  public $operation_garrot_id;

  /** @var integer COperation ID */
  public $operation_id;

  /** @var string Côté du garrot (gauche, droit, N/A) */
  public $cote;

  /** @var dateTime Date et heure de pose du garrot */
  public $datetime_pose;

  /** @var dateTime Date et heure de retrait du garrot */
  public $datetime_retrait;

  /** @var integer CMediusers ID */
  public $user_pose_id;

  /** @var integer CMediusers ID */
  public $user_retrait_id;

  /** @var integer Pression du garrot en mmHg */
  public $pression;

  /** @var integer Durée de pose du garrot (en minutes) */
  public $_duree;

  /** @var COperation Opération */
  public $_ref_operation;

  /** @var CMediusers Utilisateur ayant posé le garrot */
  public $_ref_user_pose;

  /** @var CMediusers Utilisateur ayant retiré le garrot */
  public $_ref_user_retrait;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "operation_garrot";
    $spec->key   = "operation_garrot_id";

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                     = parent::getProps();
    $props["operation_id"]     = "ref class|COperation back|garrots";
    $props['cote']             = 'enum list|gauche|droit|N/A default|N/A notNull';
    $props['datetime_pose']    = 'dateTime notNull';
    $props['datetime_retrait'] = 'dateTime moreThan|datetime_pose';
    $props['user_pose_id']     = 'ref class|CMediusers notNull back|garrot_poses';
    $props['user_retrait_id']  = 'ref class|CMediusers back|garrot_retraits';
    $props['pression']         = 'num min|0';

    $props['_duree']           = 'num';

    return $props;
  }

  function updateFormFields() {
    parent::updateFormFields();

    $this->_duree = $this->calculDuree();
  }

  /**
   * Calcule la durée de pose du garrot
   * @return int
   */
  function calculDuree() {
    $relative = $this->datetime_retrait ? $this->datetime_retrait : CMbDT::dateTime();
    return CMbDT::minutesRelative($this->datetime_pose, $relative);
  }

  /**
   * Chargement de l'opération
   *
   * @param bool|false $cached
   *
   * @return COperation|null
   */
  function loadRefOperation($cached = false) {
    return $this->_ref_operation = $this->loadFwdRef('operation_id', $cached);
  }

  /**
   * Chargement de l'utilisateur ayant fait la pose
   *
   * @param bool|false $cached
   *
   * @return CMediusers|null
   */
  function loadRefUserPose($cached = false) {
    return $this->_ref_user_pose = $this->loadFwdRef('user_pose_id', $cached);
  }

  /**
   * Chargement de l'utilisateur ayant retiré le garrot
   *
   * @param bool|false $cached
   *
   * @return CMediusers|null
   */
  function loadRefUserRetrait($cached = false) {
    return $this->_ref_user_retrait = $this->loadFwdRef('user_retrait_id', $cached);
  }

  /**
   * Chargement des utilisateurs ayant posé et retiré le garrot
   *
   * @param bool|false $cached
   */
  function loadRefUsers($cached = false) {
    $this->loadRefUserPose($cached);
    $this->loadRefUserRetrait($cached);
  }

  /**
   * @see parent::store()
   */
  function store() {
    if (!$this->_id) {
      $this->datetime_pose = CMbDT::dateTime();
      $this->user_pose_id  = CMediusers::get()->_id;
    }

    if ($this->_id && $this->fieldModified('datetime_retrait')) {
      $this->user_retrait_id  = CMediusers::get()->_id;
    }

    return parent::store();
  }
}
