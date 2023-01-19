<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Exception;
use Ox\Core\Cache;
use Ox\Core\CMbObject;
use Ox\Core\CSQLDataSource;

/**
 * Error log data
 */
class CErrorLogData extends CMbObject
{
    public $error_log_data_id;

    public $value;
    public $value_hash;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec           = parent::getSpec();
        $spec->table    = "error_log_data";
        $spec->key      = "error_log_data_id";
        $spec->loggable = false;

        $spec->uniques['value_hash'] = ['value_hash'];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props               = parent::getProps();
        $props["value"]      = "text notNull fieldset|default";
        $props["value_hash"] = "str notNull";

        return $props;
    }

    /**
     * Stores non existing error log data, and gets an data identifier in all case.
     *
     * @param array $value Array containing error log data
     *
     * @return int data id
     * @throws Exception
     */
    static function insert($value)
    {
        $value_hash = md5($value);

        // Cache data id
        $cache = new Cache('CErrorLogData.insert', $value_hash, Cache::INNER);
        if ($value_id = $cache->get()) {
            return $value_id;
        }

        // Don't use CSQLDataSource::get() to prevent error log enslaving
        $ds = @CSQLDataSource::$dataSources["std"];
        if (!$ds) {
            throw new Exception("No datasource available");
        }

        $query = "INSERT INTO `error_log_data` (`value`, `value_hash`)
    VALUES (?1, ?2)
    ON DUPLICATE KEY UPDATE `error_log_data_id` = LAST_INSERT_ID(`error_log_data_id`)";

        $query = $ds->prepare($query, $value, $value_hash);

        if (!@$ds->exec($query)) {
            throw new Exception("Exec failed");
        }

        return $cache->put($ds->insertId());
    }
}
