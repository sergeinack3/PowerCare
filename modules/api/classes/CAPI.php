<?php
/**
 * @package Mediboard\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 */

namespace Ox\Api;

use Exception;
use Ox\AppFine\Server\Exception\CAppFineException;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Chronometer;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\System\CExchangeHTTP;

/**
 * Class CAPI
 *
 * @package Ox\Api
 */
abstract class CAPI implements IShortNameAutoloadable {
  public $_parameters = [];

  public $_data = [];

  public $_options = [];

  public $_call_id = null;
  public $_parent_call_id = null;

  public $_temp_id = null;

  public $_api_version = null;

  /** @var CExchangeHTTP */
  protected $http_exchange;

  /** @var Chronometer */
  protected $http_exchange_chrono;

  protected $_api_response = [];

  /**
   * CAPI constructor.
   *
   * @param array   $post        = []   Query post value
   * @param integer $api_version = null
   *
   * @throws Exception
   */
  function __construct($post = [], $api_version = null) {
    $this->setCallID($post)->setVersion($api_version)->setOptions($post)->setParameters($post)->setData($post)->init();
  }

  /**
   * API factory
   *
   * @param array $post Post params
   *
   * @return CAPI
   * @throws CMbException
   * @throws Exception
   */
  static public function getAPIObject($post = []) {
    if (!isset($post['command']) || !$post['command']) {
      throw new CMbException('CAPI-error-Command not provided');
    }

    $api_class = static::getAPIClass($post['command']);
    if (!$api_class || !in_array($api_class, static::getAPIClasses())) {
      throw new CMbException('CAPI-error-Command not found');
    }

    return new $api_class($post);
  }

  /**
   * Get Api Class name from
   *
   * @param array $command Command name
   *
   * @return string
   */
  static public function getAPIClass($command) {
    return null;
  }

  /**
   * Get implemented compliant classes
   *
   * @return array
   * @throws Exception
   */
  static public function getAPIClasses() {
    return CApp::getChildClasses(get_called_class());
  }


  /**
   * Check perm
   *
   * @return void
   */
  static function checkPerm() {
    CCanDo::checkRead();
  }

  /**
   * @param array $stack Parent query stack
   *
   * @return void
   */
  public function setParentStack($stack = []) {
    foreach ($stack as $_temp_id => $_id) {
      foreach ($this->_parameters as $_parameter => $_value) {
        if ($_value !== "@{$_temp_id}") {
          continue;
        }

        $this->_parameters[$_parameter] = $_id;
      }

      foreach ($this->_data as $_parameter => $_value) {
        if ($_value !== "@{$_temp_id}") {
          continue;
        }

        $this->_data[$_parameter] = $_id;
      }
    }
  }

  /**
   * @param integer $version Api version
   *
   * @return $this
   */
  private function setVersion($version) {
    $this->_api_version = $version;

    return $this;
  }

  /**
   * @param array $post Post value
   *
   * @return $this
   */
  private function setCallID($post = []) {
    $this->_call_id        = CValue::read($post, 'call_id', null);
    $this->_parent_call_id = CValue::read($post, 'parent_call_id', null);

    return $this;
  }

  /**
   * @param array $post Post value
   *
   * @return $this
   */
  private function setParameters($post = []) {
    $this->_parameters = CValue::read($post, 'parameters', []);

    if (isset($this->_parameters['temp_id']) && $this->_parameters['temp_id']) {
      $this->setTempID($this->_parameters['temp_id']);
    }

    return $this;
  }

  /**
   * @param integer $id Identifiant
   *
   * @return $this
   */
  private function setTempID($id) {
    $this->_temp_id = $id;

    return $this;
  }

  /**
   * @return null
   */
  public function getTempID() {
    return $this->_temp_id;
  }

  /**
   * @param array $post = []
   *
   * @return $this
   */
  private function setOptions($post = []) {
    $this->_options = array_merge($this->_options, CValue::read($post, 'options', []));

    return $this;
  }

  /**
   * @param array $post = []
   *
   * @return $this
   */
  private function setData($post = []) {
    $this->_data = CValue::read($post, 'data', []);

    return $this;
  }

  /**
   * On init
   *
   * @return void
   * @throws Exception
   */
  private function init() {
    $this->initExchange();
  }

  /**
   * On init Exchange
   *
   * @return void
   * @throws Exception
   */
  private function initExchange() {
    $http_exchange                = new CExchangeHTTP();
    $http_exchange->date_echange  = CMbDT::dateTime();
    $http_exchange->emetteur      = CUser::get()->user_username;
    $http_exchange->destinataire  = CAppUI::conf('mb_id');
    $http_exchange->function_name = "mb sync - " . $this->getAPIName();
    $http_exchange->input         = serialize(
      [
        'API_VERSION' => $this->_api_version,
        'OPTIONS'     => $this->_options,
        'PARAMETERS'  => $this->_parameters,
        'DATA'        => $this->_data,
        ]
    );

    $http_exchange->store();

    CApp::$chrono->stop();

    $http_exchange_chrono = new Chronometer();
    $http_exchange_chrono->start();

    $this->http_exchange_chrono = $http_exchange_chrono;
    $this->http_exchange        = $http_exchange;
  }

  /**
   * @return string
   */
  public function getAPIName() {
    return (strtolower(preg_replace('/([^A-Z])([A-Z])/', "$1_$2", preg_replace('/CAPI/i', '', get_called_class()))));
  }

  /**
   * @param string $option  = ''
   * @param mixed  $default = null
   *
   * @return mixed
   */
  function getOption($option, $default = null) {
    return CValue::read($this->_options, $option, $default);
  }

  /**
   * @param mixed $parameter Parameters
   * @param mixed $default   = null
   *
   * @return mixed
   */
  function getParameter($parameter, $default = null) {
    return CValue::read($this->_parameters, $parameter, $default);
  }

  /**
   * @return mixed
   */
  function getAPIVersion() {
    return $this->getOption('api_version');
  }

  /**
   * @return array
   */
  public function getAPIResponse() {
    return $this->_api_response;
  }

  /**
   * Run the API call
   *
   * @throws Exception
   * @return void|integer
   */
  public function run() {
    throw new CMbException('CAPI-error-Run method must be implemented');
  }

  /**
   * Run the API call
   *
   * @throws CAppFineException
   * @return void|integer
   */
  public function runNew() {
    throw new CAppFineException(0, 500, 'CAPI-error-Run method must be implemented');
  }

  /**
   * @param integer $code = 200
   *
   * @return array
   * @throws Exception
   */
  protected function stop($code = 200) {
    $this->_api_response = array_merge(
      [
        'call_id' => $this->_call_id,
        'code'    => $code,
      ],
      $this->_api_response
    );

    $this->traceCall($code);

    return $this->_api_response;
  }

  /**
   * @param integer $code = 200
   *
   * @return void
   * @throws Exception
   */
  private function traceCall($code = 200) {
    $this->http_exchange_chrono->stop();
    CApp::$chrono->start();

    $http_exchange = $this->http_exchange;

    if ($http_exchange && $http_exchange->_id) {
      $http_exchange->response_time = $this->http_exchange_chrono->total;
      $http_exchange->http_fault    = (CSync::getResponseStatus($code) == 'error') ? '1' : '0';
      $http_exchange->output        = serialize(CMbArray::toJSON($this->_api_response, true));
      $http_exchange->store();
    }
  }

  /**
   * @param string $msg  = ''
   * @param int    $code = 0
   *
   * @return Exception
   * @throws Exception
   */
  public function genericException($msg = '', $code = 0) {
    $this->stop($code);

    return new Exception($msg, $code);
  }

  /**
   * @param mixed null $_ = null
   *
   * @return CAPIInvalidParameterException
   * @throws Exception
   */
  public function invalidParameterError($_ = null) {
    $this->stop(412);

    return new CAPIInvalidParameterException('common-error-Invalid parameter: %s', 412, $_);
  }

  /**
   * @param mixed $_ = null
   *
   * @return CAPIMissingParameterException
   * @throws Exception
   */
  public function missingParameterError($_ = null) {
    $this->stop(412);

    return new CAPIMissingParameterException('common-error-Missing parameter: %s', 412, $_);
  }

  /**
   * @param mixed $_ = null
   *
   * @return CAPINoPermissionException
   * @throws Exception
   */
  public function noPermissionError($_ = null) {
    $this->stop(410);

    return new CAPINoPermissionException('common-error-No permission', 410, $_);
  }

  /**
   * @param mixed $_ = null
   *
   * @return CAPIObjectNotFoundException
   * @throws Exception
   */
  public function objectNotFoundError($_ = null) {
    $this->stop(404);

    return new CAPIObjectNotFoundException('common-error-Object not found', 404, $_);
  }
}
