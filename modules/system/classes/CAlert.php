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
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CRequest;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * The CAlert Class
 */
class CAlert extends CMbObject {
  /** @var string */
  public const RESOURCE_TYPE = 'alert';

  public const RELATION_CONTEXT = 'context';

  public $alert_id;

  // DB Fields
  public $tag;
  public $level;
  public $comments;
  public $creation_date;
  public $creation_user_id;
  public $handled;
  public $handled_date;
  public $handled_user_id;

  public $object_class;
  public $object_id;
  public $_ref_object;

  // Ref fields
  /** @var CMediusers */
  public $_ref_user;
  /** @var CUser */
  public $_ref_handled_user;
  public $_relative_date;
  public $_relative_hour;
  public $_libelle;
  public $_edit_access;

  /** @var CPatient */
  public $_ref_patient;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec           = parent::getSpec();
    $spec->table    = 'alert';
    $spec->key      = 'alert_id';
    $spec->loggable = false;

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                     = parent::getProps();
    $props["tag"]              = "str notNull fieldset|extra";
    $props["level"]            = "enum list|low|medium|high default|medium notNull fieldset|default";
    $props["comments"]         = "text fieldset|default";
    $props["creation_date"]    = "dateTime fieldset|default";
    $props["creation_user_id"] = "ref class|CMediusers back|alerts_created fieldset|default";
    $props["handled"]          = "bool notNull default|0 fieldset|default";
    $props["handled_date"]     = "dateTime fieldset|extra";
    $props["handled_user_id"]  = "ref class|CUser back|handled_alerts fieldset|extra";
    $props["object_id"]        = "ref notNull class|CMbObject meta|object_class cascade back|alerts fieldset|default";
    $props["object_class"]     = "str notNull class show|0 fieldset|default";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function store() {
    $this->completeField("handled");

    // Check some purge when creating a CAlert
    if (!$this->_id) {
      CApp::doProbably(CAppUI::conf('CAlert_purge_lifetime'), [$this, 'purgeSome']);
    }

    if (!$this->creation_date) {
      $this->creation_date = CMbDT::dateTime();
      if ($this->_id) {
        $this->creation_date = $this->loadFirstLog()->date;
      }
    }

    if (!$this->creation_user_id) {
      $this->creation_user_id = CMediusers::get()->_id;

      if ($this->_id) {
        $this->creation_user_id = $this->loadFirstLog()->user_id;
      }
    }

    if ($this->fieldModified("handled", "1") || ($this->handled && !$this->handled_date && !$this->handled_user_id)) {
      $this->handled_date    = CMbDT::dateTime();
      $this->handled_user_id = CMediusers::get()->_id;
      if ($this->handled && !$this->handled_date && !$this->handled_user_id) {
        $last_log              = $this->loadLastLog();
        $this->handled_date    = $last_log->date;
        $this->handled_user_id = $last_log->user_id;
      }
    }

    if ($this->fieldModified("handled", "0")) {
      $this->handled_date = $this->handled_user_id = "";
    }

    if ($msg = parent::store()) {
      return $msg;
    }
  }

  /**
   * Load handled user
   *
   * @return CUser|CStoredObject
   * @throws Exception
   */
  public function loadRefHandledUser() {
    return $this->_ref_handled_user = $this->loadFwdRef("handled_user_id", true);
  }

  /**
   * Load creator
   *
   * @return CMediusers|CStoredObject
   * @throws Exception
   */
  public function loadRefUser() {
    return $this->_ref_user = $this->loadFwdRef("creation_user_id", true);
  }

  /**
   * Load Object
   *
   * @return CMbObject|CStoredObject
   * @throws Exception
   */
  public function loadRefObject() {
    return $this->_ref_object = $this->loadFwdRef("object_id", true);
  }

  /**
   * Purges some CAlert according to given delay
   *
   * @return bool|resource|void
   * @throws Exception
   */
  public function purgeSome() {
    if (!$delay = CAppUI::conf('CAlert_purge_delay')) {
      return;
    }

    $date     = CMbDT::dateTime("- {$delay} days", CMbDT::format(null, "%Y-%m-%d 00:00:00"));
    $lifetime = (CAppUI::conf('CAlert_purge_lifetime')) ?: 100;
    $limit    = $lifetime * 10;

    $ds = $this->_spec->ds;

    $request = new CRequest();
    $request->addTable($this->_spec->table);

    $where = [
      'handled'      => "= '1'",
      'handled_date' => $ds->prepare('IS NOT NULL AND handled_date < ?', $date),
    ];

    $request->addWhere($where);
    $request->setLimit($limit);

    return $ds->exec($request->makeDelete());
  }

  /**
   * Handled notification
   *
   * @param int $notification_id notification ID
   *
   * @return void
   * @throws Exception
   */
  public function handled($notification_id) {
    if (!$notification_id) {
      return;
    }

    $alert = new self;
    $alert->load($notification_id);
    $alert->handled = 1;
    $alert->handled_date = "now";
    $alert->store();
  }

  /**
   * Handle perm edit for alerts on lines of prescription
   *
   * @param array $cat_ids Categories identifiers
   *
   * @return void
   * @throws Exception
   */
  public function getPermAccess($cat_ids = []) {
    switch ($this->object_class) {
      case "CPrescriptionLineElement":
        $this->_edit_access = in_array(
          $this->loadTargetObject()->_ref_element_prescription->category_prescription_id,
          $cat_ids
        );
        break;
      case "CPrescriptionLineMedicament":
      case "CPrescriptionLineMix":
        $curr_user = CMediusers::get();
        $this->_edit_access = $curr_user->isPraticien() || $curr_user->isInfirmiere() || $curr_user->isAideSoignant();
        break;
      default;
    }
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
   * @throws Exception
   * @todo remove
   */
  public function loadRefsFwd() {
    parent::loadRefsFwd();
    $this->loadTargetObject();
  }
}
