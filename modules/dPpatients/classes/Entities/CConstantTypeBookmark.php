<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Entities;

use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Mediboard\Patients\CConstantesMedicales;

/**
 * Representation of a constant type bookmark.
 */
class CConstantTypeBookmark extends CMbObject
{
    // Resource.
    public const RESOURCE_TYPE = 'constant_type_bookmark';

    // Relations.
    public const RELATION_USER    = 'user';
    public const RELATION_PATIENT = 'patient';

    // DB Field.
    /** @var int|null $constant_type_bookmark_id Constant bookmark identifier (PK). */
    public ?int $constant_type_bookmark_id = null;

    /** @var int|null $user_id User identifier. */
    public ?int $user_id = null;

    /** @var int|null $patient_id Patient identifier. */
    public ?int $patient_id = null;

    /** @var string|null $constant_type Constant type name. */
    public ?string $constant_type = null;

    /**
     * @inheritDoc
     *
     * Ensure that the same constant type cannot be bookmarked twice for the same
     * patient and connected user.
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = "constant_type_bookmark";
        $spec->key   = "constant_type_bookmark_id";

        $spec->uniques['user_patient_type'] = ['patient_id', 'user_id', 'constant_type'];

        return $spec;
    }

    /**
     * @inheritDoc
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['user_id']       = 'ref class|CMediusers notNull back|bookmarked_constants';
        $props['patient_id']    = 'ref class|CPatient notNull back|bookmarked_constants_by';
        $props['constant_type'] = 'str notNull fieldset|' . self::FIELDSET_DEFAULT;

        return $props;
    }

    /**
     * @inheritDoc
     *
     * Check if constant type is available or not in mediboard.
     * Return a message if type is not available.
     */
    public function check(): ?string
    {
        $msg = parent::check();

        try {
            $this->checkTypeAvailable();
        } catch (CMbException $e) {
            return $e->getMessage();
        }

        return $msg;
    }

    /**
     * Return the user of the constant type bookmark has an Item.
     *
     * @throws ApiException
     */
    public function getResourceUser(): Item
    {
        return new Item($this->loadFwdRef('user_id', true));
    }

    /**
     * Return the patient of the constant type bookmark has an Item.
     *
     * @throws ApiException
     */
    public function getResourcePatient(): Item
    {
        return new Item($this->loadFwdRef('patient_id', true));
    }

    /**
     * Check if constant type is available or not in mediboard.
     *     - Throw a error if check fail else do nothing.
     *
     * @return void
     * @throws CMbException
     */
    private function checkTypeAvailable(): void
    {
        if (!in_array($this->constant_type, CConstantesMedicales::$list_constantes)) {
            throw new CMbException(
                'CConstantTypeBookmark-Error-This-constant-type-is-not-available: "%s"',
                $this->constant_type
            );
        }
    }
}
