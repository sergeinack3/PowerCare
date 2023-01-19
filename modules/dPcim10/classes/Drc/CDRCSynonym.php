<?php

/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cim10\Drc;

use Ox\Core\CRequest;

/**
 * Represents a synonym of a consultation result
 */
class CDRCSynonym extends CDRC
{
    /** @var int The id of the synonym */
    public $synonym_id;

    /** @var int The id of the result */
    public $result_id;

    /** @var string The synonym */
    public $libelle;

    /**
     * CDRC constructor.
     *
     * @param int $synonym_id The synonym id
     * @param int $load       The desired loading level
     */
    public function __construct($synonym_id = null, $load = CDRC::NONE)
    {
        parent::__construct();

        $this->synonym_id = $synonym_id;

        switch ($load) {
            case CDRC::LOAD_LITE:
            case CDRC::LOAD_FULL:
                $this->load();
                break;
            default:
        }
    }

    /**
     * Load the data from the database
     *
     * @return void
     */
    public function load()
    {
        if ($this->synonym_id && self::exists($this->synonym_id)) {
            /* Base data loading */
            $query = new CRequest();
            $query->addTable('synonyms');
            $query->addSelect('*');
            $query->addWhereClause('synonym_id', "= {$this->synonym_id}");

            $result = self::$source->exec($query->makeSelect());
            if ($result) {
                $this->map(self::$source->fetchAssoc($result));
            }
        }
    }

    /**
     * Check if the synonym with the given id exists
     *
     * @param integer $synonym_id The synonym id
     *
     * @return bool
     */
    public static function exists($synonym_id)
    {
        self::getDatasource();
        $synonym_id = intval($synonym_id);
        $exists     = false;

        $query = new CRequest();
        $query->addTable('synonyms');
        $query->addSelect('COUNT(`synonym_id`) AS total');
        $query->addWhereClause('synonym_id', "= {$synonym_id}");
        $result = self::$source->exec($query->makeSelect());

        if ($result) {
            $row = self::$source->fetchAssoc($result);

            if ($row['total'] !== 0) {
                $exists = true;
            }
        }

        return $exists;
    }
}
