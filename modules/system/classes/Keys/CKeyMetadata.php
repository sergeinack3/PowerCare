<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Keys;

use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\Exceptions\CanNotMerge;
use Ox\Core\Exceptions\CouldNotMerge;
use Ox\Core\Security\Crypt\Alg;
use Ox\Core\Security\Crypt\Mode;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\System\CMergeLog;
use Ox\Mediboard\System\Keys\Exceptions\CouldNotUseKey;

/**
 * Encryption key metadata.
 */
class CKeyMetadata extends CMbObject
{
    public const NAME_MIN_LENGTH = 4;

    /** @var int Primary key */
    public $key_metadata_id;

    /** @var string */
    public $name;

    /** @var string */
    public $alg;

    /** @var string */
    public $mode;

    /** @var string Datetime of creation of the Key on the FS */
    public $creation_date;

    /** @var Alg */
    public $_alg;

    /** @var Mode */
    public $_mode;

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec = parent::getSpec();

        $spec->table           = 'key_metadata';
        $spec->key             = 'key_metadata_id';
        $spec->uniques['name'] = ['name'];
        $spec->anti_csrf       = true;

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props                  = parent::getProps();
        $props['name']          = 'str minLength|' . self::NAME_MIN_LENGTH . ' notNull';
        $props['alg']           = 'str notNull';
        $props['mode']          = 'str notNull';
        $props['creation_date'] = 'dateTime';

        return $props;
    }

    /**
     * @inheritDoc
     */
    public function updateFormFields(): void
    {
        parent::updateFormFields();

        $this->_view = $this->name;
        $this->_alg  = Alg::from($this->alg);
        $this->_mode = Mode::from($this->mode);
    }

    public function hasBeenPersisted(): bool
    {
        return ($this->_id !== null && $this->creation_date !== null);
    }

    /**
     * @param string $name
     *
     * @return static
     * @throws CouldNotUseKey
     */
    public static function loadFromName(string $name): self
    {
        if (!$name) {
            throw CouldNotUseKey::metadataNotFound($name);
        }

        $self       = new static();
        $self->name = $name;
        $self->loadMatchingObjectEsc();

        if (!$self->_id) {
            throw CouldNotUseKey::metadataNotFound($name);
        }

        return $self;
    }

    public function isAlgSymmetric(): bool
    {
        return $this->_alg->isSymmetric();
    }

    /**
     * @inheritDoc
     */
    public function check(): ?string
    {
        if (
            !$this->name
            || !is_string($this->name)
            || (strpos($this->name, ' ') !== false)
            || !preg_match('/^[a-zA-Z0-9_\-]+$/', $this->name)
        ) {
            return 'CKeyMetadata-error-Invalid name';
        }

        return parent::check();
    }

    /**
     * @inheritDoc
     */
    public function store(): ?string
    {
        if (!CUser::get()->isAdmin()) {
            return 'CKeyMetadata-error-Only admin user can alter this object.';
        }

        return parent::store();
    }

    /**
     * @inheritDoc
     */
    public function delete(): ?string
    {
        if (!CUser::get()->isAdmin()) {
            return 'CKeyMetadata-error-Only admin user can alter this object.';
        }

        return parent::delete();
    }

    /**
     * @inheritDoc
     */
    public function rawStore(): bool
    {
        if (!CUser::get()->isAdmin()) {
            return false;
        }

        return parent::rawStore();
    }

    /**
     * @inheritDoc
     */
    public function checkMerge(array $objects = []): void
    {
        throw CanNotMerge::invalidObject();
    }

    /**
     * @inheritDoc
     */
    public function merge(array $objects, bool $fast, CMergeLog $merge_log): void
    {
        throw CouldNotMerge::mergeImpossible();
    }
}
