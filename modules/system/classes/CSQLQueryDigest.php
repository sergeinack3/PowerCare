<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */
namespace Ox\Mediboard\System;
use Ox\Core\CShardedObject;

/**
 * The CAlert Class
 */
class CSQLQueryDigest extends CShardedObject {
  // Sharders
  public $hostname;
  public $threshold;

  // DB Fields
  public $checksum;
  public $sample;
  public $ts_min;
  public $ts_max;
  public $ts_cnt;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec           = parent::getSpec();
    $spec->dsn      = "cluster";
    $spec->table    = "query_digest";
    $spec->sharders = array("hostname", "threshold");
    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props  = parent::getProps();

    $props["hostname" ] = "str notNull";
    $props["threshold"] = "num notNull";

    $props["checksum"] = "num notNull";
    $props["sample"  ] = "dateTime notNull";
    $props["ts_min"  ] = "dateTime notNull";
    $props["ts_max"  ] = "dateTime notNull";
    $props["ts_count"] = "num notNull";

    return $props;
  }
}
