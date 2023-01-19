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
 * Represents the condition needed for apply a transcoding to a result on its criteria
 */
class CDRCTranscodingCriterion extends CDRC
{
    /** @var int The id */
    public $transcoding_criterion_id;

    /** @var int The transcoding item id */
    public $transcoding_id;

    /** @var int The criterion id */
    public $criterion_id;

    /** @var int The conditions in which the transcoding could be applied :
     *               (1 => The criterion must be present, 2 => the criterion must be absent) */
    public $condition;

    /**
     * CDRC constructor.
     *
     * @param int $transcoding_criterion_id The  id
     * @param int $load                     The desired loading level
     */
    public function __construct($transcoding_criterion_id = null, $load = CDRC::NONE)
    {
        parent::__construct();

        $this->transcoding_criterion_id = $transcoding_criterion_id;

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
        if ($this->transcoding_criterion_id && self::exists($this->transcoding_criterion_id)) {
            /* Base data loading */
            $query = new CRequest();
            $query->addTable('transcoding_criteria');
            $query->addSelect('*');
            $query->addWhereClause('transcoding_criterion_id', "= {$this->transcoding_criterion_id}");

            $result = self::$source->exec($query->makeSelect());
            if ($result) {
                $this->map(self::$source->fetchAssoc($result));
            }
        }
    }

    /**
     * Check if the  with the given id exists
     *
     * @param integer $transcoding_criterion_id The  id
     *
     * @return bool
     */
    public static function exists($transcoding_criterion_id)
    {
        self::getDatasource();
        $transcoding_criterion_id = intval($transcoding_criterion_id);
        $exists                   = false;

        $query = new CRequest();
        $query->addTable('transcoding_criteria');
        $query->addSelect('COUNT(`transcoding_criterion_id`) AS total');
        $query->addWhereClause('transcoding_criterion_id', "= {$transcoding_criterion_id}");
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
