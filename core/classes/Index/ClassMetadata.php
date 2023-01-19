<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Index;

use Ox\Core\Locales\Translator;
use Ox\Mediboard\Search\IIndexableObject;

/**
 * Class representing the class metadata as object for building object cache indexer
 */
class ClassMetadata implements IIndexableObject
{
    private string $id;
    private string $shortname;
    private string $table_name;
    private string $module;

    private ?string $field_name = null;

    private string $class_translation;
    private string $module_translation;

    public function __construct(
        string $id,
        string $shortname,
        string $table_name,
        string $module,
        string $class_translation,
        string $module_translation,
        ?string $field_name = null
    ) {
        $this->id                 = $id;
        $this->shortname          = $shortname;
        $this->table_name         = $table_name;
        $this->module             = $module;
        $this->class_translation  = $class_translation;
        $this->module_translation = $module_translation;
        $this->field_name         = $field_name;
    }

    /**
     * Serialize array to object
     */
    public static function fromArray(array $data, Translator $translator): self
    {
        $module = $data['module'];

        return new self(
            $data['id'],
            $data['short_name'],
            $data['table'],
            $module,
            $translator->tr($data['short_name']),
            $translator->tr("module-$module-court"),
            $data['key'],
        );
    }

    /**
     * Return actual object to array
     */
    public function toArray(): array
    {
        return [
            "shortname"          => $this->getShortname(),
            "table_name"         => $this->getTableName(),
            "field_name"         => $this->getFieldName(),
            "module"             => $this->getModule(),
            "class_translation"  => $this->getClassTranslation(),
            "module_translation" => $this->getModuleTranslation(),
            "id"                 => $this->getId(),
        ];
    }

    /**
     * Return actual object to string
     */
    public function __toString(): string
    {
        return $this->getShortname()
            . " | " . $this->getTableName()
            . " | " . $this->getFieldName()
            . " | " . $this->getModule()
            . " | " . $this->getClassTranslation()
            . " | " . $this->getModuleTranslation();
    }

    /**
     * Transform a string to object
     */
    public static function fromString(string $id, string $data): self
    {
        [$sn, $table, $field, $module, $class_translation, $module_translation] = explode(' | ', $data, 6);

        return new self($id, $sn, $table, $module, $class_translation, $module_translation, $field);
    }

    /**
     * @inheritDoc
     */
    public function getIndexableData(): array
    {
        return [
            "title" => $this->getShortname(),
            "_id"   => $this->getId(),
            "body"  => $this->getIndexableBody((string)$this),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getIndexableBody($content): string
    {
        return $content;
    }

    /**
     * @inheritDoc
     */
    public function getIndexablePatient()
    {
        // Nothing to do for this class
    }

    /**
     * @inheritDoc
     */
    public function getIndexablePraticien()
    {
        // Nothing to do for this class
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getShortname(): string
    {
        return $this->shortname;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->table_name;
    }

    /**
     * @return null|string
     */
    public function getFieldName(): ?string
    {
        return $this->field_name;
    }

    /**
     * @return string
     */
    public function getModuleTranslation(): string
    {
        return $this->module_translation;
    }

    /**
     * @return string
     */
    public function getModule(): string
    {
        return $this->module;
    }

    /**
     * @return string
     */
    public function getClassTranslation(): string
    {
        return $this->class_translation;
    }
}
