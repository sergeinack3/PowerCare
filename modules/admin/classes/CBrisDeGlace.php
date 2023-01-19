<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Check access to a sejour and ask for reason if user doesn't have the right
 */
class CBrisDeGlace extends CMbObject {
  public $bris_id;

  public $date;
  public $user_id;
  public $comment;
  public $role;

  public $_ref_user;

  // Meta
  public $object_id;
  public $object_class;
  public $_ref_object;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'bris_de_glace';
    $spec->key   = 'bris_id';

    return $spec;
  }

  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props                 = parent::getProps();
    $props["user_id"]      = "ref class|CMediusers notNull back|bris_de_glace_user";
    $props["date"]         = "dateTime notNull";
    $props["group_id"]     = "ref class|CGroups notNull back|bris_de_glace_group";
    $props["comment"]      = "text helped";
    $props['role']         = 'enum list|in_charge|consultant';
    $props['object_id']    = 'ref notNull class|CMbObject meta|object_class back|bris_de_glace_meta';
    $props['object_class'] = 'enum list|CPatient|CSejour show|0';

    return $props;
  }

  /**
   * @return CMediusers|null
   */
  function loadRefUser() {
    return $this->_ref_user = $this->loadFwdRef("user_id", true);
  }

  /**
   * Check if the sejour need to be unlock
   *
   * @param CSejour $sejour Sejour
   * @param bool    $modal  Modale
   *
   * @return bool
   */
  static function checkForSejour($sejour, $modal = true) {
    if (!self::canAccess($sejour)) {
      $sejour->loadRefPatient();
      $smarty = new CSmartyDP("modules/admin");
      $smarty->assign("sejour", $sejour);
      $smarty->assign("bris", new CBrisDeGlace());
      $smarty->assign("modale", $modal);
      $smarty->display("need_bris_de_glace.tpl");
      CApp::rip();
    }

    return true;
  }


  /**
   * Check wether 'bris de glace' is required or not
   *
   * @return bool
   */
  static function isBrisDeGlaceRequired(?int $group_id = null) {
    return CAppUI::gconf("admin CBrisDeGlace enable_bris_de_glace", $group_id);
  }

  /**
   * Check if we can access to the view following the configuration and already granted.
   *
   * @param CSejour $sejour sejour object
   *
   * @return bool
   */
    static function canAccess($sejour)
    {
        $group = $sejour->loadRefEtablissement();
        $user  = CMediusers::get();

        //check for config and elements
        if (!$sejour->_id
            || !self::isBrisDeGlaceRequired($group->_id)
            || ($sejour->praticien_id == $user->_id)
            || (!$user->use_bris_de_glace && !$sejour->bris_de_glace)
        ) {
            return true;
        }

        $today = CMbDT::date();

        $bris                  = new self();
        $ds                    = $bris->getDS();
        $where                 = [];
        $where["date"]         = $ds->prepareBetween("$today 00:00:00", "$today 23:59:59");
        $where["object_class"] = " = 'CSejour'";
        $where["object_id"]    = $ds->prepare("= ?", $sejour->_id);
        $where["user_id"]      = $ds->prepare("= ?", $user->_id);

        // no need of bris de glace
        if ($bris->countList($where)) {
            return true;
        }

        return false;
    }

  /**
   * Load all 'bris de glace' managed by user_id
   *
   * @param null|int    $user_id        User identifier
   * @param null|string $date_start     Date start
   * @param null|string $date_end       Date end
   * @param array       $object_classes Object classes
   *
   * @return CBrisDeGlace[] $briss
   */
    static function loadBrisForUser(
        $user_id = null,
        $date_start = null,
        $date_end = null,
        $object_classes = [],
        ?string $patient_id = null
    ) {
        $date_start    = $date_start ? $date_start : CMbDT::date();
        $date_end      = $date_end ? $date_end : $date_start;
        $bris          = new self();
        $ds            = $bris->getDS();
        $where         = [];
        $ljoin         = [];
        $where["date"] = $ds->prepareBetween("$date_start 00:00:00", "$date_end 23:59:59");
        if (count($object_classes)) {
            $where["object_class"] = $ds->prepareIn($object_classes);

            if (in_array("CSejour", $object_classes) && count($object_classes) === 1 && $patient_id) {
                $ljoin["sejour"] = "bris_de_glace.object_id = sejour.sejour_id AND bris_de_glace.object_class = 'CSejour'";
                $where["sejour.patient_id"] = $ds->prepare('= ?', $patient_id);
            }
        }
        if ($user_id) {
            $where["user_id"] = $ds->prepare("= ?", $user_id);
        }

        /** @var CBrisDeGlace[] $briss */
        $briss = $bris->loadList($where, "date DESC", null, null, $ljoin);

        return $briss;
    }

    /**
     * Load the sejours managed by user_id which has been broken by other
   *
   * @param null|int    $user_id    User identifier
   * @param null|string $date_start Date start
   * @param null|string $date_end   Date end
   *
   * @return CBrisDeGlace[]
   */
  static function loadBrisForOwnObject($user_id = null, $date_start = null, $date_end = null) {
    $date_start = $date_start ? $date_start : CMbDT::date();
    $date_end   = $date_end ? $date_end : $date_start;
    $user_id    = $user_id ? $user_id : CMediusers::get()->_id;

    $bris  = new CBrisDeGlace();
    $ljoin = array("sejour" => "sejour.sejour_id = bris_de_glace.object_id");
    $where = array(
      "bris_de_glace.object_class" => " = 'CSejour' ",
      "sejour.praticien_id"        => " =  '$user_id' ",
      "bris_de_glace.user_id"      => " != '$user_id' ",
      "bris_de_glace.date"         => " BETWEEN '$date_start 00:00:00' AND '$date_end 23:59:59' "
    );

    /** @var CBrisDeGlace[] $briss */
    $briss = $bris->loadList($where, "date DESC", null, null, $ljoin);

    return $briss;
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
