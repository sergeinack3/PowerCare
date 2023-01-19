<?php

/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Forms\Tag;

use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Mediboard\Forms\Traits\StandardPermTrait;
use Ox\Mediboard\System\Forms\FormComponentInterface;

/**
 * Description
 */
class CExClassFieldTagItem extends CMbObject implements FormComponentInterface
{
    use StandardPermTrait;

    /** @var int Primary key */
    public $ex_class_field_tag_item_id;

    /** @var int */
    public $ex_class_field_id;

    /** @var string */
    public $tag;

    /** @var AbstractCExClassFieldTag */
    public $_tag;

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = 'ex_class_field_tag_item';
        $spec->key   = 'ex_class_field_tag_item_id';

        $spec->uniques['field_tag'] = ['ex_class_field_id', 'tag'];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['ex_class_field_id'] = 'ref class|CExClassField notNull back|ex_class_field_tag_items cascade';
        $props['tag']               = 'enum list|' . implode('|', CExClassFieldTagFactory::getTags()) . ' notNull';

        return $props;
    }

    /**
     * @inheritdoc
     */
    public function updateFormFields(): void
    {
        parent::updateFormFields();

        $this->_tag  = CExClassFieldTagFactory::getTag($this->tag);
        $this->_view = $this->_tag->getName();
    }

    public function loadRefParentForPerm(bool $cache = true): ?CMbObject
    {
        return $this->loadFwdRef('ex_class_field_id', $cache);
    }
}
