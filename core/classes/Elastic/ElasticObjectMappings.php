<?php

/**
 * @package Mediboard\Core\Elastic
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Elastic;

use Countable;
use Iterator;
use Ox\Core\Elastic\Exceptions\ElasticMappingException;

/**
 * ElasticObjectMappings permits to store how each fields will be manipulated by elasticsearch
 */
class ElasticObjectMappings implements Countable, Iterator
{
    public const DATE_TYPE   = "date";
    public const STRING_TYPE = "string";
    public const INT_TYPE    = "int";
    public const FLOAT_TYPE  = "float";
    public const ARRAY_TYPE  = "array";

    private int   $position        = 0;
    private bool  $default_mapping = true;
    private array $fields          = [];
    private array $references      = [];

    public function __construct(bool $base_mappings = true)
    {
        if ($base_mappings) {
            $this->addDateField("date")
                ->addStringField("server_name", Encoding::UTF_8);
        }
    }


    /**
     * This is the main function to add a field
     *
     * @param string      $field_name
     * @param string      $type
     * @param Encoding    $encoding
     * @param bool        $notNull
     * @param bool        $isSeekable
     * @param string|null $reference
     *
     * @return ElasticObjectMappings
     * @throws ElasticMappingException
     */
    private function addField(
        string $field_name,
        string $type,
        string $encoding,
        bool $not_null = false,
        bool $is_seekable = false,
        string $reference = null
    ): self {
        if (!Encoding::isAValidEncoding($encoding)) {
            throw new ElasticMappingException(
                'ElasticObjectMappings-error-Encoding type must be a valid encoding (%s)',
                implode(", ", Encoding::ENCODINGS)
            );
        }

        $field = [
            "name"       => $field_name,
            "type"       => $type,
            "encoding"   => $encoding,
            "notNull"    => $not_null,
            "isSeekable" => $is_seekable,
            "reference"  => $reference,
        ];

        if ($reference) {
            $this->references[$field_name] = $reference;
        }
        $this->fields[] = $field;

        return $this;
    }

    /**
     * This is an helper to add a String field.
     *
     * @param string        $field_name
     * @param Encoding|null $encoding
     * @param bool          $not_null
     * @param bool          $is_seekable
     *
     * @return ElasticObjectMappings
     * @throws ElasticMappingException
     */
    public function addStringField(
        string $field_name,
        string $encoding = null,
        bool $not_null = false,
        bool $is_seekable = false
    ): self {
        if ($encoding && $encoding === Encoding::NONE) {
            throw new ElasticMappingException(
                'ElasticObjectMappings-error-Encoding type cannot be NONE in a string field [%s]',
                $field_name
            );
        }

        $this->addField($field_name, self::STRING_TYPE, $encoding ?? Encoding::UTF_8, $not_null, $is_seekable);

        return $this;
    }

    /**
     * This is an helper to add a Date field.
     *
     * @param string $field_name
     * @param bool   $not_null
     *
     * @return $this
     */
    public function addDateField(string $field_name, bool $not_null = false): self
    {
        $this->addField($field_name, self::DATE_TYPE, Encoding::NONE, $not_null);

        return $this;
    }

    /**
     * This is an helper to add an Int field.
     *
     * @param string $field_name
     * @param bool   $not_null
     *
     * @return $this
     */
    public function addIntField(string $field_name, bool $not_null = false): self
    {
        $this->addField($field_name, self::INT_TYPE, Encoding::NONE, $not_null);

        return $this;
    }

    /**
     * This is an helper to add a Float field.
     *
     * @param string $field_name
     * @param bool   $not_null
     *
     * @return $this
     */
    public function addFloatField(string $field_name, bool $not_null = false): self
    {
        $this->addField($field_name, self::FLOAT_TYPE, Encoding::NONE, $not_null);

        return $this;
    }

    /**
     * This is an helper to add a Array field.
     *
     * @param string $field_name
     * @param bool   $not_null
     *
     * @return $this
     */
    public function addArrayField(string $field_name, bool $not_null = false): self
    {
        $this->addField($field_name, self::ARRAY_TYPE, Encoding::NONE, $not_null);

        return $this;
    }

    /**
     * This is an helper to add a Reference field.
     *
     * @param string $field_name
     * @param string $ref
     * @param bool   $not_null
     *
     * @return $this
     */
    public function addRefField(string $field_name, string $ref, bool $not_null = false): self
    {
        $this->addField($field_name, self::INT_TYPE, Encoding::NONE, $not_null, false, $ref);

        return $this;
    }

    /**
     * @return bool
     */
    public function isDefaultMapping(): bool
    {
        return $this->default_mapping;
    }

    /**
     * @param bool $default_mapping
     *
     * @return ElasticObjectMappings
     */
    public function setDefaultMapping(bool $default_mapping): self
    {
        $this->default_mapping = $default_mapping;

        return $this;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return array
     */
    public function getReferences(): array
    {
        return $this->references;
    }

    /**
     * @param string|null $ref
     *
     * @return string|null
     */
    public function getReference(string $ref = null): ?string
    {
        return $this->references[$ref] ?? null;
    }

    /**
     * @return array
     */
    public function getDateFields(): array
    {
        return array_filter($this->fields, function ($v): bool {
            return $v["type"] === self::DATE_TYPE;
        });
    }

    /**
     * @return array
     */
    public function getStringFields(): array
    {
        return array_filter($this->fields, function ($v): bool {
            return $v["type"] === self::STRING_TYPE;
        });
    }

    public function count(): int
    {
        return count($this->fields);
    }

    public function current(): array
    {
        return $this->fields[$this->position];
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function key(): string
    {
        return $this->fields[$this->position]["name"];
    }

    public function valid(): bool
    {
        return isset($this->fields[$this->position]);
    }

    public function rewind(): void
    {
        $this->position = 0;
        $this->fields   = array_values($this->fields);
    }
}
