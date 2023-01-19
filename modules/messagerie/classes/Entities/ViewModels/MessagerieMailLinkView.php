<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie\Entities\ViewModels;

use Exception;
use Ox\Core\CModelObject;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Medimail\CMedimailMessage;
use Ox\Mediboard\Messagerie\Entities\MessagerieEntity;
use Ox\Mediboard\Messagerie\Exceptions\MessagerieLinkException;
use Ox\Mediboard\Mssante\CMSSanteMail;

/**
 * Show a messaging while waiting for a refactoring to
 * unify the different mail (Mailiz / Medimail) formats available in mediboard.
 */
final class MessagerieMailLinkView extends CModelObject
{
    /** @var string $name Item name */
    public string $name;

    /** @var string $file_extension Item name */
    public string $file_extension = '.txt';

    /** @var string $object_class Object class ref */
    public string $object_class;

    /** @var int $object_id Object id ref */
    public int $object_id;

    /** @var int|null $category_id Category id ref */
    public ?int $category_id = null;

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['name']           = 'str notNull';
        $props['file_extension'] = 'str notNull';
        $props['object_class']   = 'str notNull';
        $props['object_id']      = 'num notNull';
        $props['category_id']    = 'num';

        return $props;
    }

    /**
     * Return a instance of MessagerieMailLink
     *
     * @param string $name         Item name
     * @param int    $object_id    Item object identifier
     * @param string $object_class Item object class
     *
     * @return static
     */
    public static function createFromData(string $name, int $object_id, string $object_class): self
    {
        $view_model                 = new self();
        $view_model->name         = $name;
        $view_model->object_id    = $object_id;
        $view_model->object_class = $object_class;
        $view_model->_view        = "{$view_model->name}{$view_model->file_extension}";

        return $view_model;
    }
}
