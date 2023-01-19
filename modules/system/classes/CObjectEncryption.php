<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Mediboard\System\Keys\CKeyMetadata;

class CObjectEncryption extends CMbObject
{
    /** @var int */
    public $object_encryption_id;

    /** @var string */
    public $object_class;

    /** @var int */
    public $object_id;

    /** @var string */
    public $iv;

    /** @var int */
    public $key_id;

    /** @var string */
    public $hash;

    /**
     * @inheritdoc
     */
    public function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = "object_encryption";
        $spec->key   = "object_encryption_id";

        $spec->loggable = CMbObjectSpec::LOGGABLE_NEVER;

        $spec->uniques['object'] = ['object_class', 'object_id'];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps()
    {
        $props = parent::getProps();

        $props['object_class'] = 'str notNull class';
        $props['object_id']    = 'ref class|CMbObject meta|object_class cascade back|encryption';
        $props['iv']           = 'str';
        $props['key_id']       = 'ref class|CKeyMetadata notNull back|encrypted_objects';
        $props['hash']         = 'str';

        return $props;
    }

    /**
     * @inheritDoc
     */
    public function updateFormFields(): void
    {
        parent::updateFormFields();

        $this->_view = $this->getKeyName();
    }

    public function getKeyName(): ?string
    {
        /** @var CKeyMetadata $key */
        $key = $this->loadFwdRef('key_id');

        return ($key && $key->_id) ? $key->name : null;
    }
}
