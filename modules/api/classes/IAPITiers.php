<?php
/**
 * @package Mediboard\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Api;

use Ox\Mediboard\Patients\Constants\CActionReport;
use Ox\Mediboard\Patients\CPatient;

/**
 * Description
 */
interface IAPITiers {
  /**
   * Request of user
   *
   * @param CPatientUserAPI $patient_api    User API
   * @param array           $requests_names      constants accepted
   * @param String          $first_datetime End datetime
   * @param String          $end_datetime   first datetime
   *
   * @return CActionReport
   * @throws CAPITiersException
   */
  function synchronizeData(CPatientUserAPI $patient_api, $requests_names, $first_datetime, $end_datetime);

  /**
   * Save requests
   *
   * @param CPatientUserAPI $patient_api    User API
   * @param array           $requests_names Requests names
   * @param String          $first_datetime End datetime
   * @param String          $end_datetime   first datetime
   * @param int             $group_id       group id of etab
   *
   * @return array
   * @throws CAPITiersException
   */
  function saveRequest(CPatientUserAPI $patient_api, $requests_names, $first_datetime, $end_datetime, $group_id);


  /**
   * Ask to the user to authorize the application.
   *
   * @param String $scope scope
   *
   * @return boolean true when we have the code grant flow else false.
   */
  public function getUrlAuthentification($scope = null);


  /**
   * Create user API
   *
   * @param int $patient_id patient id
   *
   * @return CUserAPI
   */
  public function aedUserAPI($patient_id);

  /**
   * Get the acess_token and refresh_token
   *
   * @return void.
   * @throws CAPITiersException
   */
  public function authorizeApp();

  /**
   * Know if subscription is available
   *
   * @return bool
   * throws CAPITiersException
   */
  public function hasSubscription();

  /**
   * Subscript to api notification to know when new data is available
   *
   * @param array $susbcriptions specify subscriptions
   *
   * @return mixed
   * @throws CAPITiersException
   */
  public function subscription($susbcriptions = array());


  /**
   * Get class name of api
   *
   * @return String
   */
  public function getClass();

  /**
   * Know if conflict exist
   *
   * @param String $type_conflict type
   *
   * @return boolean
   */
  public function hasConflict($type_conflict);

  /**
   * Return conflict
   *
   * @param String $type_conflict type
   *
   * @return mixed
   */
  public function getConflict($type_conflict);

  /**
   * Revoke access for API
   *
   * @return mixed
   * @throws CAPITiersException
   */
  public function requestRevokeAccess();

  /**
   * Know if revoke request is available
   *
   * @return bool
   */
  public function requestRevokeAvailable();

  /**
   * Set user API
   *
   * @param CUserAPI $user_api user api
   *
   * @return void
   * @throws CAPITiersException
   */
  public function setUserAPI($user_api);

  /**
   * Set Patient user api
   *
   * @param CPatientUserAPI $patient_user_api patient user api
   *
   * @return void
   * @throws CAPITiersException
   */
  public function setPatientUserAPI($patient_user_api);

  /**
   * Set Patient
   *
   * @param CPatient $patient patient
   *
   * @return void
   * @throws CAPITiersException
   */
  public function setPatient($patient);

  /**
   * Put user to inactive
   *
   * @return void
   * @throws CAPITiersException
   */
  public function deleteUser();

  /**
   * Synchronize goals
   *
   * @param CPatientUserAPI $patientUserAPI PatientUser API
   *
   * @return void
   */
  public function synchronizeGoals(CPatientUserAPI $patientUserAPI);
}
