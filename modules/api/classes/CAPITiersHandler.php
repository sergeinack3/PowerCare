<?php
/**
 * @package Mediboard\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Api;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Mediboard\Patients\CPatient;

/**
 * Description
 */
class CAPITiersHandler {
  /** @var CAPITiers $api */
  public $api;
  public $api_class_name;

  /**
   * APITiersHandler constructor.
   *
   * @param CAPITiers $api_name name of class api tiers.
   */
  public function __construct($api_name = null) {
    if ($api_name) {
      $this->api_class_name = $api_name;
      $this->api            = new $api_name;
    }
  }

  /**
   * Update scope of user, need to call api for new token
   *
   * @param int $patient_id patient_id
   *
   * @return void
   * @throws CAPITiersException
   */
  public function updateScope($patient_id) {

    $patient_api                 = new CPatientUserAPI();
    $patient_api->patient_id     = $patient_id;
    $patient_api->api_user_class = CPatientUserAPI::getAPiUserClass($this->api->getClass());
    $patient_api->loadMatchingObject();
    $this->api->setPatientUserAPI($patient_api);

    $this->api->setPatientUserAPI($patient_api);
    $user = $patient_api->loadTargetObject();
    $this->api->setUserAPI($user);

    $this->api->authorizeApp();
    $this->api->aedUserAPI($patient_id);
  }

  /**
   * Authorize our application to get datas with a access token.
   *
   * @param string class|CPatient $patient_id patient id
   *
   * @return void
   * @throws CAPITiersException
   */
  function synchronizeAccount($patient_id) {
    if (!$patient_id) {
      throw new CAPITiersException(CAPITiersException::INVALID_PATIENT);
    }
    $this->api->authorizeApp();
    $user = $this->api->aedUserAPI($patient_id);
    /** @var CUserAPIOAuth $user_api */
    $user_api = $user;
    $this->api->setUserAPI($user_api);

    $patient_user_api                 = new CPatientUserAPI();
    $patient_user_api->patient_id     = $patient_id;
    $patient_user_api->api_user_class = $user->_class;
    $patient_user_api->api_user_id    = $user->_id;
    $patient_user_api->loadMatchingObject();
    if ($msg = $patient_user_api->store()) {
      throw new CAPITiersException(CAPITiersException::INVALID_STORE_PATIENT_USER_API, $msg);
    }
    $this->api->setPatientUserAPI($patient_user_api);
    $patient = new CPatient();
    $patient->load($patient_id);
    $patient->loadRefFirstPatientUser();

    //ajout du created date api si on peut
    $default_created_dt = CAppUI::gconf("appFine APITiers first_date_sync", $patient->_ref_first_patient_user->group_id);
    $created_dt = $this->api->getCreatedDateAPI();
    if (!$created_dt) {
      $created_dt = $default_created_dt;
    }
    else {
      // si la création du compte est avant $crea
      $created_dt = $created_dt < $default_created_dt ? $default_created_dt : $created_dt;
    }

    $user_api->created_date_api = $created_dt;
    if ($msg = $user_api->store()) {
      throw new CAPITiersException(CAPITiersException::INVALID_STORE_USER_API, $msg);
    }
    $created_dt = CMbDT::dateTime(null, $created_dt);
    // enregistrement des constantes depuis la création du compte ou depuis la date par défaut.
    $this->api->saveRequest(
      $patient_user_api, $user_api->getAcceptedConstantAsArray(), $created_dt, CMbDT::dateTime(), $patient->_ref_first_patient_user->group_id
    );

    if (!CAppUI::gconf("appFine APITiers subscribes_api_{$this->api->name_api}_active", $patient->_ref_first_patient_user->group_id)) {
      return;
    }
    // Ajout des souscription s'il y a
    if ($this->api->hasSubscription()) {
      try {
        $this->api->subscription();
        $user_api->subscribe = 1;
        if ($msg = $user_api->store()) {
          throw new CAPITiersException(CAPITiersException::INVALID_STORE_USER_API, $msg);
        }
      }
      catch (CAPITiersException $exception) {
        //on ne fais rien s'il y a un probème sur la subscription
        if ($exception->getCode() === CAPITiersException::INVALID_SUBSCRIPTION) {
          $user_api->subscribe = 0;
          $user_api->store();
        }
        else {
          throw $exception;
        }
      }
    }
  }

  /**
   * Revoke access for one API and delete the link between API and patient
   *
   * @param CPatientUserAPI $patient_user_api patient user api
   *
   * @return bool
   * @throws CAPITiersException
   */
  public
  function revokeAccess($patient_user_api) {
    if (!$patient_user_api || !$patient_user_api->_id) {
      throw new CAPITiersException(CAPITiersException::INVALID_PATIENT_USER_API);
    }

    $this->api->setPatientUserAPI($patient_user_api);
    $user_api = $patient_user_api->loadTargetObject();

    $this->api->setUserAPI($user_api);
    if (!$this->api->requestRevokeAvailable()) {
      throw new CAPITiersException(CAPITiersException::REVOKE_ACCESS_NOT_IMPLEMENTED);
    }

    if (!$this->api->requestRevokeAccess()) {
      throw  new CAPITiersException(CAPITiersException::REVOKE_ACCESS_FAILED);
    }

    $this->api->deleteUser();

    return true;
  }


  /**
   * Save request in stackRequest
   *
   * @param CPatient $patient        patient
   * @param String   $first_datetime start datetime
   * @param null     $end_datetime   end datetime
   * @param array    $constants      Constants to save
   *
   * @return void
   * @throws CAPITiersException
   */
  function saveRequest($patient, $first_datetime, $end_datetime = null, $constants = null) {
    if (!$patient->_id) {
      throw new CAPITiersException(CAPITiersException::INVALID_PATIENT);
    }

    $patient_user = $patient->loadRefFirstPatientUser();

    $patient_user_api = $patient->loadRefPatientUserAPI(
      array("api_user_class" => " = '" . CPatientUserAPI::getAPiUserClass($this->api_class_name) . "'")
    );
    if (!$patient_user_api || !$patient_user_api->_id) {
      throw new CAPITiersException(CAPITiersException::INVALID_PATIENT_USER_API);
    }

    if (!$end_datetime) {
      $end_datetime = CMbDT::dateTime();
    }

    $user_api = $patient_user_api->loadTargetObject();
    if (!$user_api || !$user_api->_id) {
      throw new CAPITiersException(CAPITiersException::INVALID_USER_API);
    }

    if (!$constants) {
      $constants = $user_api->getAcceptedConstantAsArray();
    }

    $this->api->saveRequest($patient_user_api, $constants, $first_datetime, $end_datetime, $patient_user->group_id);
  }
}
