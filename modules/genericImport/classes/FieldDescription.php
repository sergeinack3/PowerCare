<?php

/**
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport;

/**
 * Description
 */
class FieldDescription
{
    public const FIELD_TYPE_STRING    = 'Chaîne de caractères';
    public const FIELD_TYPE_DATE      = 'Date au format YYYY-MM-DD';
    public const FIELD_TYPE_DATE_TIME = 'Date et heure au format YYYY-MM-DD H:i:s';
    public const FIELD_TYPE_TIME      = 'Heure au format H:i:s';
    public const FIELD_TYPE_INT       = 'Nombre';
    public const FIELD_TYPE_FLOAT     = 'Nombre flottant';

    /** @var string */
    private $name;

    /** @var int */
    private $size;

    /** @var string */
    private $type;

    /** @var string */
    private $description;

    /** @var bool */
    private $mandatory;

    public function __construct(string $name, int $size, string $type, string $description, bool $mandatory = false)
    {
        $this->name        = $name;
        $this->size        = $size;
        $this->type        = $type;
        $this->description = $description;
        $this->mandatory   = $mandatory;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    public function isMandatory(): bool
    {
        return $this->mandatory;
    }
}
