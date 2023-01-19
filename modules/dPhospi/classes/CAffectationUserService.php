<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi;

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Class CAffectationUserService
 */
class CAffectationUserService extends CMbObject {
  // DB Table key
  public $user_service_id;

  // DB references
  public $user_id;
  public $service_id;
  public $date;

  // References
  public $_ref_user;
  public $_ref_service;
  public $_ref_responsable_jour;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec                    = parent::getSpec();
    $spec->table             = "affectation_user_service";
    $spec->key               = "user_service_id";
    $spec->uniques["unique"] = array("user_id", "service_id", "date");

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props               = parent::getProps();
    $props["user_id"]    = "ref notNull class|CMediusers back|users_service";
    $props["service_id"] = "ref class|CService back|affectations_users";
    $props["date"]       = "date";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function store() {
    if (!$this->date) {
      $where               = array();
      $where["date"]       = "IS NULL";
      $where["user_id"]    = " = '$this->user_id'";
      $where["service_id"] = " = '$this->service_id'";
      $affectation         = new self;
      $count               = $affectation->countList($where);
      if ($count) {
        return CAppUI::tr("CAffectationUserService-failed-unique");
      }
    }

    return parent::store();
  }

  /**
   * Load mediusers
   *
   * @return CMediusers
   */
  function loadRefUser() {
    return $this->_ref_user = $this->loadFwdRef("user_id", true);
  }

  /**
   * Load Service
   *
   * @return CService
   */
  function loadRefService() {
    return $this->_ref_service = $this->loadFwdRef("service_id", true);
  }

  /**
   * Liste des utilisateurs affectés à un service
   *
   * @param int $service_id Service concerné
   *
   * @return self[]
   */
  static function listUsersService($service_id) {
    $where               = [];
    $where[]             = " date IS NULL";
    $where["service_id"] = " = '$service_id'";
    $where["user_id"]    = " IS NOT NULL";

    $affectation_user  = new self;
    $affectations_user = $affectation_user->loadList($where, null, null, "user_service_id");
    foreach ($affectations_user as $_affectation) {
      $_affectation->loadRefUser();
    }

    $order_date = CMbArray::pluck($affectations_user, "date");
    $order_user_last_name = CMbArray::pluck($affectations_user, "_ref_user", "_user_last_name");
    array_multisort($order_date, SORT_DESC, $order_user_last_name, SORT_ASC, $affectations_user);

    return $affectations_user;
  }

  /**
   * Récupération du responsable du jour du service choisi
   *
   * @param int    $service_id Service concerné
   * @param string $date       Date  concerné
   *
   * @return CAffectationUserService
   */
  static function loadResponsableJour($service_id, $date) {
    $responsable             = new self;
    $responsable->date       = $date;
    $responsable->service_id = $service_id;
    $responsable->loadMatchingObject();
    $responsable->loadRefUser()->loadRefFunction();

    return $responsable;
  }

  /**
   * Récupération des responsables du jour des services choisis
   *
   * @param int[]    $services_ids Service concerné
   * @param string $date       Date  concerné
   *
   * @return CAffectationUserService[]
   */
  static function loadResponsablesJour($services_ids, $date) {
    $responsables = array();
    foreach ($services_ids as $_service_id) {
      $responsable = self::loadResponsableJour($_service_id, $date);
      $responsables[$responsable->service_id] = $responsable;
    }
    return $responsables;
  }
}
