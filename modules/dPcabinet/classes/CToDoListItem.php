<?php

/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet;

use Exception;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Mediusers\CMediusers;
use Symfony\Component\Routing\RouterInterface;

class CToDoListItem extends CMbObject
{
    public const RESOURCE_TYPE = 'todoListItem';

    /** @var int */
    public $todo_list_item_id;

    /** @var string */
    public $libelle;
    /** @var int */
    public $user_id;
    /** @var string */
    public $handled_date;

    public ?CMediusers $_ref_user;


    /**
     * @inheritDoc
     */
    public function getSpec()
    {
        $spec = parent::getSpec();

        $spec->table = 'todo_list_item';
        $spec->key   = 'todo_list_item_id';

        return $spec;
    }

    /**
     * @return string|null
     * @throws Exception
     */
    public function store()
    {
        if (!$this->user_id) {
            $this->user_id = CMediusers::get()->_id;
        }

        return parent::store();
    }

    /**
     * @inheritDoc
     */
    public function getProps()
    {
        $props                 = parent::getProps();
        $props["libelle"]      = "str notNull fieldset|default";
        $props["user_id"]      = "ref class|CMediusers notNull cascade back|user_todolistitems";
        $props["handled_date"] = "date fieldset|default";

        return $props;
    }

    /**
     * @inheritDoc
     */
    public function updateFormFields()
    {
        parent::updateFormFields();

        $this->_view = $this->libelle;
    }

    /**
     * @return CStoredObject|null
     * @throws Exception
     */
    public function loadRefUser(): ?CStoredObject
    {
        return $this->_ref_user = $this->loadFwdRef("user_id", true);
    }

    /**
     * Generate and return the self link.
     */
    public function getApiLink(RouterInterface $router): string
    {
        return $router->generate('cabinet_todolistitem_show', ['todo_list_item_id' => $this->_id]);
    }
}
