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
abstract class AbstractOxPivotImportableObject
{
    public const FIELD_ID = 'id';

    protected const FILE_NAME = null;

    /** @var array */
    protected $importable_fields = [];

    abstract protected function initFields(): void;

    final public function __construct()
    {
        $this->initFields();
    }

    public function getFileName(): string
    {
        return static::FILE_NAME;
    }

    public function getFields(): array
    {
        return array_keys($this->importable_fields);
    }

    public function getFieldInfos(string $field): ?FieldDescription
    {
        return $this->importable_fields[$field] ?? null;
    }

    public function getImportableFields(): array
    {
        return $this->importable_fields;
    }

    public function getAdditionnalInfos(): array
    {
        return [];
    }

    protected function buildFieldId(string $description): FieldDescription
    {
        return new FieldDescription(
            'id',
            80,
            FieldDescription::FIELD_TYPE_STRING,
            $description,
            true
        );
    }

    protected function buildFieldExternalId(string $field_name, string $description, bool $mandatory = false): FieldDescription
    {
        return new FieldDescription(
            $field_name,
            80,
            FieldDescription::FIELD_TYPE_STRING,
            $description,
            $mandatory
        );
    }

    protected function buildFieldDate(string $nom, string $description, bool $mandatory = false): FieldDescription
    {
        return new FieldDescription(
            $nom,
            10,
            FieldDescription::FIELD_TYPE_DATE,
            $description,
            $mandatory
        );
    }

    protected function buildFieldTime(string $nom, string $description, bool $mandatory = false): FieldDescription
    {
        return new FieldDescription(
            $nom,
            8,
            FieldDescription::FIELD_TYPE_TIME,
            $description,
            $mandatory
        );
    }

    protected function buildFieldLongText(string $nom, string $description, bool $mandatory = false): FieldDescription
    {
        return new FieldDescription(
            $nom,
            65535,
            FieldDescription::FIELD_TYPE_STRING,
            $description,
            $mandatory
        );
    }
}
