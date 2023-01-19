<?php

/**
 * @package Mediboard\Core\Elastic
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Elastic;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Ox\Core\CAppUI;
use Ox\Core\CStoredObject;
use Ox\Core\Elastic\Exceptions\ElasticObjectException;

abstract class ElasticObject
{
    /** @var string */
    public const DATE_TIME_FORMAT = "Y-m-d\TH:i:s.u e (P)";

    protected const ELASTIC_DATE_TIME_FORMAT_WITHOUT_TIMEZONE = "Y-m-d\TH:i:s.u \Z \(\Z\)";
    protected const ELASTIC_DATE_TIME_FORMAT                  = "Y-m-d\TH:i:s.u";

    // TODO: ref with cache
    /** @var ElasticObjectSettings[] */
    private static $settings = [];
    /** @var ElasticObjectMappings[] */
    private static $mappings = [];
    /** @var array */
    protected $_refs = [];

    /** @var string id of the document in the elastic index */
    protected $id = "";
    /** @var array contains the highlighted fields with html highlight tags */
    protected $highlight = [];
    /** @var DateTimeImmutable */
    protected $date;
    /** @var string witch server produce the document */
    protected $server_name = "";

    public function __construct()
    {
        $this->date        = new DateTimeImmutable('now');
        $this->server_name = CAppUI::conf("mb_id") ?? "";
    }

    /**
     * Return the concerned settings for the ElasticObject witch call it
     *
     * @return ElasticObjectSettings
     */
    public function getSettings(): ElasticObjectSettings
    {
        $class_name = static::class;
        if (!array_key_exists($class_name, self::$settings)) {
            self::$settings[$class_name] = $this->setSettings();
        }

        return self::$settings[$class_name];
    }

    /**
     * This abstract method permits to define settings for each specific ElasticObject
     *
     * @return ElasticObjectSettings
     */
    abstract public function setSettings(): ElasticObjectSettings;


    /**
     * Return the concerned mappings for the ElasticObject witch call it
     *
     * @return ElasticObjectMappings
     */
    public function getMappings(): ElasticObjectMappings
    {
        $class_name = static::class;
        if (!array_key_exists($class_name, self::$mappings)) {
            self::$mappings[$class_name] = $this->setMappings();
        }

        return self::$mappings[$class_name];
    }

    /**
     * This abstract method permits to define mappings for each specific ElasticObject
     *
     * @return ElasticObjectMappings
     */
    abstract public function setMappings(): ElasticObjectMappings;

    /**
     * This method permits to create an ElasticObject from $data.
     *
     * @param array $data Elastic data arrange to match the specific object.
     *
     * @return static
     * @throws ElasticObjectException
     * @internal This array is produced by a Repository.
     */
    public function fromArray(array $data): self
    {
        $mappings = $this->getMappings();
        if (array_key_exists("id", $data)) {
            $this->id = $data["id"];
        }

        foreach ($mappings as $_field_name => $_field) {
            if (!array_key_exists($_field_name, $data)) {
                $this->{$_field_name} = null;
                continue;
            }

            if (
                array_key_exists("highlight", $data)
                && array_key_exists($_field_name, $data["highlight"])
                && array_key_exists(0, $data["highlight"][$_field_name])
            ) {
                $this->highlight[$_field_name] = utf8_decode($data["highlight"][$_field_name][0]);
            }

            if ($data[$_field_name] === null) {
                // In case of model modification after retrieving object from ES.
                if ($_field['notNull']) {
                    throw new ElasticObjectException('ElasticObject-error-Field [%s] must not be NULL.', $_field_name);
                }

                $this->{$_field_name} = null;
                continue;
            }

            $encoding = $_field["encoding"];
            if (!Encoding::isNoneOrUTF8($encoding)) {
                $data[$_field_name] = mb_convert_encoding(
                    $data[$_field_name],
                    $encoding,
                    Encoding::UTF_8
                );
                if ($data[$_field_name] === false) {
                    throw new ElasticObjectException(
                        'ElasticObject-error-Invalid encoding field called [%s] to [%s] from UTF-8',
                        $_field_name,
                        $encoding
                    );
                }
            }

            switch ($_field["type"]) {
                case ElasticObjectMappings::STRING_TYPE:
                    $value = (string)$data[$_field_name];
                    break;

                case ElasticObjectMappings::DATE_TYPE:
                    $_date = null;
                    $pattern = "/(?P<date>.*?)\s(?P<timezone>(?P<timezone_text>.*)(?P<timezone_num> \([+-]\d{2}:\d{2}\)))?/";
                    if (preg_match($pattern, $data[$_field_name], $matches)) {
                        $timezone = new DateTimeZone($matches["timezone_text"]);
                        $_date    = DateTimeImmutable::createFromFormat(
                            self::ELASTIC_DATE_TIME_FORMAT,
                            $matches["date"],
                            $timezone
                        );
                    }

                    if ($_date instanceof DateTimeInterface) {
                        $value = $_date;
                    } else {
                        throw new ElasticObjectException(
                            'ElasticObject-error-Invalid provided date in [%s]',
                            $_field_name
                        );
                    }
                    break;

                case ElasticObjectMappings::INT_TYPE:
                    $value = (int)$data[$_field_name];
                    break;

                case ElasticObjectMappings::FLOAT_TYPE:
                    $value = (float)$data[$_field_name];
                    break;

                case ElasticObjectMappings::ARRAY_TYPE:
                    $value = (array)$data[$_field_name];
                    break;

                default:
                    throw new ElasticObjectException("ElasticObject-error-Unknown Type for [%s]", $_field_name);
            }

            $this->{$_field_name} = $value;
        }

        return $this;
    }

    /**
     * Method used to send data to ElasticSearch.
     *
     * @return array
     * @throws ElasticObjectException
     */
    public function toArray(): array
    {
        $mappings = $this->getMappings();
        $data     = [];

        foreach ($mappings as $_field_name => $_field) {
            $value = $this->{$_field_name};

            if ($value === null) {
                if ($_field['notNull']) {
                    throw new ElasticObjectException('ElasticObject-error-Field [%s] must not be NULL.', $_field_name);
                }

                $data[$_field_name] = $value;
                continue;
            }

            $encoding = $_field["encoding"];
            if (!Encoding::isNoneOrUTF8($encoding)) {
                $value = mb_convert_encoding(
                    $value,
                    Encoding::UTF_8,
                    $encoding
                );
                if ($value === false) {
                    throw new ElasticObjectException(
                        'ElasticObject-error-Invalid encoding field called [%s] to UTF-8 from [%s]',
                        $_field_name,
                        $encoding
                    );
                }
            }

            switch ($_field["type"]) {
                case ElasticObjectMappings::STRING_TYPE:
                    $value = (string)$value;
                    break;

                case ElasticObjectMappings::DATE_TYPE:
                    if ($value instanceof DateTimeInterface) {
                        $value = $value->format(self::DATE_TIME_FORMAT);
                    } elseif (is_string($value)) {
                        throw new ElasticObjectException(
                            'ElasticObject-error-Invalid provided date in [%s]',
                            $_field_name
                        );
                    }
                    break;

                case ElasticObjectMappings::INT_TYPE:
                    $value = (int)$value;
                    break;

                case ElasticObjectMappings::FLOAT_TYPE:
                    $value = (float)$value;
                    break;

                case ElasticObjectMappings::ARRAY_TYPE:
                    $value = (array)$value;
                    break;

                default:
                    throw new ElasticObjectException("ElasticObject-error-Unknown Type for [%s]", $_field_name);
            }

            $data[$_field_name] = $value;
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @param DateTimeImmutable $date
     */
    public function setDate(DateTimeImmutable $date): void
    {
        $this->date = $date;
    }

    /**
     * @return array
     */
    public function getRefs(): array
    {
        return $this->_refs;
    }

    /**
     * Give the object if possessed
     *
     * @param string $ref
     *
     * @return CStoredObject
     * @throws ElasticObjectException
     */
    public function getRef(string $ref): CStoredObject
    {
        if ($this->hasRef($ref)) {
            return $this->_refs[$ref];
        }
        throw new ElasticObjectException("ElasticObject-error-ElasticObject has no ref called %s", $ref);
    }

    /**
     * @param string $ref
     *
     * @return bool
     */
    public function hasRef(string $ref): bool
    {
        return array_key_exists($ref, $this->_refs);
    }

    /**
     * Adds reference to himself
     *
     * @param CStoredObject $object
     * @param string        $ref
     *
     * @return CStoredObject
     */
    public function addRef(CStoredObject $object, string $ref, bool $can_replace = true): CStoredObject
    {
        if (isset($this->_refs[$ref]) && !$can_replace) {
            return $this->_refs[$ref];
        }
        $this->_refs[$ref] = $object;

        return $object;
    }

    /**
     * Return value of a referenced field
     *
     * @param string $ref
     *
     * @return null
     */
    public function getRefValue(string $ref)
    {
        if (array_key_exists($ref, $this->getMappings()->getReferences())) {
            return $this->{$ref};
        }

        return null;
    }

    /**
     * @return array
     */
    public function getHighlight(): array
    {
        return $this->highlight;
    }
}
