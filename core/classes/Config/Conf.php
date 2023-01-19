<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Config;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbObject;

/**
 * Object Facade of CAppUI::conf, enabling us to inject it into other classes.
 */
class Conf
{
    /**
     * @param string $name
     *
     * @return mixed|null
     * @throws Exception
     */
    public function get(string $name)
    {
        return CAppUI::conf($name);
    }

    /**
     * @param string           $name
     * @param CMbObject|string $context
     *
     * @return mixed|null
     * @throws Exception
     */
    public function getContextual(string $name, $context)
    {
        return CAppUI::conf($name, $context);
    }

    /**
     * @param string $name
     * @param int    $group_id
     *
     * @return mixed|null
     * @throws Exception
     */
    public function getForGroupId(string $name, int $group_id)
    {
        return $this->getContextual($name, "CGroups-{$group_id}");
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     * @throws Exception
     */
    public function getStatic(string $name)
    {
        return $this->getContextual($name, 'static');
    }
}
