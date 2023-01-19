<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CRequest;

/**
 * Description
 */
class CConfigurationStdStrategy implements IConfigurationStrategy, IShortNameAutoloadable
{
    /**
     * @inheritDoc
     */
    public function getStoredConfigurations($module, CMbObjectSpec $spec, bool $static = false)
    {
        $length = mb_strlen($module) + 2;

        $request = new CRequest();
        $request->addTable($spec->table);

        $field_static = $spec->ds->hasField($spec->table, 'static');

        $select = [
            "SUBSTR(`feature`, {$length}) AS feature",
            'value',
            'object_class',
            'object_id',
            $field_static ? 'static' : '0 as "static"',
        ];

        $request->addSelect($select);

        $where = [
            'feature' => $spec->ds->prepareLike("{$module} %"),
            'value'   => 'IS NOT NULL',
        ];

        if ($field_static) {
            $where['static'] = ($static) ? "= '1'" : "= '0'";
        }

        $request->addWhere($where);

        return $spec->ds->loadList($request->makeSelect());
    }

    /**
     * @inheritDoc
     */
    public function getNullStoredConfigurations(
        $module,
        CMbObjectSpec $spec,
        $object_class = null,
        $object_id = null,
        $static = false
    ) {
        $request = new CRequest();
        $request->addTable($spec->table);
        $request->addSelect('feature');

        $where = [
            'object_class' => 'IS NULL',
            'object_id'    => 'IS NULL',
            'feature'      => $spec->ds->prepareLike("{$module} %"),
            'value'        => 'IS NULL',
            'static'       => ($static) ? "= '1'" : "= '0'",
        ];

        if ($object_class && $object_id) {
            $where['object_class'] = $spec->ds->prepare('= ?', $object_class);
            $where['object_id']    = $spec->ds->prepare('= ?', $object_id);
        }

        $request->addWhere($where);

        return $spec->ds->loadColumn($request->makeSelect());
    }

    /**
     * @inheritDoc
     */
    public function setConfig($feature, $value, CMbObject $object = null, bool $static = false)
    {
        $where = [
            "feature" => "= '$feature'",
        ];

        if ($object) {
            $where["object_class"] = "= '$object->_class'";
            $where["object_id"]    = "= '$object->_id'";
        } else {
            $where["object_class"] = "IS NULL";
            $where["object_id"]    = "IS NULL";
        }

        $_config = new CConfiguration();
        $_config->loadObject($where);

        $_config->static = ($static) ? '1' : '0';

        $inherit = ($value === $_config::INHERIT);

        if ($_config->_id && $inherit) {
            $_config->value = '';

            return $_config->store();
        }

        if (!$inherit) {
            if ($object) {
                $_config->setObject($object);
            } else {
                $_config->object_id    = null;
                $_config->object_class = null;
            }

            $_config->feature = $feature;
            $_config->value   = $value;

            return $_config->store();
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getAltFeatures(
        $module,
        CMbObjectSpec $spec,
        $object_class = null,
        $object_id = null,
        $static = false
    ) {
        $request = new CRequest();
        $request->addTable($spec->table);
        $request->addSelect('feature');

        $where = [
            'object_class' => 'IS NULL',
            'object_id'    => 'IS NULL',
            'feature'      => $spec->ds->prepareLike("{$module} %"),
            'alt_value'    => 'IS NOT NULL',
            'static'       => ($static) ? "= '1'" : "= '0'",
        ];

        if ($object_class && $object_id) {
            $where['object_class'] = $spec->ds->prepare('= ?', $object_class);
            $where['object_id']    = $spec->ds->prepare('= ?', $object_id);
        }

        $request->addWhere($where);

        return $spec->ds->loadColumn($request->makeSelect());
    }

    /**
     * @inheritDoc
     */
    public function objectValueModified(CConfiguration $configuration, $value = null): bool
    {
        return $configuration->fieldModified('value', $value);
    }
}
