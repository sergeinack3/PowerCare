<?php
/**
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Search;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CRequest;

/**
 * Description
 */
class CSearchHistory extends CMbObject {
  /**
   * @var integer Primary key
   */
  public $search_history_id;

  // DB fields
  public $entry;
  public $types;
  public $contexte;
  public $agregation;
  public $fuzzy;
  public $user_id;
  public $date;
  public $hits;

  /**
   * Initialize the class specifications
   *
   * @return CMbObjectSpec
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "search_history";
    $spec->key   = "search_history_id";

    return $spec;
  }


  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props               = parent::getProps();
    $props["entry"]      = "text seekable";
    $props["types"]      = "str maxLength|255";
    $props["contexte"]   = "enum list|" . implode("|", CSearch::$contextes) . " seekable";
    $props["user_id"]    = "ref class|CMediusers notNull back|search_history";
    $props["agregation"] = "enum list|0|1 default|0";
    $props["fuzzy"]      = "enum list|0|1 default|0";
    $props["date"]       = "dateTime notNull";
    $props["hits"]       = "num";

    return $props;
  }


  /**
   * @return bool|resource
   */
  static function purgeProbably() {
    $nbr_day = (int)CAppUI::conf('search history_purge_day');
    $limit   = 1000;

    $now      = CMbDT::dateTime();
    $to_purge = CMbDT::date("- {$nbr_day} DAY", $now) . ' 00:00:00';

    $history = new self();
    $ds      = $history->getDS();

    $where = array('date' => $ds->prepare('<= ?', $to_purge));

    $request = new CRequest();
    $request->addTable($history->_spec->table);
    $request->addWhere($where);
    $request->setLimit($limit);


    return $ds->exec($request->makeDelete());
  }


}
