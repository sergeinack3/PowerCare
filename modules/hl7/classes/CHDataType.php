<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */


namespace Ox\Interop\Hl7;

use Ox\Core\CValue;

/**
 * H* message data type
 *
 * Reference : http://www.med.mun.ca/tedhoekman/medinfo/hl7/ch200020.htm
 *
 * DT  = Date       : YYYY[MM[DD]]
 * TM  = Time       : HH[MM[SS[.S[S[S[S]]]]]][+/-ZZZZ]
 * DTM = DateTime   : YYYY[MM[DD[HHMM[SS[.S[S[S[S]]]]]]]][+/-ZZZZ]
 * TS  = Time stamp : YYYY[MM[DD[HHMM[SS[.S[S[S[S]]]]]]]][+/-ZZZZ] ^ <degree of precision>
 *
 * From Messaging workbench <http://gforge.hl7.org/gf/project/mwb/>
 *
 * @todo Use these regexps
 *
 * DT=^\d{4}((0\d)|(1[0-2]))((([0-2]\d)|(3[0-1])))?$
 * ST=^((?>\w+)|(?>\s+)|([?[:punct:]]))*$
 * FT=^((?>\w+)|(?>\s+)|([?[:punct:]]))*$
 * TX=^((?>\w+)|(?>\s+)|([?[:punct:]]))*$
 * GTS=^[\x20-\x7e]{1,199}$
 * ID=^[\x20-\x7e]*$
 * IS=^[\x20-\x7e]{1,20}$
 * NM=^[+-]?\d*\.?\d*$
 * SI=^\d{1,4}$
 * TM=^([01]?\d|2[0-3])(([0-5]\d)?)(([0-5]\d)?((.\d{1,4})?)([+-]([0]\d|1[0-3])([0-5]\d)))?$
 * TN=^(\d\d )?(\(\d\d\d\))?\d\d\d-\d\d\d\d([X,x]\d{1,5})?([B,b]\d{1,5})?([C,c][\x20-\x7e]{0,199})?$
 * DTM=^\d{4}(((0[1-9])|(1[0-2]))(((0[1-9])|([1-2]\d)|(3[0-1]))((([01]\d|2[0-3])([0-5]\d))(([0-5]\d)((\.\d{1,4}))?)?)?)?)?([+-](([0]\d|1[0-3])([0-5]\d)))?$
 */
class CHDataType extends CHL7v2
{
    const RE_HL7_DATE = '(?P<year>\d{4})(?:(?P<month>0[1-9]|1[012])(?P<day>0[1-9]|[12]\d|3[01])?)?';
    const RE_HL7_TIME = '(?P<hour>[01]\d|2[0-3])(?:(?P<minute>[0-5]\d)(?:(?P<second>[0-5]\d)?(?:\.\d{1,4})?)?)?(?P<tz>[+-]\d{4})?';

    const RE_MB_DATE = '(?P<year>\d{4})-(?P<month>0\d|1[012])-(?P<day>0\d|[12]\d|3[01])';
    const RE_MB_TIME = '(?P<hour>[01]\d|2[0-3]):(?P<minute>[0-5]\d):(?P<second>[0-5]\d)';

    static $typesBase = [
        "Date",
        "DateTime",
        "Time",
        "Double",
        "Integer",
        "String",
    ];

    static $typesMap = [
        //"TimeStamp" => "DateTime",
        "DT"  => "Date",
        "DTM" => "DateTime",
        "GTS" => "String",
        "ID"  => "String",
        "IS"  => "String",
        "FT"  => "String",
        "NM"  => "Double",
        "SI"  => "String",
        "ST"  => "String",
        "TM"  => "Time",
        "TN"  => "String",
        //"TS"  => "DateTime",
        "TX"  => "String",
    ];

    static $re_hl7 = [];
    static $re_mb  = [];

    /** @var CHMessage */
    protected $message;
    protected $type;
    protected $version;
    protected $extension;

    /**
     * CHDataType constructor.
     *
     * @param CHMessage $message   H Message
     * @param string    $datatype  Datatype string
     * @param string    $version   Version string
     * @param string    $extension Extension
     */
    protected function __construct($message, $datatype, $version, $extension)
    {
        $this->message   = $message;
        $this->type      = (string)$datatype;
        $this->version   = $version;
        $this->extension = $extension;
    }

    /**
     * Initializes the data format patterns
     *
     * @return void
     */
    static function init()
    {
        self::$re_hl7 = [
            "Date"     => '/^' . self::RE_HL7_DATE . '$/',
            "DateTime" => '/^' . self::RE_HL7_DATE . '(?:' . self::RE_HL7_TIME . ')?$/',
            "Time"     => '/^' . self::RE_HL7_TIME . '$/',
            "Double"   => '/^[+-]?\d*\.?\d*$/',
            "Integer"  => '/^[+-]?\d+$/',
            "String"   => '/.*/',
        ];

        self::$re_mb = [
            "Date"     => '/^' . self::RE_MB_DATE . '$/',
            "DateTime" => '/^' . self::RE_MB_DATE . '(?:[ T]' . self::RE_MB_TIME . ')?$/',
            "Time"     => '/^' . self::RE_MB_TIME . '$/',
            "Double"   => self::$re_hl7["Double"],
            "Integer"  => self::$re_hl7["Integer"],
            "String"   => self::$re_hl7["String"],
        ];
    }

    /**
     * Return the human readable type
     * TODO Check if all these types will always be a direct match of base types
     *
     * @param string $type The 2 or 3 letters type
     *
     * @return string The human readable type
     */
    static function mapToBaseType($type)
    {
        return CValue::read(self::$typesMap, $type, $type);
    }

    /**
     * Convert value to MB value
     *
     * @param string      $value Value to convert
     * @param CHL7v2Field $field HL7 field
     *
     * @return bool
     */
    function toMB($value, CHL7v2Field $field)
    {
        if ($this->validate($value, $field)) {
            return $value;
        }

        return false;
    }

    /**
     * Checks whether a value is valid regarding its type
     *
     * @param string|array $value The value to check
     * @param CHL7v2Field  $field The field in which the value is
     *
     * @return boolean Is the value valid regarding its type
     */
    function validate($value, CHL7v2Field $field)
    {
        if (is_array($value)) {
            $count = count($value);

            if ($count === 1) {
                $value = $value[0];
            } elseif ($count === 0) {
                $value = "";
            }
        }

        $value = trim($value);
        if ($value === "") {
            return true;
        }

        $valid = preg_match($this->getRegExpHL7(), $value);
        if (!$valid) {
            $field->error(CHL7v2Exception::INVALID_DATA_FORMAT, "'$value' ($this->type)", $field);

            return false;
        }

        return true;
    }

    /**
     * Convert value to HL7 value
     *
     * @param string      $value Value to convert
     * @param CHL7v2Field $field HL7 field
     *
     * @return bool
     */
    function toHL7($value, CHL7v2Field $field)
    {
        return $value;
    }

    /**
     * @return CHL7v2DOMDocument|null
     */
    function getSpecs()
    {
        return $this->message->getSchema(self::PREFIX_COMPOSITE_NAME, $this->type);
    }

    /**
     * @inheritdoc
     */
    function getVersion()
    {
        return $this->version;
    }

    /**
     * Get datatype string
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Parses an HL7 value
     *
     * @param string      $value The HL7 value
     * @param CHL7v2Field $field The field containing the value
     *
     * @return array|false A structure containing the elements of the value
     */
    protected function parseHL7($value, CHL7v2Field $field)
    {
        if ($value === null) {
            return [];
        }

        if (!preg_match($this->getRegExpHL7(), $value, $matches)) {
            $field->error(CHL7v2Exception::INVALID_DATA_FORMAT, "'$value' ($this->type)", $field);

            return false;
        }

        return $matches;
    }

    /**
     * Get regexp to test if it's valid as a HL7 value
     *
     * @return string|null
     */
    protected function getRegExpHL7()
    {
        return self::$re_hl7[$this->type];
    }

    /**
     * Parses an MB value
     *
     * @param string      $value The MB value
     * @param CHL7v2Field $field The field containing the value
     *
     * @return array|false A structure containing the elements of the value
     */
    protected function parseMB($value, CHL7v2Field $field)
    {
        if ($value === null || $value === "") {
            return [];
        }

        if (!preg_match($this->getRegExpMB(), $value, $matches)) {
            $field->error(CHL7v2Exception::INVALID_DATA_FORMAT, "'$value' ($this->type)", $field);

            return false;
        }

        return $matches;
    }

    /**
     * Get regexp to test if it's valid as a Mediboard value
     *
     * @return string|null
     */
    protected function getRegExpMB()
    {
        return self::$re_mb[$this->type];
    }
}

CHDataType::init();
