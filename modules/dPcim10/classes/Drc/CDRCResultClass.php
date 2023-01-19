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
 * Represents a consultation result class, based on the Cim10 chapter
 */
class CDRCResultClass extends CDRC
{
    /** @var int The id of the Result class */
    public $class_id;

    /** @var string The full text of the chaptertitle */
    public $text;

    /** @var string The Cim10 chapter */
    public $chapter;

    /** @var string The title */
    public $libelle;

    /** @var string The first Cim10 code of the class */
    public $beginning;

    /** @var string The last Cim10 code of the class */
    public $end;

    /**
     * CDRCResultClass constructor.
     *
     * @param int $class_id The class id
     * @param int $load     The desired loading level
     */
    public function __construct($class_id = null, $load = CDRC::NONE)
    {
        parent::__construct();

        $this->class_id = $class_id;

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
        if ($this->class_id && self::exists($this->class_id)) {
            $query = new CRequest();
            $query->addTable('result_classes');
            $query->addSelect('*');
            $query->addWhereClause('class_id', "= {$this->class_id}");

            $result = self::$source->exec($query->makeSelect());
            if ($result) {
                $this->map(self::$source->fetchAssoc($result));
            }
        }
    }

    /**
     * Load all the classes
     *
     * @return CDRCResultClass[]
     */
    public static function getClasses()
    {
        self::getDatasource();
        $classes = [];

        $query = new CRequest();
        $query->addTable('result_classes');
        $query->addSelect('*');
        $query->addOrder('class_id ASC');

        $result = self::$source->exec($query->makeSelect());
        if ($result) {
            while ($row = self::$source->fetchAssoc($result)) {
                $class = new CDRCResultClass();
                $class->map($row);

                $classes[$class->class_id] = $class;
            }
        }

        return $classes;
    }

    /**
     * Check if the class with the given id exists
     *
     * @param integer $class_id The class id
     *
     * @return bool
     */
    public static function exists($class_id)
    {
        self::getDatasource();
        $class_id = intval($class_id);
        $exists   = false;

        $query = new CRequest();
        $query->addTable('result_classes');
        $query->addSelect('COUNT(`class_id`) AS total');
        $query->addWhereClause('class_id', "= {$class_id}");
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
