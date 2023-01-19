<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Forms\Traits;

use Exception;
use Ox\Core\CClassMap;
use Ox\Core\CRequest;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\Forms\CExClass;

/**
 * Description
 */
trait HermeticModeTrait
{
    /** @var int */
    public $group_id;

    /** @var CGroups[] */
    public $_groups;

    /**
     * @return string|null
     * @throws Exception
     */
    protected function checkGroupStoring(): ?string
    {
        if (CExClass::inHermeticMode(true)) {
            $class = CClassMap::getSN(static::class);

            // Object creation
            if (!$this->_id) {
                if (!$this->group_id && !CMediusers::get()->isAdmin()) {
                    return "{$class}-error-Group property is mandatory";
                } else {
                    $group = CGroups::get($this->group_id);

                    if (!$group->canDo()->read) {
                        return "common-error-No permission on this object";
                    }
                }
            } elseif ($this->fieldModified("group_id")) {
                return "{$class}-error-Group property is readonly";
            }
        }

        return null;
    }

    protected function loadAvailableGroups(): void
    {
        $this->_groups = CGroups::loadGroups(PERM_READ);
    }

    /**
     * @param int $permType
     *
     * @return bool
     */
    public function getPerm($permType)
    {
        if (!CExClass::inHermeticMode(true)) {
            $forms = CModule::getActive('forms');

            return (parent::getPerm($permType) || (($forms instanceof CModule) &&  $forms->canDo()->read));
        }

        if (!$this->_id) {
            // Should not work, but...
            return parent::getPerm($permType);
        }

        if (!$this->group_id) {
            return ($permType === PERM_READ);
        }

        $group = CGroups::get($this->group_id);

        return $group->getPerm(PERM_READ);
    }

    /**
     * Return false if these object_ids are not in the $group_id
     *
     * @param array $ids
     * @param int   $group_id
     *
     * @return bool
     */
    public static function checkByGroupId(array $ids, int $group_id): bool
    {
        $self = new static();

        $table = $self->getSpec()->table;
        $key   = $self->getSpec()->key;
        $ds    = $self->getDS();

        $request = new CRequest();
        $request->addTable($table);
        $request->addWhere(
            [
                'group_id' => $ds->prepare('= ?', $group_id),
                $key       => $ds::prepareIn($ids),
            ]
        );

        $count = (int)$ds->loadResult($request->makeSelectCount());

        return (count($ids) === $count);
    }
}
