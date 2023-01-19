<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CStoredObject;

class CObjectUuid extends CStoredObject
{
    /** @var int Primary key */
    public $id;

    /** @var string Universal unique id */
    public $uuid;

    /** @var string Class name */
    public $object_class;

    /** @var int Class id */
    public $object_id;

    /** @var string Creation date */
    public $creation_date;

    /** @var CAppUI Creation user */
    public $creation_user;

    /** @var CStoredObject */
    public $_ref_object;

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec = parent::getSpec();

        $spec->table = 'object_uuid';
        $spec->key   = 'id';

        $spec->uniques['uuid']        = ['uuid'];
        $spec->uniques['object_uuid'] = ['object_class', 'object_id'];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['uuid']          = 'text fieldset|default';
        $props['object_class']  = 'str notNull';
        $props['object_id']     = 'ref notNull cascade class|CStoredObject meta|object_class back|uuids';
        $props['creation_date'] = 'dateTime notNull';
        $props['creation_user'] = 'ref class|CMediusers notNull cascade back|uuids';

        return $props;
    }

    /**
     * @return CStoredObject
     * @throws Exception
     */
    public function loadRefObject(): CStoredObject
    {
        return $this->_ref_object = $this->loadFwdRef("object_id", true);
    }

    /**
     * @return string|null
     * @throws Exception
     */
    public function store(): ?string
    {
        if (!$this->object_id || !$this->object_class) {
            return 'common-error-Missing parameter';
        }

        if (!$this->creation_user) {
            $this->creation_user = CAppUI::$user->_id;
        }

        if (!$this->creation_date) {
            $this->creation_date = "now";
        }

        return parent::store();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->uuid;
    }
}
