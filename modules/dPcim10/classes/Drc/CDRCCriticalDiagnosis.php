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
 * Represents a critical diagnosis linked to a consultation result
 */
class CDRCCriticalDiagnosis extends CDRC
{
    /** @var int The id of the diagnosis */
    public $diagnosis_id;

    /** @var string The name of the diagnosis */
    public $libelle;

    /** @var int The critical level */
    public $criticality;

    /** @var int  The group */
    public $group;

    /**
     * CDRC constructor.
     *
     * @param int $diagnosis_id The diagnosis id
     * @param int $load         The desired loading level
     */
    public function __construct($diagnosis_id = null, $load = CDRC::NONE)
    {
        parent::__construct();

        $this->diagnosis_id = $diagnosis_id;

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
        if ($this->diagnosis_id && self::exists($this->diagnosis_id)) {
            /* Base data loading */
            $query = new CRequest();
            $query->addTable('critical_diagnoses');
            $query->addSelect('*');
            $query->addWhereClause('diagnosis_id', "= {$this->diagnosis_id}");

            $result = self::$source->exec($query->makeSelect());
            if ($result) {
                $this->map(self::$source->fetchAssoc($result));
            }
        }
    }

    /**
     * Check if the critical diagnosis with the given id exists
     *
     * @param integer $diagnosis_id The critical diagnosis id
     *
     * @return bool
     */
    public static function exists($diagnosis_id)
    {
        self::getDatasource();
        $diagnosis_id = intval($diagnosis_id);
        $exists       = false;

        $query = new CRequest();
        $query->addTable('critical_diagnoses');
        $query->addSelect('COUNT(`diagnosis_id`) AS total');
        $query->addWhereClause('diagnosis_id', "= {$diagnosis_id}");
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
