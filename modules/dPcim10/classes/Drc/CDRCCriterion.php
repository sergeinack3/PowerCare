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
 * Represents a selectable criterion of a result
 */
class CDRCCriterion extends CDRC
{
    /** @var int The id of the criteria */
    public $criterion_id;

    /** @var int The id of the Consultation Result */
    public $result_id;

    /** @var int The version number */
    public $version;

    /** @var int The display order of the criteria */
    public $order;

    /** @var int The id of the title element */
    public $title_id;

    /** @var string The title */
    public $title;

    /** @var int The id of the spacing element */
    public $spacing_id;

    /** @var int The spacing (AKA level of the criteria) */
    public $spacing;

    /** @var int The id of the ponderation element */
    public $ponderation_id;

    /** @var string The ponderation of the criteria (AKA mandatory, at least 1, 2, 3 or optional) */
    public $ponderation;

    /** @var string The libelle */
    public $libelle;

    /** @var integer The id of the parent criteria */
    public $parent_id;

    /** @var CDRCCriterion The parent criteria */
    public $parent;

    /** @var bool Indicate whether this result is valid (1) or removed (0) */
    public $validity;

    /** @var CDRCTranscodingCriterion The links between Cim10 codes and the criteria */
    public $_cim_links;

    /** @var CDRCCriterion[] The descendants criteria */
    public $_descendants;

    /**
     * CDRCConsultationResult constructor.
     *
     * @param int $criterion_id The criterion id
     * @param int $load         The desired loading level
     */
    public function __construct($criterion_id = null, $load = CDRC::NONE)
    {
        parent::__construct();

        $this->criterion_id = $criterion_id;

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
        if ($this->criterion_id && self::exists($this->criterion_id)) {
            /* Base data loading */
            $query = new CRequest();
            $query->addTable('criteria');
            $query->addSelect('*');
            $query->addWhereClause('criterion_id', "= {$this->criterion_id}");

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
        if ($this->criterion_id) {
            $this->loadTitle();
            $this->loadSpacing();
            $this->loadPonderation();
            $this->loadDescendants();
        }
    }

    /**
     * Load the title from the criteria_titles table
     *
     * @return void
     */
    protected function loadTitle()
    {
        if ($this->title_id) {
            $query = new CRequest();
            $query->addTable('criteria_titles');
            $query->addSelect('title');
            $query->addWhereClause('title_id', "= {$this->title_id}");

            $result = self::$source->exec($query->makeSelect());
            if ($row = self::$source->fetchAssoc($result)) {
                $this->title = $row['title'];
            }
        }
    }

    /**
     * Load the spacing from the spacings table
     *
     * @return void
     */
    protected function loadSpacing()
    {
        if ($this->spacing_id) {
            $query = new CRequest();
            $query->addTable('spacings');
            $query->addSelect('spaces');
            $query->addWhereClause('spacing_id', "= {$this->spacing_id}");

            $result = self::$source->exec($query->makeSelect());
            if ($row = self::$source->fetchAssoc($result)) {
                $this->spacing = $row['spaces'];
            }
        }
    }

    /**
     * Load the ponderation from the ponderations table
     *
     * @return void
     */
    protected function loadPonderation()
    {
        if ($this->ponderation_id) {
            $query = new CRequest();
            $query->addTable('ponderations');
            $query->addSelect('text');
            $query->addWhereClause('ponderation_id', "= {$this->ponderation_id}");

            $result = self::$source->exec($query->makeSelect());
            if ($row = self::$source->fetchAssoc($result)) {
                $this->ponderation = $row['text'];
            }
        }
    }

    /**
     * Load the descendants criteria
     *
     * @return void
     */
    protected function loadDescendants()
    {
        $this->_descendants = [];

        $query = new CRequest();
        $query->addTable('criteria');
        $query->addSelect('*');
        $query->addWhereClause('parent_id', "= {$this->criterion_id}");
        $query->addWhereClause('validity', "= '1'");
        $query->addOrder('`order` ASC');

        $result = self::$source->exec($query->makeSelect());
        if ($result) {
            while ($row = self::$source->fetchAssoc($result)) {
                $criterion = new CDRCCriterion();
                $criterion->map($row);
                $criterion->loadReferences();

                $this->_descendants[$row['order']] = $criterion;
            }
        }
    }

    /**
     * Check if the criterion with the given id exists
     *
     * @param integer $criterion_id The criterion id
     *
     * @return bool
     */
    public static function exists($criterion_id)
    {
        self::getDatasource();
        $criterion_id = intval($criterion_id);
        $exists       = false;

        $query = new CRequest();
        $query->addTable('criteria');
        $query->addSelect('COUNT(`criterion_id`) AS total');
        $query->addWhereClause('criterion_id', "= {$criterion_id}");
        $query->addWhereClause('validity', "= '1'");
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
