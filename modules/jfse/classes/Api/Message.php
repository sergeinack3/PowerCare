<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Api;

use Ox\Core\CMbArray;

final class Message
{
    /** @var string */
    protected $id;

    /** @var int The severity level of the message */
    protected $level;

    /** @var string */
    protected $description;

    /** @var string The medical act concerned by the message */
    protected $concerned_act;

    /** @var int */
    protected $source;

    /** @var string */
    protected $source_library;

    /** @var string */
    protected $type_id;

    /** @var bool */
    protected $validation_message;

    /** @var int */
    protected $rule;

    /** @var string */
    protected $rule_id;

    /** @var bool */
    protected $breakable_rule;

    /** @var string */
    protected $rule_serial_id;

    /** @var string */
    protected $diagnosis_code;

    /** @var string */
    protected $diagnosis_module;

    /** @var int */
    protected $diagnosis_level;

    /** @var string */
    protected $type;

    public const ERROR   = 0;
    public const WARNING = 1;
    public const INFO    = 2;

    public const RULE_NONE = 0;
    public const RULE_STD  = 1;
    public const RULE_CC   = 2;
    public const RULE_DIAG = 3;

    public function __construct(
        string $id,
        int $level,
        string $description,
        ?string $concerned_act = null,
        ?int $source = null,
        ?string $source_library = null,
        ?string $type_id = null,
        bool $validation_message = false,
        ?int $rule = 0,
        ?string $rule_id = null,
        bool $breakable_rule = false,
        ?string $rule_serial_id = null,
        ?string $diagnosis_code = null,
        ?string $diagnosis_module = null,
        ?int $diagnosis_level = 0,
        string $type = 'error'
    ) {
        $this->id                 = $id;
        $this->level              = $level;
        $this->description        = $description;
        $this->concerned_act      = $concerned_act;
        $this->source             = $source;
        $this->source_library     = $source_library;
        $this->type_id            = $type_id;
        $this->validation_message = $validation_message;
        $this->rule               = $rule;
        $this->rule_id            = $rule_id;
        $this->breakable_rule     = $breakable_rule;
        $this->rule_serial_id     = $rule_serial_id;
        $this->diagnosis_code     = $diagnosis_code;
        $this->diagnosis_module   = $diagnosis_module;
        $this->diagnosis_level    = $diagnosis_level;
        $this->type               = $type;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getConcernedAct(): ?string
    {
        return $this->concerned_act;
    }

    /**
     * @return int
     */
    public function getSource(): ?int
    {
        return $this->source;
    }

    /**
     * @return string
     */
    public function getSourceLibrary(): ?string
    {
        return $this->source_library;
    }

    /**
     * @return string
     */
    public function getTypeId(): ?string
    {
        return $this->type_id;
    }

    /**
     * @return bool
     */
    public function getValidationMessage(): bool
    {
        return $this->validation_message;
    }

    /**
     * @return int
     */
    public function getRule(): int
    {
        return $this->rule;
    }

    /**
     * @return string
     */
    public function getRuleId(): ?string
    {
        return $this->rule_id;
    }

    /**
     * @return bool
     */
    public function getBreakableRule(): bool
    {
        return $this->breakable_rule;
    }

    /**
     * @return string
     */
    public function getRuleSerialId(): ?string
    {
        return $this->rule_serial_id;
    }

    /**
     * @return string
     */
    public function getDiagnosisCode(): ?string
    {
        return $this->diagnosis_code;
    }

    /**
     * @return string
     */
    public function getDiagnosisModule(): ?string
    {
        return $this->diagnosis_module;
    }

    /**
     * @return int
     */
    public function getDiagnosisLevel(): int
    {
        return $this->diagnosis_level;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType(string $type): self
    {
        if (in_array($type, ['error', 'warning', 'info'])) {
            $this->type = $type;
        }

        return $this;
    }

    /**
     * Creates an instance of a Message from the given array
     *
     * @param array $data The data
     *
     * @return static
     */
    public static function map(array $data): self
    {
        return new self(
            CMbArray::get($data, 'id'),
            CMbArray::get($data, 'level'),
            CMbArray::get($data, 'description'),
            CMbArray::get($data, 'prestationsConcernees'),
            CMbArray::get($data, 'source'),
            CMbArray::get($data, 'libSource'),
            CMbArray::get($data, 'idGenre'),
            CMbArray::get($data, 'messageValidation'),
            CMbArray::get($data, 'regle'),
            CMbArray::get($data, 'regleId'),
            CMbArray::get($data, 'regleForcable'),
            CMbArray::get($data, 'regleSerialId'),
            CMbArray::get($data, 'codeDiagn'),
            CMbArray::get($data, 'moduleDiagn'),
            CMbArray::get($data, 'niveauDiagn')
        );
    }
}
