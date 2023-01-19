<?php

/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cim10\Drc;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\Cache;
use Ox\Core\CSQLDataSource;

/**
 * The base class for the DRC base
 */
class CDRC implements IShortNameAutoloadable
{
    const NONE      = 0;
    const LOAD_LITE = 1;
    const LOAD_FULL = 2;

    /** @var int The cache layer */
    protected static $cache_layers = Cache::INNER_OUTER;

    /** @var CSQLDataSource The database source */
    protected static $source;

    /**
     * CDRC constructor.
     */
    public function __construct()
    {
        self::getDatasource();
    }

    /**
     * Map the data from the given array to the object
     *
     * @param array $data The data
     *
     * @return void
     */
    public function map($data = [])
    {
        foreach ($data as $field => $value) {
            if (property_exists($this, $field)) {
                $this->$field = $value;
            }
        }
    }

    /**
     * Load the datasource and returns it
     *
     * @return CSQLDataSource|null
     */
    protected static function getDatasource()
    {
        if (!self::$source) {
            self::$source = CSQLDataSource::get('drc', true);
        }

        return self::$source;
    }
}
