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
 * Represents the transcoding possibility between the DRC, the Cim10 and the CISP
 */
class CDRCTranscoding extends CDRC
{
    /** @var int The id of the transcoding */
    public $transcoding_id;

    /** @var int The id of the result */
    public $result_id;

    /** @var string The main Cim10 code */
    public $code_cim_1;

    /** @var string The title of the main Cim10 code */
    public $libelle_cim_1;

    /** @var string The secondary Cim10 code */
    public $code_cim_2;

    /** @var string The title of the secondary Cim10 code */
    public $libelle_cim_2;

    /** @var string The CISP code */
    public $code_cisp;

    /** @var string The title of the CISP code */
    public $libelle_cisp;

    /** @var string The subtitle of the link */
    public $subtitle;

    /** @var CDRCTranscodingCriterion[] The links to the result's criteria */
    public $_transcoding_criteria;

    /**
     * CDRC constructor.
     *
     * @param int $transcoding_id The transcoding id
     * @param int $load           The desired loading level
     */
    public function __construct($transcoding_id = null, $load = CDRC::NONE)
    {
        parent::__construct();

        $this->transcoding_id = $transcoding_id;

        switch ($load) {
            case CDRC::LOAD_LITE:
                $this->load();
                break;
            case CDRC::LOAD_FULL:
                $this->load();
                $this->loadReferences();
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
        if ($this->transcoding_id && self::exists($this->transcoding_id)) {
            /* Base data loading */
            $query = new CRequest();
            $query->addTable('transcodings');
            $query->addSelect('*');
            $query->addWhereClause('transcoding_id', "= {$this->transcoding_id}");

            $result = self::$source->exec($query->makeSelect());
            if ($result) {
                $this->map(self::$source->fetchAssoc($result));
            }
        }
    }

    /**
     * Load the foreign references
     *
     * @return void
     */
    public function loadReferences()
    {
        if ($this->transcoding_id) {
            $this->loadCriteria();
        }
    }

    /**
     * Load the transcoding criteria
     *
     * @return void
     */
    protected function loadCriteria()
    {
        $this->_transcoding_criteria = [];

        $query = new CRequest();
        $query->addTable('transcoding_criteria');
        $query->addSelect('*');
        $query->addWhereClause('transcoding_id', "= {$this->transcoding_id}");
        $query->addOrder('`transcoding_criterion_id` ASC');

        $result = self::$source->exec($query->makeSelect());
        if ($result) {
            while ($row = self::$source->fetchAssoc($result)) {
                $criterion = new CDRCTranscodingCriterion();
                $criterion->map($row);

                $this->_transcoding_criteria[] = $criterion;
            }
        }
    }

    /**
     * Check if the transcoding with the given id exists
     *
     * @param integer $transcoding_id The transcoding id
     *
     * @return bool
     */
    public static function exists($transcoding_id)
    {
        self::getDatasource();
        $transcoding_id = intval($transcoding_id);
        $exists         = false;

        $query = new CRequest();
        $query->addTable('transcodings');
        $query->addSelect('COUNT(`transcoding_id`) AS total');
        $query->addWhereClause('transcoding_id', "= {$transcoding_id}");
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
