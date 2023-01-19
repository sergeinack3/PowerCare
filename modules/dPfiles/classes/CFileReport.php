<?php

/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files;

use Ox\Core\CMbObject;

/**
 * CFile error report class in order to troubleshoot CFile issues
 */
class CFileReport extends CMbObject
{

    /**
     * @var integer Primary key
     */
    public $file_report_id;

    public $file_path;
    public $file_hash;
    public $object_class;
    public $object_id;
    public $file_size;
    public $file_unfound;
    public $db_unfound;
    public $size_mismatch;
    public $empty_file;
    public $date_mismatch;

    public static $error_types = [
        'db_unfound',
        'file_unfound',
        'date_mismatch',
        'size_mismatch',
    ];

    /** @var array $report The array containing all error types with error count */
    public $report;

    public $_error_count_by_class = [];
    public $_error_count_by_type  = [];

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec           = parent::getSpec();
        $spec->table    = "file_report";
        $spec->key      = "file_report_id";
        $spec->loggable = false;

        $spec->uniques['file_path'] = ['file_path'];

        return $spec;
    }

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props                  = parent::getProps();
        $props['file_path']     = 'str notNull';
        $props['file_hash']     = 'str notNull';
        $props['object_class']  = 'str';
        $props['object_id']     = 'num';
        $props['file_size']     = 'num default|0';
        $props['file_unfound']  = 'bool default|0 notNull';
        $props['db_unfound']    = 'bool default|0 notNull';
        $props['size_mismatch'] = 'bool default|0 notNull';
        $props['empty_file']    = 'bool default|0 notNull';
        $props['date_mismatch'] = 'bool default|0 notNull';

        return $props;
    }

    /**
     * Format report array in order to display it
     *
     * @param array $classes Classes array
     *
     * @return void
     */
    function formatReportArray($classes)
    {
        $this->report = array_fill_keys($classes, []);
        foreach ($this->report as $_class => $value) {
            foreach (self::$error_types as $_error_type) {
                $group      = ['object_class'];
                $error_list = $this->countMultipleList("$_error_type = '1'", null, $group, null, $group);
                foreach ($error_list as $_error) {
                    if ($_error['object_class'] == $_class) {
                        $this->report[$_class][$_error_type] = $_error['total'];
                    }
                }
            }
        }
    }

    /**
     * Compute error count by class and by type
     *
     * @param array $classes Classes array
     *
     * @return void
     */
    function getTotalErrorCount($classes)
    {
        $this->_error_count_by_class = array_fill_keys($classes, 0);
        $this->_error_count_by_type  = array_fill_keys(self::$error_types, 0);
        foreach ($this->report as $_class => $_errors) {
            foreach ($_errors as $_type => $_count) {
                $this->_error_count_by_class[$_class] += $_count;
                $this->_error_count_by_type[$_type]   += $_count;
            }
        }
    }
}
