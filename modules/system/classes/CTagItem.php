<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Exception;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CStoredObject;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * Tag link: links a tag to an object
 */
class CTagItem extends CMbObject
{
    public $tag_item_id;

    public $tag_id;

    public $object_class;
    public $object_id;
    public $_ref_object;

    /** @var CTag */
    public $_ref_tag;

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec                    = parent::getSpec();
        $spec->table             = "tag_item";
        $spec->key               = "tag_item_id";
        $spec->uniques["object"] = ["object_class", "object_id", "tag_id"];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props                 = parent::getProps();
        $props["tag_id"]       = "ref notNull class|CTag back|items";
        $props["object_id"]    = "ref notNull class|CMbObject meta|object_class cascade seekable back|tag_items";
        $props["object_class"] = "str notNull class show|0";

        return $props;
    }

    /**
     * Load tag
     *
     * @param bool $cache Use cache
     *
     * @return CTag
     * @throws Exception
     */
    public function loadRefTag(bool $cache = true): CTag
    {
        /** @var CTag $tag */
        $tag         = $this->loadFwdRef("tag_id", $cache);
        $this->_view = $tag->_view;
        $this->_tree = $tag->_tree;

        return $this->_ref_tag = $tag;
    }

    /**
     * @inheritdoc
     */
    public function check(): ?string
    {
        if ($msg = parent::check()) {
            return $msg;
        }

        $this->completeField("tag_id", "object_class");

        return null;
    }

    /**
     * @param CStoredObject $object
     *
     * @return void
     * @todo redefine meta raf
     * @deprecated
     */
    public function setObject(CStoredObject $object)
    {
        CMbMetaObjectPolyfill::setObject($this, $object);
    }

    /**
     * @param bool $cache
     *
     * @return bool|CStoredObject|CExObject|null
     * @throws Exception
     * @deprecated
     * @todo redefine meta raf
     */
    public function loadTargetObject(bool $cache = true)
    {
        return CMbMetaObjectPolyfill::loadTargetObject($this, $cache);
    }

    /**
     * @inheritDoc
     * @todo remove
     */
    public function loadRefsFwd(): void
    {
        parent::loadRefsFwd();
        $this->loadTargetObject();
    }
}
