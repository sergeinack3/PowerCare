<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\System\CExchangeSource;

/**
 * Exchange Transport Layer class
 */
class CExchangeTransportLayer extends CMbObject {
  // DB Fields
  public $emetteur;
  public $destinataire;
  public $date_echange;
  public $response_datetime;
  public $function_name;
  public $input;
  public $output;
  public $purge;
  public $response_time;
  public $source_id;
  public $source_class;

  // Form fields
  public $_self_sender;
  public $_self_receiver;
  
  // Filter fields
  public $_date_min;
  public $_date_max;

  public $_count_exchanges;

  /** @var array */
  public $_mysql_infos;

  /** @var CExchangeSource */
  public $_ref_source;

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();
    $props["emetteur"]          = "str";
    $props["destinataire"]      = "str";
    $props["date_echange"]      = "dateTime notNull";
    $props["response_datetime"] = "dateTime";
    $props["function_name"]     = "str notNull";
    $props["input"]             = "php show|0";
    $props["output"]            = "php show|0";
    $props["purge"]             = "bool";
    $props["response_time"]     = "float";
    $props["source_id"]         = "ref class|CExchangeSource meta|source_class cascade";
    $props["source_class"]      = ""; // À redéfinir

    $props["_self_sender"]   = "bool";
    $props["_self_receiver"] = "bool";
    $props["_date_min"]      = "dateTime";
    $props["_date_max"]      = "dateTime";
    
    return $props;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();
    
    $this->_self_sender   = $this->emetteur     == CAppUI::conf('mb_id');
    $this->_self_receiver = $this->destinataire == CAppUI::conf('mb_id');
  }

  /**
   * @inheritdoc
   */
  function store() {
    /* Possible purge when creating a CExchangeTransportLayer */
    if (!$this->_id) {
      CApp::doProbably(CAppUI::conf('eai CExchangeTransportLayer purge_probability'), array($this, 'purgeAllSome'));

      // ms
      $this->response_time = ($this->response_time) ? $this->response_time * 1000 : $this->response_time;
    }

    return parent::store();
  }

  /**
   * Purge the CExchangeTransportLayer older than the configured threshold
   *
   * @return bool|resource|void
   */
  function purgeAllSome() {
    $this->purgeEmptySome();
    $this->purgeDeleteSome();
  }

  /**
   * Purge the CExchangeTransportLayer older than the configured threshold
   *
   * @return bool|resource|void
   */
  function purgeEmptySome() {
    $purge_empty_threshold = CAppUI::conf('eai CExchangeTransportLayer purge_empty_threshold');

    $date  = CMbDT::dateTime("- {$purge_empty_threshold} days");
    $limit = CAppUI::conf("eai CExchangeTransportLayer purge_probability") * 10;

    $where                 = array();
    $where["purge"]        = " = '0'";
    $where["date_echange"] = " < '$date'";

    $exchange_ids    = $this->loadIds($where, null, $limit);
    $in_exchange_ids = CSQLDataSource::prepareIn($exchange_ids);

    // Marquage des échanges
    $ds    = $this->getDS();
    $query = "UPDATE `{$this->_spec->table}` SET
                `input` = NULL,
                `output` = NULL,
                `purge` = '1'
              WHERE `{$this->_spec->key}` $in_exchange_ids";
    $ds->exec($query);
  }

  /**
   * Purge the CExchangeTransportLayer older than the configured threshold
   *
   * @return bool|resource|void
   */
  function purgeDeleteSome() {
    $purge_delete_threshold = CAppUI::conf('eai CExchangeTransportLayer purge_delete_threshold');

    $date  = CMbDT::dateTime("- {$purge_delete_threshold} days");
    $limit = CAppUI::conf("eai CExchangeTransportLayer purge_probability") * 10;

    $ds = $this->getDS();

    $query = new CRequest();
    $query->addTable($this->_spec->table);
    $query->addWhereClause("date_echange", "< '$date'");
    $query->addWhereClause("purge", "= '1'");
    $query->setLimit($limit);
    $ds->exec($query->makeDelete());
  }

  /**
   * Get child exchanges
   *
   * @param string $class Classname
   *
   * @return string[] Data format classes collection
   * @throws Exception
   */
  static function getAll($class = CExchangeTransportLayer::class) {
    return CApp::getChildClasses($class, true, true);
  }

  /**
   * Count exchanges
   *
   * @return int|void
   */
  function countExchangesTL() {
    // Total des échanges
    $this->_count_exchanges = $this->countList();
  }

  /**
   * Unserialize content
   *
   * @return void
   */
  function unserialize() {
    $this->input  = unserialize($this->input);
    $this->output = unserialize($this->output);
  }

  /**
   * Fill download exchange
   *
   * @return string Exchange
   */
  function fillDownloadExchange() {
    if ($this->purge) {
      return;
    }

    $this->unserialize();

    $input = print_r($this->input, true);

    return CAppUI::tr("{$this->_class}-input") . " : {$input} \n";
  }

  /**
   * Get mysql info
   *
   * @return array Infos
   */
  function getMysqlInfos() {
    $ds = $this->getDS();

    $db = CMbArray::get($ds->config, "dbname");

    $query = "SELECT  data_length + index_length AS 'size',
                     data_free
              FROM information_schema.TABLES
              WHERE table_schema = '$db'
              AND table_name = '{$this->_spec->table}';";

    $this->_mysql_infos = CMbArray::get($ds->loadList($query), 0);
  }

  /**
   * Load interop sender
   *
   * @return CExchangeSource
   */
  function loadRefSource(){
    return $this->_ref_source = $this->loadFwdRef("source_id", true);
  }

  /**
   * Sends exchange data via related exchange source
   *
   * @return void
   */
  function send() {
    return;
  }
}
