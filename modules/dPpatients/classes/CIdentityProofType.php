<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Exception;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Description
 */
class CIdentityProofType extends CMbObject
{
    public const TRUST_LEVEL_HIGH         = '3';
    public const TRUST_LEVEL_MEDIUM       = '2';
    public const TRUST_LEVEL_LOW          = '1';
    public const PROOF_IDENTITY_NONE_CODE = 'ABS_INTEROP';

    public const PROOF_ACTE_NAISSANCE = "BIRTH_ACT";

    public const PROOF_CARTE_ID = "ID_CARD";

    public const PROOF_LIVRET_FAMILLE   = "FAMILY_RECORD_BOOK";

    public const PROOF_PASSEPORT        = "PASSEPORT";

    public const PROOF_PERMIT_RESIDENCE = "RESIDENT_PERMIT";

    /** @var int Primary key */
    public $identity_proof_type_id;
    /** @var string */
    public $label;
    /** @var string */
    public $code;
    /** @var string */
    public $trust_level;
    /** @var string */
    public $active;
    /** @var string */
    public $editable;
    /** @var string */
    public $validate_identity;
    /** @var int */
    public $group_id;

    /**
     * @return CIdentityProofType[]
     *
     * @throws Exception
     */
    public static function getEditableTypes(): array
    {
        $group = CGroups::get();

        $where = [
            'editable' => " = '1'",
            "`group_id` = {$group->_id} OR `group_id` IS NULL",
        ];

        return (new self())->loadList($where, 'trust_level DESC, label ASC') ?: [];
    }

    /**
     * @return CIdentityProofType[]
     *
     * @throws Exception
     */
    public static function getActiveTypes(): array
    {
        $group = CGroups::get();

        $where = [
            'active' => " = '1'",
            "`group_id` = {$group->_id} OR `group_id` IS NULL",
        ];

        return (new self())->loadList($where, 'trust_level DESC, editable ASC, label ASC') ?: [];
    }

    /**
     * @return CIdentityProofType[]
     *
     * @throws CMbException
     */
    public static function getActiveTypesByTrustLevel(string $trust_level): array
    {
        if (!in_array($trust_level, [self::TRUST_LEVEL_HIGH, self::TRUST_LEVEL_MEDIUM, self::TRUST_LEVEL_LOW])) {
            throw new CMbException('CIdentityProofType-error-invalid_trust_level');
        }

        $group = CGroups::get();
        $where = [
            'trust_level' => " = {$trust_level}",
            "`group_id` = {$group->_id} OR `group_id` IS NULL",
        ];

        return (new self())->loadList($where, 'trust_level DESC, editable ASC, label ASC') ?: [];
    }

    /**
     * Filter the types by code, label, trust level
     */
    public static function filter(
        string $code = null,
        string $label = null,
        string $trust_level = null,
        string $active = '1',
        int $start = 0
    ): array {
        return (new self())->loadList(
            self::getQuery($code, $label, $trust_level, $active),
            'trust_level DESC, editable ASC, label ASC',
            "{$start}, 20"
        ) ?: [];
    }

    private static function getQuery(
        string $code = null,
        string $label = null,
        string $trust_level = null,
        string $active = '1'
    ): array {
        $spec = (new self())->getSpec();
        $spec->init();

        $where = [];
        if ($code) {
            $where[] = $spec->ds->prepareLikeMulti($code, 'code');
        }

        if ($label) {
            $where[] = $spec->ds->prepareLikeMulti($label, 'label');
        }

        if ($trust_level && in_array($trust_level, self::getTrustLevels())) {
            $where[] = "`trust_level` = '{$trust_level}'";
        }

        if (is_string($active) && in_array($active, ['1', '0'])) {
            $where[] = "`active` = '{$active}'";
        }

        $where[] = "`group_id` IS NULL OR `group_id` = " . CGroups::get()->_id;

        return $where;
    }

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec = parent::getSpec();

        $spec->table = 'identity_proof_types';
        $spec->key   = 'identity_proof_type_id';

        return $spec;
    }

    public static function count(
        string $code = null,
        string $label = null,
        string $trust_level = null,
        string $active = '1'
    ): int {
        $count = (new self())->countList(self::getQuery($code, $label, $trust_level, $active));

        return !is_null($count) ? $count : 0;
    }

    /**
     * Get identity proof type
     *
     * @param string $code Identity proof type code
     *
     * @return self
     */
    public static function get(string $code): self
    {
        $identityProofType       = new self();
        $identityProofType->code = $code;
        $identityProofType->loadMatchingObject();

        return $identityProofType;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props                      = parent::getProps();
        $props['label']             = 'str notNull seekable';
        $props['code']              = 'str notNull seekable';
        $props['trust_level']       = 'enum list|' . implode('|', self::getTrustLevels()) . ' notNull';
        $props['active']            = 'bool default|1';
        $props['validate_identity'] = 'bool default|0';
        $props['editable']          = 'bool default|1';
        $props['group_id']          = 'ref class|CGroups back|identity_proof_types';

        return $props;
    }

    public static function getTrustLevels(): array
    {
        return [self::TRUST_LEVEL_HIGH, self::TRUST_LEVEL_MEDIUM, self::TRUST_LEVEL_LOW];
    }

    public function store(): ?string
    {
        if (!$this->_id && !$this->group_id) {
            $group          = CGroups::get();
            $this->group_id = $group->_id;
        }

        if (!$this->editable && $this->fieldModified('code')) {
            $this->code = $this->_old->code;
        }

        if ($this->trust_level !== self::TRUST_LEVEL_HIGH) {
            $this->validate_identity = '0';
        }

        return parent::store();
    }

    public function updateFormFields(): void
    {
        parent::updateFormFields();

        $this->_view = $this->label;
    }

    public function isEditable(): bool
    {
        return $this->editable === '1';
    }

    public function isActive(): bool
    {
        return $this->active === '1';
    }

    public function isValidate(): bool
    {
        return $this->validate_identity === '1';
    }
}
