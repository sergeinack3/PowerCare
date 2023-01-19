<?php
/**
 * @package Mediboard\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Api;

use Exception;
use Ox\Core\CAppUI;

/**
 * Description
 */
class CAPITiersException extends Exception {

  const INVALID_CODE_GRANT_FLOW = 1;
  const UNKNOWN_ERROR = 2;
  const INVALID_PATIENT_USER_API = 3;
  const INVALID_USER_API = 4;
  const REVOKE_ACCESS_NOT_IMPLEMENTED = 5;
  const REVOKE_ACCESS_FAILED = 6;
  const TOKEN_NOT_AVAILABLE = 7;
  const INVALID_STORE_USER_API = 8;
  const INVALID_STORE_PATIENT_USER_API = 9;
  const INVALID_PATIENT = 10;
  const INVALID_DELETE_PATIENT_USER_API = 11;
  const INVALID_DELETE_USER_API = 12;
  const INSUFFICIENT_SCOPE = 13;
  const UNSUPPORTED_CONSTANT = 14;
  const UNSUPPORTED_FORMAT_DATE = 15;
  const INVALID_TYPE_HEADER = 16;
  const API_NOT_FOUND = 17;
  const INVALID_TOKEN = 18;
  const INVALID_REQUEST = 19;
  const INVALID_REFRESH_TOKEN = 20;
  const TOO_MANY_REQUEST = 21;
  const INVALID_STORE_STACK_REQUEST = 22;
  const INVALID_DELETE_STACK_REQUEST = 23;
  const INVALID_NOTIFICATION = 24;
  const INVALID_SIGNATURE = 25;
  const INVALID_SUBSCRIPTION = 26;
  const INVALID_DELETE_SUBSCRIPTION = 27;
  const INVALID_GOALS = 28;
  const USER_API_ID_NOT_FOUND = 29;
  const INVALID_CONF = 30;
  const NOT_CREATED_DATE_API = 31;

  /**
   * CAPITiersException constructor.
   *
   * @param int    $id  of exception
   * @param String $msg optionnal msg
   */
  public function __construct($id, $msg = "") {
    $message = CAppUI::tr("CAPITiersException-" . $id, $msg);
    parent::__construct($message, $id);
  }
}
