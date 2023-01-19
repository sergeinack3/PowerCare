<?php
/**
 * @package Mediboard\Webservices
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Webservices;

use Ox\Core\CApp;
use ReflectionException;

/**
 * CWebservice
 */
class CWebservice
{
    /**
     * @var array Services
     */
    public $services = [];

    /**
     * Get services classes
     *
     * @param string $class Class name
     *
     * @return array
     */
    public function getServicesClasses($class)
    {
        $this->services = CApp::getChildClasses($class, false, true);

        return $this->services;
    }

    /**
     * Gets the class methods' names
     *
     * @param string $class     The class name or an object instance
     * @param string $top_class Top class name
     *
     * @return array
     * @throws ReflectionException
     */
    public function getClassMethods($class, $top_class = null)
    {
        $methods = [];
        foreach (get_class_methods($class) as $_method) {
            if (!CApp::isMethodOverridden($class, $_method)) {
                continue;
            }

            if ($_method && ($_method != "__construct")) {
                $methods[] = $_method;
            }
        }
        if ($top_class && ($top_class != ($parent_class = get_parent_class($class)))) {
            $methods = array_merge($methods, $this->getClassMethods($parent_class, $top_class));
        }

        return $methods;
    }
}
