<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;

/**
 * Class CHL7v2TableEntry
 * HL7 Table Entry
 */
class CHL7v2TableEntry extends CHL7v2TableObject
{
    // DB Table key
    public $table_entry_id;

    public $number;

    public $code_hl7_from;
    public $code_hl7_to;

    public $code_mb_from;
    public $code_mb_to;

    public $description;
    public $user;

    public $codesystem_id;

    /**
     * Get table values
     *
     * @param string $table   Table HL7
     * @param bool   $from_mb true
     *
     * @return array The table
     */
    static function getTable($table, $from_mb = true)
    {
        return CHL7v2::getTable($table, $from_mb);
    }

    /**
     * Get HL7 value
     *
     * @param string $table         Table HL7
     * @param string $mbValue       Mediboard value
     * @param string $default_value Default value if not found in the HL table
     *
     * @return string
     */
    static function mapTo(string $table, ?string $mbValue = null, ?string $default_value = null)
    {
        if (!$mbValue) {
            return $default_value;
        }

        return CHL7v2::getTableHL7Value($table, $mbValue, $default_value);
    }

    /**
     * Get Mediboard value
     *
     * @param string $table    Table HL7
     * @param string $hl7Value HL7 value
     *
     * @return string
     */
    static function mapFrom($table, $hl7Value)
    {
        if ($value = CHL7v2::getTableMbValue($table, $hl7Value)) {
            return $value;
        }

        return null;
    }

    /**
     * Get table description
     *
     * @param string $table    Table HL7
     * @param string $hl7Value HL7 value
     *
     * @return string
     */
    static function getDescription($table, $hl7Value)
    {
        if ($value = CHL7v2::getTableDescription($table, $hl7Value)) {
            return $value;
        }

        return null;
    }

    /**
     * @see parent::getSpec()
     */
    function getSpec()
    {
        $spec                             = parent::getSpec();
        $spec->table                      = "table_entry";
        $spec->key                        = "table_entry_id";
        $spec->uniques["number_code_hl7"] = ["number", "code_hl7_from", "code_mb_from"];

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    function getProps()
    {
        $props                  = parent::getProps();
        $props["number"]        = "num notNull maxLength|5 seekable";
        $props["code_hl7_from"] = "str maxLength|30 protected";
        $props["code_hl7_to"]   = "str maxLength|30 protected";
        $props["code_mb_from"]  = "str maxLength|30 protected";
        $props["code_mb_to"]    = "str maxLength|30 protected";
        $props["description"]   = "str seekable";
        $props["user"]          = "bool notNull default|0";
        $props["codesystem_id"] = "str maxLength|80";

        return $props;
    }

    /**
     * @see parent::updateFormFields()
     */
    function updateFormFields()
    {
        parent::updateFormFields();

        $this->_view      = $this->description;
        $this->_shortview = $this->number;
    }
}
