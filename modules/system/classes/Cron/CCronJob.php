<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Cron;

use Cron\CronExpression;
use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\CViewAccessToken;

/**
 * Class for manage the cronjob
 */
class CCronJob extends CMbObject {

  /** @var integer Primary key */
  public $cronjob_id;
  public $name;
  public $description;
  public $active;
  public $params;
  public $execution;
  public $servers_address;
  public $mode;
  public $token_id;

  public $_frequently;
  public $_second;
  public $_minute;
  public $_hour;
  public $_day;
  public $_month;
  public $_week;
  public $_user_id;

  public $_next_datetime = array();

  /** @var  CronExpression */
  public $_cron_expression;

  /** @var CViewAccessToken */
  public $_token;
  public $_generate_token = false;

  public $_url;
  public $_params;
  public $_servers = array();
  public $_lasts_status = [
    'ok' => 0,
    'ko' => 0,
  ];

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "cronjob";
    $spec->key   = "cronjob_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();

    $props["name"]            = "str notNull";
    $props["active"]          = "bool notNull default|1";
    $props["params"]          = "text";
    $props["description"]     = "text";
    $props["execution"]       = "str notNull";
    $props["servers_address"] = "str";
    $props["mode"]            = "enum list|acquire|lock default|lock";
    $props["token_id"]        = "ref class|CViewAccessToken nullify autocomplete|label back|jobs";

    $props["_frequently"] = "enum list|@yearly|@monthly|@weekly|@daily|@hourly";
    $props["_second"]     = "str";
    $props["_minute"]     = "str";
    $props["_hour"]       = "str";
    $props["_day"]        = "str";
    $props["_month"]      = "str";
    $props["_week"]       = "str";
    $props["_user_id"]         = "num";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();

    $this->_view = $this->name;

    if (strpos($this->execution, "@") === 0) {
      $this->_frequently = $this->execution;
    }
    else {
      [$this->_second, $this->_minute, $this->_hour, $this->_day, $this->_month, $this->_week] = explode(" ", $this->execution);
    }

    $params = strtr($this->params ?? "", array("\r\n" => "&", "\n" => "&", " " => ""));
    parse_str($params, $this->_params);

    if ($this->servers_address) {
      $this->_servers = explode("|", $this->servers_address);
    }
  }

  /**
   * @inheritdoc
   */
  function check() {
    if ($msg = $this->checkExecutionLine()) {
      return $msg;
    }

    return parent::check();
  }

  /**
   * @inheritdoc
   */
  function store() {
    $this->completeField('_second', '_minute', '_hour', '_day', '_month', '_week', 'params', 'token_id');

    if (!$this->params && !$this->token_id) {
      return 'CCronJob-error-Param or token mandatory';
    }

    $parts = array(
      $this->_second !== null && $this->_second !== "" ? $this->_second : "0",
      $this->_minute !== null && $this->_minute !== "" ? $this->_minute : "*",
      $this->_hour !== null && $this->_hour !== "" ? $this->_hour : "*",
      $this->_day !== null && $this->_day !== "" ? $this->_day : "*",
      $this->_month !== null && $this->_month !== "" ? $this->_month : "*",
      $this->_week !== null && $this->_week !== "" ? $this->_week : "*"
    );

    if ($this->_frequently) {
      $this->execution = $this->_frequently;
    }
    else {
      $this->execution = implode(" ", $parts);
    }

    if ($this->_generate_token) {
      $this->generateTokenFromParams();
    }

    return parent::store();
  }

  /**
   * Verification of the line cron
   *
   * @return String|null
   */
  function checkExecutionLine() {
    //Durée - aucune vérification à effectuer
    if (strpos($this->execution, "@") === 0) {
      return null;
    }

    $parts = explode(" ", $this->execution);
    if (count($parts) !== 6) {
      return "Longueur de l'expression incorrecte";
    }

    foreach ($parts as $_index => $_part) {
      $virgules = explode(",", $_part);
      foreach ($virgules as $_virgule) {
        $slashs = explode("/", $_virgule);
        $count  = count($slashs);
        if ($count > 2) {
          return "Plusieurs '/' détectées : $_part";
        }
        $left  = CMbArray::get($slashs, 0);
        $right = CMbArray::get($slashs, 1);
        if ($count == 2) {
          if (!$this->checkParts($_index, $right)) {
            return "Après '/' nombre obligatoire : $_part";
          }
        }

        $dashs = explode("-", $left);
        $count = count($dashs);
        if (count($dashs) > 2) {
          return "Plusieurs '-' détectées : $_part";
        }
        $left  = CMbArray::get($dashs, 0);
        $right = CMbArray::get($dashs, 1);
        if ($count == 2) {
          if (!$this->checkParts($_index, $right)) {
            return "Après '-' nombre obligatoire : $_part";
          }
          if ($right <= $left) {
            return "Borne collection incorrecte: $_part";
          }
        }

        if ($left !== "*" && !$this->checkParts($_index, $left)) {
          return "Nombre ou '*' erronée : $_part";
        }
      }
    }

    return null;
  }

  /**
   * Verify the part of the cron
   *
   * @param Integer $position part of the cron
   * @param String  $value    Value of the part
   *
   * @return bool
   */
  private function checkParts($position, $value) {
    $result = false;
    $regex  = "#^[0-9]{1,2}$#";
    $min    = 0;
    switch ($position) {
      //cas seconde
      case 0:
        //cas minute
      case 1:
        $max = 59;
        break;
      //cas heure
      case 2:
        $max = 23;
        break;
      //cas jour
      case 3:
        $min = 1;
        $max = 31;
        break;
      //cas mois
      case 4:
        $min = 1;
        $max = 12;
        break;
      //cas jour
      case 5:
        $max   = 7;
        $regex = "#^[0-7]{1}$#";
        break;
      default:
        return false;
    }

    if (preg_match($regex, $value)) {
      if ($value >= $min && $value <= $max) {
        $result = true;
      }
    }

    return $result;
  }

  /**
   * Get the n futur execution
   *
   * @param int $next Nombre iteration of the cron job in the futur
   *
   * @return null|String[]
   */
  function getNextDate($next = 5) {
    $next_datetime = array();
    if (!$this->_cron_expression) {
      $this->getCronExpression();
    }

    try {
      for ($i = 0; $i < $next; $i++) {
        $next_datetime[] = $this->_cron_expression->getNextRunDate("now", $i, true)->format('Y-m-d H:i:s');
      }
    }
    catch (Exception $e) {
      return null;
    }

    return $this->_next_datetime = $next_datetime;
  }

  /**
   * @return CronExpression
   */
  function getCronExpression() {
    if (strpos($this->execution, "@") === 0) {
      return $this->_cron_expression = CronExpression::factory($this->execution);
    }

    // Removing seconds expression feature which was hard-coded in precedent library
    $execution_wo_seconds = implode(' ', array($this->_minute, $this->_hour, $this->_day, $this->_month, $this->_week));

    return $this->_cron_expression = CronExpression::factory($execution_wo_seconds);
  }

  /**
   * Make the URL
   *
   * @param string $base Base url to query
   *
   * @return string
   * @throws Exception
   */
  function makeUrl($base) {
    $query = CMbString::toQuery($this->_params);

    // If token ignore params and use only token
    if ($this->token_id) {
      /** @var CViewAccessToken $token */
      $token = $this->loadFwdRef('token_id');

      if ($token && $token->_id) {
        $query = CMbString::toQuery(
          [
            'token'               => $token->hash,
            'execute_cron_log_id' => $this->_params['execute_cron_log_id'],
          ]
        );
      }
    }

    $url = "$base/?$query";

    return $this->_url = $url;
  }

  /**
   * @param bool $cached
   *
   * @return CStoredObject
   * @throws Exception
   */
  function loadRefToken($cached = true) {
    return $this->_token = $this->loadFwdRef('token_id', $cached);
  }

  /**
   * Generate a CViewAccessToken using the current user and $this->params.
   * If the token is successfully created link it in token_id and remove the params
   *
   * @return void
   * @throws Exception
   */
  private function generateTokenFromParams() {
    // Check if a token if already used in the params

    preg_match('/^token=(?<hash>\w+)$/', $this->params, $matches);
    if ($matches && isset($matches['hash'])) {
      $token = CViewAccessToken::getByHash($matches['hash']);

      // If token exists
      if ($token && $token->_id) {
        $this->token_id = $token->_id;
        $this->params   = '';

        return;
      }
    }

    // Create a new token
    $token             = new CViewAccessToken();
    $token->label      = $this->name;
    $token->params     = $this->params;
    $token->user_id    = $this->_user_id ?: CUser::get()->_id;
    $token->restricted = '1';
    $token->loadMatchingObjectEsc();

    $token->_hash_length = 10;

    if ($msg = $token->store()) {
      CAppUI::stepAjax($msg, UI_MSG_WARNING);

      return;
    }

    if ($token && $token->_id) {
      $this->token_id = $token->_id;
      $this->params   = '';
    }
  }

  /**
   * @param int $limit Number of logs to load
   *
   * @return void
   * @throws Exception
   */
  function loadLastsStatus($limit = 10) {
    $logs = $this->loadBackRefs('cron_logs', 'cronjob_log_id DESC', $limit);

    /** @var CCronJobLog $_log */
    foreach ($logs as $_log) {
      if ((((int) $_log->status) && $_log->status < 400) || ($_log->status == 'finished')) {
        $this->_lasts_status['ok']++;
      }
      else {
        $this->_lasts_status['ko']++;
      }
    }
  }
}
